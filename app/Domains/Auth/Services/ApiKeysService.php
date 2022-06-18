<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\ApiKeys;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Class ApiKeysService.
 */
class ApiKeysService extends BaseService
{
    /**
     * ApiKeysService constructor.
     *
     * @param  ApiKeys  $apikeys
     */
    public function __construct(ApiKeys $apikeys)
    {
        $this->model = $apikeys;
    }

    /**
     * @param  array  $data
     *
     * @return ApiKeys
     * @throws GeneralException
     * @throws \Throwable
     */
    public function store(array $data = []): ApiKeys
    {
        DB::beginTransaction();

        try {
            $apikeys = $this->createApiKeys($data);
        } catch (Exception $e) {
            DB::rollBack();
            throw new GeneralException(__('There was a problem creating this API key. Please try again.'));
        }

        DB::commit();

        return $apikeys;
    }

    /**
     * @param  ApiKeys  $apikeys
     * @param  array  $data
     *
     * @return ApiKeys
     * @throws \Throwable
     */
    public function update(ApiKeys $apikeys, array $data = []): ApiKeys
    {
        DB::beginTransaction();

        
        try {
            $apikeys->update([
                'key' => $data['key'],
                'company_id' => $data['company'],
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem updating this API key. Please try again.'));
        }

        
        DB::commit();

        return $apikeys;
    }

    /**
     * @param  ApiKeys  $apikeys
     *
     * @return ApiKeys
     * @throws GeneralException
     */
    public function delete(ApiKeys $apikeys): ApiKeys
    {
        if ($this->deleteById($apikeys->id)) {
            return $apikeys;
        }

        throw new GeneralException('There was a problem deleting this api keys. Please try again.');
    }

    /**
     * @param ApiKeys $apikeys
     *
     * @throws GeneralException
     * @return ApiKeys
     */
    public function restore(ApiKeys $apikeys): ApiKeys
    {
        if ($apikeys->restore()) {
            return $apikeys;
        }

        throw new GeneralException(__('There was a problem restoring this api keys. Please try again.'));
    }

    /**
     * @param  array  $data
     *
     * @return ApiKeys
     */
    protected function createApiKeys(array $data = []): ApiKeys
    {
        return $this->model::create([
            'key' => $data['key'],
            'company_id' => $data['company'] ?? null,
        ]);
    }


  /**
     * @param  ApiKeys  $apiKeys
     *
     * @return ApiKeys
     * @throws GeneralException
     */
    public function toggle(ApiKeys $apiKeys): ApiKeys
    {
        $apiKeys->status = !$apiKeys->status;
        $apiKeys->save();
        return $apiKeys;
    }


}
