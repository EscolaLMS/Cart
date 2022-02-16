<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->morphs('buyable');
            $table->unsignedInteger('quantity');
            $table->json('options')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->on('orders')->references('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
}
