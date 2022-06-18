<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Settings;

use App\Domains\Auth\Http\Requests\Backend\Upload\DeleteUploadFileRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Exceptions\GeneralException;
use App\Http\Controllers\Controller;
use App\Domains\Auth\Models\Setting;
use App\Domains\Auth\Models\UrlsFile;
use App\Domains\Auth\Models\File;
use App\Domains\Auth\Models\ScheduledFile;
use App\Domains\Auth\Services\UpdateFileService;
use App\Domains\Auth\Services\SettingsService;
use App\Domains\Auth\Services\LoadingService;

use Session;

/**
 * Class UpdateFileSettingsController.
 */
class UpdateFileSettingsController extends Controller
{
    /**
     * @var SettingsService
     */
    protected $settingsService;

    /**
     * @var UpdateFileService
     */
    protected $updateFileService;

    /**
     * @var loadingService
     */
    protected $loadingService;

    /**
     * UpdateFileSettingsController constructor.
     *
     * @param  UpdateFileService  $updateFileService
     * @param  SettingsService  $settingsService
     * @param  LoadingService $loadingService
     */
    public function __construct(UpdateFileService $updateFileService, SettingsService $settingsService, LoadingService $loadingService)
    {
        $this->updateFileService = $updateFileService;
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

        return view('backend.auth.settings.updatefile.index', [
                        'settings' => $settings, 
                        'file_all' => $file_all, 
                        'file_with_dimensions' => $file_with_dimensions, 
                        'file_with_child' => $file_with_child, 
                        'file_with_child_no_dimensions' => $file_with_child_no_dimensions
                    ]
        );
    }

    public function upload_file()
    {
        $urls_file = $this->updateFileService->store([
            'filename' => $_FILES['upload_file']['name'], 
            'tmp_name' => $_FILES['upload_file']['tmp_name'],
            'scheduled' => 0
        ]);
        $data = array('url' => siteUrl().'/share?file=uploads/'.auth()->user()->company_id.'/'.$urls_file->filename);
        $subject = 'RapidAds - New Files Requested';
        $this->settingsService->send_email('backend.includes.mail', $subject, $data);
        return redirect()->route('admin.auth.settings.updatefile.index')->withFlashSuccess(__('The url file was successfully uploaded.'));
    }

    public function save_data_import_settings(Request $request) 
    {
        $data = [
            "onetime_import_image_type" => $request->onetime_import_image_type, 
            "onetime_product_image" => $request->onetime_product_image,
            "onetime_nf_image" => $request->onetime_nf_image, 
            "onetime_ingredient_image" => $request->onetime_ingredient_image, 
            "scheduled_import_file_type" => $request->scheduled_import_file_type, 
            "scheduled_column_url" => $request->scheduled_column_url, 
            "scheduled_column_name" => $request->scheduled_column_name, 
            "scheduled_product_image" => $request->scheduled_product_image, 
            "scheduled_nf_image" => $request->scheduled_nf_image, 
            "scheduled_ingredient_image" => $request->scheduled_ingredient_image
        ];

        foreach ($data as $key => $value) {
            if ($this->settingsService->getbyKey($key) !== null) {
                $this->settingsService->update([$key => $value]);
            } else {
                $this->settingsService->store([$key => $value]);
            }
        }
        
        return redirect()->route('admin.auth.settings.updatefile.index')->withFlashSuccess(__('The Data Import Settings were successfully saved.'));
 
    }

    public function get_files_from_sftp(Request $request) {
        $filelist = unserialize($request->filelist);
        foreach($filelist as $file) {
            ScheduledFile::firstOrCreate(['filename' => $file], ['status' => 0]);
        }
    }

    public function run_schedule() {
        $sftp_address = $this->settingsService->getbyKey('sftp_address');
        $sftp_port = $this->settingsService->getbyKey('sftp_port');
        $sftp_dir_path = $this->settingsService->getbyKey('sftp_dir_path');
        $sftp_username = $this->settingsService->getbyKey('sftp_username');
        $sftp_password = $this->settingsService->getbyKey('sftp_password');
        $command = "php scripts/schedule.php"
                    ." --address ".$sftp_address
                    ." --port ".$sftp_port
                    ." --directory ".$sftp_dir_path
                    ." --username ".$sftp_username
                    ." --password ".$sftp_password;

        $log = shell_exec($command);
        $this->loop_schedule();
        return redirect()->route('admin.auth.settings.updatefile.index');
    }

