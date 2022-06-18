<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\Dimension;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Class DimensionService.
 */
class DimensionService extends BaseService
{
    /**
     * DimensionService constructor.
     *
     * @param  Dimension  $dimension
     */
    public function __construct(Dimension $dimension)
    {
        $this->model = $dimension;
    }

    
    /**
     * @param  array  $data
     *
     * @return Dimension
     * @throws \Throwable
     */
    public function store(array $data = []): Dimension
    {
        DB::beginTransaction();

        try {
            $dimension = $this->createDimension($data);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem updating this dimension. Please try again.'));
        }

        DB::commit();

        return $dimension;
    }


    /**
     * @param  Dimension  $dimension
     * @param  array  $data
     *
     * @return Dimension
     * @throws \Throwable
     */
    public function update(Dimension $dimension, array $data = []): Dimension
    {
        DB::beginTransaction();

        try {
            $dimension->update([
                'GTIN' => $data['gtin'],
                'width' => $data['width'],
                'height' => $data['height'],
                'company_id' => $data['company_id']
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem updating this dimension. Please try again.'));
        }

        DB::commit();

        return $dimension;
    }

    /**
     * @param  Dimension  $dimension
     *
     * @return Dimension
     * @throws GeneralException
     */
    public function delete(Dimension $dimension): Dimension
    {
        if ($this->deleteById($dimension->id)) {
            return $dimension;
        }

        throw new GeneralException('There was a problem deleting this dimension. Please try again.');
    }

    /**
     * @param  array  $data
     *
     * @return Dimension
     */
    protected function createDimension(array $data = []): Dimension
    {
        return $this->model::create([
            'GTIN' => $data['gtin'] ?? null,
            'width' => $data['width'] ?? 0,
            'height' => $data['height'] ?? 0,
            'company_id' => $data['company_id'] ?? 0,
        ]);
    }
}
