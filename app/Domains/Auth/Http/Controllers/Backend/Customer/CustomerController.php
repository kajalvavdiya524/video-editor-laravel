<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Customer;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Domains\Auth\Http\Requests\Backend\Customer\DeleteCustomerRequest;
use App\Domains\Auth\Http\Requests\Backend\Customer\EditCustomerRequest;
use App\Domains\Auth\Http\Requests\Backend\Customer\UpdateCustomerRequest;
use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Models\DefaultTheme;
use App\Domains\Auth\Services\CustomerService;
use App\Http\Controllers\Controller;

/**
 * Class CustomerController.
 */
class CustomerController extends Controller
{
    /**
     * @var CustomerService
     */
    protected $customerService;

    /**
     * CustomerController constructor.
     *
     * @param  CustomerService  $customerService
     */
    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('backend.auth.customer.index');
    }

    /**
     * @return mixed
     */
    public function create()
    {
        $companies = Company::all();
        return view('backend.auth.customer.create', ['companies' => $companies]);
    }

    /**
     * @param  StoreCustomerRequest  $request
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $file = $request->file('logo');
        $logo_filename = uniqid() . '.' . $file->clientExtension();
        Storage::disk('public')->putFileAs('img/customers', $file, $logo_filename);

        $file = $request->file('xlsx_template');
        if (isset($file)) {
            $xlsx_filename = uniqid() . '.' . $file->clientExtension();
            Storage::disk('public')->putFileAs('xlsx/', $file, $xlsx_filename);
        }

        $company_ids = [];
        $companies = Company::all();
        for ($i = 0; $i < count($companies); $i++) {
            if (isset($data['company' . ($i + 1)])) {
                $company_ids[] = $data['company' . ($i + 1)];
            }
        }

        $customer = $this->customerService->store([
            'name' => $data['name'],
            'value' => strtolower($data['name']),
            'system' => false,
            'image_url' => 'img/customers/' . $logo_filename,
            'xlsx_template_url' => isset($xlsx_filename) ? 'xlsx/' . $xlsx_filename : '',
            'companies' => $company_ids
        ]);

        DefaultTheme::create([
            'customer_id' => $customer->id,
            'attributes' => '[{"name":"Background Colors","list":[{"name":"White","list":[{"name":"type","value":"solid","type":"fill_type"},{"name":"color","value":"#ffffff","type":"color"}]}]},{"name":"Circle Tags","type":"color","list":[{"name":"Orange","list":[{"name":"circle","value":"#e6873b","type":"color"},{"name":"text1","value":"#ffffff","type":"color"},{"name":"text2","value":"#324b14","type":"color"},{"name":"text3","value":"#ffffff","type":"color"}]}]},{"name":"Burst Tags","list":[{"name":"Red","list":[{"name":"circle","value":"#ffffff","type":"color"},{"name":"text","value":"#e21f4a","type":"color"}]}]},{"name":"Shadow Effects","list":[{"name":"Product Drop Shadow 1","list":[{"name":"Opacity","value":"44","type":"number"},{"name":"Angle","value":"145","type":"number"},{"name":"Distance","value":"8","type":"number"},{"name":"Spread","value":"0","type":"number"},{"name":"Size","value":"16","type":"number"}]},{"name":"Product Drop Shadow 1","list":[{"name":"Opacity","value":"33","type":"number"},{"name":"Angle","value":"145","type":"number"},{"name":"Distance","value":"16","type":"number"},{"name":"Spread","value":"0","type":"number"},{"name":"Size","value":"32","type":"number"}]}]},{"name":"Background Images","type":"background","list":[{"name":"Image1","list":[{"name":"Template","type":"background_template","value":0},{"name":"Filename","type":"background","value":"../image/url_path/image.png"}]}]}]'
        ]);

        return redirect()->route('admin.auth.customer.show', $customer)->withFlashSuccess(__('The customer was successfully created.'));
    }

    /**
     * @param  Customer  $customer
     *
     * @return mixed
     */
    public function show(Customer $customer)
    {
        return view('backend.auth.customer.show')
            ->withCustomer($customer);
    }

    /**
     * @param  EditCustomerRequest  $request
     * @param  Customer  $customer
     *
     * @return mixed
     */
    public function edit(EditCustomerRequest $request, Customer $customer)
    {
        $companies = Company::all();
        return view('backend.auth.customer.edit', ['customer' => $customer, 'companies' => $companies]);
    }

    /**
     * @param  UpdateCustomerRequest  $request
     * @param  Customer  $customer
     *
     * @return mixed
     * @throws \Throwable
     */
    public function update(Request $request, Customer $customer)
    {
        $updates = [];
        $data = $request->all();

        $file = $request->file('logo');
        if (isset($file)) {
            if (!empty($customer->image_url)) {
                Storage::disk('public')->putFileAs('img/customers', $file, basename($customer->image_url));
            } else {
                $logo_filename = uniqid() . '.' . $file->clientExtension();
                Storage::disk('public')->putFileAs('img/customers', $file, $logo_filename);
                $updates['image_url'] = 'img/customers' . $logo_filename;
            }
        }

        $file = $request->file('xlsx_template');
        if (isset($file)) {
            if (!empty($customer->xlsx_template_url)) {
                Storage::disk('public')->putFileAs('xlsx/', $file, basename($customer->xlsx_template_url));
            } else {
                $xlsx_filename = uniqid() . '.' . $file->clientExtension();
                Storage::disk('public')->putFileAs('xlsx/', $file, $xlsx_filename);
                $updates['xlsx_template_url'] = 'xlsx/' . $xlsx_filename;
            }
        }

        $company_ids = [];
        $companies = Company::all();
        for ($i = 0; $i < count($companies); $i++) {
            if (isset($data['company' . ($i + 1)])) {
                $company_ids[] = $data['company' . ($i + 1)];
            }
        }

        $updates['name'] = $data['name'];
        $updates['value'] = strtolower($data['name']);
        $updates['companies'] = $company_ids;

        $this->customerService->update($customer, $updates);

        return redirect()->route('admin.auth.customer.show', $customer)->withFlashSuccess(__('The customer was successfully updated.'));
    }

    /**
     * @param  DeleteCustomerRequest  $request
     * @param  Customer  $customer
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function destroy(DeleteCustomerRequest $request, Customer $customer)
    {
        Storage::disk('public')->delete($customer->image_url);
        $this->customerService->delete($customer);
        return redirect()->route('admin.auth.customer.index')->withFlashSuccess(__('The customer was successfully deleted.'));
    }

    public function download_xlsx_template(Request $request, Customer $customer)
    {
        return Storage::disk('public')->download($customer->xlsx_template_url, $customer->name . '_template.xlsx');
    }

     /**
     * @return mixed
     */
    public function toggle(Request $request, Customer $customer)
    {
        $this->customerService->toggle($customer);
        return redirect()->route('admin.auth.customer.index', $customer->id)->withFlashSuccess(__('The Customer status was successfully toggled.'));
    }

}
