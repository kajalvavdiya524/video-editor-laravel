<?php

namespace App\Domains\Auth\Http\Controllers\Backend\Settings;

use Illuminate\Http\Request;
use App\Exceptions\GeneralException;
use App\Http\Controllers\Controller;
use App\Domains\Auth\Models\ProductSelection;
use App\Domains\Auth\Services\ProductSelectionService;

/**
 * Class ProductSelectionController.
 */
class ProductSelectionController extends Controller
{
    /**
     * ProductSelectionController constructor.
     *
     * @param  ProductSelectionService  $productSelectionService
     */
    public function __construct(ProductSelectionService $productSelectionService)
    {
        $this->productSelectionService = $productSelectionService;
    }

    public function generate_product_selections_report()
    {
        return $this->productSelectionService->generate_product_selections_report("productSelections_".date("Y_m_d").".xlsx");
    }

    /**
     * @param  ProductSelection  $productSelection
     *
     * @return mixed
     * @throws \App\ProductSelections\GeneralProductSelection
     */
    public function destroy(Request $request, ProductSelection $productSelection)
    {
        $this->productSelectionService->destroy($productSelection);

        return redirect()->route('admin.auth.settings.advanced.deleted')->withFlashSuccess(__('The product selection was successfully deleted.'));
    }
}
