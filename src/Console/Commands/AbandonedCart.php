<?php

namespace EscolaLms\Cart\Console\Commands;

use Carbon\Carbon;
use EscolaLms\Cart\Events\AbandonedCartEvent;
use EscolaLms\Cart\Services\Contracts\CartManagerContract;
use Illuminate\Console\Command;

class AbandonedCart extends Command
{
    protected $signature = 'cart:abandoned-event';

    protected $description = 'Find all abandoned cart in 24-48h and run event';

    private CartManagerContract $cartManager;

    public function __construct(CartManagerContract $cartManager)
    {
        parent::__construct();
        $this->cartManager = $cartManager;
    }

    public function handle()
    {
        $abandonedCarts = $this->cartManager->getAbandonedCarts(Carbon::now()->subHours(24), Carbon::now()->subHours(48));
        foreach ($abandonedCarts as $abandonedCart) {
            AbandonedCartEvent::dispatch($abandonedCart);
        }
    }
}
