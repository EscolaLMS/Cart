<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClientInformationToOrderTable extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('client_name')->nullable();
            $table->string('client_street')->nullable();
            $table->string('client_postal')->nullable();
            $table->string('client_city')->nullable();
            $table->string('client_country')->nullable();
            $table->string('client_company')->nullable();
            $table->string('client_taxid')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'client_name',
                'client_street',
                'client_postal',
                'client_city',
                'client_country',
                'client_company',
                'client_taxid',
            ]);
        });
    }
}
