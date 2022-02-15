<?php

namespace EscolaLms\Cart\Database\Seeders;

use EscolaLms\Cart\Contracts\Product;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Models\OrderItem;
use EscolaLms\Cart\Models\User;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Models\User as ModelsUser;
use EscolaLms\Payments\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class OrdersSeeder extends Seeder
{
    use WithFaker;

    public function run()
    {
        $shopService = app(ShopServiceContract::class);

        $student = User::role(UserRole::STUDENT)->first();
        if (!$student) {
            // This will only be called if Users were not seeded before CartSeeder was Called
            $users = User::factory()->count(10)->create();
            /** @var User $user */
            foreach ($users as $user) {
                $user->assignRole(UserRole::STUDENT);
            }
        }

        $students = User::role(UserRole::STUDENT)->take(10)->get();

        /** @var User $student */
        foreach ($students as $student) {
            $products = $shopService->listProductsBuyableByUser($student)->random(rand(1, 3));
            $price = $products->reduce(fn ($acc, Product $product) => $acc + $product->getBuyablePrice(), 0);

            /** @var Order $order */
            $order = Order::factory()->has(Payment::factory()->state([
                'amount' => $price,
                'billable_id' => $student->getKey(),
                'billable_type' => ModelsUser::class,
            ]))
                ->afterCreating(
                    fn (Order $order) => $order->items()->saveMany(
                        $products->map(
                            function (Product $product) {
                                return OrderItem::query()->make([
                                    'quantity' => 1,
                                    'buyable_id' => $product->getKey(),
                                    'buyable_type' => $product->getMorphClass(),
                                ]);
                            }
                        )
                    )
                )->create([
                    'user_id' => $student->getKey(),
                    'total' => $price,
                    'subtotal' => $price,
                ]);

            $products->each(function (Product $product) use ($student) {
                $product->attachToUser($student);
            });
        }
    }
}
