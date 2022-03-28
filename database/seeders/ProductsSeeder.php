<?php

namespace EscolaLms\Cart\Database\Seeders;

use EscolaLms\Cart\Contracts\Productable;
use EscolaLms\Cart\Facades\Shop;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductProductable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class ProductsSeeder extends Seeder
{
    use WithFaker;

    public function run()
    {
        $productableClasses = Shop::listRegisteredProductableClasses();

        if (empty($productableClasses)) {
            Product::factory()->count(5)->create();
        } else {
            foreach ($productableClasses as $productableClass) {
                assert(is_a($productableClass, Model::class, true));
                /** @var Model $productableClass */
                $productables = $productableClass::inRandomOrder()->take(5)->get();

                foreach ($productables as $productable) {
                    /** @var Model&Productable $productable */
                    $product = Product::factory()->create([
                        'name' => __('Product for :productable', ['productable' => $productable->getName()]),
                        'price' => rand(1000, 5000),
                    ]);
                    /** @var Product $product */
                    ProductProductable::create([
                        'product_id' => $product->getKey(),
                        'productable_id' => $productable->getKey(),
                        'productable_type' => $productable->getMorphClass(),
                    ]);
                }
            }
        }
    }
}
