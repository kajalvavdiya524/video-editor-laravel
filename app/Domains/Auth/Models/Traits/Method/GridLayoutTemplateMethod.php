<?php

namespace App\Domains\Auth\Models\Traits\Method;

use Illuminate\Support\Collection;

/**
 * Trait GridLayoutTemplateMethod.
 */
trait GridLayoutTemplateMethod
{

public function settings_lightweight(){

    $settings = $this->settings;

    $settings_array = (json_decode($settings,true));
    
    foreach ($settings_array as $key => $value){

        if (!is_array($value)) {
          
            if(preg_match("/^.*\?file=.+$/",$value)) {
                $settings_array[$key] = $value."&lightweight";
            }
        
        }
      

    }

    return json_encode ($settings_array);
    

}


}