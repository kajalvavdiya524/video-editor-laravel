<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Video;

use App\Domains\Auth\Http\Requests\Backend\Video\StoreCustomColorRequest;
use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Models\VideoEntityCompanies;
use App\Domains\Auth\Models\VideoThemeColor;
use App\Domains\Auth\Services\VideoService;
use App\Domains\Auth\Services\VideoThemeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domains\Auth\Models\VideoTheme;

class VideoThemeController extends Controller
{
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
        $themes = VideoTheme::all();
        $companies = Company::all();
        $videoThemeCompanies = $this->videoService->getEntityCompanies(
            $themes->pluck('id')->toArray(),
            VideoEntityCompanies::ENTITY_THEME_TYPE
        );

        $themes->transform(function($item) use ($videoThemeCompanies) {
            $item->companies = $videoThemeCompanies
                ->where(VideoEntityCompanies::FIELD_ENTITY_ID, $item->id)
                ->pluck(VideoEntityCompanies::FIELD_COMPANY_ID)
                ->toArray();

            return $item;
        });

        return view('backend.auth.video.theme.index', compact('themes', 'companies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $fonts = config('video_template.fonts');
        $colors = config('video_template.colors');

        return view('backend.auth.video.theme.create', compact('fonts', 'colors'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'font_names' => 'required',
            'default_font_name' => 'required',
            'font_size' => 'required|integer',
            'stroke_colors' => 'required',
            'stroke_width' => 'required|integer',
            'font_colors' => 'required',
            'default_font_color' => 'required',
        ]);



        $theme = VideoTheme::create([
            'name' => $request->name,
            'font_names' => implode(',', $request->font_names),
            'default_font_name' => $request->default_font_name,
            'font_size' => $request->font_size,
            'stroke_colors' => implode(',', $request->stroke_colors),
            'stroke_width' => $request->stroke_width,
            'font_colors' => implode(',', $request->font_colors),
            'default_font_color' => $request->default_font_color,
            'is_stroke_color_selector' =>  isset($request->is_stroke_color_selector) ? '1' : '2',
            'is_font_color_selector' =>  isset($request->is_font_color_selector) ? '1' : '2',
        ]);
        // custom colors
        $customRawColors = $request->get('custom_colors');
        if (!empty($customRawColors)) {
            $colorTypes = explode(',', $customRawColors[ 'color_type' ]);
            $colorNames = explode(',', $customRawColors[ 'color_name' ]);
            $colorHEX = explode(',', $customRawColors[ 'HEX_color' ]);

            foreach ($colorTypes as $key => $type) {
                $sorted_colors = [
                    'name'           => $colorNames[ $key ],
                    'hex'            => $colorHEX[ $key ] ?? '',
                    'type'           => $type,
                    'video_theme_id' => $theme->id,
                ];

                VideoThemeColor::create($sorted_colors);
            }
        }
        $request->session()->flash('message', 'Successfully created a theme');
        return redirect()->route('admin.auth.video.themes.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $theme = VideoTheme::with('colors')->findOrFail($id);
        $fonts = config('video_template.fonts');
        $colors = config('video_template.colors');
        $colors = array_combine($colors, $colors);

        $color_stroke = $colors;
        $color_fonts = $colors;

        $colorTypes = [];
        $colorNames = [];
        $colorHEX = [];

        foreach ($theme->colors as $savedColor) {
            $colorTypes[] = $savedColor->type;
            $colorNames[] = $savedColor->name;
            $colorHEX[]   = $savedColor->hex;
            if ($savedColor->type == 'Font') {
                $color_fonts[ $savedColor->hex ] = $savedColor->name;
            } else {
                $color_stroke[ $savedColor->hex ] = $savedColor->name;
            }
        }

        $colorTypes = implode(',', $colorTypes);
        $colorNames = implode(',', $colorNames);
        $colorHEX = implode(',', $colorHEX);

        return view('backend.auth.video.theme.edit', compact('theme', 'fonts', 'color_stroke', 'color_fonts', 'colorTypes', 'colorNames', 'colorHEX'));
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
        $theme = VideoTheme::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'font_names' => 'required',
            'default_font_name' => 'required',
            'font_size' => 'required|integer',
            'stroke_colors' => 'required',
            'stroke_width' => 'required|integer',
            'font_colors' => 'required',
            'default_font_color' => 'required',
        ]);

        $theme->update([
            'name' => $request->name,
            'font_names' => implode(',', $request->font_names),
            'default_font_name' => $request->default_font_name,
            'font_size' => $request->font_size,
            'stroke_colors' => implode(',', $request->stroke_colors),
            'stroke_width' => $request->stroke_width,
            'font_colors' => implode(',', $request->font_colors),
            'default_font_color' => $request->default_font_color,
            'is_stroke_color_selector' =>  isset($request->is_stroke_color_selector) ? '1' : '2',
            'is_font_color_selector' =>  isset($request->is_font_color_selector) ? '1' : '2',
        ]);

        $customRawColors = $request->get('custom_colors');
        VideoThemeColor::where('video_theme_id', $id)->delete();
        if (!empty($customRawColors)) {
            $colorTypes = explode(',', $customRawColors[ 'color_type' ]);
            $colorNames = explode(',', $customRawColors[ 'color_name' ]);
            $colorHEX   = explode(',', $customRawColors[ 'HEX_color' ]);

            foreach ($colorTypes as $key => $type) {
                $sorted_colors = [
                    'name'           => $colorNames[ $key ],
                    'hex'            => $colorHEX[ $key ] ?? '',
                    'type'           => $type,
                    'video_theme_id' => $id,
                ];

                VideoThemeColor::create($sorted_colors);
            }
        }

        $request->session()->flash('message', 'Successfully updated a theme');
        return redirect()->route('admin.auth.video.themes.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $theme = VideoTheme::with('colors')->findOrFail($id);
        $theme->colors->delete();
        $theme->delete();
        
        return response()->json([
            'message' => 'Successfully deleted'
        ]);
    }

    public function getThemes()
    {
        $themes = VideoTheme::all();
        return response()->json(compact('themes'));
    }

    public function themePosition(Request $request){

        if($request->value){

            $results = VideoTheme::where('theme_number',$request->value)        
                        ->where('id', '!=' , $request->id)
                    ->get();
                    
                $theme = VideoTheme::findOrFail($request->id);
                $theme->update(['theme_number'=>$request->value]);
                
                return response()->json([
                    'message' => 'Success',
                    'data' => [
                        'results' => $theme,
                    ]
                ]);

        }else{
            $theme = VideoTheme::findOrFail($request->id);
            $theme->update(['theme_number'=>$request->value]);
            
            return response()->json([
                'message' => 'Success',
                'data' => [
                    'results' => $theme,
                ]
            ]);

        }  
            
    }

    public function addOrRemoveCompany(Request $request)
    {
        $dataInput = $request->input();
        $dataTheme = VideoTheme::find($dataInput['entityId']);
        $data = $this->videoService->addOrRemoveCompany(
            $dataInput,
            VideoEntityCompanies::ENTITY_THEME_TYPE,
            $dataTheme->all_companies
        );

        VideoThemeService::updateAllCompaniesColumn($data, $dataInput['entityId']);

        return response()->json($data);
    }

}
