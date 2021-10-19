<?php

namespace EscolaLms\Cart\Tests;

use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use EscolaLms\Auth\Tests\Models\Client;
use EscolaLms\Cart\CartServiceProvider;
use EscolaLms\Cart\Providers\AuthServiceProvider;
use EscolaLms\Cart\Tests\Models\User as TestUser;
use EscolaLms\Courses\EscolaLmsCourseServiceProvider;
use EscolaLms\Payments\Providers\PaymentsServiceProvider;
use EscolaLms\Scorm\EscolaLmsScormServiceProvider;
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
            EscolaLmsCourseServiceProvider::class,
            EscolaLmsScormServiceProvider::class,
            PaymentsServiceProvider::class,
            CartServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('auth.providers.users.model', TestUser::class);
        $app['config']->set('passport.client_uuids', false);
        $app['config']->set('app.debug', env('APP_DEBUG', true));
    }
}
