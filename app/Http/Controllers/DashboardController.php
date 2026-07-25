<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $topCategory = Favorite::query()
            ->select('category')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->orderBy('category')
            ->first();

        $topProduct = Favorite::query()
            ->select(['product_id', 'title'])
            ->selectRaw('COUNT(*) as total')
            ->groupBy('product_id', 'title')
            ->orderByDesc('total')
            ->orderBy('title')
            ->first();

        return view('dashboard', [
            'dashboardStats' => [
                'topCategory' => [
                    'name' => $topCategory?->category,
                    'favorites' => (int) ($topCategory?->getAttribute('total') ?? 0),
                ],
                'topProduct' => [
                    'name' => $topProduct?->title,
                    'favorites' => (int) ($topProduct?->getAttribute('total') ?? 0),
                ],
                'averagePrice' => round((float) (Favorite::query()->avg('price') ?? 0), 2),
                'activeUsers' => User::query()->has('favorites')->count(),
            ],
        ]);
    }
}
