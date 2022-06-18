<?php

namespace App\Domains\Auth\Services;

use Illuminate\Support\Collection;
use App\Domains\Auth\Models\UrlsFile;
use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Models\Setting;
use App\Domains\Auth\Models\NewMapping;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Domains\Auth\Http\Imports\UrlsFileImport;
use App\Domains\Auth\Http\Imports\NewMappingImport;

use App\Domains\Auth\Http\Exports\FileAllExport;
use App\Domains\Auth\Http\Exports\FileDimensionExport;
use App\Domains\Auth\Http\Exports\FileNoDimensionExport;
use App\Domains\Auth\Http\Exports\FileChildExport;
use App\Domains\Auth\Http\Exports\FileChildNoDimensionExport;
use App\Domains\Auth\Http\Exports\NewMappingExport;

use Exception;
use ZipArchive;
/**
 * Class UpdateFileService.
 */
class UpdateFileService extends BaseService
{
    /**
     * UpdateFileService constructor.
     *
     * @param  UrlsFile  $urls_file
     */
    public function __construct(UrlsFile $urls_file)
    {
        $this->model = $urls_file;
    }

    /**
     * @param  array  $data
     *
     * @return UrlsFile
     * @throws GeneralException
     * @throws \Throwable
     */
    public function store(array $data = []): UrlsFile
    {
        $user = auth()->user();
        $tmpfile = $data['tmp_name'];
        $filename = $data['filename'];

        try {
            Storage::disk('s3')->put('uploads/'.$user->company_id.'/'.$filename, file_get_contents($tmpfile));
            if (!$data['scheduled']) {
                Storage::disk('public')->put($filename, file_get_contents($tmpfile));
            }

        } catch (Exception $e) {
            throw new GeneralException(__($e->getMessage()));
        }

        DB::beginTransaction();

        $import = new UrlsFileImport();
        Excel::import($import, $filename);
        $totalCount = $import->getRowCount();
        $id_column = $import->getIdColumn();

        $id_column = $id_column == "" ? "GTIN" : $id_column;

        $urls_file = $this->model::create([
            'company_id' => $user->company_id,
            'user_id' => $data['scheduled'] ? 0 : $user->id,
            'filename' => $data['filename'], 
            'rows' => $totalCount,
            'status' => 0,
            'url' => 'uploads/'.$user->company_id.'/'.$filename, 
            'id_column' => $id_column, 
            'new_prod' => $import->getNewCount("Products"),
            'new_nf' => $import->getNewCount("Nutrition_Facts"),
            'new_ingr' => $import->getNewCount("Ingredients"),
            'changed_prod' => $import->getChangedCount("Products"),
            'changed_nf' => $import->getChangedCount("Nutrition_Facts"),
            'changed_ingr' => $import->getChangedCount("Ingredients")
        ]);

        DB::commit();

        return $urls_file;
    }

    
    /**
     * @param  UrlsFile  $urls_file
     *
     * @return UrlsFile
     * @throws GeneralException
     */
    public function delete(UrlsFile $urls_file): UrlsFile
    {
        Storage::disk('s3')->delete($urls_file->url);

        if ($this->deleteById($urls_file->id)) {
            return $urls_file;
        }

        throw new GeneralException('There was a problem deleting this urls file. Please try again.');
    }

