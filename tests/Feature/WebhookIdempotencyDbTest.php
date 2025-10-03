<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('enforces unique event id in stripe_webhook_events', function (): void {
    $eventId = 'evt_test_idem_123';

    DB::table('stripe_webhook_events')->insert([
        'event_id' => $eventId,
        'type' => 'invoice.payment_succeeded',
        'payload' => json_encode(['id' => $eventId]),
        'received_at' => now(),
    ]);

    try {
        DB::table('stripe_webhook_events')->insert([
            'event_id' => $eventId,
            'type' => 'invoice.payment_succeeded',
            'payload' => json_encode(['id' => $eventId]),
            'received_at' => now(),
        ]);
        // if no exception thrown, force fail
        expect(true)->toBeFalse();
    } catch (Throwable $e) {
        // expected unique constraint violation
        expect(DB::table('stripe_webhook_events')->where('event_id', $eventId)->count())->toBe(1);
    }
});
