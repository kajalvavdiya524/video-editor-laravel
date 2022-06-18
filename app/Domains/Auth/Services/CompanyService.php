<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\Company;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Class CompanyService.
 */
class CompanyService extends BaseService
{
    /**
     * CompanyService constructor.
     *
     * @param  Company  $company
     */
    public function __construct(Company $company)
    {
        $this->model = $company;
    }

    /**
     * @param  array  $data
     *
     * @return Company
     * @throws GeneralException
     * @throws \Throwable
     */
    public function store(array $data = []): Company
    {
        DB::beginTransaction();

        try {
            $company = $this->createCompany([
                'name' => $data['name'],
                'address' => $data['address'],
                'use_azure' => $data['use_azure'],
                'active' => isset($data['active']) && $data['active'] === '1',
                'notification_emails' => ''
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem creating this company. Please try again.'));
        }

        DB::commit();

        return $company;
    }

    /**
     * @param  Company  $company
     * @param  array  $data
     *
     * @return Company
     * @throws \Throwable
     */
    public function update(Company $company, array $data = []): Company
    {
        DB::beginTransaction();

        try {
            $company->update([
                'name' => $data['name'],
                'address' => $data['address'],
                'use_azure' => $data['use_azure'],
                'has_mrhi' => $data['has_mrhi'] == 'on',
                'has_pilot' => $data['has_pilot'] == 'on'
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem updating this company. Please try again.'));
        }

        DB::commit();

        return $company;
    }

    /**
     * @param  Company  $company
     * @param $status
     *
     * @return Company
     * @throws GeneralException
     */
    public function mark(Company $company, $status): Company
    {
        $company->active = $status;

        if ($company->save()) {
            return $company;
        }

        throw new GeneralException(__('There was a problem updating this company. Please try again.'));
    }

    /**
     * @param  Company  $company
     *
     * @return Company
     * @throws GeneralException
     */
    public function delete(Company $company): Company
    {
        if ($this->deleteById($company->id)) {
            return $company;
        }

        throw new GeneralException('There was a problem deleting this company. Please try again.');
    }

    /**
     * @param Company $company
     *
     * @throws GeneralException
     * @return Company
     */
    public function restore(Company $company): Company
    {
        if ($company->restore()) {
            return $company;
        }

        throw new GeneralException(__('There was a problem restoring this company. Please try again.'));
    }

    /**
     * @param  Company  $company
     *
     * @return bool
     * @throws GeneralException
     */
    public function destroy(Company $company): bool
    {
        if ($company->forceDelete()) {
            return true;
        }

        throw new GeneralException(__('There was a problem permanently deleting this company. Please try again.'));
    }

    /**
     * @param  array  $data
     *
     * @return Company
     */
    protected function createCompany(array $data = []): Company
    {
        return $this->model::create([
            'name' => $data['name'] ?? null,
            'address' => $data['address'] ?? null,
            'active' => $data['active'] ?? true,
            'use_azure' => $data['use_azure'],
            'notification_emails' => $data['notification_emails'],
            'has_mrhi' => 0,
            'has_pilot' => 0
        ]);
    }
}