    public function loop_schedule() {
        $scheduled_file = ScheduledFile::where('status', 0)->first();
        if (!$scheduled_file) {
            return 0;
        }

        $urls_file = $this->updateFileService->store([
            'filename' => $scheduled_file->filename, 
            'tmp_name' => $scheduled_file->filename,
            'scheduled' => 1
        ]); 

        $scheduled_file->status = 1;
        $scheduled_file->save();

        $this->updateFileService->get_files($urls_file); 
        return 1;
    }

    /**
     * @param  Request  $request
     */
    public function download_list(Request $request) {
        $urls_file = UrlsFile::where('id', $request->id)->first();
        return Storage::disk('s3')->download($urls_file->zip_file_url); // zip_file_url = list_file_url
    }

    /**
     * @param  Request  $request
     */
    public function download_files(Request $request) {
        return $this->updateFileService->download_files($request->id, $request->type);
    }

    public function download_image_dimension()
    {
        return Storage::disk('s3')->download('templates/Image and Dimensions Template.xlsx');
    }

    /**
     * @param  Request  $request
     */
    public function get_files(Request $request) {
        $urlsfile_id = $request->post('id');
        $urls_file = UrlsFile::where('id', $urlsfile_id)->first();
        return $this->updateFileService->get_files($urls_file);
    }

    /**
     * @param  DeleteUploadFileRequest  $request
     * @param  UrlsFile  $urls_file
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function destroy(DeleteUploadFileRequest $request, UrlsFile $urls_file)
    {
        $this->updateFileService->delete($urls_file);

        return redirect()->route('admin.auth.settings.updatefile.deleted')->withFlashSuccess(__('The url file was successfully deleted.'));
    }

    public function export_file_list(Request $request) 
    {
        return $this->updateFileService->export_file_list($request->type, "file_".$request->type.".xlsx");
    }

    public function update_schedule(Request $request)
    {
        $data = [
            'sftp_address' => $request->ftp_address, 
            'sftp_port' => $request->ftp_port, 
            'sftp_dir_path' => $request->dir_path, 
            'sftp_username' => $request->username, 
            'sftp_password' => $request->password, 
            'scheduled_days_of_week' => implode(',', $request->scheduled_days_of_week), 
            'scheduled_time' => $request->scheduled_time
        ];

        foreach ($data as $key => $value) {
            if ($this->settingsService->getbyKey($key) !== null) {
                $this->settingsService->update([$key => $value]);
            } else {
                $this->settingsService->store([$key => $value]);
            }
        }
        
        return redirect()->route('admin.auth.settings.updatefile.index')->withFlashSuccess(__('The URL Files retrieval schedule configuration was successfully updated.'));
    }

    public function update_image_uploading_progress(Request $request) 
    {
        $total = $request->get('total');
        $current = $request->get('current');
        $this->updateFileService->store_upload_progress('{ "current": '.$current.', "total": '.$total.' }');
    }

    public function finish_compressing(Request $request) 
    {
        return $this->updateFileService->finish_compressing($request);
    }

    public function ajax_check_compressing() {
        $zip_filename = $this->settingsService->getbyKey('zip_filename');
        $count = $this->settingsService->getbyKey('compressing');
        if (!$count || !$zip_filename) {
            return 0;
        }
        Setting::where('key', 'zip_filename')->delete();
        Setting::where('key', 'compressing')->delete();
        return array(
            'filename' => $zip_filename,
            'count' => $count
        );
    }

    public function ajax_uploading_progress() {
        return $this->updateFileService->get_upload_progress();
    }

    public function stop_upload_progress() {
        $this->updateFileService->stop_upload_progress();
        
        $scheduled_import_file_type = $this->settingsService->getbyKey('scheduled_import_file_type');
        if ($scheduled_import_file_type != 0) {
            $this->loop_schedule();
        }
    }

    public function download_mapping()
    {
        return Storage::disk('s3')->download('templates/ID Mapping Template.xlsx');
    }

    public function export_mapping()
    {
        return $this->settingsService->exportMapping('mappings.xlsx');
    }

    public function mapping(Request $request)
    {
        if ($request->action == 'delete') {
            $this->settingsService->deleteMapping();
            return redirect()->route('admin.auth.settings.updatefile.index')->withFlashSuccess(__('The mapping data were successfully deleted.'));
        } else {
            $this->settingsService->updateMapping($request->file('mapping'));
            $this->loadingService->store([
                'filename' => $_FILES['mapping']['name'], 
                'tmp_name' => $_FILES['mapping']['tmp_name'], 
                'type' => 'Mapping'
            ]);
            return redirect()->route('admin.auth.settings.updatefile.index')->withFlashSuccess(__('The mapping data were successfully updated.'));
        }
    }
}
