<?php

namespace App\Domains\Auth\Services;

use Exception;
use Illuminate\Support\Facades\DB;

use App\Domains\Auth\Models\GridLayoutTemplate;
use App\Exceptions\GeneralException;
use App\Services\BaseService;

class GridLayoutTemplateService extends BaseService
{
    public function __construct(GridLayoutTemplate $gridLayoutTemplate)
    {
        $this->model = $gridLayoutTemplate;
    }

    public function getTemplate($layout_id, $instance_id) {
        return $this->model::where('layout_id', $layout_id)
                    ->where('instance_id', $instance_id)
                    ->first();
    }

    public function store(array $data = []): GridLayoutTemplate
    {
        DB::beginTransaction();

        try {     
            $gridLayoutTemplate = $this->model::create($data);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__($e->getMessage()));
        }
        DB::commit();

        return $gridLayoutTemplate;
    }

    public function update(GridLayoutTemplate $gridLayoutTemplate, array $data = []): GridLayoutTemplate
    {
        DB::beginTransaction();

        try {
            $gridLayoutTemplate->update($data);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__($e->getMessage()));
        }

        DB::commit();

        return $gridLayoutTemplate;
    }

    public function delete(GridLayoutTemplate $gridLayoutTemplate): GridLayoutTemplate
    {
        if ($this->deleteById($gridLayoutTemplate->id)) {
            return $gridLayoutTemplate;
        }

        throw new GeneralException('There was a problem deleting this dimension. Please try again.');
    }
}