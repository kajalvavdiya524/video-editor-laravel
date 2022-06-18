<?php

namespace App\Domains\Auth\Http\Exports;

use App\Domains\Auth\Models\File;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class FileDimensionExport implements FromCollection
{
    public function collection()
    {
        $user = auth()->user();
        $company_id = $user->company_id;
        if ($user->isMasterAdmin()) {
            return File::where('has_dimension', 1)
                        ->select('name', 'path', 'ASIN', 'UPC', 'parent_gtin', 'child_gtin', 'width', 'height')
                        ->get()
                        ->prepend(['Name', 'Path', 'ASIN', 'UPC', 'ParentCaseGTIN', 'IndividualUnitGTIN', 'Width', 'Height']);
        } else {
            return File::where('has_dimension', 1)
                        ->where('company_id', $user->company_id)
                        ->select('name', 'path', 'ASIN', 'UPC', 'parent_gtin', 'child_gtin', 'width', 'height')
                        ->get()
                        ->prepend(['Name', 'Path', 'ASIN', 'UPC', 'ParentCaseGTIN ', 'IndividualUnitGTIN', 'Width', 'Height']);
        }
    }
}