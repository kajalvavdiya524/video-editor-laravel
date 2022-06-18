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
 * Class PilotTemplateService.
 */
class PilotTemplateService extends BaseService
{
    protected $bannerService;

    /**
     * PilotTemplateService constructor.
     *
     */
    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    private function get_config($request, $template)
    {
        $config = array();
        $config["customer"] = $request->customer;
        $config["template"] = $template;
        $config["width"] = config("templates.Pilot.width")[$template];
        $config["height"] = config("templates.Pilot.height")[$template];
        $config["product_dimensions"] = config("templates.Pilot.product_dimensions")[$template];
        $config["file_ids"] = $request->file_ids;
        $config["text1"] = isset($request->text1) ? $request->text1 : " ";
        $config["text2"] = isset($request->text2) ? $request->text2 : " ";
        $config["text1_color"] = $request->text1_color;
        $config["text2_color"] = $request->text2_color;
        $config["text2_font"] = $request->text2_font;
        $config["text2_font_size"] = $request->text2_font_size;

        $config["x_offset"] = $request->x_offset[$template];
        $config["y_offset"] = $request->y_offset[$template];
        $config["angle"] = $request->angle[$template];
        $config["scale"] = $request->scale[$template];
        $config["x_offset_button"] = $request->x_offset_button[$template];
        $config["y_offset_button"] = $request->y_offset_button[$template];

        $config["background_pattern"] = $request->background_pattern;
        $config["background_type"] = $request->background_type;
        $config["background_color"] = $request->background_color;
        $background_filename = null;
        if (isset($request->background) && $request->background_type == "background_image") {
            $background_filename = uniqid() . ".png";
            $temp_files[] = $background_filename;
            $arr = explode("/", $request->background);
            $arr[count($arr) - 2] = $template;
            $background_path = implode("/", $arr);
            file_put_contents($background_filename, file_get_contents($background_path));
        }
        $config["background"] = $background_filename;
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

        return $config;
    }

    private function get_output_filename($request, $template)
    {
        $project_name = $request->project_name;
        $output_dimension_str = config("templates.Pilot.output_dimensions")[$template];
        $filename = (!empty($project_name) ? $project_name : "output_") . $output_dimension_str;
        return $filename;
    }


