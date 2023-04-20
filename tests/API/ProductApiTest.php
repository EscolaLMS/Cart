<?php

namespace EscolaLms\Cart\Tests\API;

use EscolaLms\Cart\Database\Seeders\CartPermissionSeeder;
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
}
