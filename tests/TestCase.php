<?php

namespace EscolaLms\Cart\Tests;

use EscolaLms\Cart\CartServiceProvider;
use EscolaLms\Cart\Tests\Models\User;
use EscolaLms\Payments\Providers\PaymentsServiceProvider;
use Laravel\Passport\PassportServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends \EscolaLms\Core\Tests\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ...parent::getPackageProviders($app),
            PermissionServiceProvider::class,
            PassportServiceProvider::class,
            CartServiceProvider::class,
            PaymentsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('passport.client_uuids', false);
        $app['config']->set('app.debug', env('APP_DEBUG', true));
    }
}
