<?php

namespace App\Domains\Auth\Services;

use Exception;
use Illuminate\Support\Facades\DB;

use App\Domains\Auth\Models\GridLayout;
use App\Exceptions\GeneralException;
use App\Services\BaseService;

class GridLayoutService extends BaseService
{
    public function __construct(GridLayout $gridLayout)
    {
        $this->model = $gridLayout;
    }

    public function store(array $data = []): GridLayout
    {
        DB::beginTransaction();

        try {     
            $gridLayout = $this->model::create($data);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__($e->getMessage()));
        }
        DB::commit();

        return $gridLayout;
    }

    public function update(GridLayout $gridLayout, array $data = []): GridLayout
    {
        DB::beginTransaction();

        try {
            $gridLayout->update($data);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__($e->getMessage()));
        }

        DB::commit();

        return $gridLayout;
    }

    public function delete(GridLayout $gridLayout): GridLayout
    {
        if ($this->deleteById($gridLayout->id)) {
            return $gridLayout;
        }

        throw new GeneralException('There was a problem deleting this dimension. Please try again.');
    }

    public function copy(GridLayout $gridLayout): GridLayout
    {
        $user = auth()->user();

        $newGridLayout = $gridLayout->replicate();
        $newGridLayout->name = $newGridLayout->name . "_copy";
        $newGridLayout->user_id = $user->id;
        $newGridLayout->save();

        $newGridLayout->companies()->attach($user->company_id);

        foreach ($gridLayout->templates as $template) {
            $newTemplate = $template->replicate();
            $newTemplate->layout_id = $newGridLayout->id;
            $newTemplate->save();
        }

        return $newGridLayout;
    }

    public function findTemplateIdByInstance($layout_id, $instance_id){

        $layout = GridLayout::find($layout_id);
        
        if ($layout){
            foreach (json_decode($layout->settings) as $template){
                if ($template->instance_id == $instance_id)
                    return $template->template_id;
            }
        }

        return null;

    }
}