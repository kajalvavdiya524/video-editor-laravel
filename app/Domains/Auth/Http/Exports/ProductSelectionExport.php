<?php

namespace App\Domains\Auth\Http\Exports;

use App\Domains\Auth\Models\ProductSelection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductSelectionExport implements FromCollection
{
    public function collection()
    {
        $user = auth()->user();
        $company_id = $user->company_id;
        if ($user->isMasterAdmin()) {
            return ProductSelection::leftJoin('files', 'product_selections.file_id', '=', 'files.id')
                        ->select('product_selections.id', 'files.name', 'product_selections.count')
                        ->get()
                        ->prepend(['ID', 'Filename', 'Selection Count']);
        } else {
            return ProductSelection::where('company_id', $user->company_id)
                        ->leftJoin('files', 'product_selections.file_id', '=', 'files.id')
                        ->select('product_selections.id', 'files.filename', 'product_selections.count')
                        ->get()
                        ->prepend(['ID', 'Filename', 'Selection Count']);
        }
    }
}