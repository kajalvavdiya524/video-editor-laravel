<?php

namespace App\Domains\Auth\Http\Exports;

use App\Domains\Auth\Models\NewMapping;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class NewMappingExport implements FromCollection, WithColumnFormatting
{
    use Exportable;

    public function __construct($isDeliverable, $mode=null)
    {
        $this->isDeliverable = $isDeliverable;
        $this->mode = $mode;
    }

    public function collection()
    {
        $user = auth()->user();
        $company_id = $user->company_id;
        $collection = collect();
        if ($user->isMasterAdmin()) {
            if ($this->isDeliverable) {
                $rows = NewMapping::select('GTIN', 'child_links', 'ASIN', 'brand', 'product_name', 'image_url', 'nf_url', 'ingredient_url', 'status')
                            ->get();
                foreach($rows as $row) {
                    if ($row->status == "new") {
                        $collection->prepend([strval($row->GTIN), $row->child_links, $row->ASIN, $row->brand, $row->product_name, $row->image_url, $row->nf_url, $row->ingredient_url, "", "", ""]);
                    } else {
                        $prod = "";
                        $nf = "";
                        $ingre = "";
                        if (strpos($row->status, "changed_prod") !== false) {
                            $prod = $row->image_url;
                        }
                        if (strpos($row->status, "changed_nf") !== false) {
                            $nf = $row->nf_url;
                        }
                        if (strpos($row->status, "changed_ingre") !== false) {
                            $ingre = $row->ingredient_url;
                        }
                        if ($prod != "" || $nf != "" || $ingre != "") {
                            $collection->prepend([strval($row->GTIN), $row->child_links, $row->ASIN, $row->brand, $row->product_name, "", "", "", $prod, $nf, $ingre]);
                        }
                    }
                }
                $collection->prepend(['GTIN', 'Child Links', 'ASIN', 'Brand', 'Product Name', 'New Image', 'New Nutrition Facts Image', 'New Ingredients Image', 'Updated Image', 'Updated Nutrition Facts Image', 'Updated Ingredients Image']);
                return $collection;
            }
            if ($this->mode == "NewOnly") {
                return NewMapping::where('status', 'new')
                    ->select('GTIN', 'child_links', 'ASIN', 'brand', 'product_name', 'image_url', 'nf_url', 'ingredient_url', 'width', 'height', 'depth')
                    ->get()
                    ->prepend(['GTIN', 'Child Links', 'ASIN', 'Brand', 'Product Name', 'Image URL', 'Nutrition Facts Image URL', 'Ingredients Image URL', 'Width', 'Height', 'Depth']);
            }
            if ($this->mode == "New&Changed") {
                return NewMapping::where('status', 'new')
                    ->orWhere('status', 'LIKE', '%changed_prod%')
                    ->select('GTIN', 'child_links', 'ASIN', 'brand', 'product_name', 'image_url', 'nf_url', 'ingredient_url', 'width', 'height', 'depth')
                    ->get()
                    ->prepend(['GTIN', 'Child Links', 'ASIN', 'Brand', 'Product Name', 'Image URL', 'Nutrition Facts Image URL', 'Ingredients Image URL', 'Width', 'Height', 'Depth']);
            }
            return NewMapping::whereNotIn('status', [' ', 'file exist', 'child_link'])
                ->select('GTIN', 'child_links', 'ASIN', 'brand', 'product_name', 'image_url', 'nf_url', 'ingredient_url', 'width', 'height', 'depth')
                ->get()
                ->prepend(['GTIN', 'Child Links', 'ASIN', 'Brand', 'Product Name', 'Image URL', 'Nutrition Facts Image URL', 'Ingredients Image URL', 'Width', 'Height', 'Depth']);
        } else {
            if ($this->isDeliverable) {
                $rows = NewMapping::whereIn('company_id', [0, $user->company_id])
                    ->select('GTIN', 'child_links', 'ASIN', 'brand', 'product_name', 'image_url', 'nf_url', 'ingredient_url')
                    ->get();
                foreach($rows as $row) {
                    if ($row->status == "new") {
                        $collection->prepend([strval($row->GTIN), $row->child_links, $row->ASIN, $row->brand, $row->product_name, $row->image_url, $row->nf_url, $row->ingredient_url, "", "", ""]);
                    } else {
                        $prod = "";
                        $nf = "";
                        $ingre = "";
                        if (strpos($row->status, "changed_prod") !== false) {
                            $prod = $row->image_url;
                        }
                        if (strpos($row->status, "changed_nf") !== false) {
                            $nf = $row->nf_url;
                        }
                        if (strpos($row->status, "changed_ingre") !== false) {
                            $ingre = $row->ingredient_url;
                        }
                        if ($prod != "" || $nf != "" || $ingre != "") {
                            $collection->prepend([strval($row->GTIN), $row->child_links, $row->ASIN, $row->brand, $row->product_name, "", "", "", $prod, $nf, $ingre]);
                        }
                    }
                }
                $collection->prepend(['GTIN', 'Child Links', 'ASIN', 'Brand', 'Product Name', 'New Image', 'New Nutrition Facts Image', 'New Ingredients Image', 'Updated Image', 'Updated Nutrition Facts Image', 'Updated Ingredients Image']);
                return $collection;
            }
            if ($this->mode == "NewOnly") {
                return NewMapping::where('status', 'new')
                    ->whereIn('company_id', [0, $user->company_id])
                    ->select('GTIN', 'child_links', 'ASIN', 'brand', 'product_name', 'image_url', 'nf_url', 'ingredient_url', 'width', 'height', 'depth')
                    ->get()
                    ->prepend(['GTIN', 'Child Links', 'ASIN', 'Brand', 'Product Name', 'Image URL', 'Nutrition Facts Image URL', 'Ingredients Image URL', 'Width', 'Height', 'Depth']);
            }
            if ($this->mode == "New&Changed") {
                return NewMapping::where('status', 'new')
                    ->orWhere('status', 'LIKE', '%changed_prod%')
                    ->whereIn('company_id', [0, $user->company_id])
                    ->select('GTIN', 'child_links', 'ASIN', 'brand', 'product_name', 'image_url', 'nf_url', 'ingredient_url', 'width', 'height', 'depth')
                    ->get()
                    ->prepend(['GTIN', 'Child Links', 'ASIN', 'Brand', 'Product Name', 'Image URL', 'Nutrition Facts Image URL', 'Ingredients Image URL', 'Width', 'Height', 'Depth']);
            }
            return NewMapping::whereIn('company_id', [0, $user->company_id])
                ->whereNotIn('status', [' ', 'file exist', 'child_link'])
                ->select('GTIN', 'child_links', 'ASIN', 'brand', 'product_name', 'image_url', 'nf_url', 'ingredient_url', 'width', 'height', 'depth')
                ->get()
                ->prepend(['GTIN', 'Child Links', 'ASIN', 'Brand', 'Product Name', 'Image URL', 'Nutrition Facts Image URL', 'Ingredients Image URL', 'Width', 'Height', 'Depth']);
        }
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function getRowCount(): int
    {
        $user = auth()->user();
        $company_id = $user->company_id;
        $rows = array();
        if ($user->isMasterAdmin()) {
            $rows = NewMapping::whereIn('status', ['new', 'changed_prod', 'changed_nf', 'changed_ingre'])
                            ->select('GTIN')
                            ->get();
        } else {
            $rows = NewMapping::whereIn('company_id', [0, $user->company_id])
                            ->whereIn('status', ['new', 'changed_prod', 'changed_nf', 'changed_ingre'])
                            ->select('GTIN')
                            ->get();
        }

        return count($rows);
    }
}