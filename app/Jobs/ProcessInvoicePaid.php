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

class ProcessInvoicePaid implements ShouldQueue
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
        $invoice = (array) Arr::get($this->payload, 'data.object', []);
        $subscriptionId = (string) ($invoice['subscription'] ?? '');
        if ($subscriptionId === '') {
            return;
        }

        $periodStart = (int) Arr::get($invoice, 'lines.data.0.period.start', 0);
        $periodEnd = (int) Arr::get($invoice, 'lines.data.0.period.end', 0);

        $update = [
            'payment_status' => 'paid',
            'current_month_used_count' => 0,
        ];
        if ($periodStart > 0) {
            $update['current_period_start'] = CarbonImmutable::createFromTimestampUTC($periodStart);
        }
        if ($periodEnd > 0) {
            $update['current_period_end'] = CarbonImmutable::createFromTimestampUTC($periodEnd);
        }

        UserSubscription::query()
            ->where('stripe_subscription_id', $subscriptionId)
            ->update($update);
    }
}
