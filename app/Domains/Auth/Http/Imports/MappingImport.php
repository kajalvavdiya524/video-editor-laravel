<?php

namespace App\Domains\Auth\Http\Imports;

use App\Domains\Auth\Models\Mapping;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class MappingImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $company_id = auth()->user()->company_id;

        foreach ($rows as $row) 
        {
            $ASIN = $row[0];
            $UPC = $row[1];
            $TCIN = '';
            $WMT_ID = '';

            if ($ASIN != 'ASIN' && $UPC != 'UPC' && isset($ASIN) && isset($UPC)) {
                $exist = Mapping::where('ASIN', $ASIN)->first();
                if ($exist && $exist->company_id == $company_id) {
                    $exist->ASIN = $ASIN;
                    $exist->UPC = $UPC;
                    $exist->TCIN = $TCIN;
                    $exist->WMT_ID = $WMT_ID;
                    $exist->company_id = $company_id;
                    $exist->save();
                } else {
                    Mapping::create([
                        'ASIN' => $ASIN,
                        'UPC' => $UPC,
                        'TCIN' => $TCIN,
                        'WMT_ID' => $WMT_ID,
                        'company_id' => $company_id,
                    ]);
                }
            }
        }
    }
}