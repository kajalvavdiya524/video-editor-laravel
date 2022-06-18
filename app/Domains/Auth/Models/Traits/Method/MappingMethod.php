<?php

namespace App\Domains\Auth\Models\Traits\Method;

use Illuminate\Support\Collection;

/**
 * Trait MappingMethod.
 */
trait MappingMethod
{
    
    /**
     * @return Mapping
     */
    public static function getMapping($file_id)
    {
        $user = auth()->user();
        $new_file_id = $file_id;
        if (substr($file_id, 0, 3) == "000") {
            $new_file_id = substr($file_id, 3, strlen($file_id) - 3);
        } else if (substr($file_id, 0, 2) == "00") {
            $new_file_id = substr($file_id, 2, strlen($file_id) - 2);
        }
        $query = self::where("UPC", "like", $new_file_id."%")
                    ->orWhere("ASIN", $new_file_id);
        if (!$user->isMasterAdmin()) {
            $query->whereIn("company_id", [0, $user->company_id]);
        }
        return $query->first();
    }
    
    /**
     * @return Mapping
     */
    public static function getMappings($file_id)
    {
        $user = auth()->user();
        $query = self::where("UPC", $file_id)
                    ->orWhere("ASIN", $file_id);
        if (!$user->isMasterAdmin()) {
            $query->whereIn("company_id", [0, $user->company_id]);
        }
        return $query->get();
    }

}
