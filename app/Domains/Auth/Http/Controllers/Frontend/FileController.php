<?php

namespace App\Domains\Auth\Http\Controllers\Frontend;

use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Domains\Auth\Models\File;
use App\Domains\Auth\Models\Company;
use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Services\BannerService;
use App\Domains\Auth\Services\UploadImageService;

use Datatables;
use ZipArchive;

/**
 * Class FileController.
 */
class FileController extends Controller
{
    /**
     * @var BannerService
     */
    protected $bannerService;

    /**
     * @var UploadImageService
     */
    protected $uploadImageService;

    /**
     * FileController constructor.
     *
     * @param  BannerService  $bannerService
     */
    public function __construct(BannerService $bannerService, UploadImageService $uploadImageService)
    {
        $this->bannerService = $bannerService;
        $this->uploadImageService = $uploadImageService;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('frontend.user.file');
    }

    public function list()
    {
        return view('frontend.user.file_list');
    }

    public function ajax_file_list(Request $request)
    {
        $company = auth()->user()->company;

        if ($company && $company->use_azure) {
            $endpoint = 'https://davebigdatastorage.blob.core.windows.net';
            $sasToken = 'sv=2020-08-04&ss=bfqt&srt=sco&sp=rwdlacuptfx&se=2022-07-27T04:59:18Z&st=2021-07-26T20:59:18Z&spr=https&sig=rTrZTXj5MK%2Fkcztyyx%2F1tZI04ULDuh9lLP47j6jIfsU%3D';
            $endpoint = sprintf('BlobEndpoint=%s;SharedAccessSignature=%s;', $endpoint, $sasToken);
            $client = BlobRestProxy::createBlobService($endpoint);
            $adapter = new AzureBlobStorageAdapter($client, 'container_name');
            $filesystem = new Filesystem($adapter);
            return response()->json($filesystem->listContents());
        } else {
            $search_key = preg_replace('!\s+!', ',', urldecode($request->searchKey));
            $search_key_list = explode(",", $search_key);
            $result = $this->get_pagination_files_list($search_key_list, $request->pageNumber - 1, $request->pageSize);
            for ($i = 0; $i < count($result['items']); $i++) {
                $item = $result['items'][$i];

                if ($item['thumbnail'] != '') {
                    if (!Storage::disk('s3')->exists($item['thumbnail']) && Storage::disk('s3')->exists($item['path'])) {
                        $url = siteUrl() . '/share?file=' . $item['path'];
                        $imagick = new \Imagick($url);
                        $imagick->scaleImage(0, 128);
                        Storage::disk('s3')->put($item['thumbnail'], $imagick->getImageBlob());
                    }

                    $item['url'] = siteUrl() . '/share?file=' . $item['thumbnail'];
                } else {
                    $item['url'] = siteUrl() . '/share?file=' . $item['path'];
                }
                $item['full_url'] = siteUrl() . '/share?file=' . $item['path'];
                $result['items'][$i] = $item;
            }
            return response()->json($result);
        }
    }

