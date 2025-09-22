<?php

use App\Jobs\ProcessCheckoutSessionCompleted;
use App\Jobs\ProcessCustomerSubscriptionDeleted;
use App\Jobs\ProcessCustomerSubscriptionUpdated;
use App\Jobs\ProcessInvoicePaid;
use App\Jobs\ProcessInvoicePaymentFailed;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('services.stripe.secret', '');
});

afterEach(function () {
    Carbon::setTestNow(); // reset
});

function makeStripePlan(array $overrides = []): SubscriptionPlan
{
    $defaults = [
        'name' => 'Basic',
        'price' => 1200,
        'lesson_count' => 3,
        'allowed_category_ids' => [],
        'stripe_product_id' => 'prod_test',
        'stripe_price_id' => 'price_test',
        'is_active' => true,
    ];

    return SubscriptionPlan::create(array_intersect_key(
        array_merge($defaults, $overrides),
        $defaults
    ));
}

it('creates or updates user subscription on checkout.session.completed (new subscription)', function (): void {
    Carbon::setTestNow(Carbon::parse('2025-01-01 00:00:00'));

    $user = User::factory()->create();
    $plan = makeStripePlan([
        'name' => 'Basic',
        'price' => 1200,
        'lesson_count' => 3,
        'stripe_product_id' => 'prod_basic',
        'stripe_price_id' => 'price_basic',
    ]);

    $payload = [
        'data' => [
            'object' => [
                'subscription' => 'sub_cs_new',
                'client_reference_id' => (string) $user->id,
                'metadata' => [
                    'app_user_id' => (string) $user->id,
                    'app_plan_id' => (string) $plan->id,
                ],
            ],
        ],
    ];

    (new ProcessCheckoutSessionCompleted($payload))->handle();

    $this->assertDatabaseHas('user_subscriptions', [
        'stripe_subscription_id' => 'sub_cs_new',
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'incomplete',
        'payment_status' => 'paid',
        'current_month_used_count' => 0,
        'remaining_lessons' => 3,
    ]);
});

it('transfers remaining lessons hint on plan switch at checkout.session.completed', function (): void {
    Carbon::setTestNow(Carbon::parse('2025-02-01 00:00:00'));

    $user = User::factory()->create();
    $plan = makeStripePlan([
        'name' => 'Plus',
        'price' => 2200,
        'lesson_count' => 4,
        'stripe_product_id' => 'prod_plus',
        'stripe_price_id' => 'price_plus',
    ]);

    $payload = [
        'data' => [
            'object' => [
                'subscription' => 'sub_cs_switch',
                'client_reference_id' => (string) $user->id,
                'metadata' => [
                    'app_user_id' => (string) $user->id,
                    'app_plan_id' => (string) $plan->id,
                    'switch_from_app_plan_id' => '1',
                    'switch_to_app_plan_id' => (string) $plan->id,
                    'remaining_lessons_hint' => '2',
                ],
            ],
        ],
    ];

    (new ProcessCheckoutSessionCompleted($payload))->handle();

    // remaining_lessons = new_plan.lesson_count + hint = 4 + 2 = 6
    $this->assertDatabaseHas('user_subscriptions', [
        'stripe_subscription_id' => 'sub_cs_switch',
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'remaining_lessons' => 6,
        'current_month_used_count' => 0,
        'status' => 'incomplete',
        'payment_status' => 'paid',
    ]);
});

it('updates status and period on customer.subscription.updated', function (): void {
    $user = User::factory()->create();
    $plan = makeStripePlan([
        'name' => 'Pro',
        'price' => 3500,
        'lesson_count' => 6,
        'stripe_product_id' => 'prod_pro',
        'stripe_price_id' => 'price_pro',
    ]);

    UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_upd',
        'status' => 'active',
        'payment_status' => 'paid',
        'current_month_used_count' => 0,
        'current_period_start' => Carbon::parse('2025-03-01 00:00:00'),
        'current_period_end' => Carbon::parse('2025-03-31 23:59:59'),
    ]);

    $start = Carbon::parse('2025-04-01 00:00:00')->timestamp;
    $end = Carbon::parse('2025-04-30 23:59:59')->timestamp;

    $payload = [
        'data' => [
            'object' => [
                'id' => 'sub_upd',
                'status' => 'past_due',
                'current_period_start' => $start,
                'current_period_end' => $end,
            ],
        ],
    ];

    (new ProcessCustomerSubscriptionUpdated($payload))->handle();

    $this->assertDatabaseHas('user_subscriptions', [
        'stripe_subscription_id' => 'sub_upd',
        'status' => 'past_due',
        'current_period_start' => Carbon::createFromTimestamp($start),
        'current_period_end' => Carbon::createFromTimestamp($end),
    ]);
});

