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
 * Class SamTemplateService.
 */
class SamTemplateService extends BaseService
{
    protected $bannerService;

    /**
     * SamTemplateService constructor.
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
        $config["width"] = config("templates.Sam.width")[$template];
        $config["height"] = config("templates.Sam.height")[$template];
        $config["product_dimensions"] = config("templates.Sam.product_dimensions")[$template];
        $config["file_ids"] = $request->file_ids;
        $config["header"] = isset($request->header) ? $request->header : " ";
        $config["subhead"] = isset($request->subhead) ? $request->subhead : " ";
        $config["pre_header"] = $request->pre_header;
        $config["x_offset"] = $request->x_offset;
        $config["y_offset"] = $request->y_offset;

        $logo_filename = null;
        if (isset($request->logo)) {
            $logo_filename = uniqid() . "." . $request->logo->getClientOriginalExtension();
            $temp_files[] = $logo_filename;
            file_put_contents($logo_filename, file_get_contents($request->file("logo")));
        } else if (isset($request->logo_saved)) {
            $logo_filename = uniqid() . ".png";
            $temp_files[] = $logo_filename;
            file_put_contents($logo_filename, file_get_contents($request->logo_saved));
        }
        $config["logo"] = $logo_filename;
        $config["disclaimer"] = isset($request->disclaimer) ? $request->disclaimer : " ";
        $config["cta"] = $request->cta;
        $config["include_psd"] = $request->include_psd;
        $config["background_color"] = "#F6F6F6";
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

        // Pre Header
        $json["layers"][] = $this->bannerService->get_text_layer2("pre-header", $config["product_dimensions"]["left"], 13, 95, 50, $config["pre_header"], "Gibson-SemiBold", "Gibson-SemiBold.otf", 14, "#0067A0", "left", 0);
        // Header
        $json["layers"][] = $this->bannerService->get_text_layer2("Header", 140, 13, 165, 50, $config["header"], "Gibson-SemiBold", "Gibson-SemiBold.otf", 14, "#424242", "left", 0);
        
        // Subhead
        if ($config["subhead"] != " ") {
            $json["layers"][] = $this->bannerService->get_text_layer2("Header", 310, 13, 80, 50, $config["subhead"], "Gibson-Regular", "Gibson-Regular.ttf", 14, "#686868", "left", 0);            
        }
        // Logo
        if ($config["logo"]) {
            $logo = new \Imagick($config["logo"]);
            $logo->thumbnailImage(null, 28);
            $json["layers"][] = $this->bannerService->get_smartobject_layer("logo", $config["logo"], 475, 6, $logo->getImageWidth(), $logo->getImageHeight());
        }
        // Product Image
        $count = count($files);
        $count = $count > 2 ? 2 : $count;
        $products = array();
        $sum_width = 0;
        $max_height = 0;
        $products_width = $config["product_dimensions"]["width"];
        for ($i = 0; $i < $count; $i++) {
            $sum_width += $files[$i]["width"];
            $products[] = new \Imagick($product_filenames[$i]);
        }

        for ($i = 0; $i < $count; $i++) {
            $w = $products_width * $files[$i]["width"] / $sum_width;
            $products[$i]->thumbnailImage($w, null);
            $h = $products[$i]->getImageHeight();
            if ($max_height < $h) {
                $max_height = $h;
            }
        }

        $sum_width = 0;
        $r = $config["product_dimensions"]["height"] < $max_height ? $config["product_dimensions"]["height"] / $max_height : 1;
        for ($i = 0; $i < $count; $i++) {
            $w = $products[$i]->getImageWidth() * $r;
            $h = $products[$i]->getImageHeight() * $r;
            $max_height = $max_height * $r;
            if ($r < 1) {
                $products[$i]->scaleImage($w, $h, true);
            }
            $sum_width += $w;
        }

        $x = 600 + ($config["product_dimensions"]["width"] - $sum_width) / 2;
        for ($i = 0; $i < $count; $i++) {
            $w = $products[$i]->getImageWidth();
            $h = $products[$i]->getImageHeight();
            $x += $config["x_offset"][$i];
            $y = $config["product_dimensions"]["height"] + $config["y_offset"][$i] - $h;
            $json["layers"][] = $this->bannerService->get_smartobject_layer($files[$i]["name"], $product_filenames[$i], $x, $y, $w, $h, 0, null, null, null, 0);
            $x = $x + $w + 10;
        }
        // Disclamier
        if ($config["disclaimer"] != " ") {
            $json["layers"][] = $this->bannerService->get_text_layer2("Disclaimer", 850, 15, 65, 50, $config["disclaimer"], "Gibson-Regular", "Gibson-Regular.ttf", 10, "#686868", "left", 0);            
        }
        // CTA
        $json["layers"][] = $this->bannerService->get_text_layer2("cta", 1000, 13, 75, 50, $config["cta"], "Gibson-SemiBold", "Gibson-SemiBold.otf", 14, "#424242", "left", 0);
        
        // Arrow
        $arrow_path = "img/backgrounds/Sam/arrow.png";
        $arrow = new \Imagick($arrow_path);
        $arrow->thumbnailImage(null, 12);
        $json["layers"][] = $this->bannerService->get_smartobject_layer("arrow", $arrow_path, $config["width"] - $arrow->getImageWidth() - 42, 14, $arrow->getImageWidth(), $arrow->getImageHeight());

        array_unshift($json["layers"], $this->bannerService->get_pixel_layer("Background", 0, 0, $config["width"], $config["height"], $config["background_color"]));

        return base64_encode(json_encode($json));
    }

    private function get_history_settings($request)
    {
        if (isset($request->logo)) {
            $filename = 'uploads/'. auth()->user()->company_id . '/' . uniqid() . "." . $request->logo->getClientOriginalExtension();
            Storage::disk('s3')->put($filename, file_get_contents($request->file("logo")));
        } else if (isset($request->logo_saved)) {
            $filename = $request->logo_saved;
        }
        $settings = array(
            "customer" => $request->customer,
            "output_dimensions" => $request->output_dimensions,
            "project_name" => $request->project_name,
            "file_ids" => $request->file_ids,
            "x_offset" => $request->x_offset,
            "y_offset" => $request->y_offset,
            "output_filename" => $request->output_filename,
            "header" => $request->header,
            "subhead" => $request->subhead,
            "pre_header" => $request->pre_header,
            "cta" => $request->cta,
            "disclaimer" => $request->disclaimer,
            "logo" => isset($request->logo) || isset($request->logo_saved) ? url('/share?file='). $filename : null,
            "background_color" => "#F6F6F6",
            "include_psd" => $request->include_psd,
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

        if ($preview) {
            $response = ["files" => [], "log" => []];
            $config = $this->get_config($request);
            $jpeg_file_id = uniqid();
            $jpeg_file = $jpeg_file_id.".jpg";

            $log = "";
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
                            'headline' => "Sam",
                            'size' => config("templates.Sam.output_dimensions")[$request->output_dimensions],
                            'settings' => $this->get_history_settings($request),
                            'jpg_files' => implode(" ", $output_jpg_files),
                            'is_master' => $request->parent_id == 0 ? true : false,
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
                            'headline' => "Sam",
                            'size' => config("templates.Sam.output_dimensions")[$request->output_dimensions],
                            'settings' => $this->get_history_settings($request),
                            'jpg_files' => implode(" ", $output_jpg_files),
                            'is_master' => $request->parent_id == 0 ? true : false,
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
