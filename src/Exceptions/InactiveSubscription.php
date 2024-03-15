<?php

namespace EscolaLms\Cart\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Throwable;

class InactiveSubscription extends UnprocessableEntityHttpException
{
    public function __construct($message = "You do not have an active subscription", $code = 422, ?Throwable $previous = null)
    {
        parent::__construct($message, $previous, $code);
    }
}
