<?php
namespace App\Domains\Auth\Models\Traits\Method;

use Illuminate\Support\Collection;

/**
 * Trait ApiKeysMethod.
 */
trait ApiKeysMethod
{
    
    /**
     * @return 
     */
    public static function findByToken($aToken)
    {
        return self::where('key','=',$aToken)->first();
    }

}