    private function get_psd($files, $product_filenames, $config, $isPreview = true)
    {
        $json = array(
            "width" => $config["width"],
            "height" => $config["height"],
            "layers" => array()
        );

        if ($config["template"] == 0) {
            $json["layers"][] = $this->bannerService->get_pixel_layer("color_background", 0, 0, $config["width"], $config["height"], $config["background_color"]);

            if ($config["background_pattern"] == "texture") {
                $texture = new \Imagick("img/backgrounds/Pilot/texture.png");
                $texture_width = $texture->getImageWidth();
                $texture_height = $texture->getImageHeight();
                $x_row = ceil($config["width"] / $texture_width);
                $y_row = ceil($config["height"] / $texture_height);
                for ($i = 0; $i < $x_row; $i++) {
                    for ($j = 0; $j < $y_row; $j++) {
                        $json["layers"][] = $this->bannerService->get_smartobject_layer("background_texture", "img/backgrounds/Pilot/texture.png", $i * $texture_width, $j * $texture_height, $texture_width, $texture_height);
                    }
                }
            }

            if ($config["background_type"] == "background_image" && $config["background"]) {
                $background = new \Imagick($config["background"]);
                $background->thumbnailImage($config["width"], null);
                $json["layers"][] = $this->bannerService->get_smartobject_layer("image_background", $config["background"], 0, $config["height"] - $background->getImageHeight(), $config["width"], $background->getImageHeight());
            }
            // Logo
            if ($config["logo"]) {
                $logo = new \Imagick($config["logo"]);
                $logo->thumbnailImage(387, null);
                $json["layers"][] = $this->bannerService->get_smartobject_layer("logo", $config["logo"], 47, 70, $logo->getImageWidth(), $logo->getImageHeight());
            }

            if ($config["background_type"] == "background_image") {
                // Text 1
                $text1_arr = $this->bannerService->get_multiline_text($config["text1"], "Amazon-Ember", 107, $config["width"] - 100);
                if ($config["text1"] != "") {
                    for ($i = 0; $i < count($text1_arr); $i++) {
                        if (!empty($text1_arr[$i])) {
                            $json["layers"][] = $this->bannerService->get_text_layer2("text1" . ($i + 1), 50, 203 + 112 * $i, $config["width"], 50, array($text1_arr[$i]), "GothamNarrow-Ultra", "Gotham Narrow Ultra.otf", 107, $config["text1_color"], "left", 0, true);
                        }
                    }
                }

                // Text 2
                $text2_arr = $this->bannerService->get_multiline_text($config["text2"], "Amazon-Ember", $config["text2_font_size"], 600);
                $text2_font_path = "";
                if ($config["text2_font"] == "GothamNarrow-Ultra") {
                    $text2_font_path = "Gotham Narrow Ultra.otf";
                } else {
                    $text2_font_path = "MuseoSans_300_Italic.otf";
                }
                if ($config["text2"] != "") {
                    for ($i = 0; $i < count($text2_arr); $i++) {
                        if (!empty($text2_arr[$i])) {
                            $json["layers"][] = $this->bannerService->get_text_layer2("text2" . ($i + 1), 45, 602 + 78 * $i, $config["width"], 50, array($text2_arr[$i]), $config["text2_font"], $text2_font_path, intval($config["text2_font_size"]), $config["text2_color"], "left", 0);
                        }
                    }
                }

                // Button
                // $json["layers"][] = $this->bannerService->get_pixel_layer("shop now background_shadow", 51, 852, 365, 88, "#8888883f", 0, null, 8, [1, 1, 1, 1]);
                $json["layers"][] = $this->bannerService->get_pixel_layer("shop now background", 45 + intval($config["x_offset_button"]), 844 + intval($config["y_offset_button"]), 365, 88, "#d41e3d", 0, null, 12, [1, 1, 1, 1]);
                $json["layers"][] = $this->bannerService->get_text_layer2("shop now", 45 + intval($config["x_offset_button"]), 865 + intval($config["y_offset_button"]), 365, 50, "SHOP NOW", "GothamNarrow-Ultra", "Gotham Narrow Ultra.otf", 55, "#ffffff", "center", 60);
            } else {
                // Text 1
                $text1_arr = $this->bannerService->get_multiline_text($config["text1"], "Amazon-Ember", 100, $config["width"] - 100);
                if ($config["text1"] != "") {
                    for ($i = 0; $i < count($text1_arr); $i++) {
                        if (!empty($text1_arr[$i])) {
                            $json["layers"][] = $this->bannerService->get_text_layer2("text1" . ($i + 1), 50, 203 + 104 * $i, $config["width"], 50, array($text1_arr[$i]), "GothamNarrow-Ultra", "Gotham Narrow Ultra.otf", 100, $config["text1_color"], "left", 0);
                        }
                    }
                }

                // Text 2
                $text2_arr = $this->bannerService->get_multiline_text($config["text2"], "Amazon-Ember", 64, 450);
                if ($config["text2"] != "") {
                    for ($i = 0; $i < count($text2_arr); $i++) {
                        if (!empty($text2_arr[$i])) {
                            $json["layers"][] = $this->bannerService->get_text_layer2("text2" . ($i + 1), 52, 631 + 79 * $i, $config["width"], 50, array($text2_arr[$i]), "MuseoSans-300Italic", "MuseoSans_300_Italic.otf", 64, $config["text2_color"], "left", 0);
                        }
                    }
                }

                // Button
                // $json["layers"][] = $this->bannerService->get_pixel_layer("shop now background_shadow", 51, 852, 365, 88, "#8888883f", 0, null, 8, [1, 1, 1, 1]);
                $json["layers"][] = $this->bannerService->get_pixel_layer("shop now background", 45 + intval($config["x_offset_button"]), 873 + intval($config["y_offset_button"]), 365, 89, "#004e7d", 0, null, 12, [1, 1, 1, 1]);
                $json["layers"][] = $this->bannerService->get_text_layer2("shop now", 45 + intval($config["x_offset_button"]), 895 + intval($config["y_offset_button"]), 365, 50, "SHOP NOW", "GothamNarrow-Ultra", "Gotham Narrow Ultra.otf", 50, "#ffffff", "center", 60);

                // Product Image
                if (count($files)) {
                    $product_origin = new \Imagick($product_filenames[0]);
                    $product_origin->scaleImage($config["width"] * 0.5, $config["height"] * 0.5, true);
                    $origin_w = $product_origin->getImageWidth();
                    $origin_h = $product_origin->getImageHeight();
                    $product = new \Imagick($product_filenames[0]);
                    $product->scaleImage($config["width"] * 0.5 * $config["scale"], $config["height"] * 0.5 * $config["scale"], true);
                    $w = $product->getImageWidth();
                    $h = $product->getImageHeight();
                    $json["layers"][] = $this->bannerService->get_smartobject_layer($files[0]["name"], $product_filenames[0], $config["width"] - $origin_w - 70 + intval($config["x_offset"]), $config["height"] - $origin_h - 40 + intval($config["y_offset"]), $w, $h, intval($config["angle"]));
                }
            }
        } else if ($config["template"] == 1) {
            $json["layers"][] = $this->bannerService->get_pixel_layer("color_background", 0, 0, $config["width"], $config["height"], $config["background_color"]);

            if ($config["background_pattern"] == "texture") {
                $texture = new \Imagick("img/backgrounds/Pilot/texture.png");
                $texture_width = $texture->getImageWidth();
                $texture_height = $texture->getImageHeight();
                $x_row = ceil($config["width"] / $texture_width);
                $y_row = ceil($config["height"] / $texture_height);
                for ($i = 0; $i < $x_row; $i++) {
                    for ($j = 0; $j < $y_row; $j++) {
                        $json["layers"][] = $this->bannerService->get_smartobject_layer("background_texture", "img/backgrounds/Pilot/texture.png", $i * $texture_width, $j * $texture_height, $texture_width, $texture_height);
                    }
                }
            }

            if ($config["background_type"] == "background_image" && $config["background"]) {
                $background = new \Imagick($config["background"]);
                $background->thumbnailImage(null, $config["height"]);
                $json["layers"][] = $this->bannerService->get_smartobject_layer("image_background", $config["background"], $config["width"] - $background->getImageWidth(), 0, $background->getImageWidth(), $config["height"]);
            }
            // Logo
            if ($config["logo"]) {
                $logo = new \Imagick($config["logo"]);
                $logo->thumbnailImage(528, null);
                $json["layers"][] = $this->bannerService->get_smartobject_layer("logo", $config["logo"], 70, 118, $logo->getImageWidth(), $logo->getImageHeight());
            }

            if ($config["background_type"] == "background_image") {
                // Text 1
                $text1_arr = $this->bannerService->get_multiline_text($config["text1"], "Amazon-Ember", 102, 900);
                if ($config["text1"] != "") {
                    $top = (375 - 105 * count($text1_arr)) / 2;
                    for ($i = 0; $i < count($text1_arr); $i++) {
                        if (!empty($text1_arr[$i])) {
                            $json["layers"][] = $this->bannerService->get_text_layer2("text1" . ($i + 1), 738, $top + 105 * $i, 900, 50, array($text1_arr[$i]), "GothamNarrow-Ultra", "Gotham Narrow Ultra.otf", 102, $config["text1_color"], "left", 0);
                        }
                    }
                }

                // Button
                $json["layers"][] = $this->bannerService->get_pixel_layer("shop now background", 2689 + intval($config["x_offset_button"]), 200 + intval($config["y_offset_button"]), 278, 125, "#d41e3d", 0, null, 10, [1, 1, 1, 1]);
                $json["layers"][] = $this->bannerService->get_text_layer2("shop now", 2689 + intval($config["x_offset_button"]), 235 + intval($config["y_offset_button"]), 278, 50, "SHOP", "GothamNarrow-Ultra", "Gotham Narrow Ultra.otf", 70, "#ffffff", "center", 70);
            } else {
                // Text 1
                $text1_arr = $this->bannerService->get_multiline_text($config["text1"], "Amazon-Ember", 96, 1300);
                if ($config["text1"] != "") {
                    $top = (375 - 105 * count($text1_arr)) / 2;
                    for ($i = 0; $i < count($text1_arr); $i++) {
                        if (!empty($text1_arr[$i])) {
                            $json["layers"][] = $this->bannerService->get_text_layer2("text1" . ($i + 1), 736, $top + 105 * $i, 900, 50, array($text1_arr[$i]), "GothamNarrow-Ultra", "Gotham Narrow Ultra.otf", 96, $config["text1_color"], "left", 0);
                        }
                    }
                }

                // Button
                $json["layers"][] = $this->bannerService->get_pixel_layer("shop now background", 2668 + intval($config["x_offset_button"]), 120 + intval($config["y_offset_button"]), 279, 125, "#d41e3d", 0, null, 10, [1, 1, 1, 1]);
                $json["layers"][] = $this->bannerService->get_text_layer2("shop now", 2668 + intval($config["x_offset_button"]), 145 + intval($config["y_offset_button"]), 279, 50, "SHOP", "GothamNarrow-Ultra", "Gotham Narrow Ultra.otf", 75, "#ffffff", "center", 70);

                // Product Image
                if (count($files)) {
                    $product_origin = new \Imagick($product_filenames[0]);
                    $product_origin->scaleImage(645, 325, true);
                    $origin_w = $product_origin->getImageWidth();
                    $origin_h = $product_origin->getImageHeight();
                    $product = new \Imagick($product_filenames[0]);
                    $product->scaleImage(645 * $config["scale"], 325 * $config["scale"], true);
                    $w = $product->getImageWidth();
                    $h = $product->getImageHeight();
                    $json["layers"][] = $this->bannerService->get_smartobject_layer($files[0]["name"], $product_filenames[0], 1965 + (700 - $origin_w) / 2 + intval($config["x_offset"]), ($config["height"] - $origin_h) / 2 + intval($config["y_offset"]), $w, $h, intval($config["angle"]));
                }
            }
        } else if ($config["template"] == 2) {
            $json["layers"][] = $this->bannerService->get_pixel_layer("color_background", 0, 0, $config["width"], $config["height"], $config["background_color"]);

            if ($config["background_pattern"] == "texture") {
                $texture = new \Imagick("img/backgrounds/Pilot/texture.png");
                $texture_width = $texture->getImageWidth();
                $texture_height = $texture->getImageHeight();
                $x_row = ceil($config["width"] / $texture_width);
                $y_row = ceil($config["height"] / $texture_height);
                for ($i = 0; $i < $x_row; $i++) {
                    for ($j = 0; $j < $y_row; $j++) {
                        $json["layers"][] = $this->bannerService->get_smartobject_layer("background_texture", "img/backgrounds/Pilot/texture.png", $i * $texture_width, $j * $texture_height, $texture_width, $texture_height);
                    }
                }
            }

            if ($config["background_type"] == "background_image" && $config["background"]) {
                $background = new \Imagick($config["background"]);
                $background->thumbnailImage(null, $config["height"]);
                $json["layers"][] = $this->bannerService->get_smartobject_layer("image_background", $config["background"], $config["width"] - $background->getImageWidth(), 0, $background->getImageWidth(), $config["height"]);
            }
            // Logo
            if ($config["logo"]) {
                $logo = new \Imagick($config["logo"]);
                $logo->thumbnailImage(675, null);
                $json["layers"][] = $this->bannerService->get_smartobject_layer("logo", $config["logo"], 99, 175, $logo->getImageWidth(), $logo->getImageHeight());
            }

            if ($config["background_type"] == "background_image") {
                // Text 1
                $text1_arr = $this->bannerService->get_multiline_text($config["text1"], "Amazon-Ember", 150, 1000);
                if ($config["text1"] != "") {
                    $top = (474 - 140 * count($text1_arr)) / 2;
                    for ($i = 0; $i < count($text1_arr); $i++) {
                        if (!empty($text1_arr[$i])) {
                            $json["layers"][] = $this->bannerService->get_text_layer2("text1" . ($i + 1), 892, $top + 140 * $i, 1000, 50, array($text1_arr[$i]), "GothamNarrow-Ultra", "Gotham Narrow Ultra.otf", 150, $config["text1_color"], "left", 0);
                        }
                    }
                }

                // Button
                $json["layers"][] = $this->bannerService->get_pixel_layer("shop now background", 2577 + intval($config["x_offset_button"]), 230 + intval($config["y_offset_button"]), 391, 175, "#d41e3d", 0, null, 17, [1, 1, 1, 1]);
                $json["layers"][] = $this->bannerService->get_text_layer2("shop now", 2577 + intval($config["x_offset_button"]), 273 + intval($config["y_offset_button"]), 391, 50, "SHOP", "GothamNarrow-Ultra", "Gotham Narrow Ultra.otf", 105, "#ffffff", "center", 70);
            } else {
                // Text 1
                $text1_arr = $this->bannerService->get_multiline_text($config["text1"], "Amazon-Ember", 150, 1000);
                if ($config["text1"] != "") {
                    $top = (474 - 140 * count($text1_arr)) / 2;
                    for ($i = 0; $i < count($text1_arr); $i++) {
                        if (!empty($text1_arr[$i])) {
                            $json["layers"][] = $this->bannerService->get_text_layer2("text1" . ($i + 1), 910, $top + 140 * $i, 1000, 50, array($text1_arr[$i]), "GothamNarrow-Ultra", "Gotham Narrow Ultra.otf", 150, $config["text1_color"], "left", 0);
                        }
                    }
                }

                // Button
                $json["layers"][] = $this->bannerService->get_pixel_layer("shop now background", 2558 + intval($config["x_offset_button"]), 152 + intval($config["y_offset_button"]), 391, 175, "#d41e3d", 0, null, 17, [1, 1, 1, 1]);
                $json["layers"][] = $this->bannerService->get_text_layer2("shop now", 2558 + intval($config["x_offset_button"]), 195 + intval($config["y_offset_button"]), 391, 50, "SHOP", "GothamNarrow-Ultra", "Gotham Narrow Ultra.otf", 105, "#ffffff", "center", 70);

                // Product Image
                if (count($files)) {
                    $product_origin = new \Imagick($product_filenames[0]);
                    $product_origin->scaleImage(810, null);
                    $origin_w = $product_origin->getImageWidth();
                    $origin_h = $product_origin->getImageHeight();
                    $product = new \Imagick($product_filenames[0]);
                    $product->scaleImage(810 * $config["scale"], null);
                    $w = $product->getImageWidth();
                    $h = $product->getImageHeight();
                    $json["layers"][] = $this->bannerService->get_smartobject_layer($files[0]["name"], $product_filenames[0], 1677 + intval($config["x_offset"]), ($config["height"] - $origin_h) / 2 + intval($config["y_offset"]), $w, $h, intval($config["angle"]));
                }
            }
        }

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
            "text1" => $request->text1,
            "text2" => $request->text2,
            "text1_color" => $request->text1_color,
            "text2_color" => $request->text2_color,
            "text2_font" => $request->text2_font,
            "text2_font_size" => $request->text2_font_size,
            "background_pattern" => $request->background_pattern,
            "background_type" => $request->background_type,
            "background_color" => $request->background_color,
            "x_offset" => $request->x_offset,
            "y_offset" => $request->y_offset,
            "angle" => $request->angle,
            "scale" => $request->scale,
            "x_offset_button" => $request->x_offset_button,
            "y_offset_button" => $request->y_offset_button, 
            "background" => $request->background, 
            "export_all" => $request->export_all,
            "include_psd" => $request->include_psd,
            "logo" => isset($request->logo) || isset($request->logo_saved) ? url('/share?file='). $filename : null,
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
            $filename = uniqid() . ".png";
            $contents = Storage::disk('s3')->get($file["path"]);
            Storage::disk('public')->put($filename, $contents);
            $product_filenames[] = $filename;
            $temp_files[] = $filename;
        }

