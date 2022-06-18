<?php

namespace App\Domains\Auth\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Domains\Auth\Models\History;
use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Models\Theme;
use App\Domains\Auth\Models\Template;
use App\Domains\Auth\Models\TemplateField;
use App\Domains\Auth\Models\Images;
use App\Domains\Auth\Services\HistoryService;
use App\Domains\Auth\Services\ProjectService;
use App\Domains\Auth\Http\Requests\Frontend\History\DeleteHistoryRequest;
use App\Domains\Auth\Http\Requests\Frontend\History\EditHistoryRequest;
/**
 * Class HistoryController.
 */
class HistoryController extends Controller
{
    /**
     * @var HistoryService
     */
    protected $historyService;

    /**
     * @var ProjectService
     */
    protected $projectService;

    /**
     * HistoryController constructor.
     *
     * @param  HistoryService  $historyService
     * @param  HistoryService  $historyService
     */
    public function __construct(HistoryService $historyService, ProjectService $projectService)
    {
        $this->historyService = $historyService;
        $this->projectService = $projectService;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('frontend.user.history', [ 'active_columns' => auth()->user()->getActiveDraftColumns() ]);
    }

    public function download(EditHistoryRequest $request, History $history) {

        return Storage::disk('s3')->download( $history->url, $history->projectname.".zip" );

    }

    public function show(Request $request, History $history) {
        return ['jpg_files' => $history->jpg_files];
    }

    public function publish(Request $request, History $history) {
        $project = json_decode(json_encode($history), true);
        $this->projectService->store($project);
        $this->historyService->destroy($history);
        return redirect()->route('frontend.history.index')->withFlashSuccess(__('The draft was successfully published.'));
    }

