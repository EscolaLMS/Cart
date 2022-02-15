<?php

namespace EscolaLms\Cart\Tests\Mocks;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProductsMigration
{
    public static function run()
    {
        if (Schema::hasTable('test_products')) {
            Schema::drop('test_products');
        }
        if (Schema::hasTable('test_products_users')) {
            Schema::drop('test_products_users');
        }
        Schema::create('test_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('price');
            $table->unsignedInteger('tax_rate')->default(0);
            $table->timestamps();
        });
        Schema::create('test_products_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_product_id');
            $table->foreignId('user_id');
        });
    }
}
