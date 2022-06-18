<?php

namespace App\Domains\Auth\Http\Exports;

use App\Domains\Auth\Models\MyException;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExceptionExport implements FromCollection
{
    public function collection()
    {
        $user = auth()->user();
        $company_id = $user->company_id;
        if ($user->isMasterAdmin()) {
            return MyException::leftJoin('companies', 'my_exceptions.company_id', '=', 'companies.id')
                            ->join('users', 'my_exceptions.user_id', '=', 'users.id')
                            ->select('my_exceptions.updated_at', 'file_id', 'companies.name', 'users.name AS username', 'message')
                            ->get()
                            ->prepend(['Date', 'File ID', 'Company', 'User', 'Message']);
        } else {
            return MyException::join('companies', 'my_exceptions.company_id', '=', 'companies.id')
                            ->join('users', 'my_exceptions.user_id', '=', 'users.id')
                            ->where('my_exceptions.company_id', $user->company_id)
                            ->select('my_exceptions.updated_at', 'file_id', 'companies.name', 'users.name AS username', 'message')
                            ->get()
                            ->prepend(['Date', 'File ID', 'Company', 'User', 'Message']);
        }
    }
}