    public function edit(EditHistoryRequest $request, History $history)
    {
        $customers = Customer::all();
        $settings = json_decode($history->settings);
        $template_id = $settings->output_dimensions;
        $dc = Customer::where('id', $history->customer)->first();
        $customer_id = $dc->id;
        // Todo: move to service
        $query = Theme::where('customer_id', $customer_id)->where('status', '1')->orderBy('order');
        if (!auth()->user()->isMasterAdmin()) {
            $query->whereIn('company_id', [0, auth()->user()->company_id]);
        }
        $themes = $query->get();
        $theme_list = [];
        foreach($themes as $theme) {
            $theme_list[] = [
                'id' => $theme->id,
                'name' => $theme->name,
                'attributes' => json_decode($theme->attributes)
            ];
        }
        
        if ($customer_id == 3) {
            return view('frontend.user.create_AmazonFresh', [
                'settings' => $settings, 
                'showlogs' => 0, 
                'customers' => $customers, 
                'customer' => $customer_id
                ]
            );
        } else if ($customer_id == 2) {
            $template = Template::find($template_id);
            $new_templates = $this->get_templates(2);
            $new_template_names = array_column($new_templates, 'name');
            if ($template->system) {
                if ($template_id == 0) {
                    return view(
                        'frontend.create.create_Box',
                        [
                            'settings' => $settings,
                            'showlogs' => 0,
                            'customers' => $customers,
                            'customer' => $customer_id,
                            'new_templates' => $new_template_names,
                            'template' => $template
                        ]
                    );
                } else if ($template_id == 1) {
                    return view(
                        'frontend.create.create_Box',
                        [
                            'settings' => $settings,
                            'showlogs' => 0,
                            'customers' => $customers,
                            'customer' => $customer_id,
                            'new_templates' => $new_template_names,
                            'template' => $template
                        ]
                    );
                } else if ($template_id == 2) {
                    return view(
                        'frontend.create.create_NutritionFacts',
                        [
                            'settings' => $settings,
                            'showlogs' => 0,
                            'customers' => $customers,
                            'customer' => $customer_id,
                            'new_templates' => $new_template_names,
                            'template' => $template
                        ]
                    );
                } else if ($template_id == 3) {
                    return view(
                        'frontend.create.create_NutritionFacts',
                        [
                            'settings' => $settings,
                            'showlogs' => 0,
                            'customers' => $customers,
                            'customer' => $customer_id,
                            'new_templates' => $new_template_names,
                            'template' => $template
                        ]
                    );
                } else if ($template_id == 4) {
                    return view(
                        'frontend.create.create_ImageCompilation',
                        [
                            'settings' => $settings,
                            'showlogs' => 0,
                            'customers' => $customers,
                            'customer' => $customer_id,
                            'new_templates' => $new_template_names,
                            'template' => $template
                        ]
                    );
                } else if ($template_id == 5) {
                    return view(
                        'frontend.create.create_VirtualBundle',
                        [
                            'settings' => $settings,
                            'showlogs' => 0,
                            'customers' => $customers,
                            'customer' => $customer_id,
                            'new_templates' => $new_template_names,
                            'template' => $template
                        ]
                    );
                }
            } else {
                // $template_id = $new_templates[$template - 6]['id'];
                $image_list = Images::all();
                return view(
                    'frontend.create.create_NewTemplate',
                    [
                        'settings' => $settings,
                        'showlogs' => 0,
                        'customers' => $customers,
                        'customer' => 'Amazon',
                        'customer_id' => 2,
                        'dc' => $dc,
                        'templates' => $new_templates,
                        'template' => $template, 
                        'template_fields' => $template->fields,
                        'themes' => $theme_list,
                        'image_list' => $image_list
                    ]
                );
            }
        } else if ($customer_id == 1) {
            return view('frontend.user.create', [
                'settings' => $settings, 
                'showlogs' => 0, 
                'customers' => $customers,
                'customer' => $customer_id
                ]
            );
        } else if ($customer_id == 8) {
            return view('frontend.user.create_MRHI', [
                'settings' => $settings, 
                'showlogs' => 0, 
                'customers' => $customers,
                'customer' => $customer_id
                ]
            );
        } else if ($customer_id == 4) {
            return view('frontend.create.create_Kroger', [
                'settings' => $settings, 
                'showlogs' => 0, 
                'customers' => $customers,
                'customer' => $customer_id,
                'themes' => $theme_list
                ]
            );
        } else if ($customer_id == 5) {
            return view('frontend.create.create_Superama', [
                'settings' => $settings, 
                'showlogs' => 0, 
                'customers' => $customers,
                'customer' => $customer_id
                ]
            );
        } else if ($customer_id == 6) {
            return view('frontend.create.create_Box', [
                'settings' => $settings, 
                'showlogs' => 0, 
                'customers' => $customers,
                'customer' => $customer_id
                ]
            );
        } else if ($customer_id == 7) {
            return view('frontend.create.create_Walmart', [
                'settings' => $settings, 
                'showlogs' => 0, 
                'customers' => $customers,
                'customer' => $customer_id,
                'themes' => $theme_list
                ]
            );
        } else if ($customer_id == 9) {
            return view('frontend.create.instagram.create_Instagram_Image', [
                'settings' => $settings, 
                'showlogs' => 0, 
                'customers' => $customers,
                'customer' => $customer_id
                ]
            );
        } else if ($customer_id == 10) {
            return view('frontend.create.create_Pilot', [
                'settings' => $settings, 
                'showlogs' => 0, 
                'customers' => $customers,
                'customer' => $customer_id
                ]
            );
        } else if ($customer_id == 11) {
            return view('frontend.create.create_Sam', [
                'settings' => $settings, 
                'showlogs' => 0, 
                'customers' => $customers,
                'customer' => $customer_id
                ]
            );
        } else {
            $template = Template::find($template_id);
            $new_templates = $this->get_templates($dc->id);
            $image_list = Images::all();
            return view(
                'frontend.create.create_NewTemplate',
                [
                    'settings' => $settings,
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer' => $customer_id,
                    'customer_id' => $customer_id,
                    'dc' => $dc,
                    'templates' => $new_templates,
                    'template' => $template, 
                    'template_fields' => $template->fields,
                    'themes' => $theme_list, 
                    'image_list' => $image_list
                ]
            );
        }
    }

    public function destroy(Request $request, History $history)
    {
        $this->historyService->destroy($history);
        return redirect()->route('frontend.history.index')->withFlashSuccess(__('The history item was successfully deleted.'));
    }

    public function download_all(Request $request)
    {
        $history_ids = $request->history_ids;
        $download_name = $request->download_name;
        $result = $this->historyService->download_all($history_ids, $download_name);
        echo $result;
    }
    
    public function isExistDraft(Request $request) 
    {
        $project_name = $request->project_name;
        if ($project_name == '') {
            echo false;
        }
        $result = $this->historyService->getByProjectName($project_name);
        echo $result ? true : false;
    }

    public function columns(Request $request)
    {
        $columns = $request->all();
        unset($columns['_token']);

        $user = auth()->user();
        $user->draftColumn()->updateOrCreate(['user_id' => $user->id], ['user_id' => $user->id, 'columns' => implode(',', array_keys($columns))]);

        return redirect()->route('frontend.history.index');
    }

    private function get_templates($customer_id)
    {
        $templates = Template::where('customer_id', $customer_id)->get()->toArray();
        return $templates;
    }

    
    public function share(Request $request, $url) {
        
      
        $history = History::findByShareLink($url);
        $images = array();
       
        if (isset($history->jpg_files)){
            $pics = explode(" ", $history->jpg_files);
            foreach ($pics as $picture){
                $image = file_get_contents( url("/")."/share?file=/outputs/jpg/$picture");
                if ($image){
                    $images[]= base64_encode($image);
                }
            }

        }



        return view(
            'frontend.share',
            [
                "images" => $images
            ]
        );
        
    }


}