    public function get_files(UrlsFile $urls_file) {
        $log = "";
        $url = Storage::disk('s3')->temporaryUrl($urls_file->url, now()->addHours(1));
        Storage::disk('public')->put( $urls_file->filename, file_get_contents($url));

        DB::beginTransaction();
        Excel::import( new NewMappingImport, $urls_file->filename );
        DB::commit();

        // ToDo : Should implement with background job

        $onetime_product_image = Setting::where('key', 'onetime_product_image')->first()->value;
        $onetime_nf_image = Setting::where('key', 'onetime_nf_image')->first()->value;
        $onetime_ingredient_image = Setting::where('key', 'onetime_ingredient_image')->first()->value;
        $onetime_import_image_type = Setting::where('key', 'onetime_import_image_type')->first()->value;

        $filename = $urls_file->filename;
        $new_product_filename = "";
        $new_file_count = 0;
        if ($onetime_import_image_type == 1) {
            $filename = uniqid().".xlsx";
            $newMappingExport = new NewMappingExport(false);
            $newMappingExport->store($filename, 'public');
            $new_file_count = $newMappingExport->getRowCount();

            $newMappingOutput = new NewMappingExport(true);
            $newMappingOutput->store("uploads/tmp/".$urls_file->id.'/New_'.$urls_file->filename, 's3');
            // New Product Images Only
            $newProductMappingOutput = new NewMappingExport(false, "NewOnly");
            $new_product_filename = uniqid().".xlsx";
            $newProductMappingOutput->store($new_product_filename, 'public');
            // New or Changed Product Images Only
            $newChangedProductMappingOutput = new NewMappingExport(false, "New&Changed");
            $new_changed_product_filename = uniqid().".xlsx";
            $newChangedProductMappingOutput->store($new_changed_product_filename, 'public');
            $newChangedProductMappingOutput->store("uploads/tmp/".$urls_file->id.'/New_'.$new_changed_product_filename, 's3');
        }

        if ($new_file_count == 0 && $onetime_import_image_type == 1) {
            return "duplicated";
        }

        $callback_url = "http://dev.video-app.com/bkg_task/update_image_uploading_progress/";
        $col_name = " --column-name GTIN";
        // $upload_directory = "uploads/tmp/".$urls_file->id."/images";
        $id_column = $urls_file->id_column;
        if ($id_column == "ASIN") {
            $col_name = " --column-name '".$id_column."'";
        }
        // $col_url = " --column-url 'Image URL' ";
        // $command = "";

        if ($onetime_product_image == "on") {
            $col_url = "Image URL";
            $upload_directory = "uploads/tmp/".$urls_file->id."/images";
        }
        if ($onetime_nf_image == "on") {
            $col_url .= ",Nutrition Facts Image URL";
            $upload_directory .= ",uploads/tmp/".$urls_file->id."/images/Nutrition_Facts_Images";
            // $col_url = "Nutrition Facts Image URL";
            // $upload_directory = "uploads/tmp/".$urls_file->id."/images/Nutrition_Facts_Images";
        }
        if ($onetime_ingredient_image == "on") {
            $col_url .= ",Ingredients Image URL";
            $upload_directory .= ",uploads/tmp/".$urls_file->id."/images/Ingredients_Images";
        }
        $command = escapeshellcmd(
            "python3 /var/www/downloader/downloader.py --s3-access-key="
            .env('AWS_ACCESS_KEY_ID', '')." --s3-secret-key="
            .env('AWS_SECRET_ACCESS_KEY', '')." --s3-bucket=dev-video-app-files --s3-directory='".$upload_directory.
            "' --filename /var/www/video-app/public/'".$filename."'"
            .$col_name." --column-url '".$col_url."' --callback-url ".$callback_url
        );
        $command = $command."> /dev/null 2>/dev/null &";
        $log = shell_exec($command);

        if ($new_product_filename != "") {
            $col_url = "Image URL";
            $upload_directory = "uploads/tmp/".$urls_file->id."/images/new";
            $command = escapeshellcmd(
                "python3 /var/www/downloader/downloader.py --s3-access-key="
                .env('AWS_ACCESS_KEY_ID', '')." --s3-secret-key="
                .env('AWS_SECRET_ACCESS_KEY', '')." --s3-bucket=dev-video-app-files --s3-directory='".$upload_directory.
                "' --filename /var/www/video-app/public/'".$new_product_filename."'"
                .$col_name." --column-url '".$col_url."'"
            );
            $command = $command."> /dev/null 2>/dev/null &";
            $log = shell_exec($command);
        }
        if ($new_changed_product_filename != "") {
            $col_url = "Image URL";
            $upload_directory = "uploads/tmp/".$urls_file->id."/images";
            $command = escapeshellcmd(
                "python3 /var/www/downloader/downloader.py --s3-access-key="
                .env('AWS_ACCESS_KEY_ID', '')." --s3-secret-key="
                .env('AWS_SECRET_ACCESS_KEY', '')." --s3-bucket=dev-video-app-files --s3-directory='".$upload_directory.
                "' --filename /var/www/video-app/public/'".$new_changed_product_filename."'"
                .$col_name." --column-url '".$col_url."'"
            );
            $command = $command."> /dev/null 2>/dev/null &";
            $log = shell_exec($command);
        }
        $this->updateNewMappings();
        Setting::updateOrCreate(['key' => 'upload_id'], ['value' => $urls_file->id]);
        
        // new_changed excel file
        $urls_file->zip_file_url = "uploads/tmp/".$urls_file->id."/New_".$urls_file->filename;
        $urls_file->save();

        return $command;
    }

