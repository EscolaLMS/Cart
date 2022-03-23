<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuantityToProductsUsersTable extends Migration
{
    public function up(): void
    {
        Schema::table('products_users', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('products_users', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
}
