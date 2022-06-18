<?php

use App\Domains\Auth\Models\Company;
use Illuminate\Database\Seeder;

/**
 * Class CompanyTableSeeder.
 */
class CompanySeeder extends Seeder
{
    use DisableForeignKeys;

    /**
     * Run the database seed.
     */
    public function run()
    {
        $this->disableForeignKeys();

        // Add the test company
        Company::create([
            'name' => 'test company',
            'address' => 'test company address',
            'active' => true,
            'notification_emails' => '',
            'has_mrhi' => 0
        ]);
        Company::create([
            'name' => 'test company2',
            'address' => 'test2 company address',
            'active' => true,
            'notification_emails' => '',
            'has_mrhi' => 0
        ]);

        $this->enableForeignKeys();
    }
}
