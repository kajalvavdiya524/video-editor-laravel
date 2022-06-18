<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Settings;

use App\Domains\Auth\Models\Setting;
use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Http\Requests\Backend\Settings\UpdateTemplateSettingsRequest;
use App\Domains\Auth\Http\Requests\Backend\Settings\ResetTemplateSettingsRequest;
use App\Domains\Auth\Services\SettingsService;
use App\Http\Controllers\Controller;

/**
 * Class TemplateSettingsController.
 */
class TemplateSettingsController extends Controller
{
    /**
     * @var SettingsService
     */
    protected $settingsService;

    /**
     * TemplateSettingsController constructor.
     *
     * @param  SettingsService  $settingsService
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
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
    public function index()
    {
        $settings = array();
        $customers = $this->get_customers();
        $db_settings = Setting::all();
        foreach ($db_settings as $row) {
            $settings[$row->key] = $row->value;
        }
        return view('backend.auth.settings.template.amazon_fresh', ['customers' => $customers, 'settings' => $settings]);
    }

    public function update(UpdateTemplateSettingsRequest $request)
    {
        $this->settingsService->update($request->post());

        return redirect()->route('admin.auth.settings.template.index')->withFlashSuccess(__('The template settings were successfully updated.'));
    }

    public function reset(ResetTemplateSettingsRequest $request)
    {
        $this->settingsService->reset();

        return redirect()->route('admin.auth.settings.template.index')->withFlashSuccess(__('The template settings were successfully reset to defaults.'));
    }

    public function view($customer)
    {
        $settings = array();
        $customers = $this->get_customers();
        $db_settings = Setting::all();
        foreach ($db_settings as $row) {
            $settings[$row->key] = $row->value;
        }
        $customer_id = Customer::where('value', $customer)->first()->id;
        if ($customer == "amazon_fresh") {
            return view('backend.auth.settings.template.amazon_fresh', ['customers' => $customers, 'settings' => $settings]);
        } else {
            return view('backend.auth.settings.theme.index', [
                'customers' => $customers, 
                'customer_id' => $customer_id, 
                'customer_name' => $customer, 
                'settings' => $settings
            ]);
        }
    }
}
