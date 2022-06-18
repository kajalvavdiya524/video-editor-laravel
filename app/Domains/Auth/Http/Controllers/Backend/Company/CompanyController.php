<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Company;

use App\Domains\Auth\Http\Requests\Backend\Company\DeleteCompanyRequest;
use App\Domains\Auth\Http\Requests\Backend\Company\EditCompanyRequest;
use App\Domains\Auth\Http\Requests\Backend\Company\StoreCompanyRequest;
use App\Domains\Auth\Http\Requests\Backend\Company\UpdateCompanyRequest;
use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Services\CompanyService;
use App\Http\Controllers\Controller;

/**
 * Class CompanyController.
 */
class CompanyController extends Controller
{
    /**
     * @var CompanyService
     */
    protected $companyService;

    /**
     * CompanyController constructor.
     *
     * @param  CompanyService  $companyService
     */
    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('backend.auth.company.index');
    }

    /**
     * @return mixed
     */
    public function create()
    {
        return view('backend.auth.company.create');
    }

    /**
     * @param  StoreCompanyRequest  $request
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     * @throws \Throwable
     */
    public function store(StoreCompanyRequest $request)
    {
        $company = $this->companyService->store($request->validated());

        return redirect()->route('admin.auth.company.show', $company)->withFlashSuccess(__('The company was successfully created.'));
    }

    /**
     * @param  Company  $company
     *
     * @return mixed
     */
    public function show(Company $company)
    {
        return view('backend.auth.company.show')
            ->withCompany($company);
    }

    /**
     * @param  EditCompanyRequest  $request
     * @param  Company  $company
     *
     * @return mixed
     */
    public function edit(EditCompanyRequest $request, Company $company)
    {
        return view('backend.auth.company.edit')
            ->withCompany($company);
    }

    /**
     * @param  UpdateCompanyRequest  $request
     * @param  Company  $company
     *
     * @return mixed
     * @throws \Throwable
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $this->companyService->update($company, $request->validated());

        return redirect()->route((auth()->user()->isMasterAdmin() ? 'admin.auth.company.show' : 'admin.dashboard'), $company)
                    ->withFlashSuccess(__('The company was successfully updated.'));
    }

    /**
     * @param  DeleteCompanyRequest  $request
     * @param  Company  $company
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function destroy(DeleteCompanyRequest $request, Company $company)
    {
        $this->companyService->delete($company);

        return redirect()->route('admin.auth.company.deleted')->withFlashSuccess(__('The company was successfully deleted.'));
    }
}
