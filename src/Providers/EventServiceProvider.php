<?php

namespace EscolaLms\Cart\Providers;

use EscolaLms\Cart\Listeners\PaymentSuccessListener;
use EscolaLms\Payments\Events\PaymentSuccess;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PaymentSuccess::class => [
            PaymentSuccessListener::class,
        ]
    ];
}
