<?php

use App\Jobs\ProcessCheckoutSessionCompleted;
use App\Jobs\ProcessCustomerSubscriptionDeleted;
use App\Jobs\ProcessCustomerSubscriptionUpdated;
use App\Jobs\ProcessInvoicePaid;
use App\Jobs\ProcessInvoicePaymentFailed;
use App\Listeners\StripeWebhookListener;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Laravel\Cashier\Events\WebhookReceived;

it('dispatches jobs for allowed event types', function (): void {
    Bus::fake();

    $listener = app(StripeWebhookListener::class);

    $events = [
        'checkout.session.completed' => ProcessCheckoutSessionCompleted::class,
        'customer.subscription.created' => ProcessCustomerSubscriptionUpdated::class,
        'customer.subscription.updated' => ProcessCustomerSubscriptionUpdated::class,
        'customer.subscription.deleted' => ProcessCustomerSubscriptionDeleted::class,
        'invoice.payment_succeeded' => ProcessInvoicePaid::class,
        'invoice.payment_failed' => ProcessInvoicePaymentFailed::class,
    ];

    foreach ($events as $type => $job) {
        $payload = ['id' => 'evt_'.uniqid(), 'type' => $type];
        $listener->handle(new WebhookReceived($payload));
        Bus::assertDispatched($job);
    }
});

it('ignores disallowed events', function (): void {
    Bus::fake();

    $listener = app(StripeWebhookListener::class);
    $payload = ['id' => 'evt_xxx', 'type' => 'charge.succeeded'];

    $listener->handle(new WebhookReceived($payload));

    Bus::assertNotDispatched(ProcessCheckoutSessionCompleted::class);
    Bus::assertNotDispatched(ProcessCustomerSubscriptionUpdated::class);
    Bus::assertNotDispatched(ProcessCustomerSubscriptionDeleted::class);
    Bus::assertNotDispatched(ProcessInvoicePaid::class);
    Bus::assertNotDispatched(ProcessInvoicePaymentFailed::class);
});

it('enforces idempotency using cache for duplicate events', function (): void {
    Bus::fake();

    $listener = app(StripeWebhookListener::class);
    $eventId = 'evt_dup_'.uniqid();
    $payload = ['id' => $eventId, 'type' => 'invoice.payment_succeeded'];

    // First time: should dispatch and set cache
    $listener->handle(new WebhookReceived($payload));
    Bus::assertDispatched(ProcessInvoicePaid::class);

    // Second time: should be ignored due to idempotency cache
    Bus::fake();
    $listener->handle(new WebhookReceived($payload));
    Bus::assertNotDispatched(ProcessInvoicePaid::class);

    // Cleanup
    Cache::forget('stripe_webhook:processed:'.$eventId);
});
