<?php

use App\Domains\Auth\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    
    use DisableForeignKeys;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->disableForeignKeys();
        Customer::create([
            'name' => 'Generic',
            'value' => 'generic', 
        ]);
        Customer::create([
            'name' => 'Amazon',
            'value' => 'amazon', 
        ]);
        Customer::create([
            'name' => 'Amazon Fresh',
            'value' => 'amazon_fresh',
        ]);
        Customer::create([
            'name' => 'Kroger',
            'value' => 'kroger',
        ]);
        Customer::create([
            'name' => 'Superama',
            'value' => 'superama',
        ]);
        Customer::create([
            'name' => 'Target',
            'value' => 'target',
        ]);
        Customer::create([
            'name' => 'Walmart',
            'value' => 'walmart', 
        ]);
        Customer::create([
            'name' => 'Mobile Ready Hero',
            'value' => 'mrhi', 
        ]);
        Customer::create([
            'name' => 'Instagram',
            'value' => 'instagram', 
        ]);
        Customer::create([
            'name' => 'Pilot',
            'value' => 'pilot', 
        ]);
        Customer::create([
            'name' => 'Sam\'s club',
            'value' => 'sam', 
        ]);
        $this->enableForeignKeys();
    }
}
