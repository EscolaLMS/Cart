<?php

use EscolaLms\Cart\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsProductablesTable extends Migration
{
    public function up(): void
    {
        Schema::create('products_productables', function (Blueprint $table) {
            $table->id('id');
            $table->foreignIdFor(Product::class);
            $table->morphs('productable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('products_productables');
    }
}
