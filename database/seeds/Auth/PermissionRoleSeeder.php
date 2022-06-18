<?php

use App\Domains\Auth\Models\Permission;
use App\Domains\Auth\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Class PermissionRoleTableSeeder.
 */
class PermissionRoleSeeder extends Seeder
{
    use DisableForeignKeys;

    /**
     * Run the database seed.
     */
    public function run()
    {
        $this->disableForeignKeys();

        // Create Roles
        Role::create([
            'id' => 1,
            'name' => config('boilerplate.access.role.admin'),
        ]);

        $companyAdmin = Role::create([
            'id' => 2,
            'name' => config('boilerplate.access.role.company_admin'),
        ]);

        Role::create([
            'id' => 3,
            'name' => config('boilerplate.access.role.member'),
        ]);

        // Non Grouped Permissions
        $view_backend = Permission::create([
            'name' => 'view backend',
            'description' => 'Access Administration',
        ]);

        // Companies category
        $companies = Permission::create([
            'name' => 'access.company',
            'description' => 'All Company Permissions',
        ]);

        $companies->children()->saveMany([
            new Permission([
                'name' => 'access.company.list',
                'description' => 'View Companies',
            ]),
            new Permission([
                'name' => 'access.company.deactivate',
                'description' => 'Deactivate Companies',
                'sort' => 2,
            ]),
            new Permission([
                'name' => 'access.company.reactivate',
                'description' => 'Reactivate Companies',
                'sort' => 3,
            ]),
        ]);

        // Grouped permissions
        // Users category
        $users = Permission::create([
            'name' => 'access.user',
            'description' => 'All User Permissions',
        ]);

        $users->children()->saveMany([
            new Permission([
                'name' => 'access.user.list',
                'description' => 'View Users',
            ]),
            new Permission([
                'name' => 'access.user.deactivate',
                'description' => 'Deactivate Users',
                'sort' => 2,
            ]),
            new Permission([
                'name' => 'access.user.reactivate',
                'description' => 'Reactivate Users',
                'sort' => 3,
            ]),
            new Permission([
                'name' => 'access.user.clear-session',
                'description' => 'Clear User Sessions',
                'sort' => 4,
            ]),
            new Permission([
                'name' => 'access.user.impersonate',
                'description' => 'Impersonate Users',
                'sort' => 5,
            ]),
            new Permission([
                'name' => 'access.user.change-password',
                'description' => 'Change User Passwords',
                'sort' => 6,
            ]),
        ]);

        $companyAdmin->syncPermissions([$users, $view_backend]);

        // Assign Permissions to other Roles
        // Note: Admin (User 1) Has all permissions via a gate in the AuthServiceProvider
        // $user->givePermissionTo('view backend');

        $this->enableForeignKeys();
    }
}
