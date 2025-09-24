<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupReservationIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_group_reservations_index(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        $this->get(route('admin.group-reservations.index'))
            ->assertOk();
    }

    public function test_non_admin_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $this->actingAs($user);

        $this->get(route('admin.group-reservations.index'))
            ->assertForbidden();
    }
}
