<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TaxRateAsFloat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 3)->default(0)->change();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 3)->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('tax_rate')->default(0)->change();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('tax_rate')->default(0)->change();
        });
    }
}
