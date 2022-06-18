<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Company;

use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Services\CompanyService;
use App\Http\Controllers\Controller;

/**
 * Class DeletedCompanyController.
 */
class DeletedCompanyController extends Controller
{
    /**
     * @var CompanyService
     */
    protected $companyService;

    /**
     * DeletedCompanyController constructor.
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
        return view('backend.auth.company.deleted');
    }

    /**
     * @param  Company  $deletedCompany
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function update(Company $deletedCompany)
    {
        $this->companyService->restore($deletedCompany);

        return redirect()->route('admin.auth.company.index')->withFlashSuccess(__('The company was successfully restored.'));
    }

    /**
     * @param  Company  $deletedCompany
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function destroy(Company $deletedCompany)
    {
        abort_unless(config('boilerplate.access.company.permanently_delete'), 404);

        $this->companyService->destroy($deletedCompany);

        return redirect()->route('admin.auth.company.deleted')->withFlashSuccess(__('The company was permanently deleted.'));
    }
}
