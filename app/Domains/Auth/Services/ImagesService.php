<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Models\Images;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * Class ImagesService.
 */
class ImagesService extends BaseService
{
    /**
     * ImagesService constructor.
     *
     * @param  Images  $images
     */
    public function __construct(Images $images)
    {
        $this->model = $images;
    }

    /**
     * @param  array  $data
     *
     * @return bool
     * @throws GeneralException
     * @throws \Throwable
     */
    public function store($image_name, $list_id, $image): bool
    {
        $filename = $image->getClientOriginalName();
        $filename = str_replace(" ", "_", $filename);
        $filename = preg_replace('/[^A-Za-z0-9\-\.]/', '', $filename);

        $fn_arr = explode(".", $filename);
        array_pop($fn_arr);
        $fn_arr = implode(".", $fn_arr);
        $count = Images::where('filename', $filename)->where('list_id', $list_id)->count();
        if ($count) {
            return false;
        }

        DB::beginTransaction();

        $images = $this->createImages([
            'name' => $image_name ? $image_name : $fn_arr,
            'filename' => $filename,
            'uploaded_by' => auth()->user()->id, 
            'list_id' => $list_id,
            'url' => "files/images/$list_id/$filename"
        ]);

        DB::commit();

        Storage::disk('s3')->put("files/images/$list_id/$filename", file_get_contents($image));
        Storage::disk('public')->putFileAs("img/list/$list_id", $image, $filename);
        return true;
    }

    /**
     * @param  array  $data
     *
     * @return bool
     * @throws GeneralException
     * @throws \Throwable
     */
    public function update($image, $image_name): bool
    {
        DB::beginTransaction();

        $image->name = $image_name;
        $image->save();

        DB::commit();

        return true;
    }

    // /**
    //  * @param  Images  $images
    //  *
    //  * @return Images
    //  * @throws GeneralException
    //  */
    // public function moveup(Images $images): Images
    // {
    //     $image_list = Images::orderBy('order', 'ASC')->get();
    //     for ($i = 1; $i < count($image_list); $i ++) {
    //         if ($image_list[$i]->id == $images->id) {
    //             $image_list[$i]->order = $image_list[$i - 1]->order;
    //             $image_list[$i - 1]->order = $images->order;
    //             $image_list[$i - 1]->save();
    //             $image_list[$i]->save();
    //         }
    //     }
    //     return $images;
    // }

    // /**
    //  * @param  Images  $images
    //  *
    //  * @return Images
    //  * @throws GeneralException
    //  */
    // public function movedown(Images $images): Images
    // {
    //     $image_list = Images::orderBy('order', 'ASC')->get();
    //     for ($i = 0; $i < count($image_list) - 1; $i ++) {
    //         if ($image_list[$i]->id == $images->id) {
    //             $image_list[$i]->order = $image_list[$i + 1]->order;
    //             $image_list[$i + 1]->order = $images->order;
    //             $image_list[$i + 1]->save();
    //             $image_list[$i]->save();
    //         }
    //     }
    //     return $images;
    // }

    /**
     * @param  Images  $images
     *
     * @return Images
     * @throws GeneralException
     */
    public function delete(Images $images): bool
    {
        if ($images->forceDelete()) {
            return true;
        }

        throw new GeneralException('There was a problem deleting this images. Please try again.');
    }

    /**
     * @param  array  $data
     *
     * @return Images
     */
    protected function createImages(array $data = []): Images
    {
        $images = $this->model::create($data);
        // $images->order = $images->id;
        $images->save();

        return $images;
    }
}
