<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\Project;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * Class ProjectService.
 */
class ProjectService extends BaseService
{
    /**
     * ProjectService constructor.
     *
     * @param  Project  $project
     */
    public function __construct(Project $project)
    {
        $this->model = $project;
    }

    /**
     * @param  array  $data
     *
     * @return Project
     * @throws GeneralException
     * @throws \Throwable
     */
    public function store(array $data = []): Project
    {
        DB::beginTransaction();

        try {
            $project = $this->createProject([
                'name' => $data['name'],
                'customer' => $data['customer'],
                'output_dimensions' => $data['output_dimensions'],
                'projectname' => $data['projectname'],
                'url' => $data['url'],
                'fileid' => isset($data['fileid']) ? $data['fileid'] : "",
                'headline' => $data['headline'],
                'size' => $data['size'],
                'settings' => $data['settings'],
                'user_id' => auth()->user()->id,
                'jpg_files' => $data['jpg_files'],
                'type' => $data['type'],
                'parent_id' => $data['parent_id']
            ]);

            $team_list = [];
            foreach (auth()->user()->teams as $team) {
                $team_list[] = $team->id;
            }
            $project->teams()->sync($team_list);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem creating this project. Please try again.'));
        }

        DB::commit();

        return $project;
    }

    /**
     * @param  Project  $project
     *
     * @return bool
     * @throws GeneralException
     */
    public function destroy(Project $project): bool
    {
        Storage::disk('s3')->delete($project->url);

        if ($project->forceDelete()) {
            return true;
        }

        throw new GeneralException(__('There was a problem permanently deleting this project. Please try again.'));
    }

    public function approve(array $data)
    {
        DB::beginTransaction();

        try {
            $project = $this->model::find($data['id']);
            $project->approvals()->create([
                'requester_id' => $data['requester_id'],
                'request_time' => $data['request_timestamp'],
                'user_id' => $data['user_id'],
                'approval_time' => now(),
                'approved' => true,
                'comment' => $data['comment'],
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem approving this project. Please try again.'));
        }

        DB::commit();

        return $project;
    }

    public function reject(array $data)
    {
        DB::beginTransaction();

        try {
            $project = $this->model::find($data['id']);
            $project->approvals()->create([
                'requester_id' => $data['requester_id'],
                'request_time' => $data['request_timestamp'],
                'user_id' => $data['user_id'],
                'approval_time' => now(),
                'approved' => false,
                'comment' => $data['comment'],
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem rejecting this project. Please try again.'));
        }

        DB::commit();

        return $project;
    }

    /**
     * @param  array  $data
     *
     * @return Project
     */
    protected function createProject(array $data = []): Project
    {
        return $this->model::updateOrCreate(['projectname' => $data['projectname']], $data);
    }

    public function getByProjectName($project_name) {
        return Project::where('projectname', $project_name)->first();
    }

    public function getMasterProjects($search) {
        return [
            'items' => Project::select('id', 'name AS text')
                                ->where('type', 1)
                                ->where('name', 'like', "%$search%")
                                ->get()
        ];
    }

    public function download_all($project_ids, $download_name)
    {
        $files = [];
        $zip_file = $download_name . ".zip";
        $zip = new ZipArchive();
        if ($zip->open(public_path($zip_file), ZipArchive::CREATE) === TRUE) {
            for ($i=0; $i < count($project_ids); $i++) { 
                $project = Project::find($project_ids[$i]);
                $jpg_files = explode(" ", $project->jpg_files);
                foreach ($jpg_files as $value) {
                    $arr = explode(".", $value);
                    array_pop($arr);
                    $filename = implode(".", $arr);
                    $files[] = $filename;
                    $path = "outputs/jpg/" . $filename . ".jpg";
                    $fname = $project->projectname ? $project->projectname : $filename;
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
