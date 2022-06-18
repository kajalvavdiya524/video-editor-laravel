<?php

namespace App\Domains\Auth\Http\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class TemplateHeaderExport implements FromCollection
{
    public function collection()
    {
        return collect([config('templates.xlsx_columns')]);
    }
}