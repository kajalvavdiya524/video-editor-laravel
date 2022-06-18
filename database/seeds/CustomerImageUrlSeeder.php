<?php

use App\Domains\Auth\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerImageUrlSeeder extends Seeder
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
        Customer::where('value', 'generic')->update(['image_url' => 'img/customers/generic.png']);
        Customer::where('value', 'amazon')->update(['image_url' => 'img/customers/amazon.png']);
        Customer::where('value', 'amazon_fresh')->update(['image_url' => 'img/customers/amazon_fresh.png']);
        Customer::where('value', 'kroger')->update(['image_url' => 'img/customers/kroger.png']);
        Customer::where('value', 'superama')->update(['image_url' => 'img/customers/superama.png']);
        Customer::where('value', 'target')->update(['image_url' => 'img/customers/target.png']);
        Customer::where('value', 'walmart')->update(['image_url' => 'img/customers/walmart.png']);
        Customer::where('value', 'mrhi')->update(['image_url' => 'img/customers/mrhi.png']);
        Customer::where('value', 'instagram')->update(['image_url' => 'img/customers/instagram.png']);
        Customer::where('value', 'pilot')->update(['image_url' => 'img/customers/pilot.png']);
        $this->enableForeignKeys();
    }
}