    public function ajax_data(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->get_files_list();
            return Datatables::of($data)->make(true);
        }
    }

    /**
     * Reindex files.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function ajax_reindex(Request $request)
    {
        $files = $this->get_files_list();
        $message = $this->uploadImageService->reindex($files);
        return redirect()->route('frontend.file.index')->withFlashSuccess($message);
    }

    /**
     * Download a csv of all files stored in db.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function ajax_export(Request $request)
    {
        $list = $this->get_files_list();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="filelist.csv"');

        $fp = fopen('php://output', 'wb');
        foreach ($list as $line) {
            fputcsv($fp, $line);
        }
        fclose($fp);
    }

    /**
     * Download a zip archive of files found by id.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function ajax_download(Request $request)
    {
        $files_ids = $request->file_ids;
        $files = File::whereIn('id', $files_ids)->get();

        $files_to_delete = glob(public_path() . "/*.zip");
        $now = time();
        foreach ($files_to_delete as $item) {
            if (is_file($item)) {
                if ($now - filemtime($item) >= 3600) { //1 hour
                    unlink($item);
                }
            }
        }

        $zip_file = uniqid() . '.zip';
        $zip = new ZipArchive();
        if ($zip->open(public_path($zip_file), ZipArchive::CREATE) === TRUE) {
            foreach ($files as $file) {
                $zip->addFromString($file->name, Storage::disk('s3')->get($file->path));
            }
            $zip->close();
        }

        return response()->json(['url' => url($zip_file), 'filename' => 'files.zip']);
    }

    private function get_thumbnail_image($file, $size, $type, $filename)
    {
        $thumbnail = 'files/thumbnails/' . $size . 'x' . $size . '/' . $file->company_id . '/' . $type . $filename;
        $url = 'files/' . $file->company_id . '/' . $type . $filename;
        if ($file->is_cropped) {
            $url = 'files/cropped/' . $file->company_id . '/' . $type . $filename;
        }
        $temp_url = "";
        if (Storage::disk('s3')->exists($url)) {
            $imagick = new \Imagick(siteUrl() . '/share?file=' . $url);
            $imagick->scaleImage(0, $size);
            Storage::disk('s3')->put($thumbnail, $imagick->getImageBlob());
            $temp_url = Storage::disk('s3')->temporaryUrl($thumbnail, now()->addHours(1));
        }
        return $temp_url;
    }

    public function ajax_view(Request $request)
    {
        $files_ids = $request->file_ids;
        $files = File::whereIn('id', $files_ids)->get();
        $response = ['files' => []];
        foreach ($files as $file) {
            $filename = explode('.', $file->name)[0];
            $pri_thumbnail = 'files/thumbnails/512x512/' . $file->company_id . '/' . $file->name;
            // if (! Storage::disk('s3')->exists($pri_thumbnail)) {
            $url = siteUrl() . '/share?file=' . $file->path;
            $imagick = new \Imagick($url);
            $imagick->scaleImage(0, 512);
            Storage::disk('s3')->put($pri_thumbnail, $imagick->getImageBlob());
            // }
            $response['files'][] = [
                'url' => $this->get_thumbnail_image($file, 512, '', $file->name),
                'name' => $file->name,
                'product_name'  => $file->product_name,
                'brand'         => $file->brand,
                'path'          => $file->path,
                'thumbnail'     => Storage::disk('s3')->temporaryUrl($file->thumbnail, now()->addHours(1)),
                'nf_thumbnail'  => $this->get_thumbnail_image($file, 128, 'Nutrition_Facts_Images/', $filename . ".jpg"),
                'ingredient_thumbnail'  => $this->get_thumbnail_image($file, 128, 'Ingredients_Images/', $filename . ".jpg"),
                'nf_thumbnail_large'  => $this->get_thumbnail_image($file, 512, 'Nutrition_Facts_Images/', $filename . ".jpg"),
                'ingredient_thumbnail_large'  => $this->get_thumbnail_image($file, 512, 'Ingredients_Images/', $filename . ".jpg"),
                'company_id'    => $file->company_id,
                'asin'    => $file->ASIN,
                'upc'    => $file->UPC,
                'width'    => $file->width,
                'height'    => $file->height,
                'depth'    => $file->depth,
            ];
        }

        return $response;
    }

    public function ajax_generate_thumbnail()
    {
        $count = $this->bannerService->generate_thumbnail();
        $message = $count . ' thumbnails are generated.';
        return redirect()->route('frontend.file.index')->withFlashSuccess($message);
    }

    public function ajax_re_generate_thumbnail()
    {
        $count = $this->bannerService->generate_thumbnail(true);
        $message = $count . ' thumbnails are regenerated.';
        return redirect()->route('frontend.file.index')->withFlashSuccess($message);
    }

    public function save_cropped_image(Request $request)
    {
        $croppedImage = $request->croppedImage;
        $croppedImage = str_replace('data:image/png;base64,', '', $croppedImage);
        $croppedImage = str_replace(' ', '+', $croppedImage);
        $data = base64_decode($croppedImage);

        $type = $request->type;
        $name = $request->name;
        $company_id = $request->company_id;
        $url = 'files/cropped/' . $company_id . '/' . $type . '/' . $name;
        if ($type == "") {
            $url = 'files/cropped/' . $company_id . '/' . $name;
        }
        Storage::disk('s3')->put($url, $data);

        $file = File::where('company_id', $company_id)->where('name', $name)->first();
        $file->path = $url;
        $file->is_cropped = 1;
        $file->save();

        // Update thumbnails
        $this->get_thumbnail_image($file, 128, '', $file->name);
        return $url;
    }

    public function restore_original_image(Request $request)
    {
        $type = $request->type;
        $name = $request->name;
        $company_id = $request->company_id;

        $file = File::where('company_id', $company_id)->where('name', $name)->first();
        $url = 'files/' . $company_id . '/' . $name;
        $file->path = $url;
        $file->is_cropped = 0;
        $file->save();

        // Update thumbnails
        $this->get_thumbnail_image($file, 128, '', $file->name);
        return $url;
    }

    private function get_files_list()
    {
        $list = [];
        $db_files = null;
        $user = auth()->user();
        if ($user->isMasterAdmin()) {
            $db_files = File::all();
        } else {
            $db_files = File::where('company_id', $user->company_id)->get();
        }
        foreach ($db_files as $file) {
            $list[] = [
                'id'            => $file->id,
                'name'          => $file->name,
                'product_name'  => $file->product_name,
                'brand'         => $file->brand,
                'thumbnail'     => $file->thumbnail,
                'path'          => $file->path,
                'company_id'    => $file->company_id,
            ];
        }

        $result = [];
        $changes = $this->alt_get_files_list();

        foreach ($list as $item) {
            $status = 'indexed';
            if (in_array($item['name'], $changes[$item['company_id']]['removed'])) {
                $status = 'deleted';
                $item['id'] = 0;
            }
            $item['status'] = $status;
            $result[] = $item;
        }

        foreach ($changes as $company_id => $change) {
            foreach ($change['added'] as $item) {
                $result[] = array(
                    'id'        => 0,
                    'name'      => array_reverse(explode('/', $item))[0],
                    'product_name' => '',
                    'brand' => '',
                    'thumbnail' => '',
                    'path'      => $item,
                    'company_id' => $company_id,
                    'status'    => 'new',
                );
            }
        }
        return $result;
    }

    private function alt_get_files_list()
    {
        $user = auth()->user();
        $company_ids = [$user->company_id];
        $list = [];
        if ($user->isMasterAdmin()) {
            $company_ids = Company::all()->pluck('id')->toArray();
        }

        foreach ($company_ids as $company_id) {
            $cropped = array();
            $s3_files = Storage::disk('s3')->files('files/' . $company_id);
            $db_files = File::where('company_id', $company_id)->pluck('path')->toArray();
            $cropped_files = File::where('is_cropped', 1)->get();
            foreach ($cropped_files as $file) {
                $cropped[] = 'files/' . $company_id . '/' . $file->name;
            }
            $added = array_diff($db_files, $s3_files, $cropped); //added
            $removed = array_diff($s3_files, $db_files, $cropped); //removed
            $list[$company_id] = [
                'removed' => $added,
                'added' => $removed
            ];
        }

        return $list;
    }

    private function get_pagination_files_list($search_key_list, $page, $page_per_count)
    {
        $list = $this->get_files_list();
        if (count($search_key_list) && $search_key_list[0] != '') {
            $list = array_filter($list, function ($item) use ($search_key_list) {
                foreach ($search_key_list as $search_key) {
                    if ((strripos($item['name'], $search_key) !== false) ||
                        (strripos($item['product_name'], $search_key) !== false) ||
                        (strripos($item['brand'], $search_key) !== false)
                    ) {
                        return true;
                    }
                }
                return false;
            });
        }
        $left_count = count($list) - $page * $page_per_count;
        $count = $left_count > $page_per_count ? $page_per_count : $left_count;
        return [
            'items' => array_slice($list, ($page - 1) * $page_per_count, $count),
            'totalCount' => count($list),
            'current_page' => $page + 1,
            'from' => (($page * $page_per_count) + 1),
            'last_page' => ceil( count($list) / $page_per_count)
        ];
    }

    public function ajax_background_list(Request $request)
    {
        $search_key = preg_replace('!\s+!', ',', urldecode($request->searchKey));
        $search_key_list = explode(",", $search_key);
        $result = $this->get_pagination_background_list($search_key_list, $request->pageNumber - 1, $request->pageSize);
        for ($i = 0; $i < count($result['items']); $i++) {
            $item = $result['items'][$i];

            if ($item['thumbnail'] != '') {
                if (!Storage::disk('s3')->exists($item['thumbnail'])) {
                    $url = siteUrl() . '/share?file=' . $item['path'];
                    $imagick = new \Imagick($url);
                    $imagick->scaleImage(512, 512, true);
                    Storage::disk('s3')->put($item['thumbnail'], $imagick->getImageBlob());
                }

                $item['url'] = siteUrl() . '/share?file=' . $item['thumbnail'];
            } else {
                $item['url'] = siteUrl() . '/share?file=' . $item['path'];
            }
            $result['items'][$i] = $item;
        }
        return response()->json($result);
    }

    private function get_pagination_background_list($search_key_list, $page, $page_per_count)
    {
        $list = $this->get_background_list();
        if (count($search_key_list) && $search_key_list[0] != '') {
            $list = array_filter($list, function ($item) use ($search_key_list) {
                foreach ($search_key_list as $search_key) {
                    if (strripos($item['name'], $search_key) !== false) {
                        return true;
                    }
                }
                return false;
            });
        }
        $left_count = count($list) - $page * $page_per_count;
        $count = $left_count > $page_per_count ? $page_per_count : $left_count;
        return [
            'items' => array_slice($list, ($page - 1) * $page_per_count, $count),
            'totalCount' => count($list),
            'current_page' => $page + 1,
            'from' => (($page * $page_per_count) + 1),
            'last_page' => ceil( count($list) / $page_per_count)
        ];
    }

    private function get_background_list()
    {
        $bk_images = Storage::disk('s3')->allFiles('files/background/' . auth()->user()->company_id);
        if (!auth()->user()->isMasterAdmin()) {
            $master_bk_images = Storage::disk('s3')->allFiles('files/background/0');
            $bk_images = array_merge($master_bk_images, $bk_images);
        }
        $result =  [];
        foreach ($bk_images as $image) {
            $arr = array_reverse(explode('/', $image));
            $filename = $arr[0];
            $file = array(
                'name' => $filename,
                'path' => $image,
                'product_name' => $arr[2],
                'thumbnail' => 'files/thumbnails/512x512/background/' . explode('/background/', $image)[1]
            );
            $result[] = $file;
        }
        return $result;
    }
}
