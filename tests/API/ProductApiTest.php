<?php

namespace EscolaLms\Cart\Tests\API;

use EscolaLms\Cart\Database\Seeders\CartPermissionSeeder;
use EscolaLms\Cart\Facades\Shop;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Cart\Tests\Mocks\Product;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;

class ProductApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private TestResponse $response;
    private ShopServiceContract $shopService;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(CartPermissionSeeder::class);
        Shop::registerProduct(Product::class);

        $this->shopService = app(ShopServiceContract::class);
        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole(UserRole::STUDENT);
    }

    public function test_list_products()
    {
        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create();
        /** @var Product $product2 */
        $product2 = Product::factory()->create();

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/products');
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'data' => [
                [
                    'product_id' => $product->getKey(),
                    'product_type' => get_class($product),
                    'buyable' => true,
                    'owned' => false,
                ],
                [
                    'product_id' => $product2->getKey(),
                    'product_type' => get_class($product),
                    'buyable' => true,
                    'owned' => false,
                ]
            ]
        ]);
    }

    public function test_list_products_filtered()
    {
        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create();
        /** @var Product $product2 */
        $product2 = Product::factory()->create();

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/products', ['product_type' => $product->getMorphClass()]);
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'data' => [
                [
                    'product_id' => $product->getKey(),
                    'product_type' => get_class($product),
                    'buyable' => true,
                    'owned' => false,
                ],
                [
                    'product_id' => $product2->getKey(),
                    'product_type' => get_class($product),
                    'buyable' => true,
                    'owned' => false,
                ]
            ]
        ]);
    }
}
