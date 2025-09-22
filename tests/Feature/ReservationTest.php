<?php

use App\Models\Lesson;
use App\Models\LessonCategory;
use App\Models\LessonSchedule;
use App\Models\Reservation;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
// use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow(Carbon::parse('2025-06-01 10:00:00'));
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function createLessonGraph(array $overrides = []): array
{
    $category = LessonCategory::factory()->create([
        'name' => 'Yoga',
    ]);

    $planDefaults = [
        'name' => 'Basic',
        'price' => 1200,
        'lesson_count' => 3,
        'allowed_category_ids' => [$category->id],
        'stripe_product_id' => 'prod_basic',
        'stripe_price_id' => 'price_basic',
        'is_active' => true,
    ];
    $plan = SubscriptionPlan::create(array_intersect_key(
        array_merge($planDefaults, $overrides['plan'] ?? []),
        $planDefaults
    ));

    $lesson = Lesson::factory()->create(array_merge([
        'category_id' => $category->id,
        'capacity' => 2,
        'booking_deadline_hours' => 1,
        'cancel_deadline_hours' => 1,
    ], $overrides['lesson'] ?? []));

    $schedule = LessonSchedule::factory()->create(array_merge([
        'lesson_id' => $lesson->id,
        'start_datetime' => Carbon::now()->addHours(2),
        'end_datetime' => Carbon::now()->addHours(3),
        'current_bookings' => 0,
        'is_active' => true,
    ], $overrides['schedule'] ?? []));

    return compact('category', 'plan', 'lesson', 'schedule');
}

it('creates a reservation and updates counters atomically', function (): void {
    $user = User::factory()->create();
    ['plan' => $plan, 'lesson' => $lesson, 'schedule' => $schedule] = createLessonGraph();

    $subscription = UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_reservation_1',
        'status' => 'active',
        'payment_status' => 'paid',
        // remaining_lessons を真実源として利用（作成時にデクリメントされる想定）
        'remaining_lessons' => 3,
        'current_month_used_count' => 0,
        'current_period_start' => Carbon::now()->startOfMonth(),
        'current_period_end' => Carbon::now()->endOfMonth(),
    ]);

    $result = Reservation::createWithValidation([
        'user_id' => $user->id,
        'lesson_schedule_id' => $schedule->id,
        'user_subscription_id' => $subscription->id,
    ]);

    expect($result['success'])->toBeTrue();
    /** @var Reservation $reservation */
    $reservation = $result['reservation'];

    // 予約が作成され、スケジュールとサブスクのカウンタが更新される
    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'user_id' => $user->id,
        'lesson_schedule_id' => $schedule->id,
        'status' => Reservation::STATUS_CONFIRMED,
    ]);

    $this->assertDatabaseHas('lesson_schedules', [
        'id' => $schedule->id,
        'current_bookings' => 1,
    ]);

    $this->assertDatabaseHas('user_subscriptions', [
        'id' => $subscription->id,
        'remaining_lessons' => 2,
        'current_month_used_count' => 0,
    ]);
});

it('prevents booking when booking deadline has passed', function (): void {
    $user = User::factory()->create();
    ['plan' => $plan, 'lesson' => $lesson, 'schedule' => $schedule] = createLessonGraph([
        'lesson' => ['booking_deadline_hours' => 3],
        // 開始2h前、締切3h前 → すでに締切超過
        'schedule' => [
            'start_datetime' => Carbon::now()->addHours(2),
        ],
    ]);

    $subscription = UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_reservation_deadline',
        'status' => 'active',
        'payment_status' => 'paid',
        'remaining_lessons' => 3,
        'current_month_used_count' => 0,
        'current_period_start' => Carbon::now()->startOfMonth(),
        'current_period_end' => Carbon::now()->endOfMonth(),
    ]);

    $result = Reservation::createWithValidation([
        'user_id' => $user->id,
        'lesson_schedule_id' => $schedule->id,
        'user_subscription_id' => $subscription->id,
    ]);

    expect($result['success'])->toBeFalse();
    expect($result['errors'])->toContain('予約受付期限を過ぎています。');
});