    public function updateNewMappings() {
        $user = auth()->user();
        $company_id = $user->company_id;
        $rows = array();
        if ($user->isMasterAdmin()) {
            $rows = NewMapping::all();
        } else {
            $rows = NewMapping::whereIn('company_id', [0, $user->company_id])->get();
        }

        foreach($rows as $row) {
            $row->status = "";
            $row->save();
        }
    }

    public function export_file_list($type, $file)
    {
        if ($type == 'all') {
            return Excel::download(new FileAllExport, $file);
        } else if ($type == 'dimensions') {
            return Excel::download(new FileDimensionExport, $file);
        } else if ($type == 'no_dimensions') {
            return Excel::download(new FileNoDimensionExport, $file);
        } else if ($type == 'child') {
            return Excel::download(new FileChildExport, $file);
        } else if ($type == 'child_no_dimensions') {
            return Excel::download(new FileChildNoDimensionExport, $file);
        }
        return 0;
    }

    public function finish_compressing($data)
    {
        Setting::updateOrCreate(['key' => 'zip_filename'], ['value' => $data->filename]);
        Setting::updateOrCreate(['key' => 'compressing'], ['value' => $data->count]);
        
        $urls_file = UrlsFile::where('id', $data->file_id)->first();
        $urls_file->status = 1;
        $urls_file->save();
        return true;
    }

    public function store_upload_progress($data)
    {
        Setting::updateOrCreate(['key' => 'upload_progress'], ['value' => $data]);
    }

    public function get_upload_progress()
    {
        $result = Setting::where('key', 'upload_progress')->first();
        if ($result) {
            return $result->value;
        }
        return "";
    }

    public function get_upload_id()
    {
        $result = Setting::where('key', 'upload_id')->first();
        if ($result) {
            return $result->value;
        }
        return "";
    }
    
    public function stop_upload_progress()
    {
        $urlsfile_id = $this->get_upload_id();
        $urls_file = UrlsFile::where('id', $urlsfile_id)->first();
        $urls_file->status = 1;
        $urls_file->save();

        Setting::where('key', 'upload_progress')->delete();
        // Setting::where('key', 'upload_id')->delete();
    }

    public function download_files($urlsfile_id, $type) {
        $urls_file = UrlsFile::where('id', $urlsfile_id)->first();
        $urls_file->status = 2;
        $urls_file->save();

        // set compressing flag on Setting
        Setting::updateOrCreate(['key' => 'compressing'], ['value' => 0]);

        $command = escapeshellcmd(
            "php scripts/compress.php --awsAccessKey=".env('AWS_ACCESS_KEY_ID', '')
            ." --awsSecretKey=".env('AWS_SECRET_ACCESS_KEY', '')
            ." --bucket=dev-video-app-files"
            ." --uri=uploads/tmp/".$urlsfile_id."/images/"
            ." --output='".$urls_file->filename."'"
            ." --maxCount=300"
            ." --fileId=".$urlsfile_id
            ." --type=".$type
        );
        $log = shell_exec($command."> /dev/null 2>/dev/null &");
        return $command;
    }
}
