<?php

namespace App\Domains\Auth\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Domains\Auth\Models\Project;
use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Models\Theme;
use App\Domains\Auth\Models\Template;
use App\Domains\Auth\Models\TemplateField;
use App\Domains\Auth\Models\Images;
use App\Domains\Auth\Services\ProjectService;
/**
 * Class ProjectController.
 */
class ProjectController extends Controller
{
    /**
     * @var ProjectService
     */
    protected $projectService;

    /**
     * ProjectController constructor.
     *
     * @param  ProjectService  $projectService
     */
    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('frontend.user.project', [ 'active_columns' => auth()->user()->getActiveProjectColumns() ]);
    }

    public function download(Request $request, Project $project) {

        return Storage::disk('s3')->download( $project->url, $project->projectname.".zip" );

    }

    public function show(Request $request, Project $project) {
        return ['jpg_files' => $project->jpg_files];
    }

    public function edit(Request $request, Project $project)
    {
        $customers = Customer::all();
        $settings = json_decode($project->settings);
        $template_id = $settings->output_dimensions;
        $dc = Customer::where('id', $project->customer)->first();
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
                        'customer' => $customer_id,
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

    public function destroy(Request $request, Project $project)
    {
        $project->teams()->sync(array());
        $this->projectService->destroy($project);

        return redirect()->route('frontend.projects.index')->withFlashSuccess(__('The project item was successfully deleted.'));
    }

    public function isExistProject(Request $request) 
    {
        $project_name = $request->project_name;
        if ($project_name == '') {
            echo false;
        }
        $result = $this->projectService->getByProjectName($project_name);
        echo $result ? true : false;
    }

    public function columns(Request $request)
    {
        $columns = $request->all();
        unset($columns['_token']);

        $user = auth()->user();
        $user->projectColumn()->updateOrCreate(['user_id' => $user->id], ['user_id' => $user->id, 'columns' => implode(',', array_keys($columns))]);

        return redirect()->route('frontend.projects.index');
    }

    public function request_approve(Request $request, Project $project)
    {
        return view('frontend.user.request_approve', [
            'project' => $project,
            'requester_id' => $request->requester_id,
            'requeste_timestamp' => $request->request_timestamp
        ]);
    }

    public function approve(Request $request, Project $project)
    {
        $this->projectService->approve([
            'id' => $project->id,
            'requester_id' => $request->requester_id,
            'request_timestamp' => $request->request_timestamp,
            'user_id' => $request->user_id,
            'comment' => $request->comment
        ]);
        
        return response()->json(['approve' => 'success']);
    }

    public function reject(Request $request, Project $project)
    {
        $this->projectService->reject([
            'id' => $project->id,
            'requester_id' => $request->requester_id,
            'request_timestamp' => $request->request_timestamp,
            'user_id' => $request->user_id,
            'comment' => $request->comment
        ]);

        return response()->json(['reject' => 'success']);
    }

    public function subprojects(Request $request, Project $project)
    {
        return view('frontend.user.subproject', [ 'active_columns' => auth()->user()->getActiveProjectColumns(), 'parent_id' => $project->id ]);
    }

    public function master_projects(Request $request)
    {
        return response()->json($this->projectService->getMasterProjects($request->search));
    }

    public function countries()
    {
        return response()->json(array_keys(config('lang')));
    }

    public function languages(Request $request)
    {
        return response()->json(config('lang')[$request->c]);
    }
    
    private function get_templates($customer_id)
    {
        $templates = Template::where('customer_id', $customer_id)->get()->toArray();
        return $templates;
    }

    public function download_all(Request $request)
    {
        $project_ids = $request->project_ids;
        $download_name = $request->download_name;
        $result = $this->projectService->download_all($project_ids, $download_name);
        echo $result;
    }

    public function share(Request $request, $url) {

        $project = Project::findByShareLink($url);
        $images = array();
       
        if (isset($project->jpg_files)){
            $pics = explode(" ", $project->jpg_files);
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
