<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Settings;

use App\Domains\Auth\Http\Requests\Backend\Loading\DeleteLoadingHistoryRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Exceptions\GeneralException;
use App\Http\Controllers\Controller;
use App\Domains\Auth\Models\LoadingHistory;
use App\Domains\Auth\Services\LoadingService;
use Mail;

/**
 * Class LoadingHistoryController.
 */
class LoadingHistoryController extends Controller
{

    /**
     * @var loadingService
     */
    protected $loadingService;

    /**
     * LoadingHistoryController constructor.
     *
     * @param  LoadingService $loadingService
     */
    public function __construct(LoadingService $loadingService)
    {
        $this->loadingService = $loadingService;
    }

    /**
     * @param  LoadingHistory  $loading_history
     */
    public function download_file(LoadingHistory $loading_history) {
        return Storage::disk('s3')->download($loading_history->url);
    }

    /**
     * @param  DeleteLoadingHistoryRequest  $request
     * @param  LoadingHistory  $loading_history
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function destroy(DeleteLoadingHistoryRequest $request, LoadingHistory $loading_history)
    {
        $this->loadingService->delete($loading_history);

        return redirect()->route('admin.auth.settings.updatefile.deleted')->withFlashSuccess(__('The uploaded file was successfully deleted.'));
    }
}
