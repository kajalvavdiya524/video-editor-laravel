<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Positioning;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Domains\Auth\Services\PositioningService;
use App\Http\Controllers\Controller;

/**
 * Class PositioningController.
 */
class PositioningController extends Controller
{
    /**
     * @var PositioningService
     */
    protected $positioningService;

    /**
     * PositioningController constructor.
     *
     * @param  PositioningService  $positioningService
     */
    public function __construct(PositioningService $positioningService)
    {
        $this->positioningService = $positioningService;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('backend.auth.positioning.index');
    }

    public function upload(Request $request)
    {
        $this->positioningService->uploadXLSX($request->file('positioning'));
        return redirect()->route('admin.auth.positioning.index')->withFlashSuccess(__('XLSX file has been successfully updated.'));
    }

    public function export()
    {
        return view('backend.auth.positioning.index');
    }
}
