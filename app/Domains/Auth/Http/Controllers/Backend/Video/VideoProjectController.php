<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Video;

use App\Domains\Auth\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\Domains\Auth\Http\Exports\VideoTemplateExport;
use App\Domains\Auth\Http\Imports\VideoTemplateImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
use App\Domains\Auth\Models\VideoProject;
use App\Domains\Auth\Models\VideoTheme;
use App\Domains\Auth\Models\VideoThemeColor;
use App\Domains\Auth\Services\VideoTemplateService;
use stdClass;

class VideoProjectController extends Controller
{
    private $excel_folder = 'public/projects/';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $projects = VideoProject::where('is_draft', request()->routeIs('admin.auth.video.drafts.index'))
            ->orderBy('order')->get();
        $projects->transform(
            function ($item) {
                $std = new stdClass();

                $std->id = $item->id;
                $std->name = $item->name;
                $std->user = $item->user->name;
                $std->updated_at = $item->updated_at;
                $std->file_name = $item->file_name;
                $std->visibility = $item->visibility;
                $std->path = url('storage/projects').'/'.$item->file_name;

                return $std;
            }
        );

        return view('backend.auth.video.project.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        if(isset($request->themeID) && !empty($request->themeID) && (!empty($request->newFontColor) || !empty($request->newStrokeColor)) )
        {
            $theme = VideoTheme::find($request->themeID);
            $font_colors = implode(",", $request->newFontColor);
            $stroke_colors = implode(",", $request->newStrokeColor);
            $theme->font_colors = ($font_colors) ? $theme->font_colors.','.$font_colors : $theme->font_colors;
            $theme->stroke_colors = ($stroke_colors) ? $theme->stroke_colors.','.$stroke_colors : $theme->stroke_colors;
            $theme->save();

            if(isset($request->newFontColor) && !empty($request->newFontColor)){
                foreach ($request->newFontColor as $font) {
                    $sorted_font_colors = [
                        'name'           => $font,
                        'hex'            => $font,
                        'type'           => 'Font',
                        'video_theme_id' => $request->themeID,
                    ];
                    VideoThemeColor::create($sorted_font_colors);
                }
            }

            if(isset($request->newStrokeColor) && !empty($request->newStrokeColor)){
                foreach ($request->newStrokeColor as $stroke) {
                    $sorted_stroke_colors = [
                        'name'           => $stroke,
                        'hex'            => $stroke,
                        'type'           => 'Stroke',
                        'video_theme_id' => $request->themeID,
                    ];
                    VideoThemeColor::create($sorted_stroke_colors);
                }
            }
            
        }

        $name = 'New Project';
        $isDraft = false;

        if(isset($request->filename) && $request->filename) {
            $name = $request->filename;
        }

        if(isset($request->isDraft) && $request->isDraft) {
            $isDraft = $request->isDraft;
        }

        if($request->Thumbnail){
            $file = $request->Thumbnail;
        }
        else{
            $file=null;
        }
        $project = VideoProject::where('name', $name)->first();

        $file_name = $name . '.xlsx';
        Excel::store(new VideoTemplateExport($request->rows), $this->excel_folder . $file_name);

        if (!$project) {
            $order = VideoProject::max('order') + 1;

            $project = Auth::user()->videoProjects()->create([
                'name' => $name,
                'file_name' => $file_name,
                'order' => $order,
                'visibility' => true,
                'thumbnail_image'=>$file,
                'is_draft' => $isDraft
            ]);
        }else{
            $pro = VideoProject::find($project['id']);
            $pro->thumbnail_image = $file;
            $pro->is_draft = $isDraft;
            $pro->save();
            $project = VideoProject::where('name', $name)->first();
        }

        return response()->json([
            'message' => 'Saved successfully',
            'data' => [
                'project' => $project
            ]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $project = VideoProject::findOrFail($id);
        $rows = VideoTemplateService::importTemplate($this->excel_folder . $project->file_name);

        foreach($rows as $key => &$row) {
            $row['id'] = $key;
        }

        return response()->json([
            'rows' => $rows,
            'project' => $project
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $project = VideoProject::findOrFail($id);

        $validatedData = $request->validate([
            'name' => [
                'sometimes',
                'required',
                Rule::unique('video_projects', 'name')->ignore($project->id)
            ],
            'file_name' => [
                'sometimes',
                'required',
                Rule::unique('video_projects', 'file_name')->ignore($project->id)
            ],
            'visibility' => 'sometimes|required|boolean'
        ]);

        $project->name = isset($request->name) ? $request->name : $project->name;
        $project->visibility = isset($request->visibility) ? $request->visibility : $project->visibility;

        if (isset($request->file_name)) {
            $old = $this->excel_folder . $project->file_name;
            $new = $this->excel_folder . $request->file_name;

            if (!Storage::exists($new)) {
                Storage::move($old, $new);
                $project->file_name = $request->file_name;
            }
        }

        $project->update();

        return response()->json([
            'message' => 'Successfully updated',
            'data' => [
                'project' => $project
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $project = VideoProject::findOrFail($id);
        $project->delete();
        Storage::delete($this->excel_folder . $project->file_name);

        return response()->json([
        	'message' => 'Successfully deleted a project'
        ]);
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getProjects(Request $request)
    {
        if ($request->has('drafts')) {
            /** @var Collection $projectsDrafts */
            $projectsDrafts = VideoProject::Where('user_id', Auth::user()->id)->where('visibility', true)
                ->where('is_draft', true)->orderBy('order')->get();

            return response()->json(compact('projectsDrafts'));
        }

        /** @var Collection $usersInCompany */
        $usersIdsInCompany = Auth::user()->company->users->pluck('id');

        /** @var Collection $projects */
        $projects = VideoProject::where('visibility', true)->where('is_draft', false)
            ->whereIn('user_id', $usersIdsInCompany)->orderBy('order')->get();

        return response()->json(compact('projects'));
    }

    public function getProjectDrafts()
    {
        $user_id = Auth::user()->id;

        $projects = VideoProject::where([['visibility', '=', true], ['user_id', '=', $user_id]])->orderBy('order')
            ->get();

        return response()->json(compact('projects'));
    }

    public function updateProjectsOrder(Request $request)
    {
    	$orders = $request->orders;

    	foreach($orders as $key => $order) {
            $project = VideoProject::find($order);
    		$project->update([
    			'order' => $key
    		]);
    	}

    	return response()->json([
    		'message' => 'Successfully updated order'
    	]);
    }
}
