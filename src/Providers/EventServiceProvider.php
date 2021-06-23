<?php

namespace EscolaLms\Cart\Providers;

use EscolaLms\Cart\Events\OrderPaid;
use EscolaLms\Cart\Listeners\AttachOrderedCoursesToUser;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderPaid::class => [
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
