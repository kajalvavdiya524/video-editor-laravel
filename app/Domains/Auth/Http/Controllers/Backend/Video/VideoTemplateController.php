<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Video;

use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Models\VideoEntityCompanies;
use App\Domains\Auth\Services\VideoService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use GuzzleHttp\Client;
use ZipArchive;
use File;
use Auth;
use Illuminate\Support\Str;
use App\Domains\Auth\Http\Exports\VideoTemplateExport;

use App\Domains\Auth\Events\VideoCreationStarted;
use App\Domains\Auth\Events\VideoCreationCompleted;

use App\Domains\Auth\Models\VideoTemplate;
use App\Domains\Auth\Models\VideoCreation;
use App\Domains\Auth\Models\VideoTheme;
use App\Domains\Auth\Models\VideoThemeColor;
use App\Domains\Auth\Services\MediaService;
use App\Domains\Auth\Services\VideoTemplateService;

class VideoTemplateController extends Controller
{
    private $excel_folder = 'public/templates/';

    /**
     * @var VideoService
     */
    private $videoService;

    public function __construct(VideoService $videoService)
    {
        $this->videoService = $videoService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $templates = VideoTemplate::orderBy('order')->get();
        $companies = Company::all();
        $videoTemplatesCompanies = $this->videoService->getEntityCompanies(
            $templates->pluck('id')->toArray(),
            VideoEntityCompanies::ENTITY_TEMPLATE_TYPE
        );

        $templates->transform(function($item) use ($videoTemplatesCompanies) {
            $std = new \stdClass();

            $std->id = $item->id;
            $std->name = $item->name;
            $std->file_name = $item->file_name;
            $std->readonly = $item->readonly;
            $std->visibility = $item->visibility;
            $std->path = url('storage/templates') . '/' . $item->file_name;
            $std->companies = $videoTemplatesCompanies
                ->where(VideoEntityCompanies::FIELD_ENTITY_ID, $item->id)
                ->pluck(VideoEntityCompanies::FIELD_COMPANY_ID)
                ->toArray();
            $std->all_companies = $item->all_companies;

            return $std;
        });

        return view('backend.auth.video.template.index', compact('templates', 'companies'));
    }

