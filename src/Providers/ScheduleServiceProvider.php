<?php

namespace EscolaLms\Cart\Providers;

use EscolaLms\Cart\Console\Commands\AbandonedCart;
use EscolaLms\Cart\Jobs\ExpireRecursiveProduct;
use EscolaLms\Cart\Jobs\RenewRecursiveProduct;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;


class ScheduleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            // commands
            $schedule->command(AbandonedCart::class)->dailyAt('1:00');

            // jobs
            $schedule->job(RenewRecursiveProduct::class)->hourly();
            $schedule->job(ExpireRecursiveProduct::class)->daily();
        });

    }
}
