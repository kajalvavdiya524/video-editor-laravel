<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Company;

use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Services\CompanyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Class CompanyStatusController.
 */
class DeactivatedCompanyController extends Controller
{
    /**
     * @var CompanyService
     */
    protected $companyService;

    /**
     * DeactivatedCompanyController constructor.
     *
     * @param  CompanyService  $companyService
     */
    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('backend.auth.company.deactivated');
    }

    /**
     * @param  Request  $request
     * @param  Company  $company
     * @param $status
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function update(Request $request, Company $company, $status)
    {
        $this->companyService->mark($company, (int) $status);

        return redirect()->route(
            (int) $status === 1 || ! $request->user()->can('access.company.reactivate') ?
                'admin.auth.company.index' :
                'admin.auth.company.deactivated'
        )->withFlashSuccess(__('The company was successfully updated.'));
    }
}
