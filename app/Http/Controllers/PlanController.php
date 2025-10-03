<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Contracts\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = SubscriptionPlan::query()
            ->active()
            ->orderBy('id')
            ->get();

        $activePriceIds = [];
        if (auth()->check()) {
            $user = auth()->user();
            $activeSubs = $user->userSubscriptions()
                ->active()
                ->paid()
                ->where(function ($q) {
                    $q->whereNull('current_period_end')
                        ->orWhere('current_period_end', '>=', now());
                })
                ->with('plan')
                ->get();
            $activePriceIds = $activeSubs->pluck('plan.stripe_price_id')->filter()->values()->all();
        }

        return view('plans.index', [
            'plans' => $plans,
            'activePriceIds' => $activePriceIds,
        ]);
    }
}
