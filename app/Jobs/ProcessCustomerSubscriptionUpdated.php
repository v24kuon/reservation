<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        // Stub: implement status/payment_status/period sync
    }
}
