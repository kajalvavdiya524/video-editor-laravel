<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Settings;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Exceptions\GeneralException;
use App\Http\Controllers\Controller;
use App\Domains\Auth\Models\MyException;
use App\Domains\Auth\Services\ExceptionService;

/**
 * Class ExceptionController.
 */
class ExceptionController extends Controller
{
    /**
     * ExceptionController constructor.
     *
     */
    public function __construct(ExceptionService $exceptionService)
    {
        $this->exceptionService = $exceptionService;
    }

    public function generate_exceptions_report()
    {
        return $this->exceptionService->generate_exceptions_report("exceptions_".date("Y_m_d").".xlsx");
    }

    /**
     * @param  MyException  $exception
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function destroy(Request $request, MyException $exception)
    {
        $this->exceptionService->destroy($exception);

        return redirect()->route('admin.auth.settings.advanced.deleted')->withFlashSuccess(__('The exception was successfully deleted.'));
    }
}
