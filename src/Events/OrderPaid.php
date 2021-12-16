<?php


namespace EscolaLms\Cart\Events;

use EscolaLms\Cart\Models\Contracts\CanOrder;
use EscolaLms\Cart\Models\Order;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPaid extends EscolaLmsCartTemplateEvent
{
}
