<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFooterTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::factory()->create();
    }

    /** @test */
    public function home_link_is_active_only_on_home_route(): void
    {
        $user = $this->createUser();

        // Visit home
        $this->actingAs($user)
            ->get('/')
            ->assertSee('bg-blue-50', false)
            ->assertSee('ホーム', false);

        // Visit mypage where home must not be active
        $this->actingAs($user)
            ->get('/mypage')
            ->assertDontSee('bg-blue-50', false);
    }

    /** @test */
    public function mypage_link_is_active_on_mypage(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get('/mypage')
            ->assertSee('マイページ', false)
            ->assertSee('bg-blue-50', false);
    }

    /** @test */
    public function stores_link_is_active_on_stores_index(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get('/stores')
            ->assertSee('店舗一覧', false)
            ->assertSee('bg-blue-50', false);
    }

    /** @test */
    public function instructors_link_is_active_on_instructors_index(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get('/instructors')
            ->assertSee('インストラクター', false)
            ->assertSee('bg-blue-50', false);
    }
}
