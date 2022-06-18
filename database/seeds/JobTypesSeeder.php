<?php

use Illuminate\Database\Seeder;
use App\Domains\Auth\Models\JobTypes;

class JobTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        JobTypes::create([
            'id' => 1,
            'name' => 'Banner',
        ]);
    }
}
