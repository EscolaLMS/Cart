<?php

namespace EscolaLms\Cart\Tests\API;

use EscolaLms\Cart\EscolaLmsCartServiceProvider;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Settings\Database\Seeders\PermissionTableSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;

class ConfigApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists(\EscolaLms\Settings\EscolaLmsSettingsServiceProvider::class)) {
            $this->markTestSkipped();
        }

        $this->seed(PermissionTableSeeder::class);

        Config::set('escola_settings.use_database', true);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('admin');
    }

    public function testAdministrableConfigApi()
    {
        Config::set(EscolaLmsCartServiceProvider::CONFIG_KEY . '.min_product_price', 0);
        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/config'
        );

        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'min_product_price' => [
                'full_key' => 'escolalms_cart.min_product_price',
                'key' => 'min_product_price',
                'rules' => [
                    'required',
                    'numeric',
                    'min:0',
                ],
                'value' => 0,
                'readonly' => false,
                'public' => true,
            ],
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'POST',
            '/api/admin/config',
            [
                'config' => [
                    [
                        'key' => 'escolalms_cart.min_product_price',
                        'value' => 10,
                    ],
                ]
            ]
        );
        $this->response->assertOk();

        $this->response = $this->json(
            'GET',
            '/api/config'
        );
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'escolalms_cart' => [
                'min_product_price' => 10,
            ]
        ]);
    }
}
