<?php

namespace EscolaSoft\Cart\Tests\API;

use EscolaLms\Courses\Models\Course;
use EscolaSoft\Cart\Tests\Models\User;
use EscolaSoft\Cart\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class CartApiTest extends TestCase
{
    use WithoutMiddleware, DatabaseTransactions;

    private $response;

    /**
     * @test
     */
    public function test_cart_items_list()
    {
        $course = Course::factory()->create();
        $user = User::factory()->create();
        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/course/' . $course->getKey());
        $this->response->assertStatus(200);

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/cart');
        $this->assertObjectHasAttribute('items', $this->response->getData());
        $this->assertNotEmpty($this->response->getData()->items);
        $cartItemsId = array_map(function ($item) {
            return $item->id;
        }, $this->response->getData()->items);
        $this->assertTrue(in_array($course->getKey(), $cartItemsId));
    }

    public function test_add_course_to_cart()
    {
        $course = Course::factory()->create();
        $user = User::factory()->create();

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/course/' . $course->getKey());
        $this->response->assertStatus(200);
        $this->assertTrue((bool)$user->cart->getKey());
        $this->assertTrue(in_array($course->getKey(), $user->cart->items->pluck('buyable_id')->toArray()));
    }
}