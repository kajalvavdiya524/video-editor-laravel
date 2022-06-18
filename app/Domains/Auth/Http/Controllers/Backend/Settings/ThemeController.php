<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Settings;

use Illuminate\Support\Facades\Storage;
use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Models\Template;
use App\Domains\Auth\Models\Theme;
use App\Domains\Auth\Models\DefaultTheme;
use App\Domains\Auth\Services\ThemeService;
use App\Domains\Auth\Services\TemplateService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use function GuzzleHttp\json_decode;

/**
 * Class ThemeController.
 */
class ThemeController extends Controller
{
    protected $themeService;
    protected $templateService;

    /**
     * ThemeController constructor.
     *
     * @param  ThemeService  $themeService
     */
    public function __construct(ThemeService $themeService, TemplateService $templateService)
    {
        $this->themeService = $themeService;
        $this->templateService = $templateService;
    }

    private function get_customers()
    {
        $customers = [];
        $user = auth()->user();
        if ((!$user->isMember()) || (!$user->isTeamMember())) {
            if ($user->isMasterAdmin()) {
                $customers = Customer::all();
            } else {
                $customers = Customer::where('system', 1)->get();
                $customers = $customers->merge($user->company->customers);
            }
        } else {
            foreach ($user->teams as $team) {
                foreach ($team->customers as $customer) {
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
     * @return \Illuminate\View\View
     */
    public function index($customer_id = 4)
    {
        $customer_name = Customer::where('id', $customer_id)->first()->value;
        $customers = $this->get_customers();
        return view('backend.auth.settings.theme.index', ['customers' => $customers, 'customer_id' => $customer_id, 'customer_name' => $customer_name]);
    }

    /**
     * @return mixed
     */
    public function create($customer_id)
    {
        $theme = DefaultTheme::where('customer_id', $customer_id)->first();
        $customer_name = Customer::where('id', $customer_id)->first()->name;
        $templates = $this->templateService->getByCustomerId($customer_id);
        return view('backend.auth.settings.theme.create', ['customer_id' => $customer_id, 'customer_name' => $customer_name, 'theme' => $theme, 'templates' => $templates]);
    }

    /**
     * @return mixed
     */
    public function copy(Request $request, $customer_id, Theme $theme)
    {
        $new_theme = $this->themeService->copy($theme);
        $s3 = Storage::disk('s3');
        $images = $s3->allFiles('files/background/'.$customer_id.'/0/' . $theme->id);
        $s3->deleteDirectory('files/background/'.$customer_id.'/0/' . $new_theme->id); // If the file already exists, it will throw an exception.  In my case I'm deleting the entire folder to simplify things.
        foreach ($images as $image) {
            $new_loc = str_replace('files/background/'.$customer_id.'/0/' . $theme->id, 'files/background/'.$customer_id.'/0/' . $new_theme->id, $image);
            $s3->copy($image, $new_loc);
        }

        return redirect()->route('admin.auth.settings.theme.index', $customer_id)->withFlashSuccess(__('The theme was successfully created.'));
    }

    /**
     * @return mixed
     */
    public function toggle(Request $request, $customer_id, Theme $theme)
    {
        $theme = $this->themeService->toggle($theme);
        return redirect()->route('admin.auth.settings.theme.index', $customer_id)->withFlashSuccess(__('The theme status was successfully changed.'));
    }

    /**
     * @return mixed
     */
    public function moveup(Request $request, $customer_id, Theme $theme)
    {
        $theme = $this->themeService->moveup($theme);
        return redirect()->route('admin.auth.settings.theme.index', $customer_id)->withFlashSuccess(__('The theme order was successfully changed.'));
    }

    /**
     * @return mixed
     */
    public function movedown(Request $request, $customer_id, Theme $theme)
    {
        $theme = $this->themeService->movedown($theme);
        return redirect()->route('admin.auth.settings.theme.index', $customer_id)->withFlashSuccess(__('The theme order was successfully changed.'));
    }

    /**
     * @param  Request  $request
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     * @throws \Throwable
     */
    public function store(Request $request, $customer_id)
    {
        $theme = $this->themeService->store($request->post());
        $id = $theme->id;
        $template_count = $request->post('template_count');
        $templates = json_decode($request->post('templates'));
        $filenames = json_decode($request->post('file_names'));
        $files = $request->file();
        $index = 0;
        foreach ($files as $value) {
            $ext = $value->extension();
            if ($ext == "psd") {
                $fn = uniqid();
                file_put_contents($fn.'psd', file_get_contents($value));
                $im = new \Imagick($fn.'psd');
                $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                $im->setImageFormat('png');
                $im->writeImage($fn.'png');
                $value = $fn.'png';
            } else if ($ext == "svg") {
                $fn = uniqid();
                file_put_contents($fn.'svg', file_get_contents($value));
                $im = new \Imagick($fn.'svg');
                $im->setImageFormat('png');
                $im->writeImage($fn.'png');
                $value = $fn.'png';
            }
            $filename = $filenames[$index];
            if ($filenames[$index] == "") {
                $filename = pathinfo($value->getClientOriginalName(), PATHINFO_FILENAME);
            }
            if ($templates[$index] == -1) {
                foreach ($templates as $template) {
                    Storage::disk('s3')->put('files/background/'.$customer_id.'/0/' . $id . '/' . $template . '/' . strtolower($filename) . ".png", file_get_contents($value));
                }
            }
            Storage::disk('s3')->put('files/background/'.$customer_id.'/0/' . $id . '/' . $templates[$index] . '/' . strtolower($filename) . ".png", file_get_contents($value));
            $index++;
        }
        return redirect()->route('admin.auth.settings.theme.index', $customer_id)->withFlashSuccess(__('The theme was successfully created.'));
    }

    /**
     * @param  Request  $request
     * @param  Theme  $theme
     *
     * @return mixed
     */
    public function edit(Request $request, $customer_id, Theme $theme)
    {
        $templates = $this->templateService->getByCustomerId($customer_id);
        $customer_name = Customer::where('id', $customer_id)->first()->name;
        return view('backend.auth.settings.theme.edit', ['theme' => $theme, 'customer_id' => $customer_id, 'customer_name' => $customer_name, 'templates' => $templates]);
    }

    /**
     * @param  Request  $request
     * @param  Theme  $theme
     *
     * @return mixed
     * @throws \Throwable
     */
    public function update(Request $request, $customer_id)
    {
        $id = $request->post('theme_id');
        $templates = json_decode($request->post('templates'));
        $filenames = json_decode($request->post('file_names'));
        $files = $request->file();
        $index = 0;
        foreach ($files as $value) {
            $filename = strtolower($value->getClientOriginalName());
            if ($filenames[$index] != "") {
                $filename = strtolower($filenames[$index]) . ".png";
            }
            $ext = $value->extension();
            if ($ext == "psd") {
                $fn = uniqid();
                file_put_contents($fn.'psd', file_get_contents($value));
                $im = new \Imagick($fn.'psd');
                $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                $im->setImageFormat('png');
                $im->writeImage($fn.'png');
                $value = $fn.'png';
            } else if ($ext == "svg") {
                $fn = uniqid();
                file_put_contents($fn.'svg', file_get_contents($value));
                $im = new \Imagick($fn.'svg');
                $im->setImageFormat('png');
                $im->writeImage($fn.'png');
                $value = $fn.'png';
            }

            $arr = explode(".", $filename);
            $arr[count($arr) - 1] = "png";
            $filename = implode(".", $arr);
            
            if ($templates[$index] != -2) {
                if ($templates[$index] == -1) {
                    foreach ($templates as $template) {
                        Storage::disk('s3')->put('files/background/'.$customer_id.'/0/'. $id.'/'.$template.'/'.$filename, file_get_contents($value));
                    }
                }
                Storage::disk('s3')->put('files/background/'.$customer_id.'/0/'.$id.'/'.$templates[$index].'/'.$filename, file_get_contents($value));
            }
            $index++;
        }
        $theme = Theme::find($id);
        $this->themeService->update($theme, $request->post());
        return redirect()->route('admin.auth.settings.theme.index', $customer_id)->withFlashSuccess(__('The theme was successfully updated.'));
    }

    /**
     * @param  Request  $request
     * @param  Theme  $theme
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function destroy(Request $request, $customer_id, Theme $theme)
    {
        $this->themeService->delete($theme);
        Storage::disk('s3')->deleteDirectory('files/background/'.$customer_id.'/0/' . $theme->id);

        return redirect()->route('admin.auth.settings.theme.index', $customer_id)->withFlashSuccess(__('The theme was successfully deleted.'));
    }
}
