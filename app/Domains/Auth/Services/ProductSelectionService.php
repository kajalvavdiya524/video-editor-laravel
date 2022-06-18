<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\ProductSelection;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use App\Domains\Auth\Http\Exports\ProductSelectionExport;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Class ProductSelectionService.
 */
class ProductSelectionService extends BaseService
{
    /**
     * ProductSelectionService constructor.
     *
     * @param  ProductSelection  $exception
     */
    public function __construct(ProductSelection $exception)
    {
        $this->model = $exception;
    }

    /**
     * @param  array  $data
     *
     * @return ProductSelection
     * @throws GeneralException
     * @throws \Throwable
     */
    public function store(array $data = []): ProductSelection
    {
        DB::beginTransaction();

        try {
            $exception = $this->createProductSelection([
                'file_id' => $data['file_id'], 
                'company_id' => auth()->user()->company_id
            ]);
        } catch (ProductSelection $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem creating this exception. Please try again.'));
        }

        DB::commit();

        return $exception;
    }

    /**
     * @param  ProductSelection  $exception
     *
     * @return bool
     * @throws GeneralException
     */
    public function destroy(ProductSelection $product_selection): bool
    {
        if ($product_selection->forceDelete()) {
            return true;
        }

        throw new GeneralException(__('There was a problem permanently deleting this item. Please try again.'));
    }

    public function generate_exceptions_report($file)
    {
        return Excel::download(new ProductSelectionExport, $file);
    }
    
    /**
     * @param  array  $data
     *
     * @return ProductSelection
     */
    protected function createProductSelection(array $data = []): ProductSelection
    {
        return $this->model::create($data);
    }

    public function generate_product_selections_report($file) 
    {
        return Excel::download(new ProductSelectionExport, $file);
    }
}
