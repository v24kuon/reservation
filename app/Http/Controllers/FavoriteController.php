<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\UserFavorite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    /**
     * Toggle favorite for a store for the authenticated user.
     */
    public function toggleStore(Request $request, Store $store): RedirectResponse
    {
        $user = $request->user();

        if (! $store->is_active) {
            abort(404, 'Store not found or inactive');
        }

        // Atomic toggle to avoid race: try delete first; if nothing deleted, insert (ignore on unique conflict)
        $criteria = [
            'user_id' => $user->id,
            'favoritable_type' => Store::class,
            'favoritable_id' => $store->id,
        ];

        $deleted = UserFavorite::query()->where($criteria)->delete();

        if ($deleted === 0) {
            DB::table('user_favorites')->insertOrIgnore($criteria + [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back();
    }
}
