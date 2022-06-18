<?php

namespace App\Domains\Auth\Services;

use Illuminate\Support\Collection;
use App\Domains\Auth\Models\Uploadimg;
use App\Domains\Auth\Models\StockImage;
use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Models\File;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Class UploadImageService.
 */
class UploadImageService extends BaseService
{
    protected $bannerService;
    /**
     * UploadImageService constructor.
     *
     * @param  Uploadimg  $upload_image
     */
    public function __construct(Uploadimg $upload_image, BannerService $bannerService)
    {
        $this->model = $upload_image;
        $this->bannerService = $bannerService;
    }

    /**
     * @param  array  $data
     *
     * @return Uploadimg
     * @throws GeneralException
     * @throws \Throwable
     */
    public function store(array $data = [])
    {
        $tmpfile = $data['tmp_name'];
        $filename = $data['filename'];
        $company_id = $data['company_id'];
        $image_type = isset($data['image_type']) ? $data['image_type'] : 'product_image';

        $ext = array_reverse(explode(".", $filename))[0];
        if ($ext == "psd") {
            $fn = uniqid();
            file_put_contents($fn.'psd', file_get_contents($tmpfile));
            $im = new \Imagick($fn.'psd');
            $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            $im->setImageFormat('png');
            $im->writeImage($fn.'png');
            $tmpfile = $fn.'png';

            $arr = explode(".", $filename);
            $arr[count($arr) - 1] = "png";
            $filename = implode(".", $arr);
        }

        try {
            if ($image_type == "product_image") {
                Storage::disk('s3')->put('files/'.$company_id.'/'.$filename, file_get_contents($tmpfile));
            } else {
                Storage::disk('s3')->put('stock/'.$company_id.'/'.$filename, file_get_contents($tmpfile));
            }
    
            if (file_exists($filename)) {
                unlink($filename);
            }
        } catch (Exception $e) {
            throw new GeneralException(__($e->getMessage()));
        }

        DB::beginTransaction();

        try {
            $upload_image = null;
            if ($image_type == "product_image") {
                $upload_image = $this->model::create([
                    'company_id' => $company_id,
                    'filename' => $filename, 
                    'url' => 'files/'.$company_id.'/'.$filename
                ]);
            } else {
                $this->model::create([
                    'company_id' => $company_id,
                    'filename' => $filename, 
                    'url' => 'stock/'.$company_id.'/'.$filename
                ]);
                $upload_image = StockImage::create([
                    'company_id' => $company_id,
                    'filename' => $filename, 
                    'url' => 'stock/'.$company_id.'/'.$filename
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem storing this image file. Please try again.'));
        }

        DB::commit();
        return $upload_image;
    }

    public function reindex($files) 
    {
        $deleted_files_count = 0;
        $new_files_count = 0;
        foreach ($files as $file) {
            if ($file['status'] == 'deleted') {
                File::where('path', $file['path'])->delete();
                $deleted_files_count++;
            }
            if ($file['status'] == 'new') {
                $map_info = $this->bannerService->get_file_map_info(explode('.', $file['name'])[0]);
                $new_files_count++;
                $new_item = new File;
                $new_item->name = $file['name'];
                $new_item->path = $file['path'];
                $new_item->product_name = $map_info['product_name'];
                $new_item->brand = $map_info['brand'];
                $new_item->ASIN = $map_info['ASIN'];
                $new_item->UPC = $map_info['UPC'];
                $new_item->parent_gtin = $map_info['parent_gtin'];
                $new_item->child_gtin = $map_info['child_gtin'];
                $new_item->width = $map_info['width'];
                $new_item->height = $map_info['height'];
                $new_item->depth = $map_info['depth'];
                $new_item->has_dimension = $map_info['has_dimension'];
                $new_item->has_child = $map_info['has_child'];
                $new_item->company_id = $file['company_id'];
                $new_item->save();
            }
        }
        $this->set_reindexed();
        // Generate thumbnails after reindexing
        $this->bannerService->generate_thumbnail();

        $message = '';
        if ($new_files_count) {
            $message = $message.$new_files_count.' file(s) were added. ';
        }
        if ($deleted_files_count) {
            $message = $message.$deleted_files_count.' file(s) were deleted. ';
        }
        if (auth()->user()->isMasterAdmin()) {
            $message = $message.File::all()->count().' files in total are indexed.';
        } else {
            $message = $message.File::where('company_id', auth()->user()->company_id)->count().' files in total are indexed.';
        }
        return $message;
    }

    public function set_reindexed() 
    {
        DB::beginTransaction();

        try {
            $new_files = $this->model::where('is_reindexed', 0);
            $new_files->update(['is_reindexed' => 1]);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem reindexing this image file. Please try again.'));
        }

        DB::commit();
    }

    /**
     * @param  Uploadimg  $upload_image
     * @param  array  $data
     *
     * @return Uploadimg
     * @throws \Throwable
     */
    public function update(Uploadimg $upload_image, array $data = []): Uploadimg
    {
        try {
            if ($data['company_id'] != $upload_image->company_id)
                Storage::disk('s3')->move($upload_image->url, 'files/'.$data['company_id'].'/'.$upload_image->filename);
        } catch (Exception $e) {
            throw new GeneralException(__($e->getMessage()));
        }

        DB::beginTransaction();

        try {
            $upload_image->update([
                'company_id' => $data['company_id'],
                'ASIN' => $data['asin'],
                'UPC' => $data['upc'],
                'GTIN' => $data['gtin'],
                'width' => $data['width'],
                'height' => $data['height'],
                'url' => 'files/'.$data['company_id'].'/'.$upload_image->filename
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            throw new GeneralException(__('There was a problem updating this uploaded file. Please try again.'));
        }

        DB::commit();

        return $upload_image;
    }
    
    /**
     * @param  Uploadimg  $upload_image
     *
     * @return Uploadimg
     * @throws GeneralException
     */
    public function delete(Uploadimg $upload_image): Uploadimg
    {
        Storage::disk('s3')->delete($upload_image->url);

        $files = File::where('path', $upload_image->url)->get();
        foreach($files as $file) {
            $file->delete();
        }
        if ($this->deleteById($upload_image->id)) {
            return $upload_image;
        }

        throw new GeneralException('There was a problem deleting this urls file. Please try again.');
    }
}
