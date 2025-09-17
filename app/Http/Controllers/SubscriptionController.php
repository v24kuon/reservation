<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Stripe\StripeClient;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Create a Stripe Checkout Session for the given plan and redirect user.
     */
    public function createCheckoutSession(Request $request, int $planId): RedirectResponse
    {
        $user = $request->user();

        $plan = SubscriptionPlan::query()->active()->findOrFail($planId);

        // 既存の同一プラン（同一Price）への重複加入を抑止
        if (method_exists($user, 'subscribedToPrice') && $user->subscribedToPrice($plan->stripe_price_id)) {
            throw ValidationException::withMessages([
                'subscription' => 'すでにこのプランに加入済みです。',
            ]);
        }

        // Validate price/product in Stripe and environment consistency
        $price = $this->getValidatedStripePrice($plan->stripe_product_id, $plan->stripe_price_id);

        // Ensure Stripe customer exists
        $user->createOrGetStripeCustomer();

        // Success / Cancel URLs
        // Task 26/27 完了後は route 名の絶対URLに移行（HTTPSはグローバルでforceScheme推奨）
        if (app('router')->has('subscription.success')) {
            $successUrl = route('subscription.success', ['session_id' => '{CHECKOUT_SESSION_ID}'], true);
        } else {
            $successUrl = url('/subscription/success').'?session_id={CHECKOUT_SESSION_ID}';
        }
        if (app('router')->has('subscription.cancel')) {
            $cancelUrl = route('subscription.cancel', [], true);
        } else {
            $cancelUrl = url('/subscription/cancel');
        }

        try {
            // 同一ユーザー×プランの短期的な二重発行を抑止
            // フロント付与のIdempotency-Keyを優先。無ければセッションID由来の安定キーを生成
            $idemKey = $request->header('Idempotency-Key')
                ?? ('checkout:'.hash('sha256', $user->getKey().':'.$plan->getKey().':'.$request->session()->getId()));
            $session = $this->stripe()->checkout->sessions->create([
                'mode' => 'subscription',
                'customer' => $user->stripe_id,
                'line_items' => [
                    [
                        'price' => $price->id,
                        'quantity' => 1,
                    ],
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'locale' => 'ja',
                'client_reference_id' => (string) $user->getKey(),
                'metadata' => [
                    'app_plan_id' => (string) $plan->getKey(),
                    'app_user_id' => (string) $user->getKey(),
                    'stripe_product_id' => is_string($price->product)
                        ? $price->product
                        : ($price->product->id ?? ''),
                    'stripe_price_id' => (string) $price->id,
                ],
                // Optional: enable promotion codes in future if needed
                // 'allow_promotion_codes' => true,
            ], [
                'idempotency_key' => $idemKey,
            ]);
        } catch (\Throwable $e) {
            report($e);
            if ($e instanceof \Stripe\Exception\ApiErrorException) {
                logger()->warning('Stripe API error', [
                    'request_id' => $e->getRequestId(),
                    'stripe_code' => $e->getStripeCode(),
                ]);
            }
            throw ValidationException::withMessages([
                'subscription' => '決済セッションの作成に失敗しました。時間をおいて再度お試しください。',
            ]);
        }

        return redirect()->away($session->url);
    }

    /**
     * Shared Stripe client with retries.
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
                'stripe' => 'StripeのAPIキーが未設定です（.env の STRIPE_SECRET を設定してください）。',
            ]);
        }

        $client = new StripeClient([
            'api_key' => $secret,
            'max_network_retries' => 2,
        ]);

        return $client;
    }

    /**
     * Retrieve and validate a Stripe Price with optional product match.
     */
    private function getValidatedStripePrice(?string $productId, string $priceId): \Stripe\Price
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
}
