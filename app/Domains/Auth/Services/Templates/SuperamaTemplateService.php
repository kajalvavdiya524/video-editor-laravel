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
 * Class SuperamaTemplateService.
 */
class SuperamaTemplateService extends BaseService
{
    protected $bannerService;

    /**
     * SuperamaTemplateService constructor.
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
        $config["width"] = config("templates.Superama.width")[$template];
        $config["height"] = config("templates.Superama.height")[$template];
        $config["product_dimensions"] = config("templates.Superama.product_dimensions")[$template];
        $config["headline"] = isset($request->headline) ? $request->headline : "Precios";
        $config["subheadline"] = isset($request->subheadline) ? $request->subheadline : "irresistables";
        $config["description"] = isset($request->description) ? $request->description : "Vigencia del 16 al 30 de noviembre de 2020.";

        $config["file_ids"] = $request->file_ids;
        $config["product_space1"] = $request->product_space1;
        $config["multi1"] = isset($request->multi1) ? $request->multi1 : "2";
        $config["price1"] = isset($request->price1) ? $request->price1 : "38";
        $config["unit_cost1"] = isset($request->unit_cost1) ? $request->unit_cost1 : "22";
        $config["weight1"] = isset($request->weight1) ? $request->weight1: "500";
        
        $config["file_ids2"] = $request->file_ids2;
        $config["product_space2"] = $request->product_space2;
        $config["multi2"] = isset($request->multi2) ? $request->multi2 : "2";
        $config["price2"] = isset($request->price2) ? $request->price2 : "25";
        $config["unit_cost2"] = isset($request->unit_cost2) ? $request->unit_cost2 : "15";
        $config["weight2"] = isset($request->weight2) ? $request->weight2: "580";

        $config["include_psd"] = $request->include_psd;

        // Save background image if exists in request.
        $background_filename = null;
        if (isset($request->background)) {
            $background_filename = uniqid().".".$request->background->getClientOriginalExtension();
            $temp_files[] = $background_filename;
            file_put_contents($background_filename, file_get_contents($request->file("background")));
        }
        $config["background"] = $background_filename;
        $config["background_color"] = "#FFFFFF";
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

    private function get_psd($files1, $files2, $product_filenames1, $product_filenames2, $config)
    {
        $json = array(
            "width" => $config["width"],
            "height" => $config["height"],
            "layers" => array()
        );

        $baseline = $config["product_dimensions"]["baseline"];
        // Headline
        $json["layers"][] = $this->bannerService->get_text_layer("Headline", $baseline[0], 80, $baseline[1] - $baseline[0] - 10, 70, $config["headline"], "Proxima-Nova-Bold", 80, "#ff0000", "center", 0);
        // Sub headline
        $json["layers"][] = $this->bannerService->get_text_layer("Sub headline", $baseline[0], 150, $baseline[1] - $baseline[0] - 10, 50, $config["subheadline"], "Amazon-Ember", 65, "#ff0000", "center", 0);
        // Description
        $json["layers"][] = $this->bannerService->get_text_layer("Description", $baseline[0], 270, $baseline[1] - $baseline[0] - 10, 50, $config["description"], "Amazon-Ember", 20, "#535353", "center", 0);

        /* Product */
        $count1 = count($files1);
        $count2 = count($files2);
        $products1 = array();
        $products2 = array();

        $sum_width = 0;
        $max_height = 0;
        $products_width1 = $baseline[2] - $baseline[1] - $config["product_space1"] * ( $count1 - 1 );
        for ($i = 0; $i < $count1; $i++) {
            $sum_width += $files1[$i]["width"];
            $products1[] = new \Imagick($product_filenames1[$i]);
        }
        for ($i = 0; $i < $count1; $i++) {
            $w = $products_width1 * $files1[$i]["width"] / $sum_width;
            $products1[$i]->thumbnailImage($w, null);
            $h = $products1[$i]->getImageHeight();
            if ($max_height < $h) {
                $max_height = $h;
            }
        }
        $r = $config["product_dimensions"]["height"] < $max_height ? $config["product_dimensions"]["height"] / $max_height : 1;
        $products_width1 *= $r;
        for ($i = 0; $i < $count1; $i++) {
            $w = $products1[$i]->getImageWidth() * $r;
            $h = $products1[$i]->getImageHeight() * $r;
            if ($r < 1) {
                $products1[$i] ->scaleImage($w, $h, true);
            }
        }

