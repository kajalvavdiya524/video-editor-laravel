<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\Mapping;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Class MappingService.
 */
class MappingService extends BaseService
{
    /**
     * MappingService constructor.
     *
     * @param  Mapping  $mapping
     */
    public function __construct(Mapping $mapping)
    {
        $this->model = $mapping;
    }

    
    /**
     * @param  array  $data
     *
     * @return Mapping
     * @throws \Throwable
     */
    public function store(array $data = []): Mapping
    {
        DB::beginTransaction();

        try {
            $mapping = $this->createMapping($data);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem updating this mapping. Please try again.'));
        }

        DB::commit();

        return $mapping;
    }


    /**
     * @param  Mapping  $mapping
     * @param  array  $data
     *
     * @return Mapping
     * @throws \Throwable
     */
    public function update(Mapping $mapping, array $data = []): Mapping
    {
        DB::beginTransaction();

        try {
            $mapping->update([
                'ASIN' => $data['asin'],
                'UPC' => $data['upc'],
                'company_id' => $data['company_id']
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem updating this mapping. Please try again.'));
        }

        DB::commit();

        return $mapping;
    }

    /**
     * @param  Mapping  $mapping
     *
     * @return Mapping
     * @throws GeneralException
     */
    public function delete(Mapping $mapping): Mapping
    {
        if ($this->deleteById($mapping->id)) {
            return $mapping;
        }

        throw new GeneralException('There was a problem deleting this mapping. Please try again.');
    }

    /**
     * @param  array  $data
     *
     * @return Mapping
     */
    protected function createMapping(array $data = []): Mapping
    {
        return $this->model::create([
            'ASIN' => $data['asin'] ?? null,
            'UPC' => $data['upc'] ?? null,
            'company_id' => $data['company_id'] ?? null,
        ]);
    }
}
