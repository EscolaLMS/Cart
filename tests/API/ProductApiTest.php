<?php

namespace EscolaLms\Cart\Tests\API;

use EscolaLms\Cart\Database\Seeders\CartPermissionSeeder;
use EscolaLms\Cart\Enums\SubscriptionStatus;
use EscolaLms\Cart\Facades\Shop;
use EscolaLms\Cart\Http\Resources\ProductResource;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductProductable;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Cart\Tests\Mocks\ExampleProductable;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Models\User;
use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Tags\Models\Tag;
use EscolaLms\Templates\Events\ManuallyTriggeredEvent;
use EscolaLms\Templates\Facades\Template as FacadesTemplate;
use EscolaLms\Templates\Models\Template;
use EscolaLms\Templates\Models\TemplateSection;
use EscolaLms\Templates\Tests\Mock\TestChannel;
use EscolaLms\Templates\Tests\Mock\TestUserVariables;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;

class ProductApiTest extends TestCase
{
    use DatabaseTransactions, CreatesUsers;

    private User $user;
    private TestResponse $response;
    private ShopServiceContract $shopService;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(CartPermissionSeeder::class);
        Shop::registerProductableClass(ExampleProductable::class);

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
                ProductResource::make($product->refresh())->toArray(null),
                ProductResource::make($product2->refresh())->toArray(null),
            ]
        ]);
    }

    public function test_get_product_not_found()
    {
        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create();
        $product->delete();

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/admin/products/' . $product->getKey());
        $this->response->assertStatus(422);
    }

    public function test_search_products()
    {
        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create();
        $productable = ExampleProductable::factory()->create();
        $product->productables()->save(new ProductProductable([
            'productable_type' => $productable->getMorphClass(),
            'productable_id' => $productable->getKey()
        ]));
        $product->tags()->save(new Tag(['title' => 'test-tag']));

        /** @var Product $product2 */
        $product2 = Product::factory()->create();
        $productable2 = ExampleProductable::factory()->create();
        $product2->productables()->save(new ProductProductable([
            'productable_type' => $productable->getMorphClass(),
            'productable_id' => $productable2->getKey()
        ]));
        /** @var Product $product2 */
        $product3 = Product::factory()->create(['purchasable' => false]);
        $productable3 = ExampleProductable::factory()->create();
        $product3->productables()->save(new ProductProductable([
            'productable_type' => $productable->getMorphClass(),
            'productable_id' => $productable3->getKey()
        ]));

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/products', ['productable_type' => ExampleProductable::class]);
        $this->response->assertOk();

        $this->response->assertJsonFragment([
            ProductResource::make($product->refresh())->toArray(null),
        ]);
        $this->response->assertJsonFragment([
            ProductResource::make($product2->refresh())->toArray(null),
        ]);
        $this->response->assertJsonMissing([
            ProductResource::make($product3->refresh())->toArray(null),
        ]);

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/products', ['productable_id' => $productable->getKey(), 'productable_type' => ExampleProductable::class]);
        $this->response->assertOk();

        $this->response->assertJsonFragment([
            ProductResource::make($product->refresh())->toArray(null),
        ]);
        $this->response->assertJsonMissing([
            ProductResource::make($product2->refresh())->toArray(null),
        ]);
        $this->response->assertJsonMissing([
            ProductResource::make($product3->refresh())->toArray(null),
        ]);

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/products', ['tags' => ['test-tag']]);
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            ProductResource::make($product->refresh())->toArray(null),
        ]);
        $this->response->assertJsonMissing([
            ProductResource::make($product2->refresh())->toArray(null),
        ]);
        $this->response->assertJsonMissing([
            ProductResource::make($product3->refresh())->toArray(null),
        ]);

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/products', ['tags' => ['test-negative']]);
        $this->response->assertOk();
        $this->response->assertJsonMissing([
            ProductResource::make($product->refresh())->toArray(null),
        ]);
        $this->response->assertJsonMissing([
            ProductResource::make($product2->refresh())->toArray(null),
        ]);
        $this->response->assertJsonMissing([
            ProductResource::make($product3->refresh())->toArray(null),
        ]);
    }

    public function testTriggerEventManuallyForProductUsers(): void
    {
        Event::fake([ManuallyTriggeredEvent::class]);

        $student = $this->makeStudent();
        $product = Product::factory()->create();
        $product->users()->sync($student);
        FacadesTemplate::register(ManuallyTriggeredEvent::class, TestChannel::class, TestUserVariables::class);
        $template = Template::factory()->create([
            'channel' => TestChannel::class,
            'event' => ManuallyTriggeredEvent::class,
        ]);

        TemplateSection::factory(['key' => 'title', 'template_id' => $template->getKey()])->create();
        TemplateSection::factory(['key' => 'content', 'template_id' => $template->getKey(), 'content' => TestUserVariables::VAR_USER_EMAIL])->create();

        $admin = $this->makeAdmin();
        $this->response = $this->actingAs($admin, 'api')
            ->json('POST', "/api/admin/products/{$product->getKey()}/trigger-event-manually/{$template->getKey()}");
        $this->response->assertOk();
    }

    public function testTriggerEventManuallyForProductUsersInvalidTemplate(): void
    {
        Event::fake([ManuallyTriggeredEvent::class]);

        $student = $this->makeStudent();
        $product = Product::factory()->create();
        $product->users()->sync($student);
        FacadesTemplate::register(ManuallyTriggeredEvent::class, TestChannel::class, TestUserVariables::class);
        $template = Template::factory()->create([
            'channel' => TestChannel::class,
            'event' => ManuallyTriggeredEvent::class,
        ]);

        $admin = $this->makeAdmin();
        $this->response = $this->actingAs($admin, 'api')
            ->json('POST', "/api/admin/products/{$product->getKey()}/trigger-event-manually/{$template->getKey()}");
        $this->response->assertStatus(400);
    }

    public function testProductAvailableAndSoldQuantity(): void
    {
        $student = $this->makeStudent();
        $product = Product::factory()->create([
            'limit_total' => 10,
        ]);
        $product->users()->sync([$student->getKey() => ['quantity' => 2]]);
        $this
            ->json('GET', "/api/products/{$product->getKey()}")
            ->assertOk()
            ->assertJsonFragment([
                'id' => $product->getKey(),
                'available_quantity' => 8,
                'sold_quantity' => 2,
            ]);
    }

    public function testProductGrossPrice(): void
    {
        $product = Product::factory()->create([
            'price' => 1000,
            'tax_rate' => 23.0,
        ]);
        $this
            ->json('GET', "/api/products/{$product->getKey()}")
            ->assertOk()
            ->assertJsonFragment([
                'id' => $product->getKey(),
                'price' => 1000,
                'gross_price' => 1230,
            ]);
    }

    public function test_search_my_products_unauthorized(): void
    {
        $this->getJson('api/products/my')
            ->assertUnauthorized();
    }

    /**
     * @dataProvider myProductsFilterDataProvider
     */
    public function test_search_my_products(array $filters, callable $generator, int $filterCount): void
    {
        $user = $this->makeStudent();
        $generator($user)->each(fn($factory) => $factory->create());

        $this->actingAs($user, 'api')
            ->getJson('api/products/my?' . http_build_query($filters))
            ->assertOk()
            ->assertJsonCount($filterCount, 'data')
            ->assertJsonStructure(['data' => [[
                'id',
                'type',
                'name',
                'is_active',
                'end_date',
                'end_date',
                'productables',
            ]]]);
    }

    public function testCancelSubscriptionActive(): void
    {
        Carbon::setTestNow(Carbon::now()->startOfDay());
        $user = $this->makeStudent();
        $product = Product::factory()->subscription()->state(['recursive' => true])->create();
        $product->users()->sync([$user->getKey() => ['end_date' => Carbon::now()->addDay(), 'status' => SubscriptionStatus::ACTIVE]]);

        $this->actingAs($user, 'api')
            ->postJson('api/products/cancel/' . $product->getKey())
            ->assertOk();

        $this->assertDatabaseHas('products_users', [
            'user_id' => $user->getKey(),
            'product_id' => $product->getKey(),
            'status' => SubscriptionStatus::CANCELLED,
            'end_date' => Carbon::now()->addDay()
        ]);
    }

    public function testCancelSubscriptionExpired(): void
    {
        Carbon::setTestNow(Carbon::now()->startOfDay());
        $user = $this->makeStudent();
        $product = Product::factory()->subscription()->state(['recursive' => true])->create();
        $product->users()->sync([$user->getKey() => ['end_date' => Carbon::now()->subDay(), 'status' => SubscriptionStatus::EXPIRED]]);

        $this->actingAs($user, 'api')
            ->postJson('api/products/cancel/' . $product->getKey())
            ->assertOk();

        $this->assertDatabaseHas('products_users', [
            'user_id' => $user->getKey(),
            'product_id' => $product->getKey(),
            'status' => SubscriptionStatus::EXPIRED,
            'end_date' => Carbon::now()->subDay()
        ]);
    }

    public function testCancelSubscriptionNotFound(): void
    {
        $user = $this->makeStudent();

        $this->actingAs($user, 'api')
            ->postJson('api/products/cancel/123')
            ->assertNotFound();
    }

    public function testCancelSubscriptionForbidden(): void
    {
        $user = config('auth.providers.users.model')::factory()->create();

        $this->actingAs($user, 'api')
            ->postJson('api/products/cancel/123')
            ->assertForbidden();
    }

    public function testCancelSubscriptionUnauthorized(): void
    {
        $this->postJson('api/products/cancel/123')
            ->assertUnauthorized();
    }

    public function myProductsFilterDataProvider(): array
    {
        return [
            [
                'filter' => [],
                'data' => (function (User $user) {
                    $tasks = collect();
                    $tasks->push(Product::factory()->count(5)->hasAttached(User::factory()->count(2)));
                    $tasks->push(Product::factory()->count(3)->hasAttached($user));

                    return $tasks;
                }),
                'filterCount' => 3,
            ],
            [
                'filter' => [
                    'type' => 'subscription',
                ],
                'data' => (function (User $user) {
                    $tasks = collect();
                    $tasks->push(Product::factory()->count(5)->hasAttached(User::factory()->count(2)));
                    $tasks->push(Product::factory()->count(2)->hasAttached($user));
                    $tasks->push(Product::factory()->state(['type' => 'subscription'])->count(1)->hasAttached($user));

                    return $tasks;
                }),
                'filterCount' => 1,
            ],
            [
                'filter' => [
                    'active' => false,
                ],
                'data' => (function (User $user) {
                    $tasks = collect();
                    $tasks->push(Product::factory()->count(5)->hasAttached(User::factory()->count(2)));
                    $tasks->push(Product::factory()->count(3)->hasAttached($user, ['end_date' => Carbon::now()->addMonth()]));
                    $tasks->push(Product::factory()->count(1)->hasAttached($user, ['end_date' => null]));
                    $tasks->push(Product::factory()->count(2)->hasAttached($user, ['end_date' => Carbon::now()->subMonth()]));

                    return $tasks;
                }),
                'filterCount' => 2,
            ],
            [
                'filter' => [
                    'active' => true,
                ],
                'data' => (function (User $user) {
                    $tasks = collect();
                    $tasks->push(Product::factory()->count(5)->hasAttached(User::factory()->count(2)));
                    $tasks->push(Product::factory()->count(3)->hasAttached($user, ['end_date' => Carbon::now()->addMonth()]));
                    $tasks->push(Product::factory()->count(1)->hasAttached($user, ['end_date' => null]));
                    $tasks->push(Product::factory()->count(2)->hasAttached($user, ['end_date' => Carbon::now()->subMonth()]));

                    return $tasks;
                }),
                'filterCount' => 4,
            ],
        ];
    }
}
