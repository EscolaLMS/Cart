<?php

namespace EscolaLms\Cart\Database\Seeders;

use EscolaLms\Cart\Enums\CartPermissionsEnum;
use EscolaLms\Core\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CartPermissionSeeder extends Seeder
{
    public function run()
    {
        $admin   = Role::findOrCreate(UserRole::ADMIN, 'api');
        $tutor   = Role::findOrCreate(UserRole::TUTOR, 'api');

        foreach (CartPermissionsEnum::asArray() as $const => $value) {
            Permission::findOrCreate($value, 'api');
        }

        $admin->givePermissionTo([
            CartPermissionsEnum::LIST_ALL_ORDERS,
        ]);
        $tutor->givePermissionTo([
            CartPermissionsEnum::LIST_AUTHORED_COURSE_ORDERS,
        ]);
    }
}
