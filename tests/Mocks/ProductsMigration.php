<?php

namespace EscolaLms\Cart\Tests\Mocks;

use DB;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProductsMigration
{
    public static function run()
    {
        DB::transaction(function () {
            if (DB::connection() instanceof MySqlConnection) {
                DB::statement('DROP TEMPORARY TABLE IF EXISTS test_products');
                DB::statement('DROP TEMPORARY TABLE IF EXISTS test_products_users');
            }
            if (DB::connection() instanceof PostgresConnection) {
                DB::statement('DROP TABLE IF EXISTS test_products');
                DB::statement('DROP TABLE IF EXISTS test_products_users');
            }
            DB::statement('CREATE TEMPORARY TABLE test_products (`id` int(10) unsigned NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`))');
            Schema::table('test_products', function (Blueprint $table) {
                $table->string('name');
                $table->unsignedInteger('price');
                $table->unsignedInteger('tax_rate')->default(0);
                $table->timestamps();
            });
            DB::statement('CREATE TEMPORARY TABLE test_products_users (`id` int(10) unsigned NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`))');
            Schema::table('test_products_users', function (Blueprint $table) {
                $table->foreignId('test_product_id');
                $table->foreignId('user_id');
            });
        });
    }
}
