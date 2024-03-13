<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubscriptionsFieldsToProductUserTable extends Migration
{
    public function up(): void
    {
        Schema::table('products_users', function (Blueprint $table) {
            $table->dateTime('end_date')->nullable();
            $table->string('status')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products_users', function (Blueprint $table) {
            $table->dropColumn('end_date');
            $table->dropColumn('status');
        });
    }
}
