<?php

namespace App\Domains\Auth\Services;

use Illuminate\Support\Facades\Storage;
use App\Domains\Auth\Models\Theme;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Class ThemeService.
 */
class ThemeService extends BaseService
{
    /**
     * ThemeService constructor.
     *
     * @param  Theme  $theme
     */
    public function __construct(Theme $theme)
    {
        $this->model = $theme;
    }

    /**
     * @param  array  $data
     *
     * @return Theme
     * @throws GeneralException
     * @throws \Throwable
     */
    public function store(array $data = []): Theme
    {
        DB::beginTransaction();

        $theme = $this->createtheme([
            'name' => $data['theme_name'],
            'customer_id' => $data['customer_id'],
            'company_id' => auth()->user()->company_id,
            'attributes' => $data['json_data']
        ]);

        DB::commit();

        $attributes = json_decode($theme->attributes);
        foreach ($attributes as $attribute) {
            if ($attribute->name == "Background Images") {
                foreach ($attribute->list as $image) {
                    foreach ($image->list as $attr) {
                        if ($attr->type == "background") {
                            $url = explode("/", $attr->value);
                            $url[count($url) - 3] = $theme->id;
                            $attr->value = implode('/', $url);
                        }
                    }
                }
            }
        }
        $theme->attributes = json_encode($attributes);
        $theme->save();
        
        return $theme;
    }

    /**
     * @param  Theme  $theme
     * @param  array  $data
     *
     * @return Theme
     * @throws \Throwable
     */
    public function update(Theme $theme, array $data = []): Theme
    {
        $settings = json_decode($data['json_data']);
        $bkg_index = -1;
        for ($i=0; $i < count($settings); $i++) { 
            if ($settings[$i]->name == "Background Images") {
                $bkg_index = $i;
                break;
            }
        }
        $background_list = isset($settings[$bkg_index]) ? $settings[$bkg_index]->list : [];

        $origin_settings = json_decode($theme->attributes);
        $origin_background_list = isset($origin_settings[$bkg_index]) ? $origin_settings[$bkg_index]->list : [];

        foreach ($background_list as $bkg) {
            if ($bkg->list[1]->old_value != "1 file selected" && $bkg->list[1]->old_value != $bkg->list[1]->value) {
                $ori_path = str_replace("/share?file=", "", $bkg->list[1]->old_value);
                $path = str_replace("/share?file=", "", $bkg->list[1]->value);
                if (Storage::disk('s3')->has($ori_path)) {
                    $contents = Storage::disk('s3')->get($ori_path);
                    Storage::disk('s3')->put($path, $contents);
                }
            }
        }
        DB::beginTransaction();

        try {
            $theme->update([
                'name' => $data['theme_name'],
                'attributes' => $data['json_data']
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__($e->getMessage()));
            // throw new GeneralException(__('There was a problem updating this theme. Please try again.'));
        }

        DB::commit();

        return $theme;
    }

    /**
     * @param  Theme  $theme
     *
     * @return Theme
     * @throws GeneralException
     */
    public function copy(Theme $theme): Theme
    {
        $themes = Theme::find($theme->id);
        $newTheme = $themes->replicate();
        $newTheme->name = $newTheme->name . "_copy";
        $newTheme->company_id = auth()->user()->company_id;
        $newTheme->save();

        $newTheme->order = $newTheme->id;
        $newTheme->save();
        return $newTheme;
    }

    /**
     * @param  Theme  $theme
     *
     * @return Theme
     * @throws GeneralException
     */
    public function toggle(Theme $theme): Theme
    {
        $theme->status = !$theme->status;
        $theme->save();
        return $theme;
    }

    /**
     * @param  Theme  $theme
     *
     * @return Theme
     * @throws GeneralException
     */
    public function moveup(Theme $theme): Theme
    {
        $query = Theme::where('customer_id', $theme->customer_id);
        if (!auth()->user()->isMasterAdmin()) {
            $query->whereIn('company_id', [0, auth()->user()->company_id]);
        }
        $themes = $query->orderBy('order', 'ASC')->get();
        for ($i = 1; $i < count($themes); $i ++) {
            if ($themes[$i]->id == $theme->id) {
                $themes[$i]->order = $themes[$i - 1]->order;
                $themes[$i - 1]->order = $theme->order;
                $themes[$i - 1]->save();
                $themes[$i]->save();
            }
        }
        return $theme;
    }

    /**
     * @param  Theme  $theme
     *
     * @return Theme
     * @throws GeneralException
     */
    public function movedown(Theme $theme): Theme
    {
        $query = Theme::where('customer_id', $theme->customer_id);
        if (!auth()->user()->isMasterAdmin()) {
            $query->whereIn('company_id', [0, auth()->user()->company_id]);
        }
        $themes = $query->orderBy('order', 'ASC')->get();
        for ($i = 0; $i < count($themes) - 1; $i ++) {
            if ($themes[$i]->id == $theme->id) {
                $themes[$i]->order = $themes[$i + 1]->order;
                $themes[$i + 1]->order = $theme->order;
                $themes[$i + 1]->save();
                $themes[$i]->save();
            }
        }
        return $theme;
    }

    /**
     * @param  Theme  $theme
     *
     * @return Theme
     * @throws GeneralException
     */
    public function delete(Theme $theme): bool
    {
        if ($theme->forceDelete()) {
            return true;
        }

        throw new GeneralException('There was a problem deleting this theme. Please try again.');
    }

    public function getById($id)
    {
        return $this->model::find($id);
    }

    /**
     * @param  array  $data
     *
     * @return Theme
     */
    protected function createTheme(array $data = []): Theme
    {
        $theme = $this->model::create($data);
        $theme->order = $theme->id;
        $theme->save();

        return $theme;
    }
}
