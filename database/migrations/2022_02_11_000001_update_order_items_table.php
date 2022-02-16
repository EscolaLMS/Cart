<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrderItemsTable extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('price')->nullable();
            $table->unsignedInteger('extra_fees')->default(0);
            $table->unsignedInteger('tax_rate')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'price',
                'extra_fees',
                'tax_rate',
            ]);
        });
    }
}