it('sets canceled and failed on customer.subscription.deleted', function (): void {
    $user = User::factory()->create();
    $plan = makeStripePlan([
        'name' => 'Lite',
        'price' => 900,
        'lesson_count' => 2,
        'stripe_product_id' => 'prod_lite',
        'stripe_price_id' => 'price_lite',
    ]);

    UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_del',
        'status' => 'active',
        'payment_status' => 'paid',
        'current_month_used_count' => 0,
        'current_period_start' => Carbon::parse('2025-01-01 00:00:00'),
        'current_period_end' => Carbon::parse('2025-01-31 23:59:59'),
    ]);

    $payload = [
        'data' => [
            'object' => [
                'id' => 'sub_del',
            ],
        ],
    ];

    (new ProcessCustomerSubscriptionDeleted($payload))->handle();

    $this->assertDatabaseHas('user_subscriptions', [
        'stripe_subscription_id' => 'sub_del',
        'status' => 'canceled',
        'payment_status' => 'failed',
    ]);
});

it('resets usage and marks paid on invoice.payment_succeeded', function (): void {
    $user = User::factory()->create();
    $plan = makeStripePlan([
        'name' => 'Gold',
        'price' => 5000,
        'lesson_count' => 8,
        'stripe_product_id' => 'prod_gold',
        'stripe_price_id' => 'price_gold',
    ]);

    UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_paid',
        'status' => 'active',
        'payment_status' => 'failed',
        'current_month_used_count' => 3,
        'current_period_start' => Carbon::parse('2025-03-01 00:00:00'),
        'current_period_end' => Carbon::parse('2025-03-31 23:59:59'),
    ]);

    $start = Carbon::parse('2025-04-01 00:00:00')->timestamp;
    $end = Carbon::parse('2025-04-30 23:59:59')->timestamp;

    $payload = [
        'data' => [
            'object' => [
                'subscription' => 'sub_paid',
                'lines' => [
                    'data' => [
                        [
                            'period' => [
                                'start' => $start,
                                'end' => $end,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    (new ProcessInvoicePaid($payload))->handle();

    $this->assertDatabaseHas('user_subscriptions', [
        'stripe_subscription_id' => 'sub_paid',
        'payment_status' => 'paid',
        'current_month_used_count' => 0,
    ]);
});

it('marks payment failed on invoice.payment_failed', function (): void {
    $user = User::factory()->create();
    $plan = makeStripePlan([
        'name' => 'Silver',
        'price' => 3000,
        'lesson_count' => 5,
        'stripe_product_id' => 'prod_silver',
        'stripe_price_id' => 'price_silver',
    ]);

    UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_fail',
        'status' => 'active',
        'payment_status' => 'paid',
        'current_month_used_count' => 1,
        'current_period_start' => Carbon::parse('2025-05-01 00:00:00'),
        'current_period_end' => Carbon::parse('2025-05-31 23:59:59'),
    ]);

    $payload = [
        'data' => [
            'object' => [
                'subscription' => 'sub_fail',
            ],
        ],
    ];

    (new ProcessInvoicePaymentFailed($payload))->handle();

    $this->assertDatabaseHas('user_subscriptions', [
        'stripe_subscription_id' => 'sub_fail',
        'payment_status' => 'failed',
    ]);
});

it('is idempotent for duplicate checkout.session.completed payloads', function (): void {
    $user = User::factory()->create();
    $plan = makeStripePlan([
        'name' => 'Idem',
        'price' => 1100,
        'lesson_count' => 3,
        'stripe_product_id' => 'prod_idem',
        'stripe_price_id' => 'price_idem',
    ]);

    $payload = [
        'data' => [
            'object' => [
                'subscription' => 'sub_dup',
                'client_reference_id' => (string) $user->id,
                'metadata' => [
                    'app_user_id' => (string) $user->id,
                    'app_plan_id' => (string) $plan->id,
                ],
            ],
        ],
    ];

    (new ProcessCheckoutSessionCompleted($payload))->handle();
    (new ProcessCheckoutSessionCompleted($payload))->handle();

    $this->assertDatabaseHas('user_subscriptions', [
        'stripe_subscription_id' => 'sub_dup',
        'user_id' => $user->id,
    ]);
    $this->assertDatabaseCount('user_subscriptions', 1);
});
