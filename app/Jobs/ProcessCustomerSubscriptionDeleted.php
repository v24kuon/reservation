<?php

namespace App\Jobs;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class ProcessCustomerSubscriptionDeleted implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var array<string, mixed> */
    public array $payload;

    /** @param array<string, mixed> $payload */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle(): void
    {
        $object = (array) Arr::get($this->payload, 'data.object', []);
        $stripeId = (string) ($object['id'] ?? '');
        if ($stripeId === '') {
            return;
        }

        UserSubscription::query()
            ->where('stripe_subscription_id', $stripeId)
            ->update([
                'status' => 'canceled',
                'payment_status' => 'failed',
            ]);
    }
}
