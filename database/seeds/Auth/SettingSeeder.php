<?php

use App\Domains\Auth\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Class SettingSeeder.
 */
class SettingSeeder extends Seeder
{
    use DisableForeignKeys;

    /**
     * Run the database seed.
     */
    public function run()
    {
        $this->disableForeignKeys();

        $settings = config('settings');
        foreach ($settings as $key => $value) {
            Setting::create([
                'key' => $key,
                'value' => $value
            ]);
        }
        // CTA_pos
        $templates = config('templates.AmazonFresh.CTA_pos');
        foreach ($templates as $key => $value) {
            foreach ($value as $k => $v) {
                $cta_pos_key = "AmazonFresh_".$key."_CTA_pos_".ucfirst($k);
                Setting::create([
                    'key' => $cta_pos_key,
                    'value' => $v
                ]);
            }
        }
        
        // Kroger 
        // Setting::create([
        //     'key' => "Kroger_themes",
        //     'value' => "fall,winter"
        // ]);
        // // circle_text_color
        // $templates = config('templates.Kroger.circle_text_color');
        // foreach ($templates as $key => $value) {
        //     foreach ($value as $k => $v) {
        //         $name = "Kroger_".$key."_circle_text_color_".$v["name"];
        //         Setting::create([
        //             'key' => $name,
        //             'value' => implode(',', $v)
        //         ]);
        //     }
        // }
        // // circle_text_color
        // $templates = config('templates.Kroger.burst_color');
        // foreach ($templates as $key => $value) {
        //     foreach ($value as $k => $v) {
        //         $name = "Kroger_".$key."_burst_color_".$v["name"];
        //         Setting::create([
        //             'key' => $name,
        //             'value' => implode(',', $v)
        //         ]);
        //     }
        // }

        $this->enableForeignKeys();
    }
}
