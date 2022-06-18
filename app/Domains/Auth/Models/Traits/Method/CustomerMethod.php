<?php

namespace App\Domains\Auth\Models\Traits\Method;

use Illuminate\Support\Collection;
use App\Domains\Auth\Models\Template;
/**
 * Trait CustomerMethod.
 */
trait CustomerMethod
{
    
    /**
     * @return bool
     */
    public function hasTemplate($aTemplate)
    {
        $template = Template::find($aTemplate);
        if ($template){
            if ($template->customer_id == $this->id)
                return true;
            else 
                return false;
        }

        return false;
    }
}