it('prevents overbooking when capacity is full', function (): void {
    $user = User::factory()->create();
    ['plan' => $plan, 'lesson' => $lesson, 'schedule' => $schedule] = createLessonGraph([
        'lesson' => ['capacity' => 1],
        'schedule' => ['current_bookings' => 1],
    ]);

    $subscription = UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_reservation_full',
        'status' => 'active',
        'payment_status' => 'paid',
        'remaining_lessons' => 3,
        'current_month_used_count' => 0,
        'current_period_start' => Carbon::now()->startOfMonth(),
        'current_period_end' => Carbon::now()->endOfMonth(),
    ]);

    $result = Reservation::createWithValidation([
        'user_id' => $user->id,
        'lesson_schedule_id' => $schedule->id,
        'user_subscription_id' => $subscription->id,
    ]);

    expect($result['success'])->toBeFalse();
    expect($result['errors'])->toContain('このレッスンは満員です。');
});

it('prevents duplicate active reservation for same schedule', function (): void {
    $user = User::factory()->create();
    ['plan' => $plan, 'lesson' => $lesson, 'schedule' => $schedule] = createLessonGraph();

    $subscription = UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_reservation_dup',
        'status' => 'active',
        'payment_status' => 'paid',
        'remaining_lessons' => 3,
        'current_month_used_count' => 0,
        'current_period_start' => Carbon::now()->startOfMonth(),
        'current_period_end' => Carbon::now()->endOfMonth(),
    ]);

    $first = Reservation::createWithValidation([
        'user_id' => $user->id,
        'lesson_schedule_id' => $schedule->id,
        'user_subscription_id' => $subscription->id,
    ]);
    expect($first['success'])->toBeTrue();

    $second = Reservation::createWithValidation([
        'user_id' => $user->id,
        'lesson_schedule_id' => $schedule->id,
        'user_subscription_id' => $subscription->id,
    ]);

    expect($second['success'])->toBeFalse();
    expect($second['errors'])->toContain('同じレッスン枠に既に予約があります。');
});

it('cancels a reservation and returns counters safely', function (): void {
    $user = User::factory()->create();
    ['plan' => $plan, 'lesson' => $lesson, 'schedule' => $schedule] = createLessonGraph();

    $subscription = UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_reservation_cancel',
        'status' => 'active',
        'payment_status' => 'paid',
        'remaining_lessons' => 3,
        'current_month_used_count' => 0,
        'current_period_start' => Carbon::now()->startOfMonth(),
        'current_period_end' => Carbon::now()->endOfMonth(),
    ]);

    $result = Reservation::createWithValidation([
        'user_id' => $user->id,
        'lesson_schedule_id' => $schedule->id,
        'user_subscription_id' => $subscription->id,
    ]);

    /** @var Reservation $reservation */
    $reservation = $result['reservation'];
    $cancel = $reservation->cancelWithValidation();

    expect($cancel['success'])->toBeTrue();

    // 予約レコードの状態
    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'status' => Reservation::STATUS_CANCELED,
    ]);
    $this->assertDatabaseMissing('reservations', [
        'id' => $reservation->id,
        'status' => Reservation::STATUS_CONFIRMED,
    ]);

    $this->assertDatabaseHas('lesson_schedules', [
        'id' => $schedule->id,
        'current_bookings' => 0,
    ]);

    $this->assertDatabaseHas('user_subscriptions', [
        'id' => $subscription->id,
        'remaining_lessons' => 3,
    ]);
});

