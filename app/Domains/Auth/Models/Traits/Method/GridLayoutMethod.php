<?php

namespace App\Domains\Auth\Models\Traits\Method;


/**
 * Trait GridLayoutMethod.
 */
trait GridLayoutMethod
{

public function templates_lightweight(){

    $templates = $this->templates;
    $templates_lightweight = array();
    foreach ($templates as $template){
        
        $template->settings = $template->settings_lightweight();
        $templates_lightweight[]=$template;

        
    }

    return $templates_lightweight;

}


}