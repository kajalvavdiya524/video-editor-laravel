<?php

namespace App\Domains\Auth\Http\Controllers\Frontend;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Exceptions\GeneralException;
use App\Http\Controllers\Controller;
use App\Domains\Auth\Models\Uploadimg;
use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Models\Mapping;
use App\Domains\Auth\Models\Dimension;
use App\Domains\Auth\Models\File;
use App\Domains\Auth\Services\UploadImageService;
use App\Domains\Auth\Services\MappingService;
use App\Domains\Auth\Services\DimensionService;
use App\Domains\Auth\Services\SettingsService;
use App\Domains\Auth\Services\FileService;
use Mtownsend\RemoveBg\RemoveBg;


/**
 * Class UploadImagesController.
 */
class UploadImagesController extends Controller
{

    /**
     * @var UploadImageService
     */
    protected $uploadImageService;
    
    /**
     * @var MappingService
     */
    protected $mappingService;
    
    /**
     * @var DimensionService
     */
    protected $dimensionService;
    
    /**
     * @var SettingsService
     */
    protected $settingsService;


    /**
     * UploadImagesController constructor.
     *
     * @param  UploadImageService  $uploadImageService
     * @param  MappingService  $mappingService
     * @param  DimensionService  $dimensionService
     * @param  SettingsService  $settingsService
     */
    public function __construct(UploadImageService  $uploadImageService, MappingService  $mappingService, DimensionService  $dimensionService, SettingsService  $settingsService)
    {
        $this->uploadImageService = $uploadImageService;
        $this->mappingService = $mappingService;
        $this->dimensionService = $dimensionService;
        $this->settingsService = $settingsService;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {   
        $companies = Company::all();
        $reindex_count = Uploadimg::where('is_reindexed', 0)->count();
        return view('frontend.user.uploadimg.index', ['companies' => $companies, 'reindex_count' => $reindex_count]);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function upload_image_from_web(Request $request) {
        $apiKey = env('REMOVEBG_API_KEY');
        $removebg = new RemoveBg($apiKey);

        // from file-input
        $files = array();
        $filenames = array();
        $company_id = auth()->user()->company_id;
        $background_remove = isset($request->background_remove) ? $request->background_remove : null;
        
        // from textarea
        $url_text = trim($request->upload_images_url);
        $textAr = explode("\r\n", $url_text);
        $textAr = array_filter($textAr, 'trim');
        $file_count = count($textAr);
        foreach ($textAr as $line) {
            $lineAr = explode(" ", $line);
            if (count($lineAr) == 2) {
                $fn = preg_replace('/[^A-Za-z0-9]/', '', $lineAr[0]);
                $fn = $fn.".png";
                $furl = $lineAr[1];
            } else {
                $furl = $lineAr[0];
                $fn = array_reverse(explode("/", $furl))[0];
                $fn = explode(".", $fn);
                array_pop($fn);
                $fn = implode(".", $fn);
                $fn = preg_replace('/[^A-Za-z0-9]/', '', $fn);
                $fn = $fn.".png";
            }

            if ($background_remove) {
                $base64EncodedFile = base64_encode(file_get_contents($furl));
                $removebg->base64($base64EncodedFile)->save($fn);
            } else {
                Storage::disk('public')->put($fn, file_get_contents($furl));
            }

            $this->uploadImageService->store([
                'filename' => $fn, 
                'tmp_name' => $fn, 
                'company_id' => $company_id
            ]);
            $files[] = array(
                'name' => $fn,
                'path' => 'files/'.$company_id.'/' . $fn,
                'company_id' => $company_id, 
                'status' => 'new'
            );
            $filenames[] = $fn;
        } 

        $this->uploadImageService->reindex($files);

        if ($file_count == 1) {
            $message = "1 image file was successfully uploaded.";
        } else {
            $message = $file_count." image files were successfully uploaded.";
        }
        
        $data = array('msg' => 'Images: '.implode(', ', $filenames));
        $this->settingsService->send_email('backend.includes.text_mail', "RapidAds - ".$message, $data);
        
        return response()->json($filenames);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function upload_images(Request $request) {
        $apiKey = env('REMOVEBG_API_KEY');
        $removebg = new RemoveBg($apiKey);

        // from file-input
        $files = array();
        $filenames = array();
        $company_id = auth()->user()->isMasterAdmin() ? $request->company_id : auth()->user()->company_id;
        $background_remove = isset($request->background_remove) ? $request->background_remove : null;
        $file_array = isset($_FILES['upload_images']) ? $_FILES['upload_images'] : [];
        $file_count = isset($_FILES['upload_images']) ? count($file_array['name']) : 0;

        for ($i = 0; $i < $file_count; $i ++) {
            $fn = $file_array['name'][$i];
            $fn_arr = explode(".", $fn);
            array_pop($fn_arr);
            $fn = implode(".", $fn_arr);
            $fn = preg_replace('/[^A-Za-z0-9_]/', '', $fn);
            $fn = $fn . ".png";
            $tmp_name = $file_array['tmp_name'][$i];

            if ($background_remove == "on") {
                $base64EncodedFile = base64_encode(file_get_contents($file_array['tmp_name'][$i]));
                $removebg->base64($base64EncodedFile)->save($fn);
                $tmp_name = $fn;
            }

            $this->uploadImageService->store([
                'filename' => $fn, 
                'tmp_name' => $tmp_name, 
                'company_id' => $company_id,
                'image_type' => $request->image_type
            ]);
            $arr = explode(".", $fn);
            if ($arr[count($arr) - 1] == "psd") {
                $arr[count($arr) - 1] = "png";
                $fn = implode(".", $arr);
            }
            if ($request->image_type == "product_image") {
                $files[] = array(
                    'name' => $fn,
                    'path' => 'files/'.$company_id.'/' . $fn,
                    'company_id' => $company_id, 
                    'status' => 'new'
                );
                $filenames[] = $fn;
            }
        }

        // from textarea
        $url_text = trim($request->upload_images_url);
        $textAr = explode("\r\n", $url_text);
        $textAr = array_filter($textAr, 'trim');
        foreach ($textAr as $line) {
            $lineAr = explode(" ", $line);
            if (count($lineAr) == 2) {
                $fn = preg_replace('/[^A-Za-z0-9]/', '', $lineAr[0]);
                $fn = $fn.".png";
                $furl = $lineAr[1];
            } else {
                $furl = $lineAr[0];
                $fn = array_reverse(explode("/", $furl))[0];
                $fn = explode(".", $fn);
                array_pop($fn);
                $fn = implode(".", $fn);
                $fn = preg_replace('/[^A-Za-z0-9]/', '', $fn);
                $fn = $fn.".png";
            }

            if ($background_remove == "on") {
                $base64EncodedFile = base64_encode(file_get_contents($furl));
                $removebg->base64($base64EncodedFile)->save($fn);
            } else {
                Storage::disk('public')->put($fn, file_get_contents($furl));
            }

            $this->uploadImageService->store([
                'filename' => $fn, 
                'tmp_name' => $fn, 
                'company_id' => $company_id,
                'image_type' => $request->image_type
            ]);
            if ($request->image_type == "product_image") {
                $files[] = array(
                    'name' => $fn,
                    'path' => 'files/'.$company_id.'/' . $fn,
                    'company_id' => $company_id, 
                    'status' => 'new'
                );
                $filenames[] = $fn;
            }
        } 

        $this->uploadImageService->reindex($files);

        if ($file_count == 1) {
            $message = "1 image file was successfully uploaded.";
        } else {
            $message = $file_count." image files were successfully uploaded.";
        }
        
        $data = array('msg' => 'Images: '.implode(', ', $filenames));
        $this->settingsService->send_email('backend.includes.text_mail', "RapidAds - ".$message, $data);

        return redirect()->route('frontend.file.uploadimg.index')->withFlashSuccess(__($message));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function update_image(Request $request) {
        $id = $request->id;
        $company_id = auth()->user()->isMasterAdmin() ? $request->company : auth()->user()->company_id;
        $upload_image = Uploadimg::where('id', $id)->first();

        // Images for all companies can only be deleted by masteradmin
        if ($upload_image->company_id == 0 && !auth()->user()->isMasterAdmin()){
            return redirect()->route('frontend.file.uploadimg.deleted')->withFlashDanger(__('You are not allowed to edit this image'));
        }


        $file = File::where('name', $upload_image->filename)
                    ->where('company_id', $upload_image->company_id)->first();
        if ($file) {
            $file->ASIN = $request->asin;
            $file->UPC = $request->upc;
            $file->width = $request->width;
            $file->height = $request->height;
            $file->save();
        }

        $mapping = Mapping::where('ASIN', $upload_image->asin)
                        ->where('UPC', $upload_image->upc)
                        ->where('company_id', $upload_image->company_id)->first();
        // if ($mapping) {
        //     $this->mappingService->update($mapping, [
        //         'asin' => $request->asin,
        //         'upc' => $request->upc,
        //         'company_id' => $company_id
        //     ]);
        // } else {
        //     $this->mappingService->store([
        //         'asin' => $request->asin,
        //         'upc' => $request->upc,
        //         'company_id' => $company_id
        //     ]);
        // }

        // $dimension = Dimension::where('GTIN', $upload_image->gtin)
        //                 ->where('company_id', $upload_image->company_id)->first();
        // if ($dimension) {
        //     $this->dimensionService->update($dimension, [
        //         'gtin' => $request->gtin,
        //         'width' => $request->width,
        //         'height' => $request->height,
        //         'company_id' => $company_id
        //     ]);
        // } else {
        //     $this->dimensionService->store([
        //         'gtin' => $request->gtin,
        //         'width' => $request->width,
        //         'height' => $request->height,
        //         'company_id' => $company_id
        //     ]);
        // }

        $this->uploadImageService->update($upload_image, [
            'company_id' => $company_id, 
            'asin'  => $request->asin,
            'upc' => $request->upc,
            'gtin' => $request->gtin, 
            'width' => $request->width, 
            'height' => $request->height
        ]);

        return redirect()->route('frontend.file.uploadimg.index')->withFlashSuccess(__('Image file was successfully updated.'));

    }

    /**
     * @param  Uploadimg  $upload_image|null
     */
    public function download_image(Uploadimg $upload_image) {
        return Storage::disk('s3')->download($upload_image->url);
    }

    /**
     * @param  Uploadimg  $upload_image
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function destroy(Request $request, Uploadimg $upload_image)
    {
        // Images for all companies can only be deleted by masteradmin
        if ($upload_image->company_id == 0 && !auth()->user()->isMasterAdmin()){
            return redirect()->route('frontend.file.uploadimg.deleted')->withFlashDanger(__('You are not allowed to delete this image'));
        }

        $this->uploadImageService->delete($upload_image);
        
        $mapping = Mapping::where('ASIN', $upload_image->ASIN)
                            ->where('UPC', $upload_image->UPC)
                            ->where('company_id', $upload_image->company_id)
                            ->first();
        if ($mapping) {
            $this->mappingService->delete($mapping);
        }

        $dimension = Dimension::where('GTIN', $upload_image->GTIN)
                        ->where('company_id', $upload_image->company_id)->first();
        if ($dimension) {
            $this->dimensionService->delete($dimension);
        }
        return redirect()->route('frontend.file.uploadimg.deleted')->withFlashSuccess(__('The image file was successfully deleted.'));
    }
}
