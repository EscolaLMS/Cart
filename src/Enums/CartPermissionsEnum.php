<?php

namespace EscolaLms\Cart\Enums;

use EscolaLms\Core\Enums\BasicEnum;

class CartPermissionsEnum extends BasicEnum
{
    const LIST_ALL_ORDERS = 'cart_order_list';
    // TODO: Listing orders for authored products?

    const LIST_PRODUCTS = 'list_products';
    const ATTACH_PRODUCTS = 'attach_products';
    const BUY_PRODUCTS = 'buy_products';
}
