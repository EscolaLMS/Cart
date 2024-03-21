<?php

namespace EscolaLms\Cart\Jobs;

use EscolaLms\Cart\Models\ProductUser;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class RenewRecursiveProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(ProductServiceContract $productService): void
    {
        $productService
            ->getRecursiveProductUserBeforeExpiredEndDate(Carbon::now()->subHour(), Carbon::now()->addHour())
            ->each(fn(ProductUser $productUser) => RenewRecursiveProductUser::dispatch($productUser));
    }
}
