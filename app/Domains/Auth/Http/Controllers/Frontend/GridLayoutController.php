<?php

namespace App\Domains\Auth\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Models\Theme;
use App\Domains\Auth\Models\GridLayout;
use App\Domains\Auth\Models\GridLayoutTemplate;
use App\Domains\Auth\Services\CompanyService;
use App\Domains\Auth\Services\GridLayoutService;
use App\Domains\Auth\Services\GridLayoutTemplateService;
use App\Domains\Auth\Services\CustomerService;
use App\Domains\Auth\Services\TemplateService;
use App\Domains\Auth\Services\Templates\NewTemplateService;

/**
 * Class GridLayoutController.
 */
class GridLayoutController extends Controller
{
    protected $gridLayoutService;

    protected $gridLayoutTemplateService;

    protected $customerService;

    protected $templateService;

    protected $newTemplateService;

    protected $companyService;

    public function __construct(
        GridLayoutService $gridLayoutService,
        GridLayoutTemplateService $gridLayoutTemplateService,
        CustomerService $customerService,
        TemplateService $templateService,
        NewTemplateService $newTemplateService,
        CompanyService $companyService
    ) {
        $this->gridLayoutService = $gridLayoutService;
        $this->gridLayoutTemplateService = $gridLayoutTemplateService;
        $this->customerService = $customerService;
        $this->templateService = $templateService;
        $this->newTemplateService = $newTemplateService;
        $this->companyService = $companyService;
    }

    public function index(Request $request)
    {
        $companies = [];
        $user = auth()->user();
        if ($user->isMasterAdmin()) {
            $companies = $this->companyService->all();
        }
        return view('frontend.group.index', ['customer_id' => $request->customer_id, 'companies' => $companies]);
    }

    public function create(Request $request)
    {
        $templates = $this->customerService->getTemplates($request->customer_id);
        return view('frontend.group.create', ['customer_id' => $request->customer_id, 'templates' => $templates]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $gridLayout = $this->gridLayoutService->store([
            'customer_id' => $request->customer_id,
            'user_id' => $user->id,
            'name' => $request->name,
            'settings' => $request->settings,
            'options' => json_encode([
                'include_psd' => false,
                'stroke_color' => '#A9A9A9',
                'stroke_width' => 1,
                'show_separator' => false,
                'show_canvas' => true,
                'show_overlay' => true,
                'show_mockup' => false,
                'use_custom_naming' => false,
                'separator_color' => 'white',
                'include_template_name' => false,
                'include_template_size' => false,
                'template_name_size_position' => 'upper_left',
                'title' => $request->name
            ])
        ]);

        $gridLayout->companies()->attach($user->company_id);

        return redirect()->route('frontend.banner.group.index', ['customer_id' => $request->customer_id]);
    }

    public function assign(Request $request)
    {
        $layout_id = $request->layout_id;
        $company_ids = [];
        $params = array_keys($request->all());
        foreach ($params as $param) {
            if (str_contains($param, "company_id_")) {
                $company_ids[] = (int)explode("company_id_", $param)[1];
            }
        }

        $layout = GridLayout::find($layout_id);
        $layout->companies()->sync([auth()->user()->company_id]);
        if (count($company_ids) > 0) {
            GridLayout::find($layout_id)->companies()->attach($company_ids);
        }

        return redirect()->route('frontend.banner.group.index', ['customer_id' => $request->customer_id]);
    }

    public function edit(Request $request, $customer_id, GridLayout $layout)
    {
        $templates = $this->customerService->getTemplates($customer_id);
        return view('frontend.group.edit', ['customer_id' => $customer_id, 'layout' => $layout, 'templates' => $templates]);
    }

    public function update(Request $request, $customer_id, GridLayout $layout)
    {
        $current_instances = array();
        $this->gridLayoutService->update($layout, $request->all());
        $settings = json_decode($request->settings);
        foreach ($settings as $layout_setting) {
            $grid_layout_template = $this->gridLayoutTemplateService->getTemplate($layout->id, $layout_setting->instance_id);
            $current_instances[] =  $layout_setting->instance_id;
            if (!isset($grid_layout_template)) {
                $this->gridLayoutTemplateService->store([
                    'layout_id' => $layout->id,
                    'template_id' => $layout_setting->template_id,
                    'instance_id' => $layout_setting->instance_id,
                    'settings' => "[]"
                ]);
            }
        }
        if ( count($current_instances)){
            // delete any extra instances 
            GridLayoutTemplate::where('layout_id',$layout->id)->whereNotIn('instance_id', $current_instances)->delete();
        }else{
            //delete everything
            GridLayoutTemplate::where('layout_id',$layout->id)->delete();
        }

        return redirect()->route('frontend.banner.group.index', ['customer_id' => $customer_id]);
    }

    public function destroy(Request $request, $customer_id, GridLayout $layout)
    {
        $this->gridLayoutService->delete($layout);

        return redirect()->route('frontend.banner.group.index', ['customer_id' => $customer_id]);
    }

    public function show($customer_id, GridLayout $layout)
    {
        $customer = $this->customerService->getById($customer_id);
        return view('frontend.group.show', ['customer' => $customer, 'layout' => $layout]);
    }

    public function copy($customer_id, GridLayout $layout)
    {
        $this->gridLayoutService->copy($layout);

        return redirect()->route('frontend.banner.group.index', ['customer_id' => $customer_id]);
    }

    public function preview($customer_id, GridLayout $layout)
    {
        $shadows = [];
        foreach ($layout->templates as $instance) {
            $instance->template;
            if ($instance->template != null) {
                $instance->template->fields;
                $positioning_options = $instance->template->positioning_options;
                foreach ($positioning_options as $positioning_option) {
                    $positioning_option->fields;
                }
    
                $settings = json_decode($instance->settings);
                $arr = ['colors' => [], 'shadow' => []];
                if (isset($settings->theme)) {
                    $theme = Theme::find($settings->theme);
                    if (isset($theme)) {
                        $attributes = json_decode($theme->attributes);
                        $shadow = [];
                        if (isset($attributes[3])) {
                            $shadow = $attributes[3]->list;
                        }
                        $arr = [
                            'colors' => $attributes[0]->list,
                            'shadow' => $shadow
                        ];
                    }
                }
                $shadows[] = $arr;
            }
        }

        return response()->json([
            'name' => $layout->name,
            'settings' => json_decode($layout->settings),
            'instances' => $layout->templates_lightweight(),
            'shadows' => $shadows,
            'alignment' => $layout->alignment,
            'options' => json_decode($layout->options)
        ]);
    }

    public function change_aligns($customer_id, GridLayout $layout)
    {
        $customer = Customer::find($customer_id);

        $layout->alignment = $layout->alignment == 0 ? 1 : 0;
        $layout->save();

        return redirect()->route('frontend.banner.group.show', ['customer_id' => $customer->id, 'layout' => $layout]);
    }

    public function update_options(Request $request, $customer_id, GridLayout $layout)
    {
        $new_options = $request->all();

        unset($new_options['_token']);
        unset($new_options['_method']);
        $checkboxes = ['prepend_to_filename', 'include_psd', 'include_template_name', 'include_template_size', 'show_separator', 'use_custom_naming', 'show_canvas', 'show_overlay', 'show_mockup'];
        foreach ($checkboxes as $checkbox) {
            if (isset($new_options[$checkbox])) {
                $new_options[$checkbox] = true;
            } else {
                $new_options[$checkbox] = false;
            }
        }

        $web_page_file = $request->file('web_page');
        if (isset($web_page_file)) {
            $web_page_file_name = $web_page_file->getClientOriginalName();
            $web_page_file_path = uniqid() . '.' . $web_page_file->clientExtension();
            Storage::disk('public')->putFileAs('web_pages', $web_page_file, $web_page_file_path);
            $new_options['web_page_file_name'] = $web_page_file_name;
            $new_options['web_page_file_path'] = $web_page_file_path;
        }

        $options = json_decode($layout->options, true);
        $downloadable_templates = [];
        if (isset($options['downloadable_templates'])) {
            $downloadable_templates = $options['downloadable_templates'];
        }
        $downloadable_templates[$new_options['group']] = isset($new_options['downloadable_templates']) ? $new_options['downloadable_templates'] : [];

        $new_options['downloadable_templates'] = $downloadable_templates;
        $layout->options = json_encode($new_options);
        $layout->save();

        return redirect()->route('frontend.banner.group.show', ['customer_id' => $customer_id, 'layout' => $layout]);
    }

    public function save_changes(Request $request, $customer_id, GridLayout $layout)
    {
        $changes = $request->toArray();

        foreach ($changes as $change) {
            foreach ($layout->templates as $template) {
                if ($change['instance_id'] == $template->instance_id) {
                    $settings = json_decode($template->settings, true);
                    $settings[$change['key']] = $change['value'];
                    $template->settings = json_encode($settings);
                    $template->save();
                }
            }
        }

        return response()->json();
    }

    public function download_html($customer_id, GridLayout $layout)
    {
        $options = json_decode($layout->options);
        if (isset($options->web_page_file_path)) {
            return Storage::disk('public')->download('web_pages/' . $options->web_page_file_path, $options->web_page_file_name);
        }

        return redirect()->route('frontend.banner.group.show', ['customer_id' => $customer_id, 'layout' => $layout]);
    }
    
    public function edit_html($customer_id, GridLayout $layout)
    {
        $options = json_decode($layout->options);
        $content = '';
        if (isset($options->web_page_file_path)) {
            $content = Storage::disk('public')->get('web_pages/' . $options->web_page_file_path);
        }
        return view('frontend.group.edit_html', ['customer_id' => $customer_id, 'layout' => $layout, 'content' => $content]);
    }
    
    public function save_html(Request $request, $customer_id, GridLayout $layout)
    {
        $options = json_decode($layout->options);

        Storage::disk('public')->put('web_pages/' . $options->web_page_file_path, $request->content);

        return redirect()->route('frontend.banner.group.show', ['customer_id' => $customer_id, 'layout' => $layout]);
    }

    public function bulk_update(Request $request, $customer_id, GridLayout $layout)
    {
        $data = $request->all();
        $instance_id = $data['currentInstanceId'];
        $field_values = $data['fieldValues'];
        $instance_ids = $data['instanceIds'];
        $product_texts = $data['productTexts'];
        foreach ($layout->templates as $template) {
            if (in_array($template->instance_id, $instance_ids)) {
                $settings = json_decode($template->settings, true);
                foreach ($field_values as $key => $value) {
                    $settings[$key] = $value;
                }
                $settings['product_texts'] = json_encode($product_texts);
                $template->settings = json_encode($settings);
                $template->save();
            }
        }
        return response()->json(['success' => true]);
    }
}
