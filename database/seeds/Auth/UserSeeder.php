<?php

use App\Domains\Auth\Models\User;
use Illuminate\Database\Seeder;

/**
 * Class UserTableSeeder.
 */
class UserSeeder extends Seeder
{
    use DisableForeignKeys;

    /**
     * Run the database seed.
     */
    public function run()
    {
        $this->disableForeignKeys();

        // Add the master administrator, user id of 1
        User::create([
            'name' => 'Master Admin',
            'first_name' => 'Master',
            'last_name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => 'secret',
            'email_verified_at' => now(),
            'company_id' => 0,
            'active' => true,
        ]);

        if (app()->environment(['local', 'testing'])) {
            User::create([
                'name' => 'Company Admin',
                'first_name' => 'Company',
                'last_name' => 'Admin',
                'email' => 'admin@company.com',
                'password' => 'secret',
                'email_verified_at' => now(),
                'company_id' => 2,
                'active' => true,
            ]);
            User::create([
                'name' => 'Test User',
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'user@user.com',
                'password' => 'secret',
                'email_verified_at' => now(),
                'company_id' => 2,
                'active' => true,
            ]);
        }

        $this->enableForeignKeys();
    }
}