        $sum_width = 0;
        $max_height = 0;
        $products_width2 = $baseline[4] - $baseline[3] - $config["product_space2"] * ( $count2 - 1 );
        for ($i = 0; $i < $count2; $i++) {
            $sum_width += $files2[$i]["width"];
            $products2[] = new \Imagick($product_filenames2[$i]);
        }
        for ($i = 0; $i < $count2; $i++) {
            $w = $products_width2 * $files2[$i]["width"] / $sum_width;
            $products2[$i]->thumbnailImage($w, null);
            $h = $products2[$i]->getImageHeight();
            if ($max_height < $h) {
                $max_height = $h;
            }
        }
        $r = $config["product_dimensions"]["height"] < $max_height ? $config["product_dimensions"]["height"] / $max_height : 1;
        $products_width2 *= $r;
        for ($i = 0; $i < $count2; $i++) {
            $w = $products2[$i]->getImageWidth() * $r;
            $h = $products2[$i]->getImageHeight() * $r;
            if ($r < 1) {
                $products2[$i] ->scaleImage($w, $h, true);
            }
        }

        // Product Image 1
        $x = $baseline[1] + ($baseline[2] - $baseline[1] - $products_width1) / 2;
        for ($i = 0; $i < $count1; $i++) {
            $w = $products1[$i]->getImageWidth();
            $h = $products1[$i]->getImageHeight();
            $y = $config["product_dimensions"]["height"] - $h;
            $json["layers"][] = $this->bannerService->get_smartobject_layer($files1[$i]["name"], $product_filenames1[$i], $x, $y, $w, $h, 0, null, null, null, 0);
            $x += $w;
            $x += $config["product_space1"];
        }

        // Product Image 2
        $x = $baseline[3] + ($baseline[4] - $baseline[3] - $products_width2) / 2;
        for ($i = 0; $i < $count2; $i++) {
            $w = $products2[$i]->getImageWidth();
            $h = $products2[$i]->getImageHeight();
            $y = $config["product_dimensions"]["height"] - $h;
            $json["layers"][] = $this->bannerService->get_smartobject_layer($files2[$i]["name"], $product_filenames2[$i], $x, $y, $w, $h, 0, null, null, null, 0);
            $x += $w;
            $x += $config["product_space2"];
        }
        
        // Product Description 1
        $left = ($baseline[2] + $baseline[1] + $products_width1) / 2;
        $w = $baseline[3] + ($baseline[4] - $baseline[3] - $products_width2) / 2 - $left;
        $json["layers"][] = $this->bannerService->get_text_layer("Multiple1", $left, 40, $w, 50, $config["multi1"]."x", "Proxima-Nova-Bold", 55, "#FF0000", "center", 0);
        $json["layers"][] = $this->bannerService->get_text_layer("Price1", $left, 90, $w, 50, "$".$config["price1"], "Proxima-Nova-Bold", 55, "#FF0000", "center", 0);
        $json["layers"][] = $this->bannerService->get_text_layer("price:", $left, 145, $w, 50, "Precio:", "Proxima-Nova-Semibold", 25, "#535353", "center", 0);
        $json["layers"][] = $this->bannerService->get_text_layer("Unit cost1", $left, 175, $w, 50, "$".$config["unit_cost1"]." c/u", "Proxima-Nova-Semibold", 20, "#535353", "center", 0);
        $json["layers"][] = $this->bannerService->get_text_layer("Weight1", $left, 225, $w, 50, $config["weight1"]." g", "Proxima-Nova-Semibold", 25, "#535353", "center", 0);

        
        // Product Description 2
        $left = $baseline[4];
        $w = $baseline[4] - $left;
        $json["layers"][] = $this->bannerService->get_text_layer("Multiple2", $left, 40, $w, 50, $config["multi2"]."x", "Proxima-Nova-Bold", 55, "#FF0000", "center", 0);
        $json["layers"][] = $this->bannerService->get_text_layer("Price2", $left, 90, $w, 50, "$".$config["price2"], "Proxima-Nova-Bold", 55, "#FF0000", "center", 0);
        $json["layers"][] = $this->bannerService->get_text_layer("price:", $left, 145, $w, 50, "Precio:", "Proxima-Nova-Semibold", 25, "#535353", "center", 0);
        $json["layers"][] = $this->bannerService->get_text_layer("Unit cost2", $left, 175, $w, 50, "$".$config["unit_cost2"]." c/u", "Proxima-Nova-Semibold", 20, "#535353", "center", 0);
        $json["layers"][] = $this->bannerService->get_text_layer("Weight2", $left, 225, $w, 50, $config["weight2"]." g", "Proxima-Nova-Semibold", 25, "#535353", "center", 0);


