<?php

namespace App\Domains\Auth\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Domains\Auth\Models\Theme;
use App\Domains\Auth\Models\Images;
use App\Domains\Auth\Models\Template;
use App\Domains\Auth\Services\CustomerService;
use App\Domains\Auth\Services\GridLayoutService;
use App\Domains\Auth\Services\GridLayoutTemplateService;
use App\Domains\Auth\Services\TemplateService;
use App\Domains\Auth\Services\Templates\NewTemplateService;




/**
 * Class GridLayoutTemplateController.
 */
class GridLayoutTemplateController extends Controller
{
    protected $gridLayoutService;

    protected $gridLayoutTemplateService;

    protected $customerService;

    protected $templateService;

    protected $newTemplateService;

    public function __construct(
        GridLayoutService $gridLayoutService,
        GridLayoutTemplateService $gridLayoutTemplateService,
        CustomerService $customerService,
        TemplateService $templateService,
        NewTemplateService $newTemplateService
    ) {
        $this->gridLayoutService = $gridLayoutService;
        $this->gridLayoutTemplateService = $gridLayoutTemplateService;
        $this->customerService = $customerService;
        $this->templateService = $templateService;
        $this->newTemplateService = $newTemplateService;
    }

    public function index(Request $request)
    {
        $grid_layout_template = $this->gridLayoutTemplateService->getTemplate($request->layout, $request->instance_id);
        $customer = $this->customerService->getById($request->customer_id);
        $template = $this->templateService->getById($request->template_id);
        $positioning_options = $template->positioning_options;
        foreach ($positioning_options as $positioning_option) {
            $positioning_option->fields;
        }
        $query = Theme::where('customer_id', $request->customer_id)->where('status', '1')->orderBy('order');
        if (!auth()->user()->isMasterAdmin()) {
            $query->whereIn('company_id', [0, auth()->user()->company_id]);
        }
        $themes = $query->get();
        $theme_list = [];
        foreach ($themes as $theme) {
            $theme_list[] = [
                'id' => $theme->id,
                'name' => $theme->name,
                'attributes' => json_decode($theme->attributes)
            ];
        }
        $image_list = Images::all();
        $grid_layout = $this->gridLayoutService->getById($request->layout);
        $settings = json_decode($grid_layout->settings);
        Log::debug($grid_layout->settings);
        $templates = $this->customerService->getTemplates($request->customer_id);
        $template_instances = [];
        foreach ($settings as $row) {
            $tt = $templates->firstWhere('id', $row->template_id);
            if ($tt) {
                $template_instances[] = [
                    'name' => $tt->name,
                    'instance_id' => $row->instance_id,
                ];
            }
        }

        $instance_id = $request->instance_id;
        $previous_template = null;
        $next_template = null;

        for ($j=0 ; $j<count($template_instances); $j++){
            if ($template_instances[$j]['instance_id'] == $instance_id ){
                if ($j>0)
                    $previous_template = $template_instances[$j-1]['instance_id'];
                if ($j< (count($template_instances)-1) )
                    $next_template = $template_instances[$j+1]['instance_id'];
            }
        }

        return view('frontend.group.template', [
            'template' => $template,
            'template_instances' => $template_instances,
            'customer' => $customer,
            'instance_id' =>$instance_id,
            'layout_id' => $request->layout,
            'layout_template_id' => isset($grid_layout_template) ? $grid_layout_template->id : -1,
            'settings' => isset($grid_layout_template) ? json_decode($grid_layout_template->settings) : [],
            'themes' => $theme_list, 
            'image_list' => $image_list,
            'positioning_options' => $positioning_options,
            'previous_template' => $previous_template,
            'next_template' => $next_template,
        ]);
    }

    public function store(Request $request)
    {
        $new_template = $this->gridLayoutTemplateService->store([
            'layout_id' => $request->layout_id,
            'template_id' => $request->template_id,
            'instance_id' => $request->instance_id,
            'settings' => $this->newTemplateService->get_history_settings($request)
        ]);

        if ($request->has('next_template') && $request->next_template != ''){

            if ($request->carry_over){
               $this->update_next_template($request, $new_template->settings,$request->next_template, $request->layout_id);
            }
            
            $template_id = $this->gridLayoutService->findTemplateIdByInstance($request->layout_id, $request->next_template);

            return redirect()->route('frontend.banner.group.template', [
                'customer_id'   => $request->customer_id, 
                'layout'        => $request->layout_id,
                'instance_id'   => $request->next_template , 
                'template_id'   => $template_id, 
            ]);
        }else{
            
            return redirect()->route('frontend.banner.group.edit', ['customer_id' => $request->customer_id, 'layout' => $request->layout_id]);
        }


        return redirect()->route('frontend.banner.group.edit', ['customer_id' => $request->customer_id, 'layout' => $request->layout_id]);
    }

    private function update_next_template($request , $settings, $instance_id,$layout_id){
        
        $next_template = $this->gridLayoutTemplateService->getTemplate($layout_id, $instance_id);

        if ($next_template){
        $next_settings = json_decode($next_template->settings, 1);

        $template = Template::find($next_settings['template_id']);
     
        $next_template_fields = $template->getFieldsArray();
        
        $previous_settings =( json_decode($settings,1));

        $exceptions = array(
            "file_ids",
            "logos",
            "x_offset",
            "y_offset",
            "angle",
            "scale",
            "moveable",
            "p_width",
            "p_height",
            "theme",
            "background",
            "bk_img_offset_x",	
            "bk_img_offset_y",
        );

        foreach ($previous_settings as $key => $value){
            if (array_key_exists($key, $next_settings) && (
                array_key_exists($key, $next_template_fields) || 
                str_starts_with($key, 'upload_image') || 
                in_array ($key, $exceptions)))
                {
                 //copy the value
                 $next_settings[$key] = $value;
                }

        }

        if (array_key_exists('carry_over', $previous_settings)){
            $next_settings["carry_over"] = $previous_settings['carry_over'];
        }

        // save the settings
        $next_template->settings =json_encode($next_settings);
        $next_template->save();

        }else{

            // just save the whole thing.. unexistant fields will be ignore on the view
            // and everything will be sorted out on next save.

            $template_id = $this->gridLayoutService->findTemplateIdByInstance($layout_id, $instance_id);
            $settings = $this->newTemplateService->get_history_settings($request);
            $settings = json_decode($settings, 1);
            $settings['template_id'] = $template_id;

            $this->gridLayoutTemplateService->store([
                'layout_id' => $request->layout_id,
                'template_id' => $template_id,
                'instance_id' => $instance_id,
                'settings' => json_encode($settings),
            ]);

        }

    }

    public function update(Request $request)
    {
        $grid_layout_template = $this->gridLayoutTemplateService->getTemplate($request->layout_id, $request->instance_id);

        $settings = $this->newTemplateService->get_history_settings($request);
        
        $this->gridLayoutTemplateService->update($grid_layout_template, [
            'settings' => $settings
        ]);

        if ($request->has('next_template') && $request->next_template != ''){


            if ($request->carry_over){
               $this->update_next_template($request, $settings,$request->next_template, $request->layout_id);
            }

            $template_id = $this->gridLayoutService->findTemplateIdByInstance($request->layout_id, $request->next_template);

            return redirect()->route('frontend.banner.group.template', [
                'customer_id'   => $request->customer_id, 
                'layout'        => $request->layout_id,
                'instance_id'   => $request->next_template , 
                'template_id'   => $template_id , 
            ]);
        }else{
            
            return redirect()->route('frontend.banner.group.edit', ['customer_id' => $request->customer_id, 'layout' => $request->layout_id]);
        }
    }
}
