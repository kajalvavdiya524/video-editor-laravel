<?php

namespace App\Domains\Auth\Http\Imports;

use App\Domains\Auth\Models\ParentChild;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ParentChildImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $company_id = auth()->user()->company_id;
        foreach ($rows as $row) 
        {
            $parent = $row[0];
            $child = $row[1];
            if ($parent != 'ParentCaseGTIN' && $child != 'IndividualUnitGTIN') {
                $exist = ParentChild::where('parent', $parent)->first();
                if ($exist) {
                    $exist->child = $child;
                    $exist->save();
                } else {
                    ParentChild::create([
                        'parent' => $parent,
                        'child' => $child,
                        'company_id' => $company_id,
                    ]);
                }
            }
        }
    }
}