<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\Customer;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Class CustomerService.
 */
class CustomerService extends BaseService
{
    /**
     * CustomerService constructor.
     *
     * @param  Customer  $customer
     */
    public function __construct(Customer $customer)
    {
        $this->model = $customer;
    }

    /**
     * @param  array  $data
     *
     * @return Customer
     * @throws GeneralException
     * @throws \Throwable
     */
    public function store(array $data = []): Customer
    {
        DB::beginTransaction();

        try {
            $customer = $this->model->create($data);
            $customer->companies()->attach($data['companies']);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem creating this customer. Please try again.'));
        }

        DB::commit();

        return $customer;
    }

    /**
     * @param  Customer  $customer
     * @param  array  $data
     *
     * @return Customer
     * @throws \Throwable
     */
    public function update(Customer $customer, array $data = []): Customer
    {
        DB::beginTransaction();

        try {
            $customer->update($data);
            $customer->companies()->detach();
            $customer->companies()->attach($data['companies']);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem updating this customer. Please try again.'));
        }

        DB::commit();

        return $customer;
    }

    /**
     * @param  Customer  $customer
     *
     * @return Customer
     * @throws GeneralException
     */
    public function delete(Customer $customer): Customer
    {
        if ($this->deleteById($customer->id)) {
            return $customer;
        }

        throw new GeneralException('There was a problem deleting this customer. Please try again.');
    }

    /**
     * @param Customer $customer
     *
     * @throws GeneralException
     * @return Customer
     */
    public function restore(Customer $customer): Customer
    {
        if ($customer->restore()) {
            return $customer;
        }

        throw new GeneralException(__('There was a problem restoring this customer. Please try again.'));
    }

    public function getTemplates($customer_id)
    {
        return $this->getById($customer_id)->templates;
    }

  /**
     * @param  Customer  $customer
     *
     * @return Customer
     * @throws GeneralException
     */
    public function toggle(Customer $customer): Customer
    {
        $customer->status = !$customer->status;
        $customer->save();
        return $customer;
    }

}
