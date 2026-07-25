<?php

use App\Livewire\Favorites\Index;
use App\Livewire\Products\Catalog;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('guests cannot view favorites', function () {
    $this->get(route('favorites.index'))
        ->assertRedirect(route('login'));
});

test('a user can add a product to favorites', function () {
    fakeProductRequests();

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Catalog::class)
        ->call('toggleFavorite', 12)
        ->assertHasNoErrors();

    $favorite = $user->favorites()->firstOrFail();

    expect($favorite)
        ->product_id->toBe(12)
        ->title->toBe('Classic Black Baseball Cap')
        ->category->toBe('Clothes');
});

test('a user can remove a product from favorites', function () {
    fakeProductRequests();

    $user = User::factory()->create();
    $favorite = Favorite::factory()->for($user)->create(['product_id' => 12]);

    Livewire::actingAs($user)
        ->test(Catalog::class)
        ->call('toggleFavorite', 12);

    $this->assertModelMissing($favorite);
});

test('a user only sees and removes their own favorites', function () {
    $user = User::factory()->create();
    $ownFavorite = Favorite::factory()->for($user)->create(['title' => 'My product']);
    $otherFavorite = Favorite::factory()->create(['title' => 'Other product']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertSee('My product')
        ->assertDontSee('Other product')
        ->call('remove', $otherFavorite->id)
        ->call('remove', $ownFavorite->id);

    $this->assertModelExists($otherFavorite);
    $this->assertModelMissing($ownFavorite);
});

function fakeProductRequests(): void
{
    Http::fake([
        '*/products/12' => Http::response(favoriteProductData()),
        '*/products' => Http::response([favoriteProductData()]),
        '*/categories' => Http::response([
            ['id' => 1, 'name' => 'Clothes'],
        ]),
    ]);
}

/**
 * @return array<string, mixed>
 */
function favoriteProductData(): array
{
    return [
        'id' => 12,
        'title' => 'Classic Black Baseball Cap',
        'price' => 58,
        'description' => 'A classic cap.',
        'images' => ['https://i.imgur.com/product.jpeg'],
        'category' => ['id' => 1, 'name' => 'Clothes'],
    ];
}
