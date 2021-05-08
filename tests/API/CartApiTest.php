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

    /**
     * @test
     */
//    public function test_cart_items_list()
//    {
//
//    }

    public function test_add_course_to_cart()
    {
        $course = Course::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->json('post', '/api/cart/course/' . $course->getKey());
        $response->assertStatus(200);
        $this->assertTrue((bool)$user->cart->getKey());
        $this->assertTrue(in_array($course->getKey(), $user->cart->items->pluck('buyable_id')->toArray()));
    }
}