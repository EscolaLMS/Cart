<?php

use EscolaLms\Cart\Models\Product;
use EscolaLms\Core\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('products_users', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(User::class);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('products_users');
    }
}
