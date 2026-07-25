<?php

namespace App\Livewire\Favorites;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public function remove(int $favoriteId): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $user->favorites()->whereKey($favoriteId)->delete();
    }

    public function render(): View
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return view('livewire.favorites.index', [
            'favorites' => $user->favorites()->latest()->get(),
        ]);
    }
}
