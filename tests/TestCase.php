<?php

namespace EscolaLms\Cart\Tests;

use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use EscolaLms\Auth\Tests\Models\Client;
use EscolaLms\Cart\EscolalmsCartServiceProvider;
use EscolaLms\Cart\Models\User;
use EscolaLms\Cart\Providers\AuthServiceProvider;
use EscolaLms\Payments\Providers\PaymentsServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends \EscolaLms\Core\Tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Passport::useClientModel(Client::class);
    }

    protected function getPackageProviders($app)
    {
        return [
            ...parent::getPackageProviders($app),
            EscolaLmsAuthServiceProvider::class,
            PermissionServiceProvider::class,
            PassportServiceProvider::class,
            AuthServiceProvider::class,
            PaymentsServiceProvider::class,
            EscolalmsCartServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('passport.client_uuids', false);
        $app['config']->set('app.debug', env('APP_DEBUG', true));
    }
}
