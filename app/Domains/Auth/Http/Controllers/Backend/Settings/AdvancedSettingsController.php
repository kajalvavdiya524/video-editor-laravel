<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Settings;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Domains\Auth\Services\SettingsService;
use App\Domains\Auth\Services\LoadingService;
use App\Domains\Auth\Models\File;
use App\Domains\Auth\Models\Setting;

/**
 * Class AdvancedSettingsController.
 */
class AdvancedSettingsController extends Controller
{
    /**
     * @var SettingsService
     */
    protected $settingsService;

    /**
     * @var loadingService
     */
    protected $loadingService;

    /**
     * AdvancedSettingsController constructor.
     *
     * @param  SettingsService  $settingsService
     * @param  LoadingService $loadingService
     */
    public function __construct(SettingsService $settingsService, LoadingService $loadingService)
    {
        $this->settingsService = $settingsService;
        $this->loadingService = $loadingService;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $settings = array();
        $db_settings = Setting::all();
        foreach ($db_settings as $row) {
            $settings[$row->key] = $row->value;
        }
        if (auth()->user()->isMasterAdmin()) {
            $query = File::select('files.*');
            $file_all = count(File::all());
            $file_with_dimensions = count(File::where('has_dimension', 1)->get());
            $file_with_child = count(File::where('has_child', 1)->get());
            $file_with_child_no_dimensions = count(File::where('has_child', 1)->where('has_dimension', 0)->get());
    
        } else {
            $file_all = count(File::where('company_id', auth()->user()->company_id)->get());
            $file_with_dimensions = count(File::where('company_id', auth()->user()->company_id)->where('has_dimension', 1)->get());
            $file_with_child = count(File::where('company_id', auth()->user()->company_id)->where('has_child', 1)->get());
            $file_with_child_no_dimensions = count(File::where('company_id', auth()->user()->company_id)->where('has_child', 1)->where('has_dimension', 0)->get());
        }

        return view('backend.auth.settings.advanced.index', [
                'settings' => $settings, 
                'file_all' => $file_all, 
                'file_with_dimensions' => $file_with_dimensions, 
                'file_with_child' => $file_with_child, 
                'file_with_child_no_dimensions' => $file_with_child_no_dimensions
            ]
        );
    }

    public function dimension(Request $request)
    {
        if ($request->action == 'delete') {
            $this->settingsService->deleteDimension();
            return redirect()->route('admin.auth.settings.advanced.index')->withFlashSuccess(__('The dimensions data were successfully deleted.'));
        } else {
            $this->settingsService->updateDimension($request->file('dimension'));
            $this->loadingService->store([
                'filename' => $_FILES['dimension']['name'], 
                'tmp_name' => $_FILES['dimension']['tmp_name'], 
                'type' => 'Dimensions'
            ]);
            return redirect()->route('admin.auth.settings.advanced.index')->withFlashSuccess(__('The dimensions data were successfully updated.'));
        }
    }

    public function download_dimension()
    {
        return Storage::disk('s3')->download('templates/Item Dimensions Template.xlsx');
    }

    public function export_dimension()
    {
        return $this->settingsService->exportDimension('dimensions.xlsx');
    }

    public function parent_child(Request $request)
    {
        if ($request->action == 'delete') {
            $this->settingsService->deleteParentChild();
            return redirect()->route('admin.auth.settings.advanced.index')->withFlashSuccess(__('The parent-child data were successfully deleted.'));
        } else {
            $this->settingsService->updateParentChild($request->file('parent_child'));
            $this->loadingService->store([
                'filename' => $_FILES['parent_child']['name'], 
                'tmp_name' => $_FILES['parent_child']['tmp_name'], 
                'type' => 'Parent-Child'
            ]);
            return redirect()->route('admin.auth.settings.advanced.index')->withFlashSuccess(__('The parent-child data were successfully updated.'));
        }
    }

    public function download_parent_child()
    {
        return Storage::disk('s3')->download('templates/Parent Child GTIN Template.xlsx');
    }

    public function export_parent_child()
    {
        return $this->settingsService->exportParentChild('parent_child.xlsx');
    }

    public function psd2png()
    {
        $this->settingsService->psd2png(isset($_POST['psd2png']) ? $_POST['psd2png'] : "off");
        return redirect()->route('admin.auth.settings.advanced.index');
    }
    
    public function notification_email() 
    {
        $emails = preg_replace('!\s+!', ',', $_POST['notification']);
        $data = [
            'Notification_Emails' => $emails
        ];

        if (auth()->user()->isMasterAdmin()) {
            if ($this->settingsService->getbyKey('Notification_Emails') !== null) {
                $this->settingsService->update($data);
            } else {
                $this->settingsService->store($data);
            }
        } else {
            $this->settingsService->update_notification_email($emails);
        }
        return redirect()->route('admin.auth.settings.advanced.index')->withFlashSuccess(__('The notification emails were successfully updated.'));
    }
}
