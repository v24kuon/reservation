<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionPlanRequest;
use App\Http\Requests\UpdateSubscriptionPlanRequest;
use App\Models\LessonCategory;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Stripe\StripeClient;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $plans = SubscriptionPlan::query()
            ->latest('id')
            ->paginate(15);

        return view('admin.subscription_plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = LessonCategory::query()->orderBy('sort_order')->orderBy('id')->get();
        return view('admin.subscription_plans.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubscriptionPlanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->verifyStripeResources($data['stripe_product_id'], $data['stripe_price_id']);

        // Always override price from Stripe
        $data['price'] = $this->fetchStripePriceAmount($data['stripe_price_id']);

        // Expand any selected parent categories into their child categories (server-side safety net)
        $data['allowed_category_ids'] = $this->expandCategoryIds($data['allowed_category_ids']);

        $plan = new SubscriptionPlan();
        $plan->fill([
            'name' => $data['name'],
            'price' => $data['price'],
            'lesson_count' => $data['lesson_count'],
            'allowed_category_ids' => array_values($data['allowed_category_ids']),
            'stripe_product_id' => $data['stripe_product_id'],
            'stripe_price_id' => $data['stripe_price_id'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
        $plan->save();

        return redirect()->route('admin.subscription-plans.index')->with('status', '月謝プランを作成しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(SubscriptionPlan $subscriptionPlan): View
    {
        return view('admin.subscription_plans.show', [
            'plan' => $subscriptionPlan,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubscriptionPlan $subscriptionPlan): View
    {
        $categories = LessonCategory::query()->orderBy('sort_order')->orderBy('id')->get();
        return view('admin.subscription_plans.edit', [
            'plan' => $subscriptionPlan,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubscriptionPlanRequest $request, SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        $data = $request->validated();

        $hasActiveSubscribers = $subscriptionPlan->userSubscriptions()->active()->exists();
        $stripeChanging = $subscriptionPlan->stripe_product_id !== $data['stripe_product_id']
            || $subscriptionPlan->stripe_price_id !== $data['stripe_price_id'];
        if ($hasActiveSubscribers && $stripeChanging) {
            throw ValidationException::withMessages([
                'stripe_price_id' => '稼働中のプランは product/price の変更ができません（新規プランとして作成してください）。',
            ]);
        }

        // Verify with Stripe if IDs are provided/changed
        if ($stripeChanging) {
            $this->verifyStripeResources($data['stripe_product_id'], $data['stripe_price_id']);
        }

        // Always override price from Stripe
        $data['price'] = $this->fetchStripePriceAmount($data['stripe_price_id']);

        // Expand any selected parent categories into their child categories (server-side safety net)
        $data['allowed_category_ids'] = $this->expandCategoryIds($data['allowed_category_ids']);

        $subscriptionPlan->fill([
            'name' => $data['name'],
            'price' => $data['price'],
            'lesson_count' => $data['lesson_count'],
            'allowed_category_ids' => array_values($data['allowed_category_ids']),
            'stripe_product_id' => $data['stripe_product_id'],
            'stripe_price_id' => $data['stripe_price_id'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ])->save();

        return redirect()->route('admin.subscription-plans.index')->with('status', '月謝プランを更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        if ($subscriptionPlan->userSubscriptions()->active()->exists()) {
            return back()->withErrors('稼働中のプランは削除できません。先に利用者を移行してください。');
        }
        $subscriptionPlan->delete();

        return redirect()->route('admin.subscription-plans.index')->with('status', '月謝プランを削除しました。');
    }

    /**
     * Ajax: Lookup Stripe price and return unit_amount
     */
    public function priceLookup(Request $request): JsonResponse
    {
        $this->authorize('access-admin');

        $validated = $request->validate([
            'price_id' => ['required', 'regex:/^price_[A-Za-z0-9]+$/'],
        ]);

        // Ensure Stripe credentials exist and price is valid
        $amount = $this->fetchStripePriceAmount($validated['price_id']);

        return response()->json([
            'success' => true,
            'price' => $amount,
        ]);
    }

    /**
     * Verify Stripe product/price before saving.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function verifyStripeResources(string $productId, string $priceId): void
    {
        $secret = config('services.stripe.secret');
        if (empty($secret)) {
            throw ValidationException::withMessages([
                'stripe_price_id' => 'StripeのAPIキーが未設定です（.env の STRIPE_SECRET を設定してください）。',
            ]);
        }

        $client = new StripeClient($secret);
        try {
            $price = $client->prices->retrieve($priceId, []);
            $product = $client->products->retrieve($productId, []);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'stripe_price_id' => 'Stripeの照合に失敗しました: '.$e->getMessage(),
            ]);
        }

        if (($price->product ?? null) !== $product->id) {
            throw ValidationException::withMessages([
                'stripe_price_id' => '選択した Price は指定の Product に紐づいていません。',
            ]);
        }

        if (!($price->active ?? false)) {
            throw ValidationException::withMessages([
                'stripe_price_id' => 'Price が非アクティブです。',
            ]);
        }

        if (($price->type ?? '') !== 'recurring') {
            throw ValidationException::withMessages([
                'stripe_price_id' => 'Price は定期課金（recurring）である必要があります。',
            ]);
        }

        $interval = $price->recurring->interval ?? null;
        if ($interval !== 'month') {
            throw ValidationException::withMessages([
                'stripe_price_id' => 'Price の課金間隔は月額のみ対応しています。',
            ]);
        }

        $currency = $price->currency ?? '';
        if (strtolower($currency) !== 'jpy') {
            throw ValidationException::withMessages([
                'stripe_price_id' => 'Price の通貨は JPY のみ対応しています。',
            ]);
        }

        $isProd = app()->environment('production');
        $liveMode = (bool) ($price->livemode ?? false);
        if ($liveMode !== $isProd) {
            throw ValidationException::withMessages([
                'stripe_price_id' => $isProd
                    ? '本番環境では Live モードの Price を指定してください。'
                    : '開発環境では Test モードの Price を指定してください。',
            ]);
        }
    }

    /**
     * Fetch unit_amount from Stripe after basic validation of the Price ID.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function fetchStripePriceAmount(string $priceId): int
    {
        $secret = config('services.stripe.secret');
        if (empty($secret)) {
            throw ValidationException::withMessages([
                'stripe_price_id' => 'StripeのAPIキーが未設定です（.env の STRIPE_SECRET を設定してください）。',
            ]);
        }

        $client = new StripeClient($secret);
        try {
            $price = $client->prices->retrieve($priceId, []);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'stripe_price_id' => 'Stripeの照合に失敗しました: '.$e->getMessage(),
            ]);
        }

        if (!($price->active ?? false)) {
            throw ValidationException::withMessages([
                'stripe_price_id' => 'Price が非アクティブです。',
            ]);
        }

        if (($price->type ?? '') !== 'recurring') {
            throw ValidationException::withMessages([
                'stripe_price_id' => 'Price は定期課金（recurring）である必要があります。',
            ]);
        }

        $currency = $price->currency ?? '';
        if (strtolower($currency) !== 'jpy') {
            throw ValidationException::withMessages([
                'stripe_price_id' => 'Price の通貨は JPY のみ対応しています。',
            ]);
        }

        $isProd = app()->environment('production');
        $liveMode = (bool) ($price->livemode ?? false);
        if ($liveMode !== $isProd) {
            throw ValidationException::withMessages([
                'stripe_price_id' => $isProd
                    ? '本番環境では Live モードの Price を指定してください。'
                    : '開発環境では Test モードの Price を指定してください。',
            ]);
        }

        return (int) ($price->unit_amount ?? 0);
    }

    /**
     * Expand any selected parent category IDs to include all their direct children.
     * Stores only child IDs (and standalone leaf selections) to keep data normalized.
     */
    private function expandCategoryIds(array $selectedIds): array
    {
        $selected = collect($selectedIds)->map(fn ($v) => (int) $v)->filter()->unique()->values();
        if ($selected->isEmpty()) {
            return [];
        }

        // Fetch once
        $all = LessonCategory::query()->get(['id', 'parent_id']);
        $childrenByParent = $all->whereNotNull('parent_id')->groupBy('parent_id');
        $roots = $all->whereNull('parent_id')->pluck('id')->all();

        $expanded = collect();
        foreach ($selected as $id) {
            if (in_array($id, $roots, true)) {
                // Add all children of this root
                $children = $childrenByParent->get($id, collect());
                if ($children->isNotEmpty()) {
                    $expanded = $expanded->merge($children->pluck('id'));
                }
            } else {
                // Keep leaf selections as-is
                $expanded->push($id);
            }
        }

        return $expanded->unique()->values()->all();
    }
}
