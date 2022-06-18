<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use TruncateTable;

    /**
     * Seed the application's database.
     */
    public function run()
    {
        Model::unguard();

        $this->truncateMultiple([
            'failed_jobs',
            'ledgers',
        ]);

        $this->call(AuthSeeder::class);
        // $this->call(AnnouncementSeeder::class);

        Model::reguard();
    }
}
