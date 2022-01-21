<?php

namespace EscolaLms\Cart\Enums;

use EscolaLms\Core\Enums\BasicEnum;

class CartPermissionsEnum extends BasicEnum
{
    const LIST_ALL_ORDERS = 'cart_order-list';
    const LIST_AUTHORED_COURSE_ORDERS = 'cart_order-list_authored';
}
