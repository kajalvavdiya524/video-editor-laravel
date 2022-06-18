<?php

namespace App\Domains\Auth\Models\Traits\Method;

use Illuminate\Support\Collection;

/**
 * Trait ParentChildMethod.
 */
trait ParentChildMethod
{
    
    /**
     * @return bool
     */
    public static function getByParent($gtin)
    {
        $user = auth()->user();
        $query = self::where("parent", $gtin);
        if (!$user->isMasterAdmin()) {
            $query->whereIn("company_id", [0, $user->company_id]);
        }
        return $query->first();
    }
    
    /**
     * @return bool
     */
    public static function getByParents($gtin)
    {
        $user = auth()->user();
        $query = self::where("parent", $gtin);
        if (!$user->isMasterAdmin()) {
            $query->whereIn("company_id", [0, $user->company_id]);
        }
        return $query->get();
    }

}
