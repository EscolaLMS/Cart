<?php

use EscolaLms\Cart\Models\Category;
use EscolaLms\Cart\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsCategoriesTable extends Migration
{
    public function up(): void
    {
        Schema::create('products_categories', function (Blueprint $table) {
            $table->id('id');
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(Category::class);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('products_categories');
    }
}
