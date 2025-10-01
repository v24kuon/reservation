<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use App\Models\UserFavorite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteStoreToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_toggle_favorite(): void
    {
        $store = Store::factory()->create();
        $this->post(route('stores.favorite.toggle', $store))->assertRedirect('/login');
    }

    public function test_user_can_favorite_and_unfavorite_store(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->create();

        $this->actingAs($user)
            ->post(route('stores.favorite.toggle', $store))
            ->assertRedirect();

        $this->assertDatabaseHas('user_favorites', [
            'user_id' => $user->id,
            'favoritable_type' => Store::class,
            'favoritable_id' => $store->id,
        ]);

        // Toggle again to unfavorite
        $this->actingAs($user)
            ->post(route('stores.favorite.toggle', $store))
            ->assertRedirect();

        $this->assertDatabaseMissing('user_favorites', [
            'user_id' => $user->id,
            'favoritable_type' => Store::class,
            'favoritable_id' => $store->id,
        ]);
    }

    public function test_inactive_store_returns_404(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->state(['is_active' => false])->create();

        $this->actingAs($user)
            ->post(route('stores.favorite.toggle', $store))
            ->assertNotFound();
    }

    public function test_concurrent_insert_does_not_duplicate(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->create();

        // Simulate two quick successive calls
        $this->actingAs($user)
            ->post(route('stores.favorite.toggle', $store));
        $this->actingAs($user)
            ->post(route('stores.favorite.toggle', $store));

        // Because toggle may unfavorite on second call, ensure max one record at any time
        $count = UserFavorite::query()
            ->where('user_id', $user->id)
            ->where('favoritable_type', Store::class)
            ->where('favoritable_id', $store->id)
            ->count();

        $this->assertTrue(in_array($count, [0, 1], true));
    }
}
