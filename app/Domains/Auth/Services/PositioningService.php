<?php

namespace App\Domains\Auth\Services;

use Illuminate\Support\Facades\Storage;
use App\Domains\Auth\Http\Imports\PositioningUpdate;
use App\Domains\Auth\Models\PositioningOption;
use App\Domains\Auth\Models\PositioningOptionField;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Class PositioningService.
 */
class PositioningService extends BaseService
{
    /**
     * PositioningService constructor.
     *
     */
    public function __construct()
    {
    }

    public function uploadXLSX($file)
    {
        DB::beginTransaction();
        try {
            PositioningOptionField::truncate();
            PositioningOption::truncate();
            Excel::import(new PositioningUpdate, $file);
        } catch (Exception $e) {

            DB::rollBack();

            throw new GeneralException(__('There was a problem loading the positioning file. Please try again.'));
        }
        DB::commit();
    }
}