function makePlanForCategory(LessonCategory $category, array $overrides = []): SubscriptionPlan
{
    $defaults = [
        'name' => 'Test Plan',
        'price' => 1000,
        'lesson_count' => 2,
        'allowed_category_ids' => [$category->id],
        'stripe_product_id' => 'prod_test',
        'stripe_price_id' => 'price_test',
        'is_active' => true,
    ];

    return SubscriptionPlan::create(array_intersect_key(
        array_merge($defaults, $overrides),
        $defaults
    ));
}

it('creates reservation with descendant category and updates counters atomically', function (): void {
    Carbon::setTestNow(Carbon::parse('2025-06-01 10:00:00'));

    $user = User::factory()->create();
    $root = LessonCategory::factory()->create(['parent_id' => null]);
    $category = LessonCategory::factory()->create(['parent_id' => $root->id]);
    $plan = makePlanForCategory($category, ['lesson_count' => 3]);

    $lesson = Lesson::factory()
        ->forCategory($category)
        ->state(['capacity' => 5, 'booking_deadline_hours' => 24, 'cancel_deadline_hours' => 24])
        ->create();

    $schedule = LessonSchedule::factory()
        ->state([
            'lesson_id' => $lesson->id,
            'start_datetime' => Carbon::now()->addDays(2),
            'end_datetime' => Carbon::now()->addDays(2)->addHour(),
            'current_bookings' => 0,
        ])->create();

    $subscription = UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_test_create',
        'status' => 'active',
        'payment_status' => 'paid',
        'current_month_used_count' => 0,
        'remaining_lessons' => 3,
        'current_period_start' => Carbon::now()->startOfMonth(),
        'current_period_end' => Carbon::now()->endOfMonth(),
    ]);

    $result = Reservation::createWithValidation([
        'user_id' => $user->id,
        'lesson_schedule_id' => $schedule->id,
        'user_subscription_id' => $subscription->id,
    ]);

    expect($result['success'])->toBeTrue();
    /** @var Reservation $res */
    $res = $result['reservation'];
    expect($res->status)->toBe(Reservation::STATUS_CONFIRMED);

    $schedule->refresh();
    expect($schedule->current_bookings)->toBe(1);

    $subscription->refresh();
    expect($subscription->remaining_lessons)->toBe(2);
});

it('prevents double booking while confirmed and allows rebook after cancel', function (): void {
    Carbon::setTestNow(Carbon::parse('2025-06-02 10:00:00'));

    $user = User::factory()->create();
    $root = LessonCategory::factory()->create(['parent_id' => null]);
    $category = LessonCategory::factory()->create(['parent_id' => $root->id]);
    $plan = makePlanForCategory($category, ['lesson_count' => 2, 'allowed_category_ids' => [$category->id]]);
    $lesson = Lesson::factory()->forCategory($category)->state([
        'capacity' => 2,
        'booking_deadline_hours' => 2,
        'cancel_deadline_hours' => 2,
    ])->create();
    $schedule = LessonSchedule::factory()->state([
        'lesson_id' => $lesson->id,
        'start_datetime' => Carbon::now()->addDays(1),
        'end_datetime' => Carbon::now()->addDays(1)->addHour(),
        'current_bookings' => 0,
    ])->create();
    $subscription = UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_test_rebook',
        'status' => 'active',
        'payment_status' => 'paid',
        'current_month_used_count' => 0,
        'remaining_lessons' => 2,
        'current_period_start' => Carbon::now()->startOfMonth(),
        'current_period_end' => Carbon::now()->endOfMonth(),
    ]);

    $ok = Reservation::createWithValidation([
        'user_id' => $user->id,
        'lesson_schedule_id' => $schedule->id,
        'user_subscription_id' => $subscription->id,
    ]);
    expect($ok['success'])->toBeTrue();

    $dup = Reservation::createWithValidation([
        'user_id' => $user->id,
        'lesson_schedule_id' => $schedule->id,
        'user_subscription_id' => $subscription->id,
    ]);
    expect($dup['success'])->toBeFalse();
    expect($dup['errors'])->toContain('同じレッスン枠に既に予約があります。');

    /** @var Reservation $res */
    $res = $ok['reservation'];
    $cancel = $res->cancelWithValidation();
    expect($cancel['success'])->toBeTrue();

    // 時刻進行（境界条件の影響排除）
    Carbon::setTestNow(Carbon::now()->addMinute());

    // cancel 後の状態を確認
    $this->assertDatabaseHas('reservations', [
        'id' => $res->id,
        'status' => Reservation::STATUS_CANCELED,
    ]);
    $schedule->refresh();
    expect($schedule->current_bookings)->toBe(0);
    $subscription->refresh();
    expect($subscription->remaining_lessons)->toBe(2);

    $again = Reservation::createWithValidation([
        'user_id' => $user->id,
        'lesson_schedule_id' => $schedule->id,
        'user_subscription_id' => $subscription->id,
    ]);
    expect($again['success'])->toBeTrue();

    // 再予約後のカウンタ
    $schedule->refresh();
    expect($schedule->current_bookings)->toBe(1);
    $subscription->refresh();
    expect($subscription->remaining_lessons)->toBe(1);
});

