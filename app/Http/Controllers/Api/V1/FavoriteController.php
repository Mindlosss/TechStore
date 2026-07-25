<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreFavoriteRequest;
use App\Http\Resources\Api\V1\FavoriteResource;
use App\Mail\FavoriteAdded;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Throwable;

class FavoriteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $this->authenticatedUser($request);

        return FavoriteResource::collection(
            $user->favorites()->latest()->get(),
        );
    }

    public function store(StoreFavoriteRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $productId = $request->integer('product_id');
        $favorite = $user->favorites()->where('product_id', $productId)->first();

        if ($favorite !== null) {
            return (new FavoriteResource($favorite))->response();
        }

        try {
            $product = Http::baseUrl((string) config('services.platzi_store.url'))
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(10)
                ->retry([100, 300])
                ->get("products/{$productId}")
                ->throw()
                ->json();
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No fue posible obtener el producto.',
            ], 502);
        }

        $favorite = $user->favorites()->create([
            'product_id' => $product['id'],
            'title' => $product['title'],
            'price' => $product['price'],
            'image' => $product['images'][0] ?? null,
            'category' => $product['category']['name'],
        ]);

        Mail::to($user)->send(new FavoriteAdded($favorite));

        return (new FavoriteResource($favorite))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Request $request, int $favorite): Response
    {
        $user = $this->authenticatedUser($request);
        $user->favorites()->findOrFail($favorite)->delete();

        return response()->noContent();
    }

    private function authenticatedUser(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        return $user;
    }
}