        /* Background */
        // if (!empty($config["background"])) {
        //     $background_layer = new \Imagick();
        //     $background_layer->readImage($config["background"]);
        //     $background_layer->scaleImage($config["width"], $config["height"], true);
        //     $background_layer->writeImage($config["background"]);
        //     $image_width = $background_layer->getImageWidth();
        //     $image_height = $background_layer->getImageHeight();
        //     array_unshift($json["layers"], $this->bannerService->get_smartobject_layer("Background", $config["background"], ($config["width"] - $image_width) / 2, ($config["height"] - $image_height) / 2, $image_width, $image_height, 0));
        // } else {
        //     array_unshift($json["layers"], $this->bannerService->get_pixel_layer("Background", 0, 0, $config["width"], $config["height"], $config["background_color"]));
        // }
        $background_image = uniqid().".png";
        $background_layer = new \Imagick();
        $background_layer->readImage("img/backgrounds/Superama/Superama_background.png");
        $background_layer->scaleImage($config["width"], $config["height"], true);
        $background_layer->writeImage($background_image);
        $image_width = $background_layer->getImageWidth();
        $image_height = $background_layer->getImageHeight();
        array_unshift($json["layers"], $this->bannerService->get_smartobject_layer("Background", $background_image, ($config["width"] - $image_width) / 2, ($config["height"] - $image_height) / 2, $image_width, $image_height, 0));


        return base64_encode(json_encode($json));
    }

    private function get_history_settings($request)
    {
        $settings = array(
            "customer" => $request->customer,
            "output_dimensions" => $request->output_dimensions,
            "project_name" => $request->project_name,
            "file_ids" => $request->file_ids,
            "file_ids2" => $request->file_ids2,
            "product_space1" => $request->product_space1,
            "product_space2" => $request->product_space2,
            "output_filename" => $request->output_filename,
            "headline" => $request->headline,
            "subheadline" => $request->subheadline,
            "description" => $request->description,
            "multi1" => $request->multi1,
            "price1" => $request->price1, 
            "unit_cost1" => $request->unit_cost1,
            "weight1" => $request->weight1,
            "multi2" => $request->multi2,
            "price2" => $request->price2, 
            "unit_cost2" => $request->unit_cost2,
            "weight2" => $request->weight2,
            "include_psd" => $request->include_psd,
            "type" => $request->type,
            "product_texts" => $request->product_texts
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

        $result2 = $this->bannerService->map_files(explode(" ", $request->file_ids2));
        if ($result2["status"] == "error") {
            return $result2;
        }

        // Get trimmed product image files
        $temp_files = array();
        $product_filenames1 = array();
        $product_filenames2 = array();
        foreach ($result["files"] as $file) {
            $filename = uniqid().".png";
            $contents = Storage::disk('s3')->get($file["path"]);
            Storage::disk('public')->put($filename, $contents);
            $product_filenames1[] = $filename;
            $temp_files[] = $filename;
        }

        foreach ($result2["files"] as $file) {
            $filename = uniqid().".png";
            $contents = Storage::disk('s3')->get($file["path"]);
            Storage::disk('public')->put($filename, $contents);
            $product_filenames2[] = $filename;
            $temp_files[] = $filename;
        }

        $response = [];

        if ($preview) {
            $response = ["files" => [], "log" => []];
            $config = $this->get_config($request);
            $jpeg_file_id = uniqid();
            $jpeg_file = $jpeg_file_id.".jpg";

            $log = "";
            $input_arg = $this->get_psd($result["files"], $result2["files"], $product_filenames1, $product_filenames2, $config);
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

                    $input_arg = $this->get_psd($result["files"], $result2["files"], $product_filenames1, $product_filenames2, $config);
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
                            'headline' => "Superama",
                            'size' => config("templates.Superama.output_dimensions")[$request->output_dimensions],
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
                            'headline' => "Superama",
                            'size' => config("templates.Superama.output_dimensions")[$request->output_dimensions],
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
