<?php

namespace App\Domains\Auth\Http\Exports;

use App\Domains\Auth\Models\Mapping;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class MappingExport implements FromCollection
{
    public function collection()
    {
        $user = auth()->user();
        $company_id = $user->company_id;
        if ($user->isMasterAdmin()) {
            return Mapping::select('ASIN', 'UPC', 'TCIN', 'WMT_ID')
                            ->get()
                            ->prepend(['ASIN', 'UPC', 'TCIN', 'WMT_ID']);
        } else {
            return Mapping::whereIn('company_id', [0, $user->company_id])
                            ->select('ASIN', 'UPC', 'TCIN', 'WMT_ID')
                            ->get()
                            ->prepend(['ASIN', 'UPC', 'TCIN', 'WMT_ID']);
        }
    }
}