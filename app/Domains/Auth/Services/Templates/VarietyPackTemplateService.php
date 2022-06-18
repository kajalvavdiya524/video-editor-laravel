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
 * Class VarietyPackTemplateService.
 */
class VarietyPackTemplateService extends BaseService
{
    protected $bannerService;

    /**
     * VarietyPackTemplateService constructor.
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
        $config["product_layering"] = $request->product_layering;
        $config["product_spacing"] = $request->product_spacing;
        $config["background_color"] = $request->background_color;
        $config["drop_shadow"] = "none";
        $config["image_shadow"] = null;
        $config["fade"] = null;
        // $config["compress_size"] = 120;
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

    private function get_layer_json($files, $product_filenames, $config, $max_mapping_width, $isTop = false)
    {
        $layers = array();

        $count = count($files);
        $products = array();
        $total_mapping_width = $max_mapping_width;
        $max_height = 0;
        for ($i = 0; $i < $count; $i++) {
            $products[] = new \Imagick($product_filenames[$i]);
        }
        if ($total_mapping_width < 10) {
            $total_mapping_width = 10; 
        }

        for ($i = 0; $i < $count; $i++) {
            $product_width = $config["product_dimensions"]["width"] * $files[$i]['width'] / $total_mapping_width;
            $products[$i]->thumbnailImage($product_width, null);
            $h = $products[$i]->getImageHeight();
            $max_height = max($max_height, $h);
        }

        $product_layer_orders = array();
        if ($config["product_layering"] == "front-to-back") {
            $product_layer_orders = range(0, $count - 1);
            $product_layer_orders = array_reverse($product_layer_orders);
        } else if ($config["product_layering"] == "middle-in-front") {
            if ($count % 2) {
                $product_layer_orders[] = 0;
                for ($i = 1; $i <= ($count - 1) / 2; $i++) {
                    $product_layer_orders[] = $i * 2;
                    $product_layer_orders[] = $i * 2 - 1;
                }
            } else {
                for ($i = 0; $i < $count / 2; $i++) {
                    $product_layer_orders[] = $i;
                    $product_layer_orders[] = $count - $i - 1;
                }
            }
        } else if ($config["product_layering"] == "back-to-front") {
            $product_layer_orders = range(0, $count - 1);
        }

        $product_layers = array();
        $angleList = array();
        for ($i = 0; $i < $count; $i ++) {
            if ($count % 2) {
                if ($i == 0) {
                    $angleList[] = 0;
                } else {
                    $angleList[] = 20 * pow(0.5, ceil($i / 2)) * pow(-1, $i);
                }
            } else {
                $idx = $i + 1;
                $angleList[] = 20 * pow(0.5, ceil($idx / 2)) * pow(-1, $idx);
            }
        }
        sort($angleList);

        $r = $config["product_dimensions"]["height"] < $max_height ? $config["product_dimensions"]["height"] / $max_height : 1;
        $max_height = $max_height * $r;
        $total_width = 0;
        for ($i = 0; $i < $count; $i++) {
            $w = $products[$i]->getImageWidth() * $r;
            $h = $products[$i]->getImageHeight() * $r;
            if ($r < 1) {
                $products[$i]->scaleImage($w, $h, true);
            }
            $temp_product = clone $products[$i];
            $temp_product->rotateImage(new \ImagickPixel('none'), $angleList[$i]);
            $w = $temp_product->getImageWidth();
            if ($i == $count - 1) {
                $total_width += $w;
            } else {
                $total_width += $w + $config["product_spacing"];
            }
        }

        $r = $config["product_dimensions"]["width"] < $total_width ? $config["product_dimensions"]["width"] / $total_width : 1;
        $total_width *= $r;
        $x = 50 + ($config["width"] - $total_width) / 2;
        for ($i = 0; $i < $count; $i++) {
            if ($r < 1) {
                $w = $products[$i]->getImageWidth() * $r;
                $h = $products[$i]->getImageHeight() * $r;
                $products[$i]->scaleImage($w, $h, true);
            }
            $w = $products[$i]->getImageWidth();
            $h = $products[$i]->getImageHeight();

            $y = $config["product_dimensions"]["baseline"] + 600 - $h;
            if ($isTop) {
                $y = $config["product_dimensions"]["baseline"] + 150 - $h;
            }
            $product_layers[] = $this->bannerService->get_smartobject_layer($files[$i]["name"], $product_filenames[$i], $x, $y, $w, $h, $angleList[$i]);
            
            $products[$i]->rotateImage(new \ImagickPixel('none'), $angleList[$i]);
            $w = $products[$i]->getImageWidth();
            $x = $x + $w + $config["product_spacing"];
        }

        for ($i = 0; $i < $count; $i++) {
            $j = $product_layer_orders[$i];
            $layers[] = $product_layers[$j];
        }

        // return base64_encode(json_encode($json));
        return $layers;
    }

    private function get_psd($files, $files_top, $product_filenames, $product_top_filenames, $config)
    {
        $json = array(
            "width" => $config["width"],
            "height" => $config["height"],
            "layers" => array()
        );
        $layers_top = array();
        $total_mapping_width = 0;
        for ($i = 0; $i < count($files); $i++) {
            $total_mapping_width += $files[$i]['width'];
        }

        if ($config["template"] == 1 && $files_top) {
            $total_mapping_top_width = 0;
            for ($i = 0; $i < count($files_top); $i++) {
                $total_mapping_top_width += $files_top[$i]['width'];
            }
            $total_mapping_width = $total_mapping_width > $total_mapping_top_width ? $total_mapping_width : $total_mapping_top_width;
        }

        $box_front = null;
        $box_back = null;
        $box_front = new \Imagick("box_front.png");
        $box_back = new \Imagick("box_back.png");
        $box_front->thumbnailImage($config["width"], null);
        $box_back->thumbnailImage($config["width"], null);
        
        $json["layers"][] = $this->bannerService->get_smartobject_layer("box-back", "box_back.png", 0, $config["product_dimensions"]["baseline"], $box_back->getImageWidth(), $box_back->getImageHeight());

        $layers = $this->get_layer_json($files, $product_filenames, $config, $total_mapping_width);
        if ($config["template"] == 1 && $files_top) {
            $layers_top = $this->get_layer_json($files_top, $product_top_filenames, $config, $total_mapping_width, true);
            $json["layers"] = array_merge($json["layers"], $layers_top);
        }
        $json["layers"] = array_merge($json["layers"], $layers);

        // box
        $json["layers"][] = $this->bannerService->get_smartobject_layer("box-front", "box_front.png", 0, $config["product_dimensions"]["baseline"] + 308, $box_front->getImageWidth(), $box_front->getImageHeight());
        
        /* Background */
        array_unshift($json["layers"], $this->bannerService->get_pixel_layer("Background", 0, 0, $config["width"], $json["height"], $config["background_color"]));

