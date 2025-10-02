<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use App\Models\UserFavorite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MypageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_is_redirected_to_login(): void
    {
        $this->get('/mypage')->assertRedirect('/login');
    }

    /** @test */
    public function it_shows_empty_states_when_no_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/mypage')
            ->assertOk()
            ->assertSee('契約中のプランはありません。', false)
            ->assertSee('予約履歴はありません。', false)
            ->assertSee('お気に入りの店舗はありません。', false)
            ->assertSee('お気に入りのインストラクターはいません。', false);
    }

    /** @test */
    public function it_lists_favorite_stores_and_instructors(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->create(['name' => 'テスト店舗']);

        // お気に入り: 店舗
        UserFavorite::create([
            'user_id' => $user->id,
            'favoritable_type' => Store::class,
            'favoritable_id' => $store->id,
        ]);

        // お気に入り: インストラクター（Userモデルの講師）
        $instructor = User::factory()->create(['role' => User::ROLE_INSTRUCTOR, 'name' => '佐藤']);
        UserFavorite::create([
            'user_id' => $user->id,
            'favoritable_type' => User::class,
            'favoritable_id' => $instructor->id,
        ]);

        $this->actingAs($user)
            ->get('/mypage')
            ->assertOk()
            ->assertSee('テスト店舗', false)
            ->assertSee('佐藤', false)
            ->assertSee('お気に入り一覧', false)
            ->assertSee('店舗', false)
            ->assertSee('インストラクター', false);
    }
}
