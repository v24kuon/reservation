<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionPlanRequest;
use App\Http\Requests\UpdateSubscriptionPlanRequest;
use App\Models\LessonCategory;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $price = $this->getValidatedStripePrice($data['stripe_product_id'], $data['stripe_price_id']);
        $data['price'] = (int) ($price->unit_amount ?? 0);

        // Expand any selected parent categories into their child categories (server-side safety net)
        $data['allowed_category_ids'] = $this->expandCategoryIds($data['allowed_category_ids'] ?? []);

        $plan = new SubscriptionPlan;
        $plan->fill([
            'name' => $data['name'],
            'price' => $data['price'],
            'lesson_count' => $data['lesson_count'],
            'allowed_category_ids' => array_values($data['allowed_category_ids']),
            'stripe_product_id' => $data['stripe_product_id'],
            'stripe_price_id' => $data['stripe_price_id'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);
        $plan->save();

        return redirect()->route('admin.subscription-plans.index')->with('status', '月謝プランを作成しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(SubscriptionPlan $subscriptionPlan): View
    {
        $allowedCategories = collect();
        if (! empty($subscriptionPlan->allowed_category_ids)) {
            $allowedCategories = LessonCategory::query()
                ->whereIn('id', $subscriptionPlan->allowed_category_ids ?? [])
                ->get(['id', 'name']);
        }

        return view('admin.subscription_plans.show', [
            'plan' => $subscriptionPlan,
            'allowedCategories' => $allowedCategories,
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

        // Always validate and fetch latest price information from Stripe
        $price = $this->getValidatedStripePrice($data['stripe_product_id'], $data['stripe_price_id']);
        $data['price'] = (int) ($price->unit_amount ?? 0);

        // Expand any selected parent categories into their child categories (server-side safety net)
        $data['allowed_category_ids'] = $this->expandCategoryIds($data['allowed_category_ids'] ?? []);

        $subscriptionPlan->fill([
            'name' => $data['name'],
            'price' => $data['price'],
            'lesson_count' => $data['lesson_count'],
            'allowed_category_ids' => array_values($data['allowed_category_ids']),
            'stripe_product_id' => $data['stripe_product_id'],
            'stripe_price_id' => $data['stripe_price_id'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active', $subscriptionPlan->is_active),
        ])->save();

        return redirect()->route('admin.subscription-plans.index')->with('status', '月謝プランを更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        if ($subscriptionPlan->userSubscriptions()->active()->exists()) {
            return back()->withErrors(['subscription_plan' => '稼働中のプランは削除できません。先に利用者を移行してください。']);
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

        // Basic validation using Stripe (no product check for lookup UI)
        try {
            $price = $this->stripe()->prices->retrieve($validated['price_id'], []);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'error' => 'stripe_price_lookup_failed'], 422);
        }

        if (! (($price->active ?? false)
            && (($price->type ?? '') === 'recurring')
            && (strtolower($price->currency ?? '') === 'jpy')
            && (((bool) ($price->livemode ?? false)) === app()->environment('production'))
        )) {
            return response()->json(['success' => false, 'error' => 'stripe_price_invalid'], 422);
        }

        return response()->json([
            'success' => true,
            'price' => (int) ($price->unit_amount ?? 0),
        ]);
    }

    /**
     * Verify Stripe product/price before saving.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    private function stripe(): StripeClient
    {
        static $client = null;
        if ($client instanceof StripeClient) {
            return $client;
        }

        $secret = config('services.stripe.secret');
        if (empty($secret)) {
            throw ValidationException::withMessages([
                'stripe_price_id' => 'StripeのAPIキーが未設定です（.env の STRIPE_SECRET を設定してください）。',
            ]);
        }

        $client = new StripeClient([
            'api_key' => $secret,
        ]);

        return $client;
    }

    /**
     * Retrieve and validate a Stripe Price with optional product match.
     *
     * @return \Stripe\Price
     */
    private function getValidatedStripePrice(?string $productId, string $priceId)
    {
        try {
            $price = $this->stripe()->prices->retrieve($priceId, ['expand' => ['product']]);
        } catch (\Throwable $e) {
            report($e);
            throw ValidationException::withMessages([
                'stripe_price_id' => 'Stripeの照合に失敗しました。',
            ]);
        }

        if (! ($price->active ?? false)) {
            throw ValidationException::withMessages([
                'stripe_price_id' => 'Price が非アクティブです。',
            ]);
        }

        if (($price->type ?? '') !== 'recurring') {
            throw ValidationException::withMessages([
                'stripe_price_id' => 'Price は定期課金（recurring）である必要があります。',
            ]);
        }

        $interval = $price->recurring?->interval ?? null;
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

        // Product一致確認（productId が与えられている場合のみ）
        if (! empty($productId)) {
            $productIdFromPrice = is_object($price->product) ? ($price->product->id ?? null) : ($price->product ?? null);
            if ($productIdFromPrice !== $productId) {
                throw ValidationException::withMessages([
                    'stripe_price_id' => '選択した Price は指定の Product に紐づいていません。',
                ]);
            }
        }

        if ((int) ($price->unit_amount ?? 0) <= 0) {
            throw ValidationException::withMessages([
                'stripe_price_id' => 'Price の金額が不正です（0円以下）。',
            ]);
        }

        return $price;
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
        $allIds = $all->pluck('id')->all();

        $expanded = collect();
        foreach ($selected as $id) {
            // BFS to gather all leaf nodes; if no children, keep the node itself
            $queue = collect([$id]);
            $hasAnyChild = false;
            while ($queue->isNotEmpty()) {
                $cur = (int) $queue->shift();
                $children = $childrenByParent->get($cur, collect());
                if ($children->isEmpty()) {
                    $expanded->push($cur);
                } else {
                    $hasAnyChild = true;
                    $queue = $queue->merge($children->pluck('id'));
                }
            }
            // ここでの再 push は不要（BFS 中に葉を push 済み）
        }

        return $expanded->unique()->values()->all();
    }
}