        return base64_encode(json_encode($json));
    }

    private function get_history_settings($request)
    {
        $settings = array(
            "customer" => $request->customer,
            "output_dimensions" => $request->output_dimensions,
            "project_name" => $request->project_name,
            "file_top_ids" => $request->file_top_ids,
            "file_ids" => $request->file_ids,
            "product_layouts" => $request->product_layouts,
            "product_layering" => $request->product_layering,
            "product_spacing" => $request->product_spacing,
            "background_color" => $request->background_color,
            "type" => $request->type
        );

        return json_encode($settings);
    }

    public function run($request, $preview = false, $save = false, $publish = false)
    {
        // Map files from UTC/GTIN/ASIN/TCIN/WMT-ID
        $result_top =  null;
        if ($request->output_dimensions == 1) {
            $result_top = $this->bannerService->map_files(explode(" ", $request->file_top_ids));
            if ($result_top["status"] == "error") {
                return $result_top;
            }
        }
        
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

        $product_top_filenames = array();
        if ($request->output_dimensions == 1) {
            foreach ($result_top["files"] as $file) {
                $filename = uniqid().".png";
                $contents = Storage::disk('s3')->get($file["path"]);
                Storage::disk('public')->put($filename, $contents);
                $product_top_filenames[] = $filename;
                $temp_files[] = $filename;
            }
        }

        // box front
        $box_front_contents = Storage::disk('s3')->get("files/common/box-front.png");
        Storage::disk('public')->put("box_front.png", $box_front_contents);
        $temp_files[] = "box_front.png";
        
        // box back
        $box_back_contents = Storage::disk('s3')->get("files/common/box-back.png");
        Storage::disk('public')->put("box_back.png", $box_back_contents);
        $temp_files[] = "box_back.png";

        $response = [];
        $jpeg_file = "";

        if ($preview) {
            $response = ["files" => [], "log" => []];
            $config = $this->get_config($request);
            $jpeg_file_id = uniqid();
            $jpeg_file = $jpeg_file_id.".jpg";

            $input_arg = null;
            if ($request->output_dimensions == 1) {
                $input_arg = $this->get_psd($result["files"], $result_top["files"], $product_filenames, $product_top_filenames, $config);
            } else {
                $input_arg = $this->get_psd($result["files"], null, $product_filenames, null, $config);
            }
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

                    $input_arg = null;
                    if ($request->output_dimensions == 1) {
                        $input_arg = $this->get_psd($result["files"], $result_top["files"], $product_filenames, $product_top_filenames, $config);
                    } else {
                        $input_arg = $this->get_psd($result["files"], null, $product_filenames, null, $config);
                    }
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
                            'headline' => "VarietyPack",
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
                            'headline' => "VarietyPack",
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
