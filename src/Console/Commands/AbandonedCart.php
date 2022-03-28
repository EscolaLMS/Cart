<?php

namespace EscolaLms\Cart\Console\Commands;

use Carbon\Carbon;
use EscolaLms\Cart\Events\AbandonedCartEvent;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use Illuminate\Console\Command;

class AbandonedCart extends Command
{
    protected $signature = 'cart:abandoned-event';

    protected $description = 'Find all abandoned cart in 24-48h and run event';

    private ShopServiceContract $shopService;

    public function __construct(ShopServiceContract $shopService)
    {
        parent::__construct();
        $this->shopService = $shopService;
    }

    public function handle()
    {
        $abandonedCarts = $this->shopService->getAbandonedCarts(Carbon::now()->subHours(24), Carbon::now()->subHours(48));
        foreach ($abandonedCarts as $abandonedCart) {
            AbandonedCartEvent::dispatch($abandonedCart);
        }
    }
}