it('enforces booking deadline', function (): void {
    Carbon::setTestNow(Carbon::parse('2025-06-03 10:00:00'));

    $user = User::factory()->create();
    $root = LessonCategory::factory()->create(['parent_id' => null]);
    $category = LessonCategory::factory()->create(['parent_id' => $root->id]);
    $plan = makePlanForCategory($category, ['lesson_count' => 2, 'allowed_category_ids' => [$category->id]]);
    $lesson = Lesson::factory()->forCategory($category)->state([
        'capacity' => 2,
        'booking_deadline_hours' => 2,
    ])->create();
    $schedule = LessonSchedule::factory()->state([
        'lesson_id' => $lesson->id,
        'start_datetime' => Carbon::now()->addHour(), // 締切は1時間前（超過）
        'end_datetime' => Carbon::now()->addHours(2),
        'current_bookings' => 0,
    ])->create();
    $subscription = UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_test_deadline',
        'status' => 'active',
        'payment_status' => 'paid',
        'current_month_used_count' => 0,
        'remaining_lessons' => 2,
        'current_period_start' => Carbon::now()->startOfMonth(),
        'current_period_end' => Carbon::now()->endOfMonth(),
    ]);

    $res = Reservation::createWithValidation([
        'user_id' => $user->id,
        'lesson_schedule_id' => $schedule->id,
        'user_subscription_id' => $subscription->id,
    ]);

    expect($res['success'])->toBeFalse();
    expect($res['errors'])->toContain('予約受付期限を過ぎています。');
});

it('denies owner mismatch of user subscription', function (): void {
    Carbon::setTestNow(Carbon::parse('2025-06-04 10:00:00'));

    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $root = LessonCategory::factory()->create(['parent_id' => null]);
    $category = LessonCategory::factory()->create(['parent_id' => $root->id]);
    $plan = makePlanForCategory($category, ['lesson_count' => 2, 'allowed_category_ids' => [$category->id]]);
    $lesson = Lesson::factory()->forCategory($category)->create();
    $schedule = LessonSchedule::factory()->state([
        'lesson_id' => $lesson->id,
        'start_datetime' => Carbon::now()->addDays(1),
        'end_datetime' => Carbon::now()->addDays(1)->addHour(),
        'current_bookings' => 0,
    ])->create();
    $subscriptionB = UserSubscription::create([
        'user_id' => $userB->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_test_owner',
        'status' => 'active',
        'payment_status' => 'paid',
        'current_month_used_count' => 0,
        'remaining_lessons' => 2,
        'current_period_start' => Carbon::now()->startOfMonth(),
        'current_period_end' => Carbon::now()->endOfMonth(),
    ]);

    $res = Reservation::createWithValidation([
        'user_id' => $userA->id,
        'lesson_schedule_id' => $schedule->id,
        'user_subscription_id' => $subscriptionB->id,
    ]);

    expect($res['success'])->toBeFalse();
    expect($res['errors'])->toContain('サブスクリプションの所有者が一致しません。');
});

