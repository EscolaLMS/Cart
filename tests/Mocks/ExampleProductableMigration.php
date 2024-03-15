<?php

namespace EscolaLms\Cart\Tests\Mocks;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExampleProductableMigration
{
    public static function run()
    {
        if (Schema::hasTable('test_productables_users')) {
            Schema::drop('test_productables_users');
        }

        if (Schema::hasTable('test_productables')) {
            Schema::drop('test_productables');
        }

        Schema::create('test_productables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('test_productables_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_productable_id');
            $table->foreignId('user_id');
            $table->dateTime('end_date');
        });
    }
}
