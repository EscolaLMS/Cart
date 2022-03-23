<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuantityToProductsProductablesTable extends Migration
{
    public function up(): void
    {
        Schema::table('products_productables', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('products_productables', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
}
