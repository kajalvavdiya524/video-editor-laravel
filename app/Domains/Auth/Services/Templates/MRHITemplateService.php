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
 * Class MRHITemplateService.
 */
class MRHITemplateService extends BaseService
{
    protected $bannerService;

    /**
     * MRHITemplateService constructor.
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
        $config["width"] = config("templates.MRHI.width")[$template];
        $config["height"] = config("templates.MRHI.height")[$template];
        $config["product_dimensions"] = config("templates.MRHI.product_dimensions")[$template];
        $config["product_format"] = $request->product_format;
        $config["product_format_size"] = $request->product_size;
        $config["product_format_text_color"] = $request->product_format_text_color;
        $config["product_format_bk_color"] = $request->product_format_bk_color;
        $config["sub_text"] = $request->sub_text;
        $config["sub_text_size"] = $request->sub_text_size;
        $config["sub_text_color"] = $request->sub_text_color;
        $config["quantity"] = $request->quantity;
        $config["quantity_size"] = $request->quantity_size;
        $config["quantity_text_color"] = $request->quantity_text_color;
        $config["quantity_bk_color"] = $request->quantity_bk_color;
        $config["unit"] = $request->unit;
        $config["unit_size"] = $request->unit_size;
        $config["unit_text_color"] = $request->unit_text_color;
        $config["background_color"] = $request->background_color;
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

        $width_stripe = 0;
        $height_stripe = 0;
        $padding = 0;
        $metrics = $this->bannerService->get_font_metrics(empty($config["product_format"]) ? " " : $config["product_format"], "Amazon-Ember", $config["product_format_size"]);
        $metrics_sub = $this->bannerService->get_font_metrics(empty($config["sub_text"]) ? " " : $config["sub_text"], "Amazon-Ember", $config["sub_text_size"]);
        $metrics_qua = $this->bannerService->get_font_metrics(empty($config["quantity"]) ? " " : $config["quantity"], "Amazon-Ember", $config["quantity_size"]);
        $metrics_unit = $this->bannerService->get_font_metrics(empty($config["unit"]) ? " " : $config["unit"], "Amazon-Ember", $config["unit_size"]);
            
        if ($config['template'] == 0 || $config['template'] == 4) {
            if ($config['template'] == 0) {
                $left_stripe = 2300;
                $width_stripe = 700;
                $padding = (230 - $config["product_format_size"] / 2) - $config["product_format_size"] / 7;
            } else if ($config['template'] == 4) {
                $left_stripe = 1150;
                $width_stripe = 350;
                $padding = (115 - $config["product_format_size"] / 2) - $config["product_format_size"] / 7;
            }

            /* Quantity & Unit */
            $h_qua = $metrics_qua['textHeight'] + $metrics_unit['textHeight'];
            $y_qua = $config["product_dimensions"]["height"] - $h_qua;
            $json["layers"][] = $this->bannerService->get_pixel_layer("product_qua_background", $left_stripe, $y_qua, $width_stripe, $h_qua, $config["quantity_bk_color"], 0, null, 50, [1, 0, 0, 0]);
            if (!empty($config["quantity"])) {
                $json["layers"][] = $this->bannerService->get_text_layer("quantity", $left_stripe, $y_qua, $config["product_dimensions"]["width"] - $left_stripe, $metrics_qua["textHeight"], $config["quantity"], 'OpenSans-Bold', $config["quantity_size"], $config["quantity_text_color"], "center", 0, "horizontal");
            }
            if (!empty($config["unit"])) {
                $json["layers"][] = $this->bannerService->get_text_layer("unit", $left_stripe, $y_qua + $metrics_qua["textHeight"] - $config["unit_size"] / 5, $config["product_dimensions"]["width"] - $left_stripe, $config["product_dimensions"]["height"], $config["unit"], 'OpenSans-Bold', $config["unit_size"], $config["unit_text_color"], "center", 0, "horizontal");
            }

            /* Product Format */
            $h_format = $config["product_dimensions"]["height"] - $h_qua - 50;
            $y_format = ($h_format - $metrics["textWidth"]) / 2;
            $json["layers"][] = $this->bannerService->get_pixel_layer("product_format_background", $left_stripe, 0, $width_stripe, $h_format, $config["product_format_bk_color"], 0, null, 50, [0, 1, 0, 0]);
            
