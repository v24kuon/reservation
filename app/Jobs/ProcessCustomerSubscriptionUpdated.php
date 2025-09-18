<?php

namespace App\Jobs;

use App\Models\UserSubscription;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class ProcessCustomerSubscriptionUpdated implements ShouldQueue
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

        $status = (string) ($object['status'] ?? 'active');
        $startTs = (int) ($object['current_period_start'] ?? 0);
        $endTs = (int) ($object['current_period_end'] ?? 0);

        $update = [
            'status' => $status,
        ];
        if ($startTs > 0) {
            $update['current_period_start'] = CarbonImmutable::createFromTimestamp($startTs);
        }
        if ($endTs > 0) {
            $update['current_period_end'] = CarbonImmutable::createFromTimestamp($endTs);
        }

        // If invoice was paid, Cashier also emits invoice.payment_succeeded,
        // but here we only reflect status/period; payment_status handled elsewhere.
        UserSubscription::query()
            ->where('stripe_subscription_id', $stripeId)
            ->update($update);
    }
}
