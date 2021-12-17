<?php

namespace EscolaLms\Cart\Listeners;

use EscolaLms\Cart\Events\EscolaLmsCartOrderPaidTemplateEvent;
use EscolaLms\Cart\Services\Contracts\OrderProcessingServiceContract;

class AttachOrderedCoursesToUser
{
    private OrderProcessingServiceContract $orderProcessing;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(OrderProcessingServiceContract $orderProcessing)
    {
        $this->orderProcessing = $orderProcessing;
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(EscolaLmsCartOrderPaidTemplateEvent $event)
    {
        //TODO: move this functionality to Courses Service?
        $this->orderProcessing->processOrderItems($event->getOrder()->items, $event->getUser());
    }
}
