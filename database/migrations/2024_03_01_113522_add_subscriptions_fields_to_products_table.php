<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubscriptionsFieldsToProductsTable extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('subscription_period')->nullable();
            $table->integer('subscription_duration')->nullable();
            $table->boolean('recursive')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('subscription_period');
            $table->dropColumn('subscription_duration');
            $table->dropColumn('recursive');
        });
    }
}
