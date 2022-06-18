<?php

namespace App\Domains\Auth\Models\Traits\Method;

use Illuminate\Support\Collection;

/**
 * Trait NewMappingMethod.
 */
trait NewMappingMethod
{
    /**
     * @return object
     */
    public static function getNewMapping($file_id)
    {
        $user = auth()->user();
        $query = self::where("GTIN", "like", "%" . $file_id)->orWhere("GTIN", "like", "%" . $file_id . "_");
        if (!$user->isMasterAdmin()) {
            $query->whereIn("company_id", [0, $user->company_id]);
        }
        $new_mapping = $query->first();
        while ($new_mapping && $new_mapping->child_links != "") {
            $gtin = explode(":", $new_mapping->child_links)[0];
            $new_mapping = self::where("GTIN", $gtin)->first();
        }
        return $new_mapping;
    }

    /**
     * @return array
     */
    public static function getChildLinks($file_id)
    {
        $child_list = [];
        $user = auth()->user();

        // child
        $query = self::where("GTIN", "like", "%" . $file_id)->orWhere("GTIN", "like", "%" . $file_id . "_");
        if (!$user->isMasterAdmin()) {
            $query->whereIn("company_id", [0, $user->company_id]);
        }
        $new_mapping = $query->first();
        $child_list[] = $new_mapping->GTIN;
        while ($new_mapping && $new_mapping->child_links != "") {
            $gtin = explode(":", $new_mapping->child_links)[0];
            $child_list[] = $gtin;
            $query = self::where("GTIN", $gtin);
            if (!$user->isMasterAdmin()) {
                $query->whereIn("company_id", [0, $user->company_id]);
            }
            $new_mapping = $query->first();
        }

        // parent
        if (count($child_list)) {
            $query = self::where("child_links", "like", $child_list[0] . ":%");
        } else {
            $query = self::where("child_links", "like", "%" . $file_id . ":%");
        }
        if (!$user->isMasterAdmin()) {
            $query->whereIn("company_id", [0, $user->company_id]);
        }
        $new_mapping = $query->first();
        while ($new_mapping) {
            $child_list[] = $new_mapping->GTIN;
            $query = self::where("child_links", "like", $new_mapping->GTIN . ":%");
            if (!$user->isMasterAdmin()) {
                $query->whereIn("company_id", [0, $user->company_id]);
            }
            $new_mapping = $query->first();
        }
        return $child_list;
    }
}
