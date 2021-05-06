<?php


namespace EscolaSoft\Cart\Tests;


use EscolaSoft\Cart\CartServiceProvider;
use EscolaSoft\Cart\Tests\Models\User;
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
            CartServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('passport.client_uuids', false);
    }
}