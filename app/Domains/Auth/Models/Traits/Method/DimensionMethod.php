<?php

namespace App\Domains\Auth\Models\Traits\Method;

use Illuminate\Support\Collection;

/**
 * Trait DimensionMethod.
 */
trait DimensionMethod
{
    
    /**
     * @return Dimension
     */
    public static function getDimension($file_id)
    {
        $user = auth()->user();
        $gtin = $file_id;
        if (strlen($file_id) == 10 && is_numeric($file_id)) {
            $query = self::where("GTIN", "like", "000".$file_id."_");
        } else {
            $query = self::where("GTIN", $file_id);
        }
        if (!$user->isMasterAdmin()) {
            $query->whereIn("company_id", [0, $user->company_id]);
        }
        return $query->first();
    }

    /**
     * @return string
     */
    public static function getGTIN($upc)
    {
        $user = auth()->user();
        $query = self::where("GTIN", "LIKE", "000".$upc."%")
                    ->orWhere("GTIN", "LIKE", "00".$upc."%")
                    ->orWhere("GTIN", "LIKE", "0".$upc."%");
        if (!$user->isMasterAdmin()) {
            $query->whereIn("company_id", [0, $user->company_id]);
        }
        $dimension = $query->first();
        if ($dimension) {
            return $dimension->GTIN;
        }
        return null;
    }
    
    /**
     * @return array
     */
    public static function getGTINs($upc)
    {
        $user = auth()->user();
        $query = self::where("GTIN", "LIKE", "000".$upc."%")
                    ->orWhere("GTIN", "LIKE", "00".$upc."%")
                    ->orWhere("GTIN", "LIKE", "0".$upc."%");
        if (!$user->isMasterAdmin()) {
            $query->whereIn("company_id", [0, $user->company_id]);
        }
        $dimensions = $query->get();
        if (! $dimensions) return null;
        $result = array();
        foreach ($dimensions as $dimension) {
            $result[] = $dimension->GTIN;
        }
        return $result;
    }

}
