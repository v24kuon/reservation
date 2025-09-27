<?php

namespace Tests\Feature\Admin;

use App\Models\Lesson;
use App\Models\LessonSchedule;
use App\Models\Reservation;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PersonalReservationIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_personal_reservations_index(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);
        $this->actingAs($admin);

        $this->get(route('admin.personal-reservations.index'))
            ->assertOk();
    }

    public function test_non_admin_is_forbidden(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $this->get(route('admin.personal-reservations.index'))
            ->assertForbidden();
    }

    public function test_filters_behave_correctly(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);
        $this->actingAs($admin);

        Carbon::setTestNow(Carbon::create(2025, 9, 24, 9, 0, 0));

        $instructorA = User::factory()->create(['role' => User::ROLE_INSTRUCTOR, 'name' => 'Instructor A', 'email_verified_at' => now()]);
        $instructorB = User::factory()->create(['role' => User::ROLE_INSTRUCTOR, 'name' => 'Instructor B', 'email_verified_at' => now()]);

        $lessonA = Lesson::factory()->forInstructor($instructorA)->create(['name' => 'Lesson A', 'capacity' => 1]);
        $lessonB = Lesson::factory()->forInstructor($instructorB)->create(['name' => 'Lesson B', 'capacity' => 1]);

        $d1 = Carbon::now()->addDays(1)->setTime(10, 0);
        $d2 = Carbon::now()->addDays(3)->setTime(11, 0);

        $scheduleA = LessonSchedule::factory()->for($lessonA)->create([
            'start_datetime' => $d1->copy(),
            'end_datetime' => $d1->copy()->addHour(),
        ]);
        $scheduleB = LessonSchedule::factory()->for($lessonB)->create([
            'start_datetime' => $d2->copy(),
            'end_datetime' => $d2->copy()->addHour(),
        ]);

        $user1 = User::factory()->create(['role' => User::ROLE_USER, 'name' => 'Taro User', 'email' => 'taro@example.com', 'email_verified_at' => now()]);
        $user2 = User::factory()->create(['role' => User::ROLE_USER, 'name' => 'Jiro User', 'email' => 'jiro@example.com', 'email_verified_at' => now()]);

        $sub1 = UserSubscription::factory()->for($user1)->active()->withRemainingLessons(5)->create();
        $sub2 = UserSubscription::factory()->for($user2)->active()->withRemainingLessons(5)->create();

        Reservation::factory()->create([
            'user_id' => $user1->id,
            'user_subscription_id' => $sub1->id,
            'lesson_schedule_id' => $scheduleA->id,
            'status' => 'pending',
        ]);
        Reservation::factory()->create([
            'user_id' => $user2->id,
            'user_subscription_id' => $sub2->id,
            'lesson_schedule_id' => $scheduleB->id,
            'status' => 'confirmed',
        ]);

        // No filter
        $this->get(route('admin.personal-reservations.index'))
            ->assertOk()
            ->assertSee('Lesson A')
            ->assertSee('Lesson B');

        // instructor filter
        $resp = $this->get(route('admin.personal-reservations.index', ['instructor_id' => $instructorB->id]))
            ->assertOk()
            ->assertSee('Lesson B');
        $resp->assertViewHas('reservations', function ($reservations) use ($lessonB) {
            $items = collect($reservations->items());
            return $items->count() === 1
                && optional(optional($items->first()->lessonSchedule)->lesson)->id === $lessonB->id;
        });

        // lesson filter
        $resp = $this->get(route('admin.personal-reservations.index', ['lesson_id' => $lessonA->id]))
            ->assertOk()
            ->assertSee('Lesson A');
        $resp->assertViewHas('reservations', function ($reservations) use ($lessonA) {
            $items = collect($reservations->items());
            return $items->count() === 1
                && optional(optional($items->first()->lessonSchedule)->lesson)->id === $lessonA->id;
        });

        // status filter
        $resp = $this->get(route('admin.personal-reservations.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('pending');

        // date_from filter (include only scheduleB)
        $resp = $this->get(route('admin.personal-reservations.index', ['date_from' => $d2->toDateString()]))
            ->assertOk()
            ->assertSee('Lesson B');

        // date_to filter (include only scheduleA)
        $resp = $this->get(route('admin.personal-reservations.index', ['date_to' => $d1->toDateString()]))
            ->assertOk()
            ->assertSee('Lesson A');

        // user term filter (by name/email)
        $resp = $this->get(route('admin.personal-reservations.index', ['user' => 'taro']))
            ->assertOk()
            ->assertSee('Taro User');
    }
}


