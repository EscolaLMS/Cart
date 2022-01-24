<?php

namespace EscolaLms\Cart\Providers;

use EscolaLms\Cart\Events\CartOrderPaid;
use EscolaLms\Cart\Listeners\AttachOrderedCoursesToUser;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CartOrderPaid::class => [
            AttachOrderedCoursesToUser::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
