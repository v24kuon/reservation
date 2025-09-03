<?php

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('admin can view stores index', function () {
    $admin = adminUser();
    $this->actingAs($admin)
        ->get(route('admin.stores.index'))
        ->assertOk();
});

it('admin can create a store', function () {
    $admin = adminUser();
    $payload = [
        'name' => 'Test Store',
        'address' => 'Tokyo',
        'phone' => '03-1234-5678',
        'is_active' => true,
    ];
    $this->actingAs($admin)
        ->post(route('admin.stores.store'), $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('stores', ['name' => 'Test Store']);
});

it('validation fails without required fields', function () {
    $admin = adminUser();
    $this->actingAs($admin)
        ->post(route('admin.stores.store'), [])
        ->assertSessionHasErrors(['name','address','phone']);
});

it('admin can update a store', function () {
    $admin = adminUser();
    $store = Store::factory()->create();
    $this->actingAs($admin)
        ->patch(route('admin.stores.update', $store), ['name' => 'Renamed', 'address' => $store->address, 'phone' => $store->phone, 'is_active' => true])
        ->assertRedirect();
    $this->assertDatabaseHas('stores', ['id' => $store->id, 'name' => 'Renamed']);
});

it('admin can delete a store', function () {
    $admin = adminUser();
    $store = Store::factory()->create();
    $this->actingAs($admin)
        ->delete(route('admin.stores.destroy', $store))
        ->assertRedirect();
    $this->assertDatabaseMissing('stores', ['id' => $store->id]);
});

it('admin sees edit link on stores index', function () {
    $admin = adminUser();
    $store = Store::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.stores.index'))
        ->assertOk()
        ->assertSee('編集');
});