    public function getTemplates()
    {
    	$templates = VideoTemplate::leftJoin(VideoEntityCompanies::TABLE_NAME, VideoEntityCompanies::FIELD_ENTITY_ID, 'id')
            ->where(function ($query) {
                $query->where(VideoEntityCompanies::TABLE_NAME.'.'.VideoEntityCompanies::FIELD_COMPANY_ID, Auth::user()->company_id);
                $query->where(VideoEntityCompanies::TABLE_NAME.'.'.VideoEntityCompanies::FIELD_ENTITY_TYPE, VideoEntityCompanies::ENTITY_TEMPLATE_TYPE);
                $query->orWhere(VideoTemplate::FIELD_ALL_COMPANIES, 1);
            })
            ->where('video_templates.visibility', true)
            ->select(VideoTemplate::TABLE_NAME.'.*')
            ->groupBy('id')
            ->orderBy('order')->get();

        return response()->json(compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $template = VideoTemplate::findOrFail($id);
        $rows = VideoTemplateService::importTemplate($this->excel_folder . $template->file_name);
        return response()->json([
            'rows' => $rows,
            'template' => $template
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(VideoTemplate $template)
    {
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
        $template = VideoTemplate::findOrFail($id);

        $validatedData = $request->validate([
            'name' => [
                'sometimes',
                'required',
                Rule::unique('video_templates', 'name')->ignore($template->id)
            ],
            'file_name' => [
                'sometimes',
                'required',
                Rule::unique('video_templates', 'file_name')->ignore($template->id)
            ],
            'readonly' => 'sometimes|required|boolean',
            'visibility' => 'sometimes|required|boolean'
        ]);

        $template->name = isset($request->name) ? $request->name : $template->name;
        $template->readonly = isset($request->readonly) ? $request->readonly : $template->readonly;
        $template->visibility = isset($request->visibility) ? $request->visibility : $template->visibility;

        if (isset($request->file_name)) {
            $old = $this->excel_folder . $template->file_name;
            $new = $this->excel_folder . $request->file_name;

            if (!Storage::exists($new)) {
                Storage::move($old, $new);
                $template->file_name = $request->file_name;
            }
        }

        $template->update();

        return response()->json([
            'message' => 'Successfully updated',
            'data' => [
                'template' => $template
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
        $template = VideoTemplate::findOrFail($id);

        $template->delete();
        Storage::delete($this->excel_folder . $template->file_name);

        return response()->json([
        	'message' => 'Successfully deleted a template'
        ]);
    }

    public function updateTemplatesOrder(Request $request)
    {
    	$orders = $request->orders;

    	foreach ($orders as $key => $order) {
            $template = VideoTemplate::find($order);
    		$template->update([
    			'order' => $key
    		]);
    	}

    	return response()->json([
    		'message' => 'Successfully updated order'
    	]);
    }


    public function importFile(Request $request)
    {
        $rows = VideoTemplateService::importTemplate($request->file('template'));

        return response()->json([
            'rows' => $rows
        ]);
    }

    public function exportTemplate(Request $request)
    {
        return Excel::download(new VideoTemplateExport($request->data), '1.xlsx');
    }

    public function exportTemplateToServer(Request $request)
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

        $template = VideoTemplate::where('name', $request->filename)->first();
        Excel::store(new VideoTemplateExport($request->rows), $this->excel_folder . $request->filename . '.xlsx');

        if (!$template) {
            $order = VideoTemplate::max('order') + 1;
            $template = VideoTemplate::create([
                'name' => $request->filename,
                'file_name' => $request->filename . '.xlsx',
                'order' => $order,
                'visibility' => true,
                VideoTemplate::FIELD_ALL_COMPANIES => 0
            ]);
            $this->videoService->insertEntityOfCompany(
                $template->id,
                Auth::user()->company_id,
                VideoEntityCompanies::ENTITY_TEMPLATE_TYPE
            );
        }

        return response()->json([
            'message' => 'Successfully uploaded to server'
        ]);
    }

    public function exportAssets(Request $request)
    {
        $zipFileName = 'assets.zip';
        $public_dir = public_path();

        if(File::exists(public_path($zipFileName))) {
            File::delete(public_path($zipFileName));
        }

        // Create ZipArchive Obj
        $zip = new ZipArchive;

        if ($zip->open($public_dir . '/' . $zipFileName, ZipArchive::CREATE) === TRUE) {

            $zip->addEmptyDir('.');

            foreach ($request->data as $row) {
                if ($row['Type'] == 'Music' || $row['Type'] == 'Video' || $row['Type'] == 'Image') {
                    // Add File in ZipArchive
                    // issue(url should be changed to path)
                    $zip->addFile($row['Filename'], basename($row['Filename']));
                }
            }

            // Close ZipArchive
            $zip->close();
        }

        $filetopath = $public_dir . '/'. $zipFileName;

        return response()->json([
            'asset_path' => asset($zipFileName)
        ]);
    }

    public function upload_file(Request $request)
    {
        $file = $request->file('file');
        $filename = date("dmY").time().$file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());

        Storage::disk('samples')->putFileAs('./', $request->file('file'), $filename);

        if(empty($ext)) {
            $type = '';
        } else if($ext == 'm4a' || $ext == 'mp3') {
            $type = 'Music';
        } else if ($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'webp') {
            $type = 'Image';
        } else {
            $type = 'Video';
        }

        $duration = 0;

        if ($ext == 'mp4' || $ext == 'm4a') {
            $getID3 = new \getID3;
            $file_meta = $getID3->analyze('samples/' . $filename);
            $duration = $file_meta['playtime_seconds']; // returns an int
        }

        return response()->json([
            'path' => public_path().'/samples/'. $filename,
            'url' => './samples/' . $filename,
            'duration' => round($duration, 2),
            'type' => $type
        ]);
    }

    public function upload_thumb(Request $request)
    {
        $file = $request->file('file');
        $filename = date("dmY").time().$file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());

        Storage::disk('samples')->putFileAs('./', $request->file('file'), $filename);

        if ($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'webp') {
            $type = 'Image';
        }else {
            $type = '';
        }
        
        return response()->json([
            'path' => public_path().'/samples/'. $filename,
            'url' => 'samples/' . $filename,
            'type' => $type
        ]);
    }

    private function get_file()
    {
        $audios = array_filter(Storage::disk('audio')->files(),
            //only m4a's
            function ($item) {
                return strpos($item, '.m4a') || strpos($item, '.mp3');
            }
        );
        $getID3 = new \getID3;
        $libraries = array('None');
        $durations = array('None'=>0);
        if(count($audios)>0){
            foreach($audios as $audio){
                $file_meta = $getID3->analyze('samples/audio/' . $audio);
                $durations[$audio] = $file_meta['playtime_seconds'];
                $libraries[] = $audio;
            }
        }

        return array($libraries, $durations);
    }

    public function replaceLocalPath($path) {

        if(!$path) {
            return '';
        }

        $serverURL = env('APP_URL', 'https://dev.rapidads.io');
        if(str_starts_with($path, $serverURL)) {
            $pos = strlen($serverURL);
            return public_path() . substr($path, $pos);
        }
        return $path;
    }

    public function createVideo(Request $request) {
        $id = $request->output;
        
        /* temporary fix by Diego until this is revised by the developers  */
        
        $extension =  pathinfo(strtolower($id), PATHINFO_EXTENSION);               
        $id = substr($id, 0 , (strrpos($id, ".")));
        
        // in case they didn't imput a name generate a random filename
        if ($id == '')
            $id = uniqid();

        $input_name = uniqid() . '.xlsx';
        
        // add the required extension for each type.
        $mp4_name = $id.".".$extension;
        $vtt_name = $id.".vtt";

        /* end temporary fix by Diego */

        $rows = [];
        $audiFdIn_out = [];
        $dimensions_width='';
        $dimensions_height='';
        if($request->dimensions){
            $dimensions=explode('x',$request->dimensions[0]['dimensions']);
            $dimensions_width=$dimensions[0];
            $dimensions_height=$dimensions[1];
        }
        foreach($request->rows as $row) {
            if($row['Filename']) {
                $row['Filename'] = $this->replaceLocalPath($row['Filename']);
            }
            if($row['Props']){
                array_push($audiFdIn_out, $row['Props']);
        }
            array_push($rows, $row);
        }
        Excel::store(new VideoTemplateExport($rows), 'public/creation-templates/' . $input_name);

        $form_params['i'] = base_path().'/public/storage/creation-templates/' . $input_name;

        if (in_array(0, $request->creationOption))
            $form_params['o'] = base_path().'/public/video_creation/mp4/' . $mp4_name;
        if (in_array(1, $request->creationOption))
            $form_params['v'] = base_path().'/public/video_creation/vtt/' . $vtt_name;
        if (in_array(0, $request->creationOption) && in_array(2, $request->creationOption))
        {
            $form_params['burn'] = true;
        }

        $form_params['callback_uri'] = config('app.url') . '/video/video-creation';

        $thumbnail = null;
        if(in_array(3, $request->creationOption)){
            $thumbnail = base_path().'/public/samples/' . basename($request->thumbnail);
        }

        $dimensions_width='';
        $dimensions_height='';
        if($request->dimensions){
            $dimensions=explode('x',$request->dimensions[0]['dimensions']);
            $dimensions_width=$dimensions[0];
            $dimensions_height=$dimensions[1];
            $form_params['width'] = $dimensions_width;
            $form_params['height'] = $dimensions_height;
        }

        $client = new Client();
        $response = $client->post(
            'http://127.0.0.1:8055/video_creation/',
            [
                'form_params' => $form_params,
                'headers' => [
                    'Authorization' => 'Token 0367bfc4e099a4965a0def673b6c1c4344e1aef8',
                    'Content-Type: application/json'
                ]
            ]
        );
       
        VideoCreation::create([
            'xlsx' => $request->output,
            'user_id' => Auth::user()->id,
            'mp4' => isset($form_params['o']) ? $form_params['o'] : '',
            'vtt' => isset($form_params['v']) ? $form_params['v'] : '',
            'width'=>$dimensions_width,
            'height'=>$dimensions_height,
            'audioFadeIn_out' => $audiFdIn_out,
            'status' => 'waiting',
            'thumbnail' => !empty($thumbnail) ? $thumbnail : '', 
        ]);

        $result = $response->getBody()->getContents();
        return response()->json($result);
    }

    public function creationStarted()
    {
        return response()->json('OK');
    }

    public function creationPost(Request $request) {
        if ($request->status == 'started')
        {
            $video_creation = VideoCreation::where('status', 'waiting')->latest()->first();

            $video_creation->update([
                'status' => $request->status,
                'percent' => '0%',
                'mp4' => isset($request->kwargs['o']) ? $request->kwargs['o'] : '',
                'vtt' => isset($request->kwargs['v']) ? $request->kwargs['v'] : '',
                'task_id' => $request->task_id
            ]);
            event(new VideoCreationStarted($video_creation));
        }
        else
        {
            $video_creation = VideoCreation::where('task_id', $request->task_id)->first();

            \Log::info('======== video status  =============');
            \Log::info($request->percent);

            if ($request->status == 'working')
            {
                $video_creation->update([
                    'status' => $request->status,
                    'percent' => $request->percent
                ]);
            }
            else if ($request->status == 'OK')
            {
                $video_creation->update([
                    'status' => $request->status,
                    'percent' => '100%'
                ]);

                $zipFileName = $video_creation->task_id . '.zip';
                $public_dir = public_path();

                // Create ZipArchive Obj
                $zip = new ZipArchive;

                $zip_dir = $public_dir . '/video_creation/zips/';
                if (!file_exists($zip_dir)) {
                    mkdir($zip_dir, 0777, true);
                }

                if ($zip->open($zip_dir . $zipFileName, ZipArchive::CREATE)){
                    if (isset($request->kwargs['o']))
                        $zip->addFile($public_dir . '/video_creation/mp4/' . basename($video_creation['mp4']), basename($video_creation['mp4']));
                    if (isset($request->kwargs['v']))
                        $zip->addFile($public_dir . '/video_creation/vtt/' . basename($video_creation['vtt']), basename($video_creation['vtt']));
                    
                    if(isset($video_creation['thumbnail']) & !empty($video_creation['thumbnail'])){
                        $zip->addFile($public_dir .'/samples/' . basename($video_creation['thumbnail']) , basename($video_creation['thumbnail']));
                    }
                    $zip->close();

                    $video_creation->share()->create([
                        'uuid' => Str::uuid(),
                        'name' => $video_creation['xlsx'],
                        'file_name' => $zipFileName
                    ]);
                }
            }
            else
            {
                $video_creation->update([
                    'status' => $request->status,
                    'last_details' => json_encode($request->all())
                ]);
            }

            event(new VideoCreationCompleted($video_creation, $request->all()));
        }

        return response()->json('OK');
    }

    public function updateCustomVisibleColumns(Request $request) {
        $user = Auth::user();
        
        $user->customVisibleColumns()->updateOrCreate(
            ['user_id' => $user->id], [
                'columns' => json_encode($request->columns),
                'timeframe' => $request->timeframe,
            ]
        );

        if ($user->customVisibleColumns->timeframe == '2') {
            $default_all_columns = config('video_template.all_timeframe_columns');
        } else {
            $default_all_columns = config('video_template.all_columns');
        }
        $all_columns = $default_all_columns;

        return response()->json([
            'message' => 'Success',
            'data' => [
                'columns' => $request->columns,
                'timeframe' => $request->timeframe,
                'all_columns' => $all_columns,
            ]
        ]);
    }

    public function saveCroppedImage(Request $request) {
        request()->validate([
            'file'          => "required",
        ]);

        if($request->hasFile('file')){
            $path = $request->file('file')->storeAs('', time() . '.png', 'samples');

            return response()->json([
                'message' => 'Successfully cropped',
                'data' => [
                    'path' => './samples/' . $path
                ]
            ]);
        }

        return response()->json([
            'message' => 'File not uploaded',
        ], 422);
    }

    public function getImages(Request $request)
    {   
        $mediaService = new MediaService();
        $images = $mediaService->getImages(5);
        return response()->json($images);
    }

    public function addOrRemoveCompany(Request $request)
    {
        $dataInput = $request->input();
        $dataTemplate = VideoTemplate::find($dataInput['entityId']);
        $data = $this->videoService->addOrRemoveCompany(
            $dataInput,
            VideoEntityCompanies::ENTITY_TEMPLATE_TYPE,
            $dataTemplate->all_companies
        );

        VideoTemplateService::updateAllCompaniesColumn($data, $dataInput['entityId']);

        return response()->json($data);
    }
}
