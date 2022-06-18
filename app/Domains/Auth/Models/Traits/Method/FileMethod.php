<?php

namespace App\Domains\Auth\Models\Traits\Method;

use Illuminate\Support\Collection;

/**
 * Trait FileMethod.
 */
trait FileMethod
{
    
    /**
     * @return bool
     */
    public static function getFile($file_id)
    {
        $user = auth()->user();
        $query = self::where("name", $file_id.".png")
                        // ->orWhere("name", $file_id.".jpg")
                        ->orWhere("name", "like", "0".$file_id."%")
                        ->orWhere("name", "like", $file_id."[_].png");
                        // ->orWhere("name", "like", $file_id."_.jpg");
        // $query = self::whereIn("name", [$file_id.".png", $file_id.".jpg"]);
        if (!$user->isMasterAdmin()) {
            $query->whereIn("company_id", [0, $user->company_id]);
        }
        return $query->first();
    }

}
