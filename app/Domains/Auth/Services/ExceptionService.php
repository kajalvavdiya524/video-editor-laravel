<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\MyException;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use App\Domains\Auth\Http\Exports\ExceptionExport;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Class ExceptionService.
 */
class ExceptionService extends BaseService
{
    /**
     * ExceptionService constructor.
     *
     * @param  MyException  $exception
     */
    public function __construct(MyException $exception)
    {
        $this->model = $exception;
    }

    /**
     * @param  array  $data
     *
     * @return MyException
     * @throws GeneralException
     * @throws \Throwable
     */
    public function store(array $data = []): MyException
    {
        DB::beginTransaction();

        try {
            $exception = $this->createException([
                'file_id' => $data['file_id'], 
                'company_id' => auth()->user()->company_id,
                'user_id' => auth()->user()->id,
                'message' => $data['message']
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem creating this exception. Please try again.'));
        }

        DB::commit();

        return $exception;
    }

    /**
     * @param  MyException  $exception
     *
     * @return bool
     * @throws GeneralException
     */
    public function destroy(MyException $exception): bool
    {
        if ($exception->forceDelete()) {
            return true;
        }

        throw new GeneralException(__('There was a problem permanently deleting this exception. Please try again.'));
    }

    public function generate_exceptions_report($file)
    {
        return Excel::download(new ExceptionExport, $file);
    }
    
    /**
     * @param  array  $data
     *
     * @return MyException
     */
    protected function createException(array $data = []): MyException
    {
        return $this->model::create($data);
    }
}
