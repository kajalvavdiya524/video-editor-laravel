<?php

use App\Domains\Auth\Models\Template;
use Illuminate\Database\Seeder;

class AmazonTemplateSeeder extends Seeder
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
        $names = [
            'Variety Pack - 1 Row',
            'Variety Pack - 2 Rows',
            'Nutrition Facts',
            'Nutrition Facts Horizontal',
            'Product Video',
            'Virtual Bundle'
        ];
        for ($i = 0; $i < count($names); $i++) {
            Template::create([
                'name' => $names[$i],
                'customer_id' => 2,
                'company_id' => 0,
                'status' => 1,
                'order' => 0,
                'image_url' => 'img/templates/Amazon/' . $i . '.png',
                'system' => true,
                'system_key' => $i
            ]);
        }
        $this->enableForeignKeys();
    }
}
