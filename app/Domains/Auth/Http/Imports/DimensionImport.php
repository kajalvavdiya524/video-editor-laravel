<?php

namespace App\Domains\Auth\Http\Imports;

use App\Domains\Auth\Models\Dimension;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DimensionImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $company_id = auth()->user()->company_id;

        foreach ($rows as $row) 
        {
            $GTIN = $row[0];
            $width = $row[1];
            $height = $row[2];

            if ($GTIN != 'GTIN') {
                $exist = Dimension::where('GTIN', $GTIN)->first();
                if ($exist && $exist->company_id == $company_id) {
                    $exist->width = $width;
                    $exist->height = $height;
                    $exist->save();
                } else {
                    Dimension::create([
                        'GTIN' => $GTIN,
                        'width' => $width,
                        'height' => $height,
                        'company_id' => $company_id,
                    ]);
                }
            }
        }
    }
}