<?php
namespace EscolaLms\Cart\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Throwable;

class OrderNotFoundException extends UnprocessableEntityHttpException
{
    public function __construct($message = "Order Not Found", $code = 422, ?Throwable $previous = null)
    {
        parent::__construct($message, $previous, $code);
    }
}
