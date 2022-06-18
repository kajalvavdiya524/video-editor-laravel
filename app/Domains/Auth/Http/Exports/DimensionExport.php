<?php

namespace App\Domains\Auth\Http\Exports;

use App\Domains\Auth\Models\Dimension;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class DimensionExport implements FromCollection
{
    public function collection()
    {
        $user = auth()->user();
        if ($user->isMasterAdmin()) {
            return Dimension::select('GTIN', 'width', 'height')
                            ->get()
                            ->prepend(['GTIN', 'width', 'height']);
        } else {
            return Dimension::whereIn('company_id', [0, $user->company_id])
                            ->select('GTIN', 'width', 'height')
                            ->get()
                            ->prepend(['GTIN', 'width', 'height']);
        }
    }
}