            if (!empty($config["product_format"])) {
                if (!empty($config["sub_text"])) {
                    $json["layers"][] = $this->bannerService->get_text_layer("product_format", $left_stripe + $padding, $y_format, $config["product_dimensions"]["width"], $metrics["textWidth"], $config["product_format"], 'OpenSans-Bold', $config["product_format_size"], $config["product_format_text_color"], "left", 0, "vertical");
                    $json["layers"][] = $this->bannerService->get_text_layer("sub_text", $left_stripe + $padding + $metrics['textHeight'], ($h_format - $metrics_sub["textWidth"]) / 2, $config["product_dimensions"]["width"], $metrics_sub["textWidth"], $config["sub_text"], 'OpenSans-Bold', $config["sub_text_size"], $config["sub_text_color"], "left", 0, "vertical");
                } else {
                    $json["layers"][] = $this->bannerService->get_text_layer("product_format", $left_stripe + $padding + ($width_stripe - $metrics['textHeight']) / 4, $y_format, $config["product_dimensions"]["width"], $metrics["textWidth"], $config["product_format"], 'OpenSans-Bold', $config["product_format_size"], $config["product_format_text_color"], "left", 0, "vertical"); 
                }
            }
        } else if($config['template'] == 1 || $config['template'] == 5) {
            if ($config['template'] == 1) {
                $left_stripe = 2300;
                $width_stripe = 700;
                $h_qua = 700;
                $padding = (230 - $config["product_format_size"] / 2) - $config["product_format_size"] / 7;
            } else if ($config['template'] == 5) {
                $left_stripe = 1150;
                $width_stripe = 350;
                $h_qua = 350;
                $padding = (115 - $config["product_format_size"] / 2) - $config["product_format_size"] / 7;
            }

            /* Quantity & Unit */
            // $h_qua = $metrics_qua['textHeight'] + $metrics_unit['textHeight'];
            // $y_qua = $config["product_dimensions"]["height"] - $h_qua;
            $y_qua = 0;
            $json["layers"][] = $this->bannerService->get_pixel_layer("product_qua_background", $left_stripe, $y_qua, $width_stripe, $h_qua, $config["quantity_bk_color"], 0, null, 50, [0, 1, 0, 0]);
            if (!empty($config["quantity"])) {
                $json["layers"][] = $this->bannerService->get_text_layer("quantity", $left_stripe + $padding, $config["quantity_size"] / 3 + ($h_qua - $metrics_qua['textWidth']) / 2, $left_stripe + $padding + $metrics_qua["textHeight"], $metrics_qua["textWidth"], $config["quantity"], 'OpenSans-Bold', $config["quantity_size"], $config["quantity_text_color"], "left", 0, "vertical");
            }
            if (!empty($config["unit"])) {
                $json["layers"][] = $this->bannerService->get_text_layer("unit", $left_stripe + $padding + $metrics_qua['textHeight'], $config["unit_size"] / 4 + ($h_qua - $metrics_unit['textWidth']) / 2, $config["product_dimensions"]["width"], $metrics_unit["textWidth"], $config["unit"], 'OpenSans-Bold', $config["unit_size"], $config["unit_text_color"], "left", 0, "vertical");
            }

            /* Product Format */
            $h_format = $config["product_dimensions"]["height"] - $h_qua - 50;
            $y_format = $h_qua + 50 + ($h_format - $metrics["textWidth"]) / 2 + $config["product_format_size"] / 7;
            $json["layers"][] = $this->bannerService->get_pixel_layer("product_format_background", $left_stripe, $h_qua + 50, $width_stripe, $h_format, $config["product_format_bk_color"], 0, null, 50, [1, 0, 0, 0]);
            
            if (!empty($config["product_format"])) {
                if (!empty($config["sub_text"])) {
                    $json["layers"][] = $this->bannerService->get_text_layer("product_format", $left_stripe + $padding, $y_format, $config["product_dimensions"]["width"], $metrics["textWidth"], $config["product_format"], 'OpenSans-Bold', $config["product_format_size"], $config["product_format_text_color"], "left", 0, "vertical");
                    $json["layers"][] = $this->bannerService->get_text_layer("sub_text", $left_stripe + $padding + $metrics['textHeight'], $h_qua + 50 + ($h_format - $metrics_sub["textWidth"]) / 2, $config["product_dimensions"]["width"], $metrics_sub["textWidth"], $config["sub_text"], 'OpenSans-Bold', $config["sub_text_size"], $config["sub_text_color"], "left", 0, "vertical");
                } else {
                    $json["layers"][] = $this->bannerService->get_text_layer("product_format", $left_stripe + $padding + ($width_stripe - $metrics['textHeight']) / 4, $y_format, $config["product_dimensions"]["width"], $metrics["textWidth"], $config["product_format"], 'OpenSans-Bold', $config["product_format_size"], $config["product_format_text_color"], "left", 0, "vertical"); 
                }
            }
        } else if ($config['template'] == 2 || $config['template'] == 6) {
            if ($config['template'] == 2) {
                $left_stripe = 2300;
                $y_stripe = 2300;
                $height_stripe = 700;
                $padding = (230 - $config["product_format_size"] / 2) - $config["product_format_size"] / 7;
            } else if ($config['template'] == 6) {
                $left_stripe = 1150;
                $y_stripe = 1150;
                $height_stripe = 350;
                $padding = (115 - $config["product_format_size"] / 2) - $config["product_format_size"] / 7;
            }

            /* Quantity & Unit */
            $h_qua = $height_stripe;
            $y_qua = $config["product_dimensions"]["height"] - $h_qua;
            $offset_y = ($height_stripe - $metrics_qua["textHeight"] - $metrics_unit["textHeight"]) / 2;
            $json["layers"][] = $this->bannerService->get_pixel_layer("product_qua_background", $left_stripe, $y_qua, $height_stripe, $h_qua, $config["quantity_bk_color"], 0, null, 50, [1, 0, 0, 0]);
            if (!empty($config["quantity"])) {
                $json["layers"][] = $this->bannerService->get_text_layer("quantity", $left_stripe, $y_qua + $offset_y, $config["product_dimensions"]["width"] - $left_stripe, $y_qua + $metrics_qua["textHeight"], $config["quantity"], 'OpenSans-Bold', $config["quantity_size"], $config["quantity_text_color"], "center", 0, "horizontal");
            }
            if (!empty($config["unit"])) {
                $json["layers"][] = $this->bannerService->get_text_layer("unit", $left_stripe, $y_qua + $offset_y + $metrics_qua["textHeight"] - $config["unit_size"] / 5, $config["product_dimensions"]["width"] - $left_stripe, $config["product_dimensions"]["height"], $config["unit"], 'OpenSans-Bold', $config["unit_size"], $config["unit_text_color"], "center", 0, "horizontal");
            }

            /* Product Format */
            $json["layers"][] = $this->bannerService->get_pixel_layer("product_format_background", 0, $y_stripe, $config["product_dimensions"]["width"] - $height_stripe - 50, $height_stripe, $config["product_format_bk_color"], 0, null, 50, [0, 0, 0, 1]);
            $offset_pro_y = ($height_stripe - $metrics["textHeight"]) / 2;
            if (!empty($config["product_format"])) {
                if (!empty($config["sub_text"])) {
                    $offset_pro_y = ($height_stripe - $metrics["textHeight"] - $metrics_sub["textHeight"]) / 2;
                    $json["layers"][] = $this->bannerService->get_text_layer("product_format", 0, $y_qua + $offset_pro_y - $metrics["textHeight"] / 7, $left_stripe - 50, $height_stripe, $config["product_format"], 'OpenSans-Bold', $config["product_format_size"], $config["product_format_text_color"], "center", 0, "horizontal"); 
                    $json["layers"][] = $this->bannerService->get_text_layer("sub_text", 0, $y_qua + $metrics["textHeight"] + $offset_pro_y - $metrics["textHeight"] / 7, $left_stripe - 50, $height_stripe, $config["sub_text"], 'OpenSans-Bold', $config["sub_text_size"], $config["sub_text_color"], "center", 0, "horizontal");
                } else {
                    $json["layers"][] = $this->bannerService->get_text_layer("product_format", 0, $y_qua + $offset_pro_y - $metrics["textHeight"] / 7, $left_stripe - 50, $height_stripe, $config["product_format"], 'OpenSans-Bold', $config["product_format_size"], $config["product_format_text_color"], "center", 0, "horizontal"); 
                }
            } 
        }

        if (count($files)) {
            $file = $files[0];
            $product_filename = $product_filenames[0];

            $product = new \Imagick($product_filename);
            $product_width = $config["product_dimensions"]["width"] - $width_stripe;
            $product->thumbnailImage($product_width * 0.9, null);
            $w = $product->getImageWidth();
            $h = $product->getImageHeight();

            $product_height = ($config["product_dimensions"]["height"] - $height_stripe) - 50;
            $r = $product_height < $h ? $product_height / $h : 1;
            $w = $w * $r;
            $h = $h * $r;
            $product->scaleImage($w, $h, true);

            $x = ($product_width - $w) / 2;
            $y = ($product_height - $h) / 2;
            $product_layer = $this->bannerService->get_image_layer($file["name"], $x, $y, $x + $w, $y + $h, $product_filename);
                
            $json["layers"][] = $product_layer;
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
            "product_format" => $request->product_format,
            "product_format_text_color" => $request->product_format_text_color,
            "product_format_bk_color" => $request->product_format_bk_color,
            "quantity" => $request->quantity,
            "quantity_size" => $request->quantity_size,
            "quantity_text_color" => $request->quantity_text_color,
            "quantity_bk_color" => $request->quantity_bk_color,
            "unit" => $request->unit,
            "unit_size" => $request->unit_size,
            "unit_text_color" => $request->unit_text_color,
            "unit_bk_color" => $request->unit_bk_color,
            "output_filename" => $request->output_filename,
            "include_psd" => $request->include_psd,
            "type" => $request->type
        );

        return json_encode($settings);
    }

    public function run($request, $preview = false, $save = false, $publish = false)
    {
        // Map files from UTC/GTIN/ASIN/TCIN/WMT-ID
        $result = $this->bannerService->map_files(explode(" ", $request->file_ids), false);

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
                            'headline' => $request->product_format,
                            'size' => config("templates.Generic.output_dimensions")[$request->output_dimensions + 1],
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
                            'headline' => $request->product_format,
                            'size' => config("templates.Generic.output_dimensions")[$request->output_dimensions + 1],
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
