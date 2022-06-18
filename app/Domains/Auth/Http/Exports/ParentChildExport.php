<?php

namespace App\Domains\Auth\Http\Exports;

use App\Domains\Auth\Models\ParentChild;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class ParentChildExport implements FromCollection
{
    public function collection()
    {
        $user = auth()->user();
        if ($user->isMasterAdmin()) {
            return ParentChild::select('parent', 'child')
                            ->get()
                            ->prepend(['ParentCaseGTIN', 'IndividualUnitGTIN']);
        } else {
            return ParentChild::whereIn('company_id', [0, $user->company_id])
                            ->select('parent', 'child')
                            ->get()
                            ->prepend(['ParentCaseGTIN', 'IndividualUnitGTIN']);
        }
    }
}