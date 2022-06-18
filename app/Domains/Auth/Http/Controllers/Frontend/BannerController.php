<?php

namespace App\Domains\Auth\Http\Controllers\Frontend;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Domains\Auth\Services\BannerService;
use App\Domains\Auth\Services\SettingsService;
use App\Domains\Auth\Services\TemplateService;
use App\Domains\Auth\Services\Templates\NutritionFactsTemplateService;
use App\Domains\Auth\Services\Templates\NutritionFactsHorizontalTemplateService;
use App\Domains\Auth\Services\Templates\VarietyPackTemplateService;
use App\Domains\Auth\Services\Templates\MRHITemplateService;
use App\Domains\Auth\Services\Templates\VideoTemplateService;
use App\Domains\Auth\Services\Templates\InstagramTemplateService;
use App\Domains\Auth\Services\Templates\VirtualBundleTemplateService;
use App\Domains\Auth\Services\Templates\KrogerTemplateService;
use App\Domains\Auth\Services\Templates\SuperamaTemplateService;
use App\Domains\Auth\Services\Templates\WalmartTemplateService;
use App\Domains\Auth\Services\Templates\PilotTemplateService;
use App\Domains\Auth\Services\Templates\SamTemplateService;
use App\Domains\Auth\Services\Templates\NewTemplateService;
use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Models\File;
use App\Domains\Auth\Models\ProductSelection;
use App\Domains\Auth\Models\Theme;
use App\Domains\Auth\Models\Template;
use App\Domains\Auth\Models\Images;
use App\Domains\Auth\Models\PositioningOption;
use App\Domains\Auth\Models\PositioningOptionField;
use App\Domains\Auth\Http\Exports\TemplateXlsxExport;
use App\Domains\Auth\Http\Exports\TemplateSheetExport;
use Illuminate\Support\Facades\Validator;


use function GuzzleHttp\json_decode;

/**
 * Class BannerController.
 */
class BannerController extends Controller
{
    protected $bannerService;

    protected $settingsService;

    protected $templateService;

    protected $nfTemplateService;

    protected $vpTemplateService;

    protected $mrhiTemplateService;

    protected $videoTemplateService;

    protected $instagramTemplateService;

    protected $vbTemplateService;

    protected $nfhTemplateService;

    protected $krogerTemplateService;

    protected $superamaTemplateService;

    protected $walmartTemplateService;

    protected $pilotTemplateService;

    protected $samTemplateService;

    protected $newTemplateService;

    /**
     * BannerController constructor.
     *
     * @param  BannerService  $bannerService
     * @param  SettingsService  $settingsService
     * @param  TemplateService  $templateService
     * @param  NutritionFactsTemplateService  $nfTemplateService
     * @param  NutritionFactsHorizontalTemplateService  $nfhTemplateService
     * @param  VarietyPackTemplateService  $vpTemplateService
     * @param  MRHITemplateService  $mrhiTemplateService
     * @param  VideoTemplateService  $videoTemplateService
     * @param  InstagramTemplateService  $instagramTemplateService
     * @param  VirtualBundleTemplateService  $vbTemplateService
     * @param  KrogerTemplateService  $krogerTemplateService
     * @param  SuperamaTemplateService  $superamaTemplateService
     * @param  WalmartTemplateService  $walmartTemplateService
     * @param  PilotTemplateService  $pilotTemplateService
     * @param  SamTemplateService  $samTemplateService
     */
    public function __construct(BannerService $bannerService, SettingsService $settingsService, TemplateService $templateService,
                                NutritionFactsTemplateService $nfTemplateService, MRHITemplateService $mrhiTemplateService,
                                VarietyPackTemplateService $vpTemplateService, VideoTemplateService $videoTemplateService,
                                InstagramTemplateService $instagramTemplateService, VirtualBundleTemplateService $vbTemplateService,
                                NutritionFactsHorizontalTemplateService $nfhTemplateService, KrogerTemplateService $krogerTemplateService,
                                SuperamaTemplateService $superamaTemplateService, WalmartTemplateService $walmartTemplateService,
                                PilotTemplateService $pilotTemplateService, SamTemplateService $samTemplateService,
                                NewTemplateService $newTemplateService)
    {
        $this->bannerService = $bannerService;
        $this->settingsService = $settingsService;
        $this->templateService = $templateService;
        $this->nfTemplateService = $nfTemplateService;
        $this->vpTemplateService = $vpTemplateService;
        $this->mrhiTemplateService = $mrhiTemplateService;
        $this->videoTemplateService = $videoTemplateService;
        $this->instagramTemplateService = $instagramTemplateService;
        $this->vbTemplateService = $vbTemplateService;
        $this->nfhTemplateService = $nfhTemplateService;
        $this->krogerTemplateService = $krogerTemplateService;
        $this->superamaTemplateService = $superamaTemplateService;
        $this->walmartTemplateService = $walmartTemplateService;
        $this->pilotTemplateService = $pilotTemplateService;
        $this->samTemplateService = $samTemplateService;
        $this->newTemplateService = $newTemplateService;
    }

    private function get_customers()
    {
        $customers = [];
        $user = auth()->user();
        if (!$user->isTeamMember()) {
            if ($user->isMasterAdmin()) {
                $customers = Customer::where('status', 1)->get();
            } else {
                $customers = Customer::where('system', 1)->where('status', 1)->get();
                $customers = $customers->merge($user->company->customers);
            }
        } else {
            foreach ($user->teams as $team) {
                foreach ($team->customers as $customer) {
                    if ($customer->status == 1)
                        $customers[] = $customer;
                
                }
            }

            $models = array_map(function ($customer) {
                return $customer->id;
            }, $customers);
            $unique_models = array_unique($models);
            $customers = array_values(array_intersect_key($customers, $unique_models));
        }
        if (!$user->isMasterAdmin()) {
            $cus = array();
            foreach ($customers as $customer) {
                if (($user->company->has_mrhi || $customer->value != 'mrhi') && ($user->company->has_pilot || $customer->value != 'pilot')) {
                    $cus[] = $customer;
                }
            }
            return $cus;
        }
        return $customers;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {

        // check if previously working with another customer and template
        $current_customer = $request->session()->get('current_customer');
        $template = $request->session()->get('current_template');

        if ($current_customer){
            $customer = Customer::where('value', $current_customer)->first();
            if ($customer && $customer->status == 1){
                if ($customer->hasTemplate($template) || $template == 0 )
                    return redirect()->route('frontend.banner.customer', ['customer' => $current_customer, 'template_id' => $template]);
            
            }
        }

        $customers = $this->get_customers();
        
        $f_ids = [];
        if (isset($request->file_ids)) {
            $files_ids = preg_replace('/\s+/', " ", $request->file_ids);
            $files_ids = array_map("intval", explode(",", $files_ids));
            $files = File::whereIn('id', $files_ids)->get();
            foreach ($files as $file) {
                $arr = explode(".", $file->name);
                array_pop($arr);
                $f_ids[] = implode(".", $arr);
            }
        }
        $user = auth()->user();
        $customer_id = $user->customer_id;

        // check if the user's customer is enabled
        $customer = Customer::find($customer_id );
        if ($customer->status !== 1){
            $customer_id = null;
        }

        $hasCustomer = false;
        if (!$user->isTeamMember()) {
            $hasCustomer = true;
        } else {
            foreach ($user->teams as $team) {
                foreach ($team->customers as $cu) {
                    if ($cu->id == $customer_id) $hasCustomer = true;
                }
            }
        }
        
        if ((!$hasCustomer) || ($customer_id == 8 && !$user->company->has_mrhi)) {
            $customer_id = $customers[0]->id;
        }

        // if still doesn't have a customer.. get the first of the available list
        if (!$customer_id){
            $customer_id = $customers[0]->id;
        }

        // Amazon Fresh
        if ($customer_id == 3) {
            $settings = (object) array(
                'file_ids' => implode(" ", $f_ids),
                'show_3h' => $this->settingsService->getbyKey("Show_3H"),
                'show_text_tracking' => $this->settingsService->getbyKey("Show_Text_Tracking")
            );
            return view(
                'frontend.user.create_AmazonFresh',
                [
                    'settings' => $settings,
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer' => $customer_id
                ]
            );
        // Amazon     
        } else if ($customer_id == 2) {
            $templates = $this->templateService->getByCustomerId(2);
            $template = Template::where('customer_id', 2)->where('system', 1)->where('system_key', 0)->first();
            // Todo: move to service
            $query = Theme::where('customer_id', 2)->where('status', '1')->orderBy('order');
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
            return view(
                'frontend.create.create_Box',
                [
                    'settings' => (object) array(
                        'file_ids' => implode(" ", $f_ids),
                        'output_dimensions' => 0
                    ),
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer_id' => 2,
                    'customer' => $customer_id,
                    'templates' => $templates,
                    'template' => $template,
                    'themes' => $theme_list
                ]
            );
        // 
        // Generic
        } else if ($customer_id == 1) {
            return view(
                'frontend.user.create',
                [
                    'settings' => (object) array(
                        'file_ids' => implode(" ", $f_ids),
                        'output_dimensions' => -1
                    ),
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer' => $customer_id
                ]
            );
        // Mobile Ready Hero
        } else if ($customer_id == 8) {
            return view(
                'frontend.user.create_MRHI',
                [
                    'settings' => (object) array(
                        'file_ids' => implode(" ", $f_ids),
                        'output_dimensions' => 0
                    ),
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer' => $customer_id
                ]
            );
        // Kroger
        } else if ($customer_id == 4) {
            // Todo: move to service
            $query = Theme::where('customer_id', 4)->where('status', '1')->orderBy('order');
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
            return view(
                'frontend.create.create_Kroger',
                [
                    'settings' => (object) array(
                        'file_ids' => implode(" ", $f_ids),
                        'output_dimensions' => 0
                    ),
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer' => $customer_id,
                    'themes' => $theme_list
                ]
            );
        // Superama
        } else if ($customer_id == 5) {
            return view(
                'frontend.create.create_Superama',
                [
                    'settings' => (object) array(
                        'file_ids' => implode(" ", $f_ids),
                        'output_dimensions' => 0
                    ),
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer' => $customer_id
                ]
            );
        // Target
        } else if ($customer_id == 6) {
            return view(
                'frontend.create.create_Box',
                [
                    'settings' => (object) array(
                        'file_ids' => implode(" ", $f_ids),
                        'output_dimensions' => 0
                    ),
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer_id' => 6,
                    'customer' => $customer_id
                ]
            );
        // Walmart
        } else if ($customer_id == 7) {
            // Todo: move to service
            $query = Theme::where('customer_id', 7)->where('status', '1')->orderBy('order');
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
            return view(
                'frontend.create.create_Walmart',
                [
                    'settings' => (object) array(
                        'file_ids' => implode(" ", $f_ids),
                        'output_dimensions' => 0
                    ),
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer' => $customer_id,
                    'themes' => $theme_list
                ]
            );
        // Instagram
        } else if ($customer_id == 9) {
            return view(
                'frontend.create.instagram.create_Instagram_Image',
                [
                    'settings' => (object) array(
                        'file_ids' => implode(" ", $f_ids),
                        'output_dimensions' => 0
                    ),
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer' => $customer_id
                ]
            );
        // Pilot
        } else if ($customer_id == 10) {
            return view(
                'frontend.create.create_Pilot',
                [
                    'settings' => (object) array(
                        'file_ids' => implode(" ", $f_ids),
                        'output_dimensions' => 0
                    ),
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer' => $customer_id
                ]
            );
        // Sam's club
        } else if ($customer_id == 11) {
            return view('frontend.create.create_Sam', [
                'settings' => (object) array(
                    'file_ids'=> implode(" ", $f_ids),
                    'output_dimensions' => 0
                ),
                'showlogs' => 0,
                'customers' => $customers,
                'customer' => 11
                ]
            );
        } else {
            $customer = Customer::where('id', $customer_id)->first();
            $templates = $this->templateService->getByCustomerId($customer->id);
            $template = Template::find($templates[0]['id']);
            // Todo: move to service
            $query = Theme::where('customer_id', $customer->id)->where('status', '1')->orderBy('order');
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

            $positioning_options = $template->positioning_options;

            foreach ($positioning_options as $positioning_option) {
                $positioning_option->fields;
            }
            return view(
                'frontend.create.create_NewTemplate',
                [
                    'settings' => (object) array(
                        'file_ids' => implode(" ", $f_ids),
                        'output_dimensions' => $template->id,
                        'positioning_options' => $positioning_options
                    ),
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer' => $customer_id,
                    'customer_id' => $customer->id,
                    'dc' => $customer,
                    'templates' => $templates,
                    'template' => $template,
                    'template_fields' => $template->fields,
                    'themes' => $theme_list,
                    'image_list' => $image_list
                ]
            );
        }
    }

    public function view_customer(Request $request)
    {
        
        $customer = $request->customer;

        $customer_selected = Customer::where('value', $customer)->first();
        if (!$customer_selected || $customer_selected->status == 0){
            return redirect()->route('frontend.banner.index')->withFlashDanger("The customer does not exist or has been disabled");
        }

        $template_id = 0;

        if ($customer == 'generic' || $customer == 'amazon') {
            $template_id = -1;
        }

        if (isset($request->template_id)) {
            $template_id = $request->template_id;
            if (! $customer_selected->hasTemplate($template_id))
                $template_id = 0;
        }

      

        $request->session()->put('current_customer', $customer);
        $request->session()->put('current_template', $template_id);


        if ($customer == 'generic') return $this->view_generic($request, $template_id);
        if ($customer == 'amazon') return $this->view_amazon($request, $template_id);
        if ($customer == 'amazon_fresh') return $this->view_amazon_fresh($request, $template_id);
        if ($customer == 'kroger') return $this->view_kroger($request, $template_id);
        if ($customer == 'superama') return $this->view_superama($request, $template_id);
        if ($customer == 'target') return $this->view_target($request, $template_id);
        if ($customer == 'walmart') return $this->view_walmart($request, $template_id);
        if ($customer == 'mrhi') return $this->view_mrhi($request, $template_id);
        if ($customer == 'instagram') return $this->view_instagram($request, $template_id);
        if ($customer == 'pilot') return $this->view_pilot($request, $template_id);
        if ($customer == 'sam') return $this->view_sam($request, $template_id);

        $c = Customer::where('value', $customer)->first();
        if (isset($c)) {
            return $this->view_new($request, $c, $template_id);
        }

    }

    public function view_generic(Request $request, $template = -1)
    {
        $customers = $this->get_customers();
        return view(
            'frontend.user.create',
            [
                'settings' => (object) array('output_dimensions' => $template, 'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : ""),
                'showlogs' => 0,
                'customers' => $customers,
                'customer' => 1,
                'template' => $template
            ]
        );
    }

    public function view_amazon(Request $request, $template_id = -1)
    {
        $customers = $this->get_customers();
        $templates = $this->templateService->getByCustomerId(2);

        $template = null;
        if ($template_id > 0) {
            $template = Template::find($template_id);
        } else {
            $template = Template::where('customer_id', 2)->where('system', 1)->where('system_key', 0)->first();
        }
        // Todo: move to service
        $query = Theme::where('customer_id', 2)->where('status', '1')->orderBy('order');
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

        if ($template->system) {
            switch ($template->system_key) {
                case 0:
                    return view(
                        'frontend.create.create_Box',
                        [
                            'settings' => (object) array('output_dimensions' => $template->system_key, 'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : ""),
                            'showlogs' => 0,
                            'customers' => $customers,
                            'customer_id' => 2,
                            'customer' => 2,
                            'templates' => $templates,
                            'template' => $template
                        ]
                    );
                case 1:
                    return view(
                        'frontend.create.create_Box',
                        [
                            'settings' => (object) array(
                                'output_dimensions' => $template->system_key,
                                'file_top_ids' => isset($_COOKIE["file_top_ids"]) ? $_COOKIE["file_top_ids"] : "",
                                'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : ""
                            ),
                            'showlogs' => 0,
                            'customers' => $customers,
                            'customer_id' => 2,
                            'customer' => 2,
                            'templates' => $templates,
                            'template' => $template
                        ]
                    );
                case 2:
                    return view(
                        'frontend.create.create_NutritionFacts',
                        [
                            'settings' => (object) array('output_dimensions' => $template->system_key, 'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : ""),
                            'showlogs' => 0,
                            'customers' => $customers,
                            'customer' => 2,
                            'customer_id' => 2,
                            'templates' => $templates,
                            'template' => $template
                        ]
                    );
                case 3:
                    return view(
                        'frontend.create.create_NutritionFacts',
                        [
                            'settings' => (object) array('output_dimensions' => $template->system_key, 'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : ""),
                            'showlogs' => 0,
                            'customers' => $customers,
                            'customer' => 2,
                            'customer_id' => 2,
                            'templates' => $templates,
                            'template' => $template
                        ]
                    );
                case 4:
                    return view(
                        'frontend.create.create_ImageCompilation',
                        [
                            'settings' => (object) array(
                                'output_dimensions' => $template->system_key,
                                'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : "",
                                'images' => isset($_COOKIE["file_ids"]) ? explode(" ", $_COOKIE["file_ids"]) : [],
                                'fade_type' => isset($_COOKIE["fade_type"]) ? $_COOKIE["fade_type"] : "dissolve"
                            ),
                            'showlogs' => 0,
                            'customers' => $customers,
                            'customer' => 2,
                            'customer_id' => 2,
                            'templates' => $templates,
                            'template' => $template
                        ]
                    );
                case 5:
                    return view(
                        'frontend.create.create_VirtualBundle',
                        [
                            'settings' => (object) array(
                                'output_dimensions' => $template->system_key,
                                'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : ""
                            ),
                            'showlogs' => 0,
                            'customers' => $customers,
                            'customer' => 2,
                            'customer_id' => 2,
                            'templates' => $templates,
                            'template' => $template
                        ]
                    );
            }
        } else {
            $image_list = Images::all();
            $customer = Customer::find(2);

            return view(
                'frontend.create.create_NewTemplate',
                [
                    'settings' => (object) array(
                        'output_dimensions' => $template->id,
                        'file_ids' => isset($_COOKIE["file_ids"]) && $_COOKIE["file_ids"]!='' ? $_COOKIE["file_ids"] : ""
                    ),
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer' => 2,
                    'customer_id' => 2,
                    'dc' => $customer,
                    'templates' => $templates,
                    'template' => $template,
                    'template_fields' => $template->fields,
                    'themes' => $theme_list,
                    'image_list' => $image_list
                ]
            );
        }
    }

    public function view_amazon_fresh(Request $request, $template = 0)
    {
        $customers = $this->get_customers();
        $settings = (object) array(
            'output_dimensions' => $template,
            'show_3h' => $this->settingsService->getbyKey("Show_3H"),
            'show_text_tracking' => $this->settingsService->getbyKey("Show_Text_Tracking"),
            'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : ""
        );
        return view(
            'frontend.user.create_AmazonFresh',
            [
                'settings' => $settings,
                'showlogs' => 0,
                'customers' => $customers,
                'customer' => 3
            ]
        );
    }

    public function view_kroger(Request $request, $template = 0)
    {
        $customers = $this->get_customers();
        // Todo: move to service
        $query = Theme::where('customer_id', 4)->where('status', '1')->orderBy('order');
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
        return view(
            'frontend.create.create_Kroger',
            [
                'settings' => (object) array(
                    'output_dimensions' => $template,
                    'project_name' => isset($_COOKIE["project_name"]) ? $_COOKIE["project_name"] : "",
                    'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : ""
                ),
                'showlogs' => 0,
                'customers' => $customers,
                'customer' => 4,
                'template' => $template,
                'themes' => $theme_list
            ]
        );
    }

    public function view_superama(Request $request, $template = 0)
    {
        $customers = $this->get_customers();
        return view(
            'frontend.create.create_Superama',
            [
                'settings' => (object) array('output_dimensions' => $template, 'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : ""),
                'showlogs' => 0,
                'customers' => $customers,
                'customer' => 5,
                'template' => $template
            ]
        );
    }

    public function view_target(Request $request, $template = 0)
    {
        $customers = $this->get_customers();
        if ($template == 0) {
            return view(
                'frontend.create.create_Box',
                [
                    'settings' => (object) array('output_dimensions' => $template, 'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : ""),
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer_id' => 6,
                    'customer' => 6,
                    'template' => $template
                ]
            );
        } else if ($template == 1) {
            return view(
                'frontend.create.create_Box',
                [
                    'settings' => (object) array(
                        'output_dimensions' => $template,
                        'file_top_ids' => isset($_COOKIE["file_top_ids"]) ? $_COOKIE["file_top_ids"] : "",
                        'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : ""
                    ),
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer_id' => 6,
                    'customer' => 6,
                    'template' => $template
                ]
            );
        } else if ($template == 2) {
            return view(
                'frontend.create.create_NutritionFacts',
                [
                    'settings' => (object) array(
                        'output_dimensions' => $template,
                        'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : ""
                    ),
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer_id' => 6,
                    'customer' => 6,
                    'template' => $template
                ]
            );
        } else if ($template == 3) {
            return view(
                'frontend.create.create_NutritionFacts',
                [
                    'settings' => (object) array(
                        'output_dimensions' => $template,
                        'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : ""
                    ),
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer_id' => 6,
                    'customer' => 6,
                    'template' => $template
                ]
            );
        } else if ($template == 4) {
            return view(
                'frontend.create.create_ImageCompilation',
                [
                    'settings' => (object) array(
                        'output_dimensions' => $template,
                        'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : "",
                        'images' => isset($_COOKIE["file_ids"]) ? explode(" ", $_COOKIE["file_ids"]) : [],
                        'fade_type' => isset($_COOKIE["fade_type"]) ? $_COOKIE["fade_type"] : "dissolve"
                    ),
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer' => 6,
                    'customer_id' => 6,
                    'template' => $template
                ]
            );
        } else if ($template == 5) {
            return view(
                'frontend.create.create_VirtualBundle',
                [
                    'settings' => (object) array(
                        'output_dimensions' => $template,
                        'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : ""
                    ),
                    'showlogs' => 0,
                    'customers' => $customers,
                    'customer' => 6,
                    'customer_id' => 6,
                    'template' => $template
                ]
            );
        }
    }

    public function view_walmart(Request $request, $template = 0)
    {
        $customers = $this->get_customers();
        // Todo: move to service
        $query = Theme::where('customer_id', 7)->where('status', '1')->orderBy('order');
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
        return view(
            'frontend.create.create_Walmart',
            [
                'settings' => (object) array('output_dimensions' => $template, 'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : ""),
                'showlogs' => 0,
                'customers' => $customers,
                'customer' => 7,
                'template' => $template,
                'themes' => $theme_list
            ]
        );
    }

    public function view_mrhi(Request $request, $template = 0)
    {
        $customers = $this->get_customers();
        $product_font_size = 290;
        $quantity_font_size = 230;
        $unit_font_size = 230;
        $sub_font_size = 190;
        if ($template > 3) {
            $product_font_size = 110;
            $quantity_font_size = 100;
            $unit_font_size = 80;
            $sub_font_size = 100;
        }
        return view(
            'frontend.user.create_MRHI',
            [
                'settings' => (object) array(
                    'output_dimensions' => $template,
                    'product_size' => $product_font_size,
                    'sub_text_size' => $sub_font_size,
                    'quantity_size' => $quantity_font_size,
                    'unit_size' => $unit_font_size,
                    'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : "",
                    'product_format' => isset($_COOKIE["product_format"]) ? $_COOKIE["product_format"] : "",
                    'sub_text' => isset($_COOKIE["sub_text"]) ? $_COOKIE["sub_text"] : "",
                    'quantity' => isset($_COOKIE["quantity"]) ? $_COOKIE["quantity"] : "",
                    'unit' => isset($_COOKIE["unit"]) ? $_COOKIE["unit"] : ""
                ),
                'showlogs' => 0,
                'customers' => $customers,
                'customer' => 8
            ]
        );
    }

    public function view_instagram(Request $request, $template = 0)
    {
        $customers = $this->get_customers();
        $view_template = 'frontend.create.instagram.create_Instagram_Video';
        if ($template < 3) {
            $view_template = 'frontend.create.instagram.create_Instagram_Image';
        }
        return view(
            $view_template,
            [
                'settings' => (object) array(
                    'output_dimensions' => $template,
                    'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : "",
                    'images' => isset($_COOKIE["file_ids"]) ? explode(" ", $_COOKIE["file_ids"]) : [],
                    'fade_type' => isset($_COOKIE["fade_type"]) ? $_COOKIE["fade_type"] : "dissolve"
                ),
                'showlogs' => 0,
                'customers' => $customers,
                'customer' => 9
            ]
        );
    }

    public function view_pilot(Request $request, $template = 0)
    {
        $customers = $this->get_customers();
        return view(
            'frontend.create.create_Pilot',
            [
                'settings' => (object) array(
                    'output_dimensions' => $template,
                    'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : ""
                ),
                'showlogs' => 0,
                'customers' => $customers,
                'customer' => 10
            ]
        );
    }

    public function view_sam(Request $request, $template = 0)
    {
        $customers = $this->get_customers();
        return view('frontend.create.create_Sam', [
            'settings' => (object) array(
                'output_dimensions' => $template,
                'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : ""
            ),
            'showlogs' => 0,
            'customers' => $customers,
            'customer' => 11
            ]
        );
    }

    public function view_new(Request $request, $customer, $template_id) {
        $customers = $this->get_customers();
        $templates = $this->templateService->getByCustomerId($customer->id);
        $template = null;
        if ($template_id > 0) {
            $template = Template::find($template_id);
        } else {
            $template = Template::where('customer_id', $customer->id)->where('status', true)->first();
        }
        // Todo: move to service
        $query = Theme::where('customer_id', $customer->id)->where('status', '1')->orderBy('order');
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
        $positioning_options = null;
        if (isset($template)) {
            $positioning_options = $template->positioning_options;
            foreach ($positioning_options as $positioning_option) {
                $positioning_option->fields;
            }
        }
        return view(
            'frontend.create.create_NewTemplate',
            [
                'settings' => (object) array(
                    'output_dimensions' => isset($template) ? $template->id : 0,
                    'file_ids' => isset($_COOKIE["file_ids"]) ? $_COOKIE["file_ids"] : "",
                    'positioning_options' => $positioning_options
                ),
                'showlogs' => 0,
                'customers' => $customers,
                'customer' => $customer->id,
                'customer_id' => $customer->id,
                'dc' => $customer,
                'templates' => $templates,
                'template' => $template,
                'template_fields' => isset($template) ? $template->fields : [],
                'themes' => $theme_list,
                'image_list' => $image_list
            ]
        );
    }


      private function has_transparent_background($image){
        /*
        The color type of PNG image is stored at byte offset 25. Possible values of that 25'th byte is:
        0 - greyscale
        2 - RGB
        3 - RGB with palette
        4 - greyscale + alpha
        6 - RGB + alpha
        */
        //return (ord(@file_get_contents($image, NULL, NULL, 25, 1)) == 6);
        return (ord(substr($image,25,1))== 6 || ord(substr($image,25,1))== 4);
        
      }
      
      public function share(Request $request) {
        try {
            
            $file = $request->file;
            $lightweight = $request->has("lightweight");

            // compression factor (the lower value the more compressed)
            $compression= 60;
            
            // file extension
            $extension = pathinfo($file , PATHINFO_EXTENSION);

            if(Storage::disk('s3')->exists( $file )) {
                
                if ($lightweight){
                    $picstream = Storage::disk('s3')->get($file );
                                     
                    if ($extension == "png"){
                        // check if it was transparent background.
                        if ($this->has_transparent_background($picstream)){
                            // for now lets leave them like that
                            return Storage::disk('s3')->download( $file );
                        }
                    }
                
                    $im = new \Imagick();
                    $im->readImageBlob($picstream);
                    
                    $im->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $im->setImageCompressionQuality($compression);
                    $im->setImageFormat('jpg');
                    $imgBuff = $im->getimageblob();
                    $im->clear();

                    $mimetype = 'image/jpeg';
                    header("Cache-Control: public");
                    header("Content-Type: " . $mimetype);
                    header("Content-Description: File Transfer");
                    header("Content-Disposition: attachment; filename=" . basename($file,'.png').".jpg");
                    echo $imgBuff;exit;
                }else{
                    return Storage::disk('s3')->download( $file );
                }
            }else{
                $url = ltrim($file,"/");
                if (is_file ($file)){
                    $file = public_path()."/".$url;
                    if ($lightweight){
                        $picstream = file_get_contents($file);

                        if ($extension == "png"){
                            // check if it has transparent background.
                            if ($this->has_transparent_background($picstream)){
                                // for now lets leave them like that
                                $mimetype = mime_content_type($file);
                                header("Cache-Control: public");
                                header("Content-Type: " . $mimetype);
                                return readfile($file);
                            }
                        }

                        $im = new \Imagick();
                        $im->readImageBlob($picstream);
                       
                        $im->setImageCompression(\Imagick::COMPRESSION_JPEG);
                        $im->setImageCompressionQuality($compression);
                        $im->setImageFormat('jpg');

                        $imgBuff = $im->getimageblob();

                        /**
                         * This clears the image.jpg resource from our $img object and destroys the
                         * object. Thus, freeing the system resources allocated for doing our image
                         * manipulation.
                         */
                        $im->clear(); 
                        $mimetype = 'image/jpeg';
                        header("Cache-Control: public");
                        header("Content-Type: " . $mimetype);
                        echo $imgBuff;exit;

                    }else{
                        //$base64 = base64_encode(file_get_contents($file));
                        $mimetype = mime_content_type($file);
                        header("Cache-Control: public");
                        header("Content-Type: " . $mimetype);
                        return readfile($file);
                    }
               
                }

            }

        } catch (Exception $e) {
            \Log::error($e->getMessage() . "\n");
        }
    }

    /**
     * Download a zip archive of generated ad files and save draft.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request)
    {
        switch ($request->customer) {
            case "generic":
            case "amazon_fresh":
                return response()->json($this->bannerService->run($request, false, true));
                break;

            case "kroger":
                return response()->json($this->krogerTemplateService->run($request, false, true));
                break;

            case "superama":
                return response()->json($this->superamaTemplateService->run($request, false, true));
                break;

            case "walmart":
                return response()->json($this->walmartTemplateService->run($request, false, true));
                break;

            case "pilot":
                return response()->json($this->pilotTemplateService->run($request, false, true));
                break;

            case "amazon":
            case "target":
                if ($request->output_dimensions == 0 || $request->output_dimensions == 1) {
                    return response()->json($this->vpTemplateService->run($request, false, true));
                } else if ($request->output_dimensions == 2) {
                    return response()->json($this->nfTemplateService->run($request, false, true));
                } else if ($request->output_dimensions == 3) {
                    return response()->json($this->nfhTemplateService->run($request, false, true));
                } else if ($request->output_dimensions == 4) {
                    return response()->json($this->videoTemplateService->run($request, false, true));
                } else if ($request->output_dimensions == 5) {
                    return response()->json($this->vbTemplateService->run($request, false, true));
                } else if ($request->output_dimensions > 5) {
                    return response()->json($this->newTemplateService->run($request, false, true));
                }
                break;

            case "mrhi":
                return response()->json($this->mrhiTemplateService->run($request, false, true));
                break;

            case "instagram":
                if ($request->output_dimensions < 3) {
                    return response()->json($this->instagramTemplateService->run_image($request, false, true));
                }
                return response()->json( $this->instagramTemplateService->run_video($request, false, true) );
                break;

            case "sam":
                return response()->json( $this->samTemplateService->run($request, false, true));
                break;

            default:
                return response()->json($this->newTemplateService->run($request, false, true));
        }
        return response()->json($this->bannerService->run($request, false, true));
    }

    /**
     * Download a zip archive of generated ad files.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function download(Request $request)
    {
        switch ($request->customer) {
            case "generic":
            case "amazon_fresh":
                return response()->json($this->bannerService->run($request));
                break;

            case "kroger":
                return response()->json($this->krogerTemplateService->run($request));
                break;

            case "superama":
                return response()->json($this->superamaTemplateService->run($request));
                break;

            case "walmart":
                return response()->json($this->walmartTemplateService->run($request));
                break;

            case "pilot":
                return response()->json($this->pilotTemplateService->run($request));
                break;

            case "amazon":
            case "target":
                if ($request->output_dimensions == 0 || $request->output_dimensions == 1) {
                    return response()->json($this->vpTemplateService->run($request));
                } else if ($request->output_dimensions == 2) {
                    return response()->json($this->nfTemplateService->run($request));
                } else if ($request->output_dimensions == 3) {
                    return response()->json($this->nfhTemplateService->run($request));
                } else if ($request->output_dimensions == 4) {
                    return response()->json($this->videoTemplateService->run($request));
                } else if ($request->output_dimensions == 5) {
                    return response()->json($this->vbTemplateService->run($request));
                } else if ($request->output_dimensions > 5) {
                    return response()->json($this->newTemplateService->run($request));
                }
                break;

            case "mrhi":
                return response()->json($this->mrhiTemplateService->run($request));
                break;

            case "instagram":
                if ($request->output_dimensions < 3) {
                    return response()->json($this->instagramTemplateService->run_image($request));
                }
                return response()->json( $this->instagramTemplateService->run_video($request) );
                break;

            case "sam":
                return response()->json( $this->samTemplateService->run($request));
                break;
            default:
                return response()->json($this->newTemplateService->run($request));
                break;
        }
        return response()->json($this->bannerService->run($request));
    }

    public function download_layout_assets(Request $request)
    {
        return response()->json($this->newTemplateService->download_layout_assets($request));
    }

    public function download_layout_web(Request $request)
    {
        return response()->json($this->newTemplateService->download_layout_web($request));
    }

    public function download_layout_logos(Request $request)
    {
        return response()->json($this->newTemplateService->download_layout_logos($request));
    }

    public function download_layout_proof(Request $request)
    {
        return response()->json($this->newTemplateService->download_layout_proof($request));
    }

    public function download_xlsx_output(Request $request)
    {
        $customer = Customer::find($request->customer_id);
        return Excel::download(new TemplateXlsxExport($customer, json_decode($request->template_settings)), $customer->name . '_output.xlsx');
    }

    public function download_sheet_output(Request $request)
    {
        $customer = Customer::find($request->customer_id);
        return Excel::download(new TemplateSheetExport($customer, json_decode($request->template_settings, true)), $customer->name . '_output.xlsx');
    }

    /**
     * Download preview file.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function preview(Request $request)
    {
        switch ($request->customer) {
            case "generic":
            case "amazon_fresh":
                return response()->json($this->bannerService->run($request, true));
                break;

            case "kroger":
                return response()->json($this->krogerTemplateService->run($request, true));
                break;

            case "superama":
                return response()->json($this->superamaTemplateService->run($request, true));
                break;

            case "walmart":
                return response()->json($this->walmartTemplateService->run($request, true));
                break;

            case "pilot":
                return response()->json($this->pilotTemplateService->run($request, true));
                break;

            case "amazon":
            case "target":
                if ($request->output_dimensions == 0 || $request->output_dimensions == 1) {
                    return response()->json($this->vpTemplateService->run($request, true));
                } else if ($request->output_dimensions == 2) {
                    return response()->json($this->nfTemplateService->run($request, true));
                } else if ($request->output_dimensions == 3) {
                    return response()->json($this->nfhTemplateService->run($request, true));
                } else if ($request->output_dimensions == 4) {
                    return response()->json($this->videoTemplateService->run($request, true));
                } else if ($request->output_dimensions == 5) {
                    return response()->json($this->vbTemplateService->run($request, true));
                } else if ($request->output_dimensions > 5) {
                    return response()->json($this->newTemplateService->run($request, true));
                }
                break;

            case "mrhi":
                return response()->json($this->mrhiTemplateService->run($request, true));
                break;

            case "instagram":
                if ($request->output_dimensions < 3) {
                    return response()->json($this->instagramTemplateService->run_image($request, true));
                }
                return response()->json( $this->instagramTemplateService->run_video($request, true) );
                break;

            case "sam":
                return response()->json( $this->samTemplateService->run($request, true));
                break;
            default:
                return response()->json($this->newTemplateService->run($request, true));
                break;
        }
        return response()->json($this->bannerService->run($request, true));
    }

    /**
     * publish file.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function publish(Request $request)
    {
        switch ($request->customer) {
            case "generic":
            case "amazon_fresh":
                return response()->json($this->bannerService->run($request, false, false, true));
                break;

            case "kroger":
                return response()->json($this->krogerTemplateService->run($request, false, false, true));
                break;

            case "superama":
                return response()->json($this->superamaTemplateService->run($request, false, false, true));
                break;

            case "walmart":
                return response()->json($this->walmartTemplateService->run($request, false, false, true));
                break;

            case "pilot":
                return response()->json($this->pilotTemplateService->run($request, false, false, true));
                break;

            case "amazon":
            case "target":
                if ($request->output_dimensions == 0 || $request->output_dimensions == 1) {
                    return response()->json($this->vpTemplateService->run($request, false, false, true));
                } else if ($request->output_dimensions == 2) {
                    return response()->json($this->nfTemplateService->run($request, false, false, true));
                } else if ($request->output_dimensions == 3) {
                    return response()->json($this->nfhTemplateService->run($request, false, false, true));
                } else if ($request->output_dimensions == 4) {
                    return response()->json($this->videoTemplateService->run($request, false, false, true));
                } else if ($request->output_dimensions == 5) {
                    return response()->json($this->vbTemplateService->run($request, false, false, true));
                } else if ($request->output_dimensions > 5) {
                    return response()->json($this->newTemplateService->run($request, false, false, true));
                }
                break;

            case "mrhi":
                return response()->json($this->mrhiTemplateService->run($request, false, false, true));
                break;

            case "instagram":
                if ($request->output_dimensions < 3) {
                    return response()->json($this->instagramTemplateService->run_image($request, false, false, true));
                }
                return response()->json( $this->instagramTemplateService->run_video($request, false, false, true) );
                break;

            case "sam":
                return response()->json( $this->samTemplateService->run($request, false, false, true));
                break;
            default:
                return response()->json($this->newTemplateService->run($request, false, false, true));
        }
        return response()->json($this->bannerService->run($request, false, false, true));
    }

    /**
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function get_kroger_template_settings(Request $request)
    {
        $customer = $request->customer;
        $color_scheme = $request->color_scheme;
        $arr = array();
        if ($customer == "kroger") {
            $theme = Theme::where('customer_id', 4)->where('id', $color_scheme)->first();
            $attributes = json_decode($theme->attributes);
            $circle_text_color = $attributes[0]->list;
            // $preset_color = config("templates.Kroger.preset_color");
            $burst_color = $attributes[1]->list;
            $shadow = $attributes[2]->list;
            $arr = [
                'circle_text_color' => $circle_text_color,
                // 'preset_color' => $preset_color[$color_scheme],
                'burst_color' => $burst_color,
                'shadow' => $shadow
            ];
        } else if ($customer == "walmart") {
            $theme = Theme::where('customer_id', 7)->where('id', $color_scheme)->first();
            $attributes = json_decode($theme->attributes);
            $shadow = $attributes[0]->list;
            $arr = [
                'shadow' => $shadow
            ];
        } else if ($customer == "amazon") {
            $theme = Theme::where('customer_id', 2)->where('id', $color_scheme)->first();
            $attributes = json_decode($theme->attributes);
            $shadow = $attributes[1]->list;
            $arr = [
                'colors' => $attributes[0]->list,
                'shadow' => $shadow
            ];
        } else {
            $customer = Customer::where('value', $request->customer)->first();
            $theme = Theme::where('customer_id', $customer->id)->where('id', $color_scheme)->first();
            $arr = [
                'colors' => [],
                'shadow' => []
            ];
            if (isset($theme)) {
                $attributes = json_decode($theme->attributes);
                $shadow = $attributes[3]->list;
                $arr = [
                    'colors' => $attributes[0]->list,
                    'shadow' => $shadow
                ];
            }
        }
        return response()->json($arr);
    }

    /**
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function upload_cropped_bk_image(Request $request)
    {
        Storage::disk('s3')->put($request->filename, file_get_contents($request->croppedImage));
        return true;
    }

    /**
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function delete_cropped_bk_image(Request $request)
    {
        Storage::disk('s3')->delete($request->path);
        File::where('path', $request->path)->delete();

        return true;
    }

    /**
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function delete_bk_image(Request $request)
    {
        $arr = explode(".", $request->name);
        array_pop($arr);
        $name = implode(".", $arr);
        $path = $request->path;
        Storage::disk('s3')->delete($path);
        File::where('path', $path)->delete();
        $arr = explode("/", $path);
        $theme_id = $arr[4];
        $theme = Theme::where('id', $theme_id)->first();
        if ($theme) {
            $attributes = json_decode($theme->attributes);
            $background_images = $attributes[4]->list;
            foreach ($background_images as $key => $bg) {
                if (strtolower($bg->name) == $name) {
                    unset($background_images[$key]);
                }
            }
            $attributes[4]->list = $background_images;
            $theme->attributes = json_encode($attributes);
            $theme->save();
        }
        return true;
    }

    /**
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function upload_bk_image(Request $request)
    {
        $files = $request->file();
        $customer_id = $request->customer_id;
        $theme_id = $request->theme_id;
        $template_id = $request->template_id;
        $result = [];
        foreach ($files as $file) {
            $filename = strtolower($file->getClientOriginalName());
            $ext = $file->extension();
            if ($ext != "png") {
                $fn = uniqid();
                
                //file_put_contents($fn.$ext, file_get_contents($file));
                // $im = new \Imagick($fn.$ext);
                $picstream = file_get_contents($file);
                $im = new \Imagick();
                $im->readImageBlob($picstream);
                
                $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                $im->setImageFormat('png');
                //$im->writeImage($fn.'.png');
                file_put_contents ($fn.'.png', $im);
                
                $file = $fn.'.png';

                $arr = explode(".", $filename);
                $arr[count($arr) - 1] = "png";
                $filename = implode(".", $arr);
            }
            $path = 'files/background/'.$customer_id.'/0/'. $theme_id.'/-1/'.$filename;
            Storage::disk('s3')->put($path, file_get_contents($file));
            
            //$imagick = new \Imagick(siteUrl() . '/share?file=' . $path);
            
            $url = siteUrl() . '/share?file=' . $path;
            $picstream = file_get_contents($url);
            $imagick = new \Imagick();
            $imagick->readImageBlob($picstream);
            
            $imagick->scaleImage(512, 512, true);
            $thumbnail_path = 'files/thumbnails/512x512/background/' . $theme_id . '/' . $template_id . '/' . $filename;
            Storage::disk('s3')->put($thumbnail_path, $imagick->getImageBlob());

            $theme = Theme::where('id', $theme_id)->first();
            if ($theme) {
                $idx = 4;
                $attributes = json_decode($theme->attributes);
                foreach ($attributes as $key => $attr) {
                    if ($attr->name == "Background Images") {
                        $idx = $key;
                    }
                }
                $background_images = $attributes[$idx]->list;
                if (!is_array($background_images)) {
                    $array = [];
                    foreach ($background_images as $bi) {
                        $array[] = $bi;
                    }
                    $background_images = $array;
                }
                $arr = explode(".", $filename);
                array_pop($arr);
                $t = [
                    "name" => implode(".", $arr),
                    "list" => [
                        [
                            "name" => "Template",
                            "type" => "background_template",
                            "value" => "-1",
                            "old_value" => ""
                        ],
                        [
                            "name" => "Filename",
                            "type" => "background",
                            "value" => "/share?file=" . $path,
                            "old_value" => "/share?file=" . $path
                        ],
                        [
                            "name" => "Locked",
                            "type" => "locked",
                            "value" => "0",
                            "old_value" => ""
                        ]
                    ]
                ];
                $background_images[] = $t;
                $attributes[$idx]->list = $background_images;
                $theme->attributes = json_encode($attributes);
                $theme->save();

                $result[] = [
                    "cropped" => false,
                    "locked" => 0,
                    "name" => $filename,
                    "path" => $path,
                    "thumbnail" => $thumbnail_path
                ];
            }
        }
        return response()->json($result);
    }

    /**
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function upload_cropped_product_image(Request $request)
    {
        $company_id = $request->company_id;
        $filename = $request->filename;
        $path = $request->path;
        Storage::disk('s3')->put($path, file_get_contents($request->croppedImage));

        $new_item = new File;
        $new_item->name = $filename;
        $new_item->path = $path;
        $new_item->company_id = $company_id;
        $new_item->save();
        return true;
    }

    /**
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function get_template_settings(Request $request)
    {
        $template_id = $request->template_id;
        $template_settings = Template::where('id', $template_id)->first();
        if (isset($template_settings->fields)) {
            $template_settings["fields"] = $template_settings->fields;
        }
        return response()->json($template_settings);
    }

    /**
     * Gets all background images for specific customer
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function get_background_images(Request $request)
    {
        $customer = $request->customer;
        $template = $request->template;
        $theme_id = $request->theme;
        
        // pagination variables;
        $stock_page = 1 ;
        $stock_total_pages = 1;
        $stock_elements_per_page= env('STOCK_ELEMENTS_PER_PAGE', 9);
        $stock_total_elements = 0;

        $background_page = 1 ;
        $background_total_pages = 1;
        $background_elements_per_page=  env('BACKGROUND_ELEMENTS_PER_PAGE', 9);
        $background_total_elements = 0;

        if ($request->has('stock_page')){
            $stock_page = $request->get('stock_page');
        }else{
            // bring all
            $stock_elements_per_page= 999999;
        }

        if ($request->has('background_page')){
            $background_page = $request->get('background_page');
        }else{
            // bring all 
            $background_elements_per_page= 999999;
        }

        if ($request->has('get_only')){
            $get_only = $request->get('get_only');
        }
        
        $theme = Theme::find($theme_id);
        $attributes = json_decode($theme->attributes);
        if ($customer == "kroger") {
            $background_images = isset($attributes[3]) ? $attributes[3]->list : [];
        } else if ($customer == "walmart") {
            $background_images = isset($attributes[1]) ? $attributes[1]->list : [];
        } else if ($customer == "amazon" || $customer == "target2") {
            $background_images = isset($attributes[2]) ? $attributes[2]->list : [];
        } else {
            $background_images = isset($attributes[4]) ? $attributes[4]->list : [];
        }

        $origin_path = "";
        $result = [
            "background" => [],
            "stock" => [],
            "background_pagination" => [],
            "stock_pagination" => []
        ];
        
        if (!isset($get_only) || (isset($get_only) && $get_only == 'background')) {
            if (!is_array($background_images)) {
                $array = [];
                foreach ($background_images as $bi) {
                    $array[] = $bi;
                }
                $background_images = $array;
            }
            $background_total_elements = count($background_images);
            $background_total_pages = ceil($background_total_elements/$background_elements_per_page);
            
            $result["background_pagination"]["background_page"] = $background_page ;
            $result["background_pagination"]["background_total_pages"] =$background_total_pages ;
            $result["background_pagination"]["background_total_elements"] = $background_total_elements;
            
        //foreach ($background_images as $image) {            
        for ($j=($background_page-1)* $background_elements_per_page ;
            $j< $background_page * $background_elements_per_page &&
            $j< $background_total_elements; $j++ ) {
        
            $image = $background_images[$j];
            if ($image->list[0]->value == $template || $image->list[0]->value == -1) {
                $path = $image->list[1]->value;
                $path = str_replace("/share?file=", "", $path);
                $origin_path = $path;
                if ($image->name != "") {
                    $path_arr = explode('/', $path);
                    $path_arr[count($path_arr) - 1] = strtolower($image->name.".png");
                    $path = implode('/', $path_arr);
                }
                $filename = strtolower($image->name.".png");
                $file = array(
                    'name' => $filename,
                    'path' => $path,
                    'thumbnail' => 'files/thumbnails/512x512/background/' . $theme_id . '/' . $template . '/' . $filename,
                    'cropped' => false,
                    'locked' => isset($image->list[2]->value) ? $image->list[2]->value : 1
                );

                $url = siteUrl() . '/share?file=' . $file['path'];

                if(Storage::disk('s3')->has($path)) {
                    // $imagick = new \Imagick($url);
                    $picstream = file_get_contents($url);
                    $imagick = new \Imagick();
                    $imagick->readImageBlob($picstream);

                    $imagick->scaleImage(512, 512, true);
                    Storage::disk('s3')->put($file['thumbnail'], $imagick->getImageBlob());
                    $result["background"][] = $file;
                }
            }
        }
        $origin_path = explode("/", $origin_path);
        array_pop($origin_path);
        $origin_path = implode("/", $origin_path);
        $bk_imgs = Storage::disk('s3')->allFiles($origin_path);
        foreach ($bk_imgs as $bk) {
            if (strpos($bk, "_cropped") !== false) {
                $filename = explode("/", $bk);
                $filename = $filename[count($filename) - 1];
                $file = array(
                    'name' => $filename,
                    'path' => $origin_path."/".$filename,
                    'thumbnail' => 'files/thumbnails/512x512/background/' . $theme_id . '/' . $template . '/' . $filename,
                    'cropped' => true
                );

                $url = siteUrl() . '/share?file=' . $file['path'];
                //$imagick = new \Imagick($url);
                $picstream = file_get_contents($url);
                $imagick = new \Imagick();
                $imagick->readImageBlob($picstream);

                $imagick->scaleImage(512, 512, true);
                Storage::disk('s3')->put($file['thumbnail'], $imagick->getImageBlob());
                $result["background"][] = $file;
            }
        }
        }
        unset($imagick);
        // STOCK IMAGES
        if (!isset($get_only) || (isset($get_only) && $get_only == 'stock')){
            
        $company_id = auth()->user()->company_id;
        $stock_imgs = array ();
        $stock_imgs_all_companies = array ();
        $stock_imgs_this_company = Storage::disk('s3')->allFiles("stock/".$company_id);

        if ($company_id != 0){
            $stock_imgs_all_companies= Storage::disk('s3')->allFiles("stock/0");
        }
        
        $stock_imgs = array_merge ($stock_imgs_this_company,$stock_imgs_all_companies );
        $stock_total_elements = count($stock_imgs);
        $stock_total_pages = ceil ($stock_total_elements/$stock_elements_per_page);
        
        $result["stock_pagination"]["stock_page"] = $stock_page ;
        $result["stock_pagination"]["stock_total_pages"] =$stock_total_pages ;
        $result["stock_pagination"]["stock_total_elements"] = $stock_total_elements;
        
        //foreach ($stock_imgs as $sk) {
        for ($j=($stock_page-1)* $stock_elements_per_page ;
        $j< $stock_page * $stock_elements_per_page &&
        $j< $stock_total_elements; $j++ ) {
            
            $sk = $stock_imgs[$j];
            $filename = explode("/", $sk);
            $filename = $filename[count($filename) - 1];
            $file = array(
                'name' => $filename,
                //'path' => "stock/".$company_id."/".$filename,
                //'thumbnail' => 'files/thumbnails/512x512/stock/' . $company_id . '/' . $filename,
                'path' => $sk,
                'thumbnail' => 'files/thumbnails/512x512/'.$sk,
                'cropped' => strpos($sk, "_cropped") !== false
            );

            $url = siteUrl() . '/share?file=' . $file['path'];

           if (is_file($url)) {
            $picstream = file_get_contents($url);
            $imagick = new \Imagick();
            $imagick->readImageBlob($picstream);
           }else{
               try {
                   //$imagick = new \Imagick($url);
                   $picstream = file_get_contents($url);
                   $imagick = new \Imagick();
                   $imagick->readImageBlob($picstream);
               } catch (\Throwable $e) {
                   // ignore corrupted or non-existant files;
               }
           }
           if (isset($imagick)){
            
                   $imagick->scaleImage(512, 512, true);
                   Storage::disk('s3')->put($file['thumbnail'], $imagick->getImageBlob());
                   // need to check why would they be in the stock folder...
                   if (strpos($sk, "_cropped") !== false) {
                       $result["background"][] = $file;
                   } else {
                       $result["stock"][] = $file;
                   }
           
            }

        }
        }

        return $result;
    }

    public function get_background_stock_images(Request $request)
    {
        $stock_page = 1;
        $stock_total_pages = 1;
        $stock_elements_per_page= $request->pageSize;
        $stock_total_elements = 0;

        if ($request->has('stock_page')){
            $stock_page = $request->get('stock_page');
        }else{
            $stock_elements_per_page= 999999;
        }
        $result = [
            "stock" => [],
            "stock_pagination" => [],
            'current_page' => $stock_page,
            'from' => ((($stock_page -1) * $stock_elements_per_page) + 1),
            'last_page' => 1
        ];

        $company_id = auth()->user()->company_id;
        $stock_imgs = array ();
        $stock_imgs_all_companies = array ();
        $stock_imgs_this_company = Storage::disk('s3')->allFiles("stock/".$company_id);

        if ($company_id != 0){
            $stock_imgs_all_companies= Storage::disk('s3')->allFiles("stock/0");
        }
        
        $stock_imgs = array_merge ($stock_imgs_this_company,$stock_imgs_all_companies );
        $stock_total_elements = count($stock_imgs);
        $stock_total_pages = ceil ($stock_total_elements/$stock_elements_per_page);
        
        $result["stock_pagination"]["stock_page"] = $stock_page;
        $result["stock_pagination"]["stock_total_pages"] =$stock_total_pages ;
        $result["stock_pagination"]["stock_total_elements"] = $stock_total_elements;
        
        $result['current_page'] = $stock_page;
        $result['from'] = ((($stock_page -1) * $stock_elements_per_page) + 1);
        $result['last_page'] = $stock_total_pages;

        //foreach ($stock_imgs as $sk) {
        for ($j=($stock_page-1)* $stock_elements_per_page ;
        $j< $stock_page * $stock_elements_per_page &&
        $j< $stock_total_elements; $j++ ) {
            
            $sk = $stock_imgs[$j];
            $filename = explode("/", $sk);
            $filename = $filename[count($filename) - 1];
            $file = array(
                'name' => $filename,    
                'path' => $sk,
                'thumbnail' => 'files/thumbnails/512x512/'.$sk,
                'cropped' => strpos($sk, "_cropped") !== false,
                'url' => siteUrl() . '/share?file=files/thumbnails/512x512/'.$sk
            );

            $url = siteUrl() . '/share?file=' . $file['path'];

            if (is_file($url)) {
                $picstream = file_get_contents($url);
                $imagick = new \Imagick();
                $imagick->readImageBlob($picstream);
            }else{
                try {
                    $picstream = file_get_contents($url);
                    $imagick = new \Imagick();
                    $imagick->readImageBlob($picstream);
                } catch (\Throwable $e) {
                    // ignore corrupted or non-existant files;
                }
            }
            
            if (isset($imagick)){
                $imagick->scaleImage(512, 512, true);
                Storage::disk('s3')->put($file['thumbnail'], $imagick->getImageBlob());
                $result["stock"][] = $file;
            }

        }

        return $result;
    }

    /**
     * Check if file can share
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function can_share(Request $request)
    {
        return response()->json($this->bannerService->canRun($request));
    }

    public function get_related_files($file, $file_id)
    {
        $related_files = [];
        $child_list = $file["child_list"];
        // $filename = explode(".", $file["name"])[0];
        // $related_files = File::where("name", "like", $filename."%")->get();
        if (auth()->user()->isMasterAdmin()) {
            foreach ($child_list as $child) {
                $f = File::where("name", $child . ".png")->first();
                if ($f) {
                    $related_files[] = $f;
                }
            }
            if (!count($related_files)) {
                foreach ($child_list as $child) {
                    $f = File::where("name", "LIKE", $child . "%")->first();
                    if ($f) {
                        $related_files[] = $f;
                    }
                }
            }
        } else {
            foreach ($child_list as $child) {
                $f = File::where("name", $child . ".png")->where('company_id', auth()->user()->company_id)->first();
                if ($f) {
                    $related_files[] = $f;
                }
            }
            if (!count($related_files)) {
                foreach ($child_list as $child) {
                    $f = File::where("name", "LIKE", $child . "%")->where('company_id', auth()->user()->company_id)->first();
                    if ($f) {
                        $related_files[] = $f;
                    }
                }
            }
        }

        if (!count($related_files)) {
            return  [];
        }
        $self_file = $related_files[0];
        foreach ($related_files as $f) {
            if (!Storage::disk('s3')->exists($f['thumbnail'])) {
                $url = siteUrl() . '/share?file=' . $f['path'];
                
                //$imagick = new \Imagick($url);
                $picstream = file_get_contents($url);
                $imagick = new \Imagick();
                $imagick->readImageBlob($picstream);

                $imagick->scaleImage(0, 128);
                Storage::disk('s3')->put($f['thumbnail'], $imagick->getImageBlob());
            }

            if ($f['width'] == 0) {
                $f['width'] = 5;
            }
            if ($f['height'] == 0) {
                $f['height'] = 5;
            }
            if ($f['depth'] == 0) {
                $f['depth'] = 5;
            }

            if ($file['id'] == $f['id']) {
                $self_file = $f;
            }
        }
        array_unshift($related_files, $self_file);
        $related_files = array_unique($related_files);
        return array_values($related_files);
    }

    public function get_popular_file($files)
    {
        $max_count = 0;
        $popular_file = null;
        foreach ($files as $file) {
            $ps = ProductSelection::where('file_id', $file->id)->first();
            if ($ps && $max_count < $ps->count) {
                $popular_file = $file;
            }
        }
        return $popular_file;
    }

    /**
     * get urls for image files.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function view(Request $request)
    {
        $show_warning = false;
        if (isset($request->show_warning) && $request->show_warning) {
            $show_warning = true;
        }
        $file_ids = preg_replace('/\s+/', " ", $request->file_ids);
        $file_ids = explode(" ", $file_ids);
        $data = $this->bannerService->map_files($file_ids, !$show_warning);

        if ($data['status'] == 'error') {
            return response()->json($data);
        } else {
            if ($data['status'] == 'success') {
                $result = [
                    'status' => "success",
                    'files' => []
                ];
            } else {
                $result = [
                    'status' => "warning",
                    'messages' => $data["messages"],
                    'logs' => $data["logs"],
                    'files' => []
                ];
            }
            foreach ($data['files'] as $key => $file) {
                $related_files = $this->get_related_files($file, $file_ids[$key]);
                $popular_file = $this->get_popular_file($related_files);
                $result['files'][] = [
                    'id' => $file["id"],
                    'name' => $file["name"],
                    'isParent' => substr($file_ids[$key], -2) == '_p',
                    'popular_file' => $popular_file,
                    'related_files' => $related_files
                ];
            }
            return response()->json($result);
        }
    }

    /**
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update_product_selections(Request $request)
    {
        $company_id = auth()->user()->company_id;
        $file_ids = preg_replace('/\s+/', " ", $request->file_ids);
        $file_ids = explode(" ", $file_ids);
        foreach ($file_ids as $file_id) {
            $ps = ProductSelection::updateOrCreate(['file_id' => $file_id, 'company_id' => $company_id], ['file_id' => $file_id, 'company_id' => $company_id]);
            $ps->count = $ps->count + 1;
            $ps->save();
        }
    }

    public function store_remote_image(Request $request)
    {
        $filename = uniqid() . ".png";
        if ($request->url == "") return "";
        if (isset($request->file)) {
            Storage::disk('public')->put($filename, file_get_contents($request->file));
        } else {
            Storage::disk('public')->put($filename, file_get_contents($request->url));
        }
        $im = new \Imagick($filename);
        $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        $im->setImageFormat('png');
        $filename = uniqid() . ".png";
        $im->writeImage($filename);
        return $filename;
    }


    public function getBase64image(Request $request){
        $data = array();
        $data['success'] = 0;
        if ($request->get("image")){
            $file = public_path()."/".$request->get("image");
            $data['success'] = 1;
            $base64 = base64_encode(file_get_contents($file));
            $mimetype = mime_content_type($file);
            $blob = 'data:'.$mimetype.';base64,'.$base64;
            $data['image'] = $blob;
        }
        return response()->json($data);
    }

}
