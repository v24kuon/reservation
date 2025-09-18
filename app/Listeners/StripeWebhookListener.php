<?php

namespace App\Listeners;

use App\Jobs\ProcessCheckoutSessionCompleted;
use App\Jobs\ProcessCustomerSubscriptionDeleted;
use App\Jobs\ProcessCustomerSubscriptionUpdated;
use App\Jobs\ProcessInvoicePaid;
use App\Jobs\ProcessInvoicePaymentFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookReceived;

class StripeWebhookListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Allowed Stripe event types.
     *
     * @var array<int, string>
     */
    private array $allowedEventTypes = [
        'checkout.session.completed',
        'customer.subscription.created',
        'customer.subscription.updated',
        'customer.subscription.deleted',
        'invoice.payment_succeeded',
        'invoice.payment_failed',
    ];

    /**
     * Handle the incoming webhook from Cashier.
     */
    public function handle(WebhookReceived $event): void
    {
        $payload = $event->payload;
        $type = (string) ($payload['type'] ?? '');

        if ($type === '' || ! in_array($type, $this->allowedEventTypes, true)) {
            return; // Not allowed or malformed
        }

        // Basic idempotency using cache (7 days retention)
        $eventId = (string) ($payload['id'] ?? '');
        if ($eventId !== '') {
            $cacheKey = 'stripe_webhook:processed:'.$eventId;
            if (! Cache::add($cacheKey, true, now()->addDays(7))) {
                // Duplicate event, already processed
                return;
            }
        }

        try {
            switch ($type) {
                case 'checkout.session.completed':
                    ProcessCheckoutSessionCompleted::dispatch($payload);
                    break;

                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                    ProcessCustomerSubscriptionUpdated::dispatch($payload);
                    break;

                case 'customer.subscription.deleted':
                    ProcessCustomerSubscriptionDeleted::dispatch($payload);
                    break;

                case 'invoice.payment_succeeded':
                    ProcessInvoicePaid::dispatch($payload);
                    break;

                case 'invoice.payment_failed':
                    ProcessInvoicePaymentFailed::dispatch($payload);
                    break;
            }
        } catch (\Throwable $e) {
            Log::error('Stripe webhook listener error', [
                'type' => $type,
                'event_id' => $eventId,
                'message' => $e->getMessage(),
            ]);

            // On failure, allow Stripe to retry by removing idempotency key for this event
            if (! empty($eventId)) {
                Cache::forget('stripe_webhook:processed:'.$eventId);
            }
        }
    }
}
