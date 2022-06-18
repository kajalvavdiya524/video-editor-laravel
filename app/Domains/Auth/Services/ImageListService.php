<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\ImageList;
use App\Domains\Auth\Models\Images;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * Class ImageListService.
 */
class ImageListService extends BaseService
{
    /**
     * ImageListService constructor.
     *
     * @param  ImageList  $imagelist
     */
    public function __construct(ImageList $imagelist)
    {
        $this->model = $imagelist;
    }

    /**
     * @param  array  $data
     *
     * @return bool
     * @throws GeneralException
     * @throws \Throwable
     */
    public function store($image_list_name): bool
    {
        DB::beginTransaction();

        $this->createImagelist([
            'name' => $image_list_name,
            'created_by' => auth()->user()->id
        ]);

        DB::commit();
        return true;
    }

    /**
     * @param  array  $data
     *
     * @return bool
     * @throws GeneralException
     * @throws \Throwable
     */
    public function update($imagelist, $image_list_name): bool
    {
        DB::beginTransaction();

        $imagelist->name = $image_list_name;
        $imagelist->save();

        DB::commit();

        return true;
    }

    /**
     * @param  ImageList  $imagelist
     *
     * @return ImageList
     * @throws GeneralException
     */
    public function delete(ImageList $imagelist): bool
    {
        if ($imagelist->forceDelete()) {
            return true;
        }

        throw new GeneralException('There was a problem deleting this images. Please try again.');
    }
    
    /**
     * @param  ImageList  $imagelist
     *
     * @return ImageList
     * @throws GeneralException
     */
    public function copy(ImageList $imagelist): ImageList
    {
        $image_list = ImageList::find($imagelist->id);
        $new = $image_list->replicate();
        $new->name = $new->name . "_copy";
        $new->created_by = auth()->user()->id;
        $new->company_id = auth()->user()->company_id;
        $new->save();

        $images = Images::where('list_id', $imagelist->id)->get();
        $url = "";
        foreach ($images as $img) {
            $new_img = $img->replicate();
            $new_img->name = $new_img->name . "_copy";
            $new_img->list_id = $new->id;
            $new_img->uploaded_by = auth()->user()->id;
            $url = $img->url;
            $arr = explode("/", $url);
            $arr[2] = $new->id;
            $new_img->url = implode("/", $arr);
            $new_img->save();
            Storage::disk('s3')->copy($img->url, $new_img->url);
        }

        return $new;
    }

    /**
     * @param  array  $data
     *
     * @return ImageList
     */
    protected function createImagelist(array $data = []): ImageList
    {
        $imagelist = $this->model::create($data);
        $imagelist->save();

        return $imagelist;
    }
}
