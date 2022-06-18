<?php

use Illuminate\Database\Seeder;
use App\Domains\Auth\Models\JobStatus;

class JobStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        JobStatus::create([
            'id' => 1,
            'name' => 'Pending',
        ]);

        JobStatus::create([
            'id' => 2,
            'name' => 'Running',
        ]);

        JobStatus::create([
            'id' => 3,
            'name' => 'Done',
        ]);

        JobStatus::create([
            'id' => 4,
            'name' => 'Failed',
        ]);

        JobStatus::create([
            'id' => 5,
            'name' => 'Canceled',
        ]);
    }
}
