<?php

use App\Models\Favorite;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response
        ->assertOk()
        ->assertSee('business-dashboard');
});

test('dashboard shows business favorite indicators', function () {
    $user = User::factory()->create();
    $secondUser = User::factory()->create();
    User::factory()->create();

    Favorite::factory()->for($user)->create([
        'product_id' => 12,
        'title' => 'Popular product',
        'price' => 100,
        'category' => 'Electronics',
    ]);
    Favorite::factory()->for($secondUser)->create([
        'product_id' => 12,
        'title' => 'Popular product',
        'price' => 200,
        'category' => 'Electronics',
    ]);
    Favorite::factory()->for($secondUser)->create([
        'product_id' => 13,
        'title' => 'Other product',
        'price' => 50,
        'category' => 'Clothes',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertViewHas('dashboardStats', function (array $stats): bool {
            return $stats['topCategory'] === [
                'name' => 'Electronics',
                'favorites' => 2,
            ]
                && $stats['topProduct'] === [
                    'name' => 'Popular product',
                    'favorites' => 2,
                ]
                && $stats['averagePrice'] === 116.67
                && $stats['activeUsers'] === 2;
        });
});
