<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\UserFavorite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Toggle favorite for a store for the authenticated user.
     */
    public function toggleStore(Request $request, Store $store): RedirectResponse
    {
        $user = $request->user();

        abort_unless($store->is_active, 404);

        $favorite = UserFavorite::query()
            ->where('user_id', $user->id)
            ->where('favoritable_type', Store::class)
            ->where('favoritable_id', $store->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
        } else {
            UserFavorite::create([
                'user_id' => $user->id,
                'favoritable_type' => Store::class,
                'favoritable_id' => $store->id,
            ]);
        }

        return back();
    }
}