it('denies when schedule becomes full after another booking (capacity guard)', function (): void {
    Carbon::setTestNow(Carbon::parse('2025-06-05 10:00:00'));

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $root = LessonCategory::factory()->create(['parent_id' => null]);
    $category = LessonCategory::factory()->create(['parent_id' => $root->id]);
    $plan = makePlanForCategory($category, ['lesson_count' => 2, 'allowed_category_ids' => [$category->id]]);
    $lesson = Lesson::factory()->forCategory($category)->state(['capacity' => 1])->create();
    $schedule = LessonSchedule::factory()->state([
        'lesson_id' => $lesson->id,
        'start_datetime' => Carbon::now()->addDays(1),
        'end_datetime' => Carbon::now()->addDays(1)->addHour(),
        'current_bookings' => 0,
    ])->create();
    $sub1 = UserSubscription::create([
        'user_id' => $user1->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_full_1',
        'status' => 'active',
        'payment_status' => 'paid',
        'current_month_used_count' => 0,
        'remaining_lessons' => 2,
        'current_period_start' => Carbon::now()->startOfMonth(),
        'current_period_end' => Carbon::now()->endOfMonth(),
    ]);
    $sub2 = UserSubscription::create([
        'user_id' => $user2->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_full_2',
        'status' => 'active',
        'payment_status' => 'paid',
        'current_month_used_count' => 0,
        'remaining_lessons' => 2,
        'current_period_start' => Carbon::now()->startOfMonth(),
        'current_period_end' => Carbon::now()->endOfMonth(),
    ]);

    // 先に user1 が予約して満席にする
    $first = Reservation::createWithValidation([
        'user_id' => $user1->id,
        'lesson_schedule_id' => $schedule->id,
        'user_subscription_id' => $sub1->id,
    ]);
    expect($first['success'])->toBeTrue();

    // user2 は満席で予約不可
    $second = Reservation::createWithValidation([
        'user_id' => $user2->id,
        'lesson_schedule_id' => $schedule->id,
        'user_subscription_id' => $sub2->id,
    ]);
    expect($second['success'])->toBeFalse();
    expect($second['errors'])->toContain('このレッスンは満員です。');
});

it('allows booking exactly at the booking deadline boundary (inclusive spec)', function (): void {
    Carbon::setTestNow(Carbon::parse('2025-06-03 10:00:00'));

    $user = User::factory()->create();
    $root = LessonCategory::factory()->create(['parent_id' => null]);
    $category = LessonCategory::factory()->create(['parent_id' => $root->id]);
    $plan = makePlanForCategory($category, ['lesson_count' => 2, 'allowed_category_ids' => [$category->id]]);

    $lesson = Lesson::factory()->forCategory($category)->state(['booking_deadline_hours' => 2])->create();
    // start=12:00, now=10:00 → deadline=10:00 ちょうど
    $schedule = LessonSchedule::factory()->state([
        'lesson_id' => $lesson->id,
        'start_datetime' => Carbon::now()->addHours(2),
        'end_datetime' => Carbon::now()->addHours(3),
    ])->create();
    $subscription = UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_deadline_boundary',
        'status' => 'active',
        'payment_status' => 'paid',
        'remaining_lessons' => 2,
        'current_month_used_count' => 0,
        'current_period_start' => Carbon::now()->startOfMonth(),
        'current_period_end' => Carbon::now()->endOfMonth(),
    ]);

    $res = Reservation::createWithValidation([
        'user_id' => $user->id,
        'lesson_schedule_id' => $schedule->id,
        'user_subscription_id' => $subscription->id,
    ]);

    // 受付締切は「時間ちょうどまで許可」の仕様（<=）に従い成功
    expect($res['success'])->toBeTrue();
});
