<?php

use App\Mail\FavoriteAdded;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('a bearer token is required to manage favorites', function () {
    $this->getJson(route('api.v1.favorites.index'))->assertUnauthorized();
    $this->postJson(route('api.v1.favorites.store'), ['product_id' => 12])->assertUnauthorized();
    $this->deleteJson(route('api.v1.favorites.destroy', 1))->assertUnauthorized();
});

test('a user can list only their favorites with a bearer token', function () {
    $user = User::factory()->create();
    Favorite::factory()->for($user)->create(['title' => 'My product']);
    Favorite::factory()->create(['title' => 'Other product']);

    $this->withToken(apiTokenFor($user))
        ->getJson(route('api.v1.favorites.index'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'My product');
});

test('a user can add a favorite with a bearer token', function () {
    Http::fake([
        '*/products/12' => Http::response(apiProductData()),
    ]);
    Mail::fake();

    $user = User::factory()->create();

    $response = $this->withToken(apiTokenFor($user))
        ->postJson(route('api.v1.favorites.store'), ['product_id' => 12]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.product_id', 12)
        ->assertJsonPath('data.title', 'Classic Black Baseball Cap');

    $favorite = $user->favorites()->firstOrFail();

    Mail::assertSent(
        FavoriteAdded::class,
        fn (FavoriteAdded $mail): bool => $mail->hasTo($user->email)
            && $mail->favorite->is($favorite),
    );
});

test('product id is required to add a favorite', function () {
    $user = User::factory()->create();

    $this->withToken(apiTokenFor($user))
        ->postJson(route('api.v1.favorites.store'))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('product_id');
});

test('a user can delete only their own favorites', function () {
    $user = User::factory()->create();
    $ownFavorite = Favorite::factory()->for($user)->create();
    $otherFavorite = Favorite::factory()->create();
    $token = apiTokenFor($user);

    $this->withToken($token)
        ->deleteJson(route('api.v1.favorites.destroy', $otherFavorite))
        ->assertNotFound();

    $this->assertModelExists($otherFavorite);

    $this->withToken($token)
        ->deleteJson(route('api.v1.favorites.destroy', $ownFavorite))
        ->assertNoContent();

    $this->assertModelMissing($ownFavorite);
});

function apiTokenFor(User $user): string
{
    return $user->createToken('tests')->plainTextToken;
}

/**
 * @return array<string, mixed>
 */
function apiProductData(): array
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
