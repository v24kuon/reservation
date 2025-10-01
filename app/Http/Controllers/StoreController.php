<?php

namespace App\Http\Controllers;

use App\Models\LessonSchedule;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $stores = Store::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(12);

        // preload favorite store ids for current user
        $favoriteStoreIds = $user
            ? $user->favorites()
                ->where('favoritable_type', Store::class)
                ->pluck('favoritable_id')
                ->all()
            : [];

        return view('stores.index', [
            'stores' => $stores,
            'favoriteStoreIds' => $favoriteStoreIds,
        ]);
    }

    public function show(Store $store, Request $request): View
    {
        abort_unless($store->is_active, 404);

        // Normalize Google Map URL to safe scheme or null
        $mapUrl = null;
        if (! empty($store->google_map_url)) {
            $trimmed = trim((string) $store->google_map_url);
            if (str_starts_with($trimmed, 'https://')) {
                $mapUrl = $trimmed;
            }
        }

        $upcomingSchedules = LessonSchedule::query()
            ->with(['lesson.instructor'])
            ->whereHas('lesson', fn ($q) => $q->where('store_id', $store->id))
            ->where('start_datetime', '>=', now())
            ->orderBy('start_datetime')
            ->limit(10)
            ->get();

        return view('stores.show', compact('store', 'upcomingSchedules', 'mapUrl'));
    }
}
