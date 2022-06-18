<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\History;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use ZipArchive;

/**
 * Class HistoryService.
 */
class HistoryService extends BaseService
{
    /**
     * HistoryService constructor.
     *
     * @param  History  $history
     */
    public function __construct(History $history)
    {
        $this->model = $history;
    }

    /**
     * @param  array  $data
     *
     * @return History
     * @throws GeneralException
     * @throws \Throwable
     */
    public function store(array $data = []): History
    {
        DB::beginTransaction();

        // try {
            
        // } catch (Exception $e) {
        //     DB::rollBack();

        //     throw new GeneralException(__($e->getMessage()));
        // }

        $history = $this->createHistory([
            'name' => $data['name'],
            'customer' => $data['customer'],
            'output_dimensions' => $data['output_dimensions'],
            'projectname' => $data['projectname'],
            'url' => $data['url'],
            'fileid' => isset($data['fileid']) ? $data['fileid'] : '',
            'headline' => $data['headline'],
            'size' => $data['size'],
            'settings' => $data['settings'],
            'user_id' => auth()->user()->id,
            'jpg_files' => $data['jpg_files'],
            'type' => $data['type'],
            'parent_id' => $data['parent_id']
        ]);

        DB::commit();

        return $history;
    }

    /**
     * @param  History  $history
     *
     * @return bool
     * @throws GeneralException
     */
    public function destroy(History $history): bool
    {
        Storage::disk('s3')->delete($history->url);

        if ($history->forceDelete()) {
            return true;
        }

        throw new GeneralException(__('There was a problem permanently deleting this history. Please try again.'));
    }
    
    /**
     * @param  array  $data
     *
     * @return History
     */
    protected function createHistory(array $data = []): History
    {
        return $this->model::create($data);
    }
    
    public function getByProjectName($project_name)
    {
        return History::where('projectname', $project_name)->first();
    }

    public function download_all($history_ids, $download_name)
    {
        $files = [];
        $zip_file = $download_name . ".zip";
        $zip = new ZipArchive();
        if ($zip->open(public_path($zip_file), ZipArchive::CREATE) === TRUE) {
            for ($i=0; $i < count($history_ids); $i++) { 
                $history = History::find($history_ids[$i]);
                $jpg_files = explode(" ", $history->jpg_files);
                foreach ($jpg_files as $value) {
                    $arr = explode(".", $value);
                    array_pop($arr);
                    $filename = implode(".", $arr);
                    $files[] = $filename;
                    $path = "outputs/jpg/" . $filename . ".jpg";
                    $fname = $history->projectname ? $history->projectname : $filename;
                    if (Storage::disk('s3')->exists($path)) {
                        $contents = Storage::disk('s3')->get($path);
                        $zip->addFromString($fname . ".jpg", $contents);
                    }
                    $path = "outputs/psd/" . $filename . ".psd";
                    if (Storage::disk('s3')->exists($path)) {
                        $contents = Storage::disk('s3')->get($path);
                        $zip->addFromString($fname . ".psd", $contents);
                    }
                }
            }
        }
        $zip->close();
        return $zip_file;
    }
}
