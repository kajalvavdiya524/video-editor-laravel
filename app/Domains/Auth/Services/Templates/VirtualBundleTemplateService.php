<?php

namespace App\Domains\Auth\Services\Templates;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use App\Domains\Auth\Services\BannerService;
use App\Domains\Auth\Models\Setting;
use Exception;
use ZipArchive;

/**
 * Class VirtualBundleTemplateService.
 */
class VirtualBundleTemplateService extends BaseService
{
    protected $bannerService;

    /**
     * VirtualBundleTemplateService constructor.
     *
     */
    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    private function get_config($request)
    {
        $config = array();
        $template = $request->output_dimensions;
        $config["customer"] = $request->customer;
        $config["template"] = $template;
        $config["width"] = config("templates.Amazon.width")[$template];
        $config["height"] = config("templates.Amazon.height")[$template];
        $config["product_dimensions"] = config("templates.Amazon.product_dimensions")[$template];
        $config["product_layouts"] = $request->product_layouts;
        $config["background_color"] = $request->background_color;
        $config["drop_shadow"] = "none";
        $config["image_shadow"] = null;
        $config["fade"] = null;
        return $config;
    }

    private function get_output_filename($request)
    {
        $output_filename = $request->output_filename;
        $project_name = $request->project_name;
        $customer = $request->customer;
        
        $filename = !empty($output_filename) ? $output_filename : (!empty($project_name) ? $project_name : "output");
        return $filename;
    }


    private function get_psd($files, $product_filenames, $config)
    {
        $json = array(
            "width" => $config["width"],
            "height" => $config["height"],
            "layers" => array()
        );

        $geometry = array();
        if ($config["product_layouts"] == 0) {
            $geometry["width"] = $json["width"] * 0.7;
            $geometry["height"] = $json["height"] * 0.7;
        } else if ($config["product_layouts"] == 1) {
            $geometry["width"] = $json["width"] * 0.9;
            $geometry["height"] = $json["height"] * 0.53;
        } else if ($config["product_layouts"] == 2) {
            $geometry["width"] = $json["width"];
            $geometry["height"] = $json["height"] * 0.53;
        } 

        /* Product */
        $products = array();
        for ($i = 0; $i < 2; $i++) {
            $products[] = new \Imagick($product_filenames[$i]);
            $w = $geometry["width"];
            $h = $geometry["height"];
            $products[$i] ->scaleImage($w, $h, true);
        }

        $x = array();
        $y = array();
        if ($config["product_layouts"] == 0) {
            $x = [0, $json["width"] - $products[1]->getImageWidth()];
            $y = [0, $json["height"] - $products[1]->getImageHeight()];
        } else if ($config["product_layouts"] == 1) {
            $x = [0, $json["width"] - $products[1]->getImageWidth()];
            $y = [0, $json["height"] - $products[1]->getImageHeight()];
        } else if ($config["product_layouts"] == 2) {
            $x = [0, 0];
            $y = [0, $json["height"] - $products[1]->getImageHeight()];
        } 

        $product_layers = array();
        for ($i = 0; $i < 2; $i++) {
            $w = $products[$i]->getImageWidth();
            $h = $products[$i]->getImageHeight();
            $json["layers"][] = $this->bannerService->get_smartobject_layer($files[$i]["name"], $product_filenames[$i], $x[$i], $y[$i], $w, $h, 0, null, $config["image_shadow"], $config["fade"], 0);
        }
        
        /* Background */
        array_unshift($json["layers"], $this->bannerService->get_color_layer("Background", 0, 0, $config["width"], $json["height"], $config["background_color"]));

        return base64_encode(json_encode($json));
    }

    private function get_history_settings($request)
    {
        $settings = array(
            "customer" => $request->customer,
            "output_dimensions" => $request->output_dimensions,
            "project_name" => $request->project_name,
            "file_ids" => $request->file_ids,
            "product_layouts" => $request->product_layouts,
            "background_color" => $request->background_color,
            "type" => $request->type
        );

        return json_encode($settings);
    }

