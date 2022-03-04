<?php

use EscolaLms\Cart\Enums\ProductType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default(ProductType::SINGLE);
            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('price_old')->nullable();
            $table->unsignedInteger('tax_rate')->default(0);
            $table->unsignedInteger('extra_fees')->default(0);
            $table->boolean('purchasable')->default(true);
            $table->string('teaser_url')->nullable();
            $table->string('description')->nullable();
            $table->string('poster_url')->nullable();
            $table->string('duration')->nullable();
            $table->unsignedInteger('limit_per_user')->nullable()->default(1);
            $table->unsignedInteger('limit_total')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('products');
    }
}
