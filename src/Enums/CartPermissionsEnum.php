<?php

namespace EscolaLms\Cart\Enums;

use EscolaLms\Core\Enums\BasicEnum;

class CartPermissionsEnum extends BasicEnum
{
    const LIST_ALL_ORDERS = 'cart_order_list';
    // TODO: Listing orders for authored products?

    const LIST_ALL_PRODUCTS = 'products_list';
    const MANAGE_PRODUCTS = 'products_manage';

    const LIST_PURCHASABLE_PRODUCTS = 'products_list_purchasable';
    const BUY_PRODUCTS = 'products_buy';
}