        $response = [];
        $jpeg_file = "";

        if ($preview) {
            $response = ["files" => [], "log" => []];
            $templates = [];
            if ($request->export_all == "on") {
                $templates[] = $request->output_dimensions;
                for ($i = 0; $i < 3; $i++) {
                    if ($i != $request->output_dimensions) {
                        $templates[] = $i;
                    }
                }
            } else {
                $templates[] = $request->output_dimensions;
            }
            foreach ($templates as $template) {
                $config = $this->get_config($request, $template);
                $jpeg_file_id = uniqid();
                $jpeg_file = $jpeg_file_id . ".jpg";

                $input_arg = $this->get_psd($result["files"], $product_filenames, $config);
                $command = escapeshellcmd("python3 /var/www/psd2/tool.py -j " . $input_arg . " -of result -p " . $jpeg_file_id);

                $log = shell_exec($command . " 2>&1");
                $temp_files = array_merge($temp_files, array("tmp_result.psd", "shadow.png", "circle.png", "result.zip"));

                $response['files'][] = $jpeg_file;
                $response['log'][] = $log;
            }
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
                $zip_file = $zip_file_id . ".zip";
                $zip_filename = (!empty($request->output_filename) ? $request->output_filename : (!empty($request->project_name) ? $request->project_name : "output"));
                $zip = new ZipArchive();
                $log = "";
                if ($zip->open(public_path($zip_file), ZipArchive::CREATE) === TRUE) {
                    $output_jpg_files = array();
                    $output_psd_files = array();
                    $templates = [];
                    if ($request->export_all == "on") {
                        $templates = [0, 1, 2];
                    } else {
                        $templates[] = $request->output_dimensions;
                    }
                    foreach ($templates as $template) {
                        $config = $this->get_config($request, $template);
                        $output_filename = $this->get_output_filename($request, $template);

                        $psd_file_id = uniqid();
                        $psd_file = $psd_file_id . ".psd";
                        $jpeg_file_id = uniqid();
                        $jpeg_file = $jpeg_file_id . ".jpg";

                        $input_arg = $this->get_psd($result["files"], $product_filenames, $config, false);
                        $command = escapeshellcmd("python3 /var/www/psd2/tool.py -j " . $input_arg . " -of " . $zip_file_id . " -o " . $psd_file_id . " -p " . $jpeg_file_id);
                        $log = shell_exec($command . " 2>&1");
                        $temp_files = array_merge($temp_files, array("tmp_result.psd", "shadow.png", "circle.png"));
                        $temp_files[] = $psd_file;
                        $temp_files[] = $jpeg_file;

                        $zip->addFile($jpeg_file, $output_filename . ".jpg");
                        $output_jpg_files[] = $jpeg_file;
                        if ($request->include_psd) {
                            $zip->addFile($psd_file, $output_filename . ".psd");
                            $output_psd_files[] = $psd_file;
                        }
                    }
                    $zip->close();

                    Storage::disk('s3')->put('outputs/' . $zip_file, file_get_contents(public_path($zip_file)));
                    $temp_files[] = $zip_file;

                    foreach ($output_jpg_files as $filename) {
                        if (file_exists($filename)) {
                            Storage::disk('s3')->put('outputs/jpg/' . $filename, file_get_contents(public_path($filename)));
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
                            'url' => 'outputs/' . $zip_file,
                            'fileid' => isset($request->file_ids) ? $request->file_ids : " ",
                            'headline' => "Pilot",
                            'size' => config("templates.Pilot.output_dimensions")[$request->output_dimensions],
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
                            'url' => 'outputs/' . $zip_file,
                            'fileid' => isset($request->file_ids) ? $request->file_ids : " ",
                            'headline' => "Pilot",
                            'size' => config("templates.Pilot.output_dimensions")[$request->output_dimensions],
                            'settings' => $this->get_history_settings($request),
                            'jpg_files' => implode(" ", $output_jpg_files),
                            'type' => $request->type,
                            'parent_id' => isset($request->parent_id) ? $request->parent_id : 0
                        ]);
                    }
                }

                $response = [
                    "url" => Storage::disk('s3')->temporaryUrl('outputs/' . $zip_file, now()->addHours(1), [
                        'ResponseContentDisposition' => 'attachment; filename="' . $zip_filename . '.zip"'
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