    public function run($request, $preview = false, $save = false, $publish = false)
    {
        // Map files from UTC/GTIN/ASIN/TCIN/WMT-ID
        
        $result = $this->bannerService->map_files(explode(" ", $request->file_ids));
        if ($result["status"] == "error") {
            return $result;
        }

        // Get trimmed product image files
        $temp_files = array();
        $product_filenames = array();
        foreach ($result["files"] as $file) {
            $filename = uniqid().".png";
            $contents = Storage::disk('s3')->get($file["path"]);
            Storage::disk('public')->put($filename, $contents);
            $product_filenames[] = $filename;
            $temp_files[] = $filename;
        }

        $response = [];
        $jpeg_file = "";

        if ($preview) {
            $response = ["files" => [], "log" => []];
            $config = $this->get_config($request);
            $jpeg_file_id = uniqid();
            $jpeg_file = $jpeg_file_id.".jpg";

            $input_arg = $this->get_psd($result["files"], $product_filenames, $config);
            $pil_font = Setting::where('key', 'pil_font')->first()->value;
            if ($pil_font == "on") {
                $command = escapeshellcmd("python3 /var/www/psd2/tool.py --pil-font -j ".$input_arg." -of result -p ".$jpeg_file_id);
            } else { 
                $command = escapeshellcmd("python3 /var/www/psd2/tool.py -j ".$input_arg." -of result -p ".$jpeg_file_id);
            }

            $log = shell_exec($command." 2>&1");
            $temp_files = array_merge($temp_files, array("tmp_result.psd", "shadow.png", "circle.png", "result.zip"));

            $response['files'][] = $jpeg_file;
            $response['log'][] = $log;
        } else {
            if ($request->type == 1) {
                $this->projectService->store([
                    'name' => $request->project_name,
                    'customer' => $request->customer,
                    'output_dimensions' => $request->output_dimensions,
                    'projectname' => $request->project_name,
                    'url' => '',
                    'fileid' => $request->file_ids,
                    'headline' => implode(" ", $request->headline),
                    'size' => $request->customer == "amazon_fresh" ? config("templates.AmazonFresh.output_dimensions")[$request->output_dimensions] : config("templates.Generic.output_dimensions")[$request->output_dimensions + 1],
                    'settings' => $this->get_history_settings($request),
                    'jpg_files' => '',
                    'type' => $request->type,
                    'parent_id' => isset($request->parent_id) ? $request->parent_id : 0
                ]);

                $response = [
                    "projectname" => $request->project_name,
                ];
            } else {
                $zip_file_id = uniqid();
                $zip_file = $zip_file_id.".zip";
                $zip_filename = (!empty($request->output_filename) ? $request->output_filename : (!empty($request->project_name) ? $request->project_name : "output"));
                $zip = new ZipArchive();
                $log = "";
                if ($zip->open(public_path($zip_file), ZipArchive::CREATE) === TRUE) {
                    $output_jpg_files = array();
                    $output_psd_files = array();
                    $config = $this->get_config($request);
                    $output_filename = $this->get_output_filename($request);

                    $psd_file_id = uniqid();
                    $psd_file = $psd_file_id.".psd";
                    $jpeg_file_id = uniqid();
                    $jpeg_file = $jpeg_file_id.".jpg";

                    $input_arg = $this->get_psd($result["files"], $product_filenames, $config);
                    $pil_font = Setting::where('key', 'pil_font')->first()->value;
                    if ($pil_font == "on") {
                        $command = escapeshellcmd("python3 /var/www/psd2/tool.py --pil-font -j ".$input_arg." -of ".$zip_file_id." -o ".$psd_file_id." -p ".$jpeg_file_id);
                    } else {
                        $command = escapeshellcmd("python3 /var/www/psd2/tool.py -j ".$input_arg." -of ".$zip_file_id." -o ".$psd_file_id." -p ".$jpeg_file_id);
                    }
                    $log = shell_exec($command." 2>&1");
                    $temp_files = array_merge($temp_files, array("tmp_result.psd", "shadow.png", "circle.png"));
                    $temp_files[] = $psd_file;
                    $temp_files[] = $jpeg_file;

                    $zip->addFile($jpeg_file, $output_filename.".jpg");
                    $output_jpg_files[] = $jpeg_file;
                    if ($request->include_psd) {
                        $zip->addFile($psd_file, $output_filename.".psd");
                        $output_psd_files[] = $psd_file;
                    }
                    $zip->close();

                    Storage::disk('s3')->put('outputs/'.$zip_file, file_get_contents(public_path($zip_file)));
                    $temp_files[] = $zip_file;

                    foreach ($output_jpg_files as $filename) {
                        if (file_exists($filename)) {
                            Storage::disk('s3')->put('outputs/jpg/'.$filename, file_get_contents(public_path($filename)));
                        }
                    };

                    foreach ($output_psd_files as $filename) {
                        if (file_exists($filename)) {
                            Storage::disk('s3')->put('outputs/psd/' . $filename, file_get_contents(public_path($filename)));
                        }
                    };

                    if ($save) {
                        $this->bannerService->save_draft([
                            'name' => $zip_filename,
                            'customer' => $request->customer,
                            'output_dimensions' => $request->output_dimensions,
                            'projectname' => $request->project_name,
                            'url' => 'outputs/'.$zip_file,
                            'fileid' => $request->file_ids,
                            'headline' => "VirtualBundle",
                            'size' => config("templates.Amazon.output_dimensions")[$request->output_dimensions],
                            'settings' => $this->get_history_settings($request),
                            'jpg_files' => implode(" ", $output_jpg_files),
                            'type' => $request->type,
                            'parent_id' => isset($request->parent_id) ? $request->parent_id : 0
                        ]);
                    }

                    if ($publish) {
                        $this->bannerService->save_project([
                            'name' => $zip_filename,
                            'customer' => $request->customer,
                            'output_dimensions' => $request->output_dimensions,
                            'projectname' => $request->project_name,
                            'url' => 'outputs/'.$zip_file,
                            'fileid' => $request->file_ids,
                            'headline' => "VirtualBundle",
                            'size' => config("templates.Amazon.output_dimensions")[$request->output_dimensions],
                            'settings' => $this->get_history_settings($request),
                            'jpg_files' => implode(" ", $output_jpg_files),
                            'type' => $request->type,
                            'parent_id' => isset($request->parent_id) ? $request->parent_id : 0
                        ]);
                    }
                }
        
                $response = [
                    "url" => Storage::disk('s3')->temporaryUrl('outputs/'.$zip_file, now()->addHours(1), [
                        'ResponseContentDisposition' => 'attachment; filename="'.$zip_filename.'.zip"'
                    ]),
                    "projectname" => $request->project_name,
                    "log" => $log
                ];
            }
        }

        if ($result["status"] == "warning") {
            $response["status"] = "warning";
            $response["messages"] = $result["messages"];
        } else if ($result["status"] == "success") {
            $response["status"] = "success";
        }

        if (!file_exists($jpeg_file)) {
            $msg = 'The system encountered an error generating the output. Support has been notified and will investigate.';
            $response["status"] = "error";
            $response["messages"][] = $msg;
            $this->bannerService->save_exception(['file_id' => $request->file_ids, 'message' => $msg]);
        }

        foreach ($temp_files as $filename) {
            if (file_exists($filename)) {
                unlink($filename);
            }
        }

        return $response;
    }

}
