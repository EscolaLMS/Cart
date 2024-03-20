<?php

namespace EscolaLms\Cart\Jobs;

use EscolaLms\Cart\Enums\SubscriptionStatus;
use EscolaLms\Cart\Models\ProductUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ExpireRecursiveProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        ProductUser::query()
            ->where('status', SubscriptionStatus::ACTIVE)
            ->where('end_date', '<=', Carbon::now()->endOfDay()->subDay())
            ->update(['status' => SubscriptionStatus::EXPIRED]);
    }
}
