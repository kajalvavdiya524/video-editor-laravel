<?php

namespace App\Domains\Auth\Services\Templates;

use Illuminate\Support\Facades\Storage;
use App;
use App\Domains\Auth\Models\Company;
use App\Services\ProofSheet;
use App\Services\BaseService;
use App\Domains\Auth\Services\BannerService;
use App\Domains\Auth\Services\ThemeService;
use ZipArchive;

/**
 * Class KrogerTemplateService.
 */
class KrogerTemplateService extends BaseService
{
    use ProofSheet;

    protected $bannerService;
    protected $themeService;

    /**
     * KrogerTemplateService constructor.
     *
     */
    public function __construct(BannerService $bannerService, ThemeService $themeService)
    {
        $this->bannerService = $bannerService;
        $this->themeService = $themeService;
    }

    private function get_config($request, $template)
    {
        $default_values = [["3", "5"], ["5", "99"], ["3", "5"], ["3", "75"], ["50", "50"]];
        $config = array();

        $theme = $this->themeService->getById(intval($request->theme));
        $attributes = json_decode($theme->attributes);
        $shadow_attrs = $attributes[2]->list;

        if (isset($shadow_attrs[0])) {
            $shadow = $shadow_attrs[0]->list;
            $config["shadow"] = array(
                "opacity" => intval($shadow[0]->value),
                "angle" => intval($shadow[1]->value),
                "distance" => intval($shadow[2]->value),
                "spread" => intval($shadow[3]->value),
                "size" => intval($shadow[4]->value)
            );
        } else {
            $config["shadow"] = null;
        }

        // $template = $request->output_dimensions;
        $config["customer"] = $request->customer;
        $config["template"] = $template;
        $config["width"] = config("templates.Kroger.width")[$template];
        $config["height"] = config("templates.Kroger.height")[$template];
        $config["product_dimensions"] = config("templates.Kroger.product_dimensions")[$template];
        $config["value1"] = isset($request->value1) ? $request->value1 : $default_values[$request->message_options][0];
        $config["value2"] = isset($request->value2) ? $request->value2 : $default_values[$request->message_options][1];
        $config["text1"] = isset($request->text1) ? $request->text1 : " ";
        $config["text2"] = isset($request->text2) ? $request->text2 : " ";
        $config["text3"] = isset($request->text3) ? $request->text3 : " ";
        $config["x_offset"] = [$request->x_offset[$template * 2], $request->x_offset[$template * 2 + 1]];
        $config["y_offset"] = [$request->y_offset[$template * 2], $request->y_offset[$template * 2 + 1]];
        $config["angle"] = [$request->angle[$template * 2], $request->angle[$template * 2 + 1]];
        $config["scale"] = [$request->scale[$template * 2], $request->scale[$template * 2 + 1]];
        // $config["shadow"] = "center";
        $config["message_options"] = $request->message_options;
        $config["circle_color"] = $request->circle_color;
        $config["text1_color"] = $request->text1_color;
        $config["text2_color"] = $request->text2_color;
        $config["text3_color"] = $request->text3_color;
        $config["burst_circle_color"] = $request->burst_circle_color;
        $config["burst_text_color"] = $request->burst_text_color;
        $config["burst_text"] = $request->burst_text;
        $config["background_type"] = $request->background_type;
        $config["background_color"] = "#FFFFFF";
        $config["g_start_color"] = $request->g_start_color;
        $config["g_end_color"] = $request->g_end_color;
        $config["legal"] = $request->legal;
        $config["show_featured"] = $request->show_featured;
        $config["show_button"] = $request->show_button;
        $background_filename = null;
        if (isset($request->background)) {
            $background_filename = uniqid() . ".png";
            $temp_files[] = $background_filename;
            $arr = explode("/", $request->background);
            $arr[count($arr) - 2] = $template;
            $background_path = implode("/", $arr);
            file_put_contents($background_filename, file_get_contents($background_path));
        }
        $config["background"] = $background_filename;

        return $config;
    }

    private function get_output_filename($request, $template)
    {
        $project_name = $request->project_name;
        $output_dimension_str = config("templates.Kroger.output_dimensions")[$template];
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
            // Featured
            if ($config["show_featured"] == "on" && $isPreview) {
                $json["layers"][] = $this->bannerService->get_pixel_layer("feature_background", 50, 50, 260, 80, "#9f9a97", 0, null, 10, [1, 1, 1, 1]);
                $json["layers"][] = $this->bannerService->get_text_layer("Featured", 50, 75, 260, 80, "Featured", "Roboto", 50, "#FFFFFF", "center", 0);
            }

            $json["layers"][] = $this->bannerService->get_circle_layer("circle1", -22, 152, 218, $config["circle_color"]);
            // Row 1
            if ($config["message_options"] == 0 || $config["message_options"] == 1) {
                $json["layers"][] = $this->bannerService->get_text_layer("H1", 0, 259, 398, 50, "ON SALE NOW", "Proxima-Nova-Extrabld", 42, $config["text1_color"], "center", 0);
            } else if ($config["message_options"] == 2) {
                $json["layers"][] = $this->bannerService->get_text_layer("H1", 0, 264, 398, 50, "BUY " . $config["value1"], "Proxima-Nova-Black", 65, $config["text1_color"], "center", 0);
            } else if ($config["message_options"] == 3) {
                $json["layers"][] = $this->bannerService->get_text_layer("H1", 0, 259, 398, 50, "SAVE UP TO", "Proxima-Nova-Extrabld", 42, $config["text1_color"], "center", 0);
            } else if ($config["message_options"] == 4) {
                $json["layers"][] = $this->bannerService->get_text_layer("H1", 0, 285, 398, 50, "ON SALE NOW", "Proxima-Nova-Extrabld", 42, $config["text1_color"], "center", 0);
            }
            // Row 2
            if ($config["message_options"] == 0) {
                $w = $this->bannerService->get_font_metrics($config["value1"], "Amazon-Ember", 142)["textWidth"];
                $w += $this->bannerService->get_font_metrics($config["value2"], "Amazon-Ember", 142)["textWidth"];
                $w += $this->bannerService->get_font_metrics("$", "Amazon-Ember", 82)["textWidth"];
                $w += $this->bannerService->get_font_metrics("/", "Amazon-Ember", 162)["textWidth"];
                $l = (436 - $w) / 2 - 22;
                $json["layers"][] = $this->bannerService->get_text_layer("3", $l, 301, 392, 50, $config["value1"], "Proxima-Nova-Black", 142, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("$", $l + 149, 308, 392, 50, "$", "Proxima-Nova-Black", 82, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("/", $l + 89, 291, 392, 50, "/", "Proxima-Nova-Black", 162, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("11", $l + 202, 301, 392, 50, $config["value2"], "Proxima-Nova-Black", 144, $config["text2_color"], "left", 20);
            } else if ($config["message_options"] == 1 || $config["message_options"] == 3) {
                $w = $this->bannerService->get_font_metrics($config["value1"], "Amazon-Ember", 142)["textWidth"];
                $w += $this->bannerService->get_font_metrics($config["value2"], "Amazon-Ember", 82)["textWidth"];
                $w += $this->bannerService->get_font_metrics("$", "Amazon-Ember", 82)["textWidth"];
                $l = (436 - $w) / 2 - 22;
                $json["layers"][] = $this->bannerService->get_text_layer("$", $l, 308, 392, 50, "$", "Proxima-Nova-Black", 82, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("5", $l + 53, 301, 392, 50, $config["value1"], "Proxima-Nova-Black", 144, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("99", $l + 146, 306, 392, 50, $config["value2"], "Proxima-Nova-Black", 82, $config["text2_color"], "left", 20);
            } else if ($config["message_options"] == 2) {
                $w = $this->bannerService->get_font_metrics("SAVE", "Amazon-Ember", 100)["textWidth"];
                $w += $this->bannerService->get_font_metrics("$", "Amazon-Ember", 60)["textWidth"];
                $w += $this->bannerService->get_font_metrics($config["value2"], "Amazon-Ember", 100)["textWidth"];
                $l = (436 - $w) / 2 - 45;
                $json["layers"][] = $this->bannerService->get_text_layer("SAVE", $l + 8, 318, 392, 50, "SAVE", "Proxima-Nova-Black", 95, $config["text2_color"], "left", 0);
                $json["layers"][] = $this->bannerService->get_text_layer("$", $l + 275, 320, 392, 50, "$", "Proxima-Nova-Black", 55, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("5", $l + 312, 318, 392, 50, $config["value2"], "Proxima-Nova-Black", 95, $config["text2_color"], "left", 20);
            } else if ($config["message_options"] == 4) {
                $w = $this->bannerService->get_font_metrics("SAVE " . $config["value1"], "Amazon-Ember", 90)["textWidth"];
                $w += $this->bannerService->get_font_metrics("¢", "Amazon-Ember", 50)["textWidth"];
                $l = (436 - $w) / 2 - 22;
                $json["layers"][] = $this->bannerService->get_text_layer("SAVE " . $config["value1"], $l, 320, 392, 50, "SAVE " . $config["value1"], "Proxima-Nova-Black", 90, $config["text2_color"], "left", 0);
                $json["layers"][] = $this->bannerService->get_text_layer("cent", $l + $w - 5, 337, 392, 50, "¢", "Proxima-Nova-Black", 50, $config["text2_color"], "left", 20);
            }
            // Row 3
            if ($config["message_options"] == 2) {
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 1", 0, 421, 392, 50, $config["text1"], "Proxima-Nova-Semibold", 26, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 2", 0, 454, 392, 50, $config["text2"], "Proxima-Nova-Semibold", 26, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 3", 0, 487, 392, 50, $config["text3"], "Proxima-Nova-Semibold", 26, $config["text3_color"], "center", 20);
            } else if ($config["message_options"] == 4) {
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 1", 0, 418, 392, 50, $config["text1"], "Proxima-Nova-Semibold", 26, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 2", 0, 451, 392, 50, $config["text2"], "Proxima-Nova-Semibold", 26, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 3", 0, 484, 392, 50, $config["text3"], "Proxima-Nova-Semibold", 26, $config["text3_color"], "center", 20);
            } else {
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 1", 0, 439, 392, 50, $config["text1"], "Proxima-Nova-Semibold", 26, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 2", 0, 472, 392, 50, $config["text2"], "Proxima-Nova-Semibold", 26, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 3", 0, 505, 392, 50, $config["text3"], "Proxima-Nova-Semibold", 26, $config["text3_color"], "center", 20);
            }
        } else if ($config["template"] == 1) {
            if ($isPreview) {
                if ($config["show_featured"] == "on") {
                    // Featured
                    $json["layers"][] = $this->bannerService->get_pixel_layer("feature_background", 10, 10, 120, 30, "#DEF7FA", 0, null, 5, [1, 1, 1, 1]);
                    $json["layers"][] = $this->bannerService->get_text_layer("Featured", 10, 15, 120, 20, "Featured", "Roboto", 20, "#000000", "center", 0);
                }
                // Shop Now
                if ($config["show_button"] == "on") {
                    $json["layers"][] = $this->bannerService->get_pixel_layer("button_border", 985, 111, 250, 74, "#000000", 0, null, 34, [1, 1, 1, 1]);
                    $json["layers"][] = $this->bannerService->get_pixel_layer("button_background", 987, 113, 246, 70, "#FFFFFF", 0, null, 33, [1, 1, 1, 1]);
                    $json["layers"][] = $this->bannerService->get_text_layer("Shop Now", 987, 125, 246, 20, "Shop Now", "Normal", 40, "#000000", "center", 0);
                }
            }

            // Burst
            $json["layers"][] = $this->bannerService->get_circle_layer("Burst circle", 180, -13, 70, $config["burst_circle_color"]);
            $burst_texts = $this->bannerService->get_multiline_text($config["burst_text"], "Amazon-Ember", 32, 140);
            if ($config["burst_text"] != "") {
                $top = (140 - 27 * count($burst_texts)) / 2 - 12;
                for ($i = 0; $i < count($burst_texts); $i++) {
                    if (!empty($burst_texts[$i])) {
                        $json["layers"][] = $this->bannerService->get_text_layer("Burst text " . ($i + 1), 181, $top + 27 * $i, 140, 50, array($burst_texts[$i]), "Proxima-Nova-Black", 28, $config["burst_text_color"], "center", 10);
                    }
                }
            }

            $json["layers"][] = $this->bannerService->get_circle_layer("circle1", 297, -63, 162, $config["circle_color"]);
            // Row 1
            if ($config["message_options"] == 0) {
                $json["layers"][] = $this->bannerService->get_text_layer("H1", 298, 17, 330, 50, "ON SALE NOW", "Proxima-Nova-Extrabld", 30, $config["text1_color"], "center", 0);
            } else if ($config["message_options"] == 1) {
                $json["layers"][] = $this->bannerService->get_text_layer("H1", 298, 16, 330, 50, "ON SALE NOW", "Proxima-Nova-Extrabld", 30, $config["text1_color"], "center", 0);
            } else if ($config["message_options"] == 2) {
                $json["layers"][] = $this->bannerService->get_text_layer("H1", 298, 21, 330, 50, "BUY " . $config["value1"], "Proxima-Nova-Black", 45, $config["text1_color"], "center", 0);
            } else if ($config["message_options"] == 3) {
                $json["layers"][] = $this->bannerService->get_text_layer("H1", 298, 17, 330, 50, "SAVE UP TO", "Proxima-Nova-Extrabld", 33, $config["text1_color"], "center", 0);
            } else if ($config["message_options"] == 4) {
                $json["layers"][] = $this->bannerService->get_text_layer("H1", 298, 37, 330, 50, "ON SALE NOW", "Proxima-Nova-Extrabld", 30, $config["text1_color"], "center", 0);
            }
            // Row 2

            if ($config["message_options"] == 0) {
                $w = $this->bannerService->get_font_metrics($config["value1"], "Amazon-Ember", 100)["textWidth"];
                $w += $this->bannerService->get_font_metrics($config["value2"], "Amazon-Ember", 100)["textWidth"];
                $w += $this->bannerService->get_font_metrics("$", "Amazon-Ember", 55)["textWidth"];
                $w += $this->bannerService->get_font_metrics("/", "Amazon-Ember", 114)["textWidth"];
                $l = (330 - $w) / 2 + 15;
                $json["layers"][] = $this->bannerService->get_text_layer("3", $l + 278, 47, 330, 50, $config["value1"], "Proxima-Nova-Black", 110, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("$", $l + 392, 55, 330, 50, "$", "Proxima-Nova-Black", 65, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("/", $l + 347, 42, 330, 50, "/", "Proxima-Nova-Black", 120, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("11", $l + 433, 47, 330, 50, $config["value2"], "Proxima-Nova-Black", 110, $config["text2_color"], "left", 20);
            } else if ($config["message_options"] == 1 || $config["message_options"] == 3) {
                $w = $this->bannerService->get_font_metrics($config["value1"], "Amazon-Ember", 100)["textWidth"];
                $w += $this->bannerService->get_font_metrics($config["value2"], "Amazon-Ember", 60)["textWidth"];
                $w += $this->bannerService->get_font_metrics("$", "Amazon-Ember", 82)["textWidth"];
                $l = (330 - $w) / 2 + 15;
                $json["layers"][] = $this->bannerService->get_text_layer("$", $l + 280, 57, 330, 50, "$", "Proxima-Nova-Black", 65, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("5", $l + 323, 52, 392, 50, $config["value1"], "Proxima-Nova-Black", 110, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("99", $l + 388, 57, 392, 50, $config["value2"], "Proxima-Nova-Black", 65, $config["text2_color"], "left", 20);
            } else if ($config["message_options"] == 2) {
                $w = $this->bannerService->get_font_metrics("SAVE", "Amazon-Ember", 80)["textWidth"];
                $w += $this->bannerService->get_font_metrics("$", "Amazon-Ember", 60)["textWidth"];
                $w += $this->bannerService->get_font_metrics($config["value2"], "Amazon-Ember", 80)["textWidth"];
                $l = (330 - $w) / 2 + 15;
                $json["layers"][] = $this->bannerService->get_text_layer("SAVE", $l + 285, 61, 392, 50, "SAVE", "Proxima-Nova-Black", 70, $config["text2_color"], "left", 0);
                $json["layers"][] = $this->bannerService->get_text_layer("$", $l + 487, 61, 392, 50, "$", "Proxima-Nova-Black", 40, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("5", $l + 517, 61, 392, 50, $config["value2"], "Proxima-Nova-Black", 70, $config["text2_color"], "left", 20);
            } else if ($config["message_options"] == 4) {
                $w = $this->bannerService->get_font_metrics("SAVE " . $config["value1"], "Amazon-Ember", 65)["textWidth"];
                $w += $this->bannerService->get_font_metrics("¢", "Amazon-Ember", 40)["textWidth"];
                $l = (330 - $w) / 2 - 8;
                $json["layers"][] = $this->bannerService->get_text_layer("SAVE " . $config["value1"], $l + 285, 64, 392, 50, "SAVE " . $config["value1"], "Proxima-Nova-Black", 70, $config["text2_color"], "left", 0);
                $json["layers"][] = $this->bannerService->get_text_layer("cent", $l + $w + 300, 64, 392, 50, "¢", "Proxima-Nova-Black", 45, $config["text2_color"], "left", 20);
            }
            // Row 3
            if ($config["message_options"] == 2) {
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 1", 298, 137, 330, 50, $config["text1"], "Proxima-Nova-Semibold", 18, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 2", 298, 162, 330, 50, $config["text2"], "Proxima-Nova-Semibold", 18, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 3", 298, 187, 330, 50, $config["text3"], "Proxima-Nova-Semibold", 18, $config["text3_color"], "center", 20);
            } else if ($config["message_options"] == 4) {
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 1", 298, 135, 330, 50, $config["text1"], "Proxima-Nova-Semibold", 18, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 2", 298, 160, 330, 50, $config["text2"], "Proxima-Nova-Semibold", 18, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 3", 298, 185, 330, 50, $config["text3"], "Proxima-Nova-Semibold", 18, $config["text3_color"], "center", 20);
            } else {
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 1", 298, 151, 330, 50, $config["text1"], "Proxima-Nova-Semibold", 18, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 2", 298, 175, 330, 50, $config["text2"], "Proxima-Nova-Semibold", 18, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 3", 298, 200, 330, 50, $config["text3"], "Proxima-Nova-Semibold", 18, $config["text3_color"], "center", 20);
            }
        } else if ($config["template"] == 2) {
            if ($isPreview) {
                // Featured
                if ($config["show_featured"] == "on") {
                    $json["layers"][] = $this->bannerService->get_pixel_layer("feature_background", 20, 20, 150, 40, "#DEF7FA", 0, null, 5, [1, 1, 1, 1]);
                    $json["layers"][] = $this->bannerService->get_text_layer("Featured", 20, 25, 150, 20, "Featured", "Roboto", 30, "#000000", "center", 0);
                }
                // Shop Now
                if ($config["show_button"] == "on") {
                    $json["layers"][] = $this->bannerService->get_pixel_layer("button_border", 2699, 149, 402, 102, "#000000", 0, null, 40, [1, 1, 1, 1]);
                    $json["layers"][] = $this->bannerService->get_pixel_layer("button_background", 2700, 150, 400, 100, "#FFFFFF", 0, null, 40, [1, 1, 1, 1]);
                    $json["layers"][] = $this->bannerService->get_text_layer("Shop Now", 2700, 165, 400, 20, "Shop Now", "Normal", 60, "#000000", "center", 0);
                }
            }

            // Burst
            $json["layers"][] = $this->bannerService->get_circle_layer("Burst circle", 435, -37, 117, $config["burst_circle_color"]);
            $burst_texts = $this->bannerService->get_multiline_text($config["burst_text"], "Amazon-Ember", 50, 234);
            if ($config["burst_text"] != "") {
                $top = (234 - 45 * count($burst_texts)) / 2 - 35;
                for ($i = 0; $i < count($burst_texts); $i++) {
                    if (!empty($burst_texts[$i])) {
                        $json["layers"][] = $this->bannerService->get_text_layer("Burst text " . ($i + 1), 435, $top + 45 * $i, 234, 50, array($burst_texts[$i]), "Proxima-Nova-Black", 45, $config["burst_text_color"], "center", 10);
                    }
                }
            }

            $json["layers"][] = $this->bannerService->get_circle_layer("circle1", 647, -90, 234, $config["circle_color"]);
            if ($config["message_options"] == 0 || $config["message_options"] == 1) {
                $json["layers"][] = $this->bannerService->get_text_layer("H1", 642, 26, 468, 50, "ON SALE NOW", "Proxima-Nova-Extrabld", 45, $config["text1_color"], "center", 0);
            } else if ($config["message_options"] == 2) {
                $json["layers"][] = $this->bannerService->get_text_layer("H1", 642, 32, 468, 50, "BUY " . $config["value1"], "Proxima-Nova-Extrabld", 72, $config["text1_color"], "center", 0);
            } else if ($config["message_options"] == 3) {
                $json["layers"][] = $this->bannerService->get_text_layer("H1", 642, 28, 468, 50, "SAVE UP TO", "Proxima-Nova-Extrabld", 45, $config["text1_color"], "center", 0);
            } else if ($config["message_options"] == 4) {
                $json["layers"][] = $this->bannerService->get_text_layer("H1", 652, 55, 468, 50, "ON SALE NOW", "Proxima-Nova-Extrabld", 45, $config["text1_color"], "center", 0);
            }

            if ($config["message_options"] == 0) {
                $w = $this->bannerService->get_font_metrics($config["value1"], "Amazon-Ember", 160)["textWidth"];
                $w += $this->bannerService->get_font_metrics($config["value2"], "Amazon-Ember", 160)["textWidth"];
                $w += $this->bannerService->get_font_metrics("$", "Amazon-Ember", 80)["textWidth"];
                $w += $this->bannerService->get_font_metrics("/", "Amazon-Ember", 170)["textWidth"];
                $l = (470 - $w) / 2 + 15;
                $json["layers"][] = $this->bannerService->get_text_layer("3", $l + 642, 73, 468, 50, $config["value1"], "Proxima-Nova-Black", 160, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("$", $l + 804, 83, 468, 50, "$", "Proxima-Nova-Black", 80, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("/", $l + 741, 68, 468, 50, "/", "Proxima-Nova-Black", 170, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("11", $l + 858, 73, 468, 50, $config["value2"], "Proxima-Nova-Black", 160, $config["text2_color"], "left", 20);
            } else if ($config["message_options"] == 1 || $config["message_options"] == 3) {
                $w = $this->bannerService->get_font_metrics($config["value1"], "Amazon-Ember", 160)["textWidth"];
                $w += $this->bannerService->get_font_metrics($config["value2"], "Amazon-Ember", 80)["textWidth"];
                $w += $this->bannerService->get_font_metrics("$", "Amazon-Ember", 80)["textWidth"];
                $l = (470 - $w) / 2;
                $json["layers"][] = $this->bannerService->get_text_layer("$", $l + 642, 80, 330, 50, "$", "Proxima-Nova-Black", 80, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("5", $l + 692, 72, 392, 50, $config["value1"], "Proxima-Nova-Black", 160, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("99", $l + 792, 82, 392, 50, $config["value2"], "Proxima-Nova-Black", 80, $config["text2_color"], "left", 20);
            } else if ($config["message_options"] == 2) {
                $w = $this->bannerService->get_font_metrics("SAVE", "Amazon-Ember", 110)["textWidth"];
                $w += $this->bannerService->get_font_metrics("$", "Amazon-Ember", 60)["textWidth"];
                $w += $this->bannerService->get_font_metrics($config["value2"], "Amazon-Ember", 110)["textWidth"];
                $l = (470 - $w) / 2 - 10;
                $json["layers"][] = $this->bannerService->get_text_layer("SAVE", $l + 632, 88, 392, 50, "SAVE", "Proxima-Nova-Black", 110, $config["text2_color"], "left", 0);
                $json["layers"][] = $this->bannerService->get_text_layer("$", $l + 934, 92, 392, 50, "$", "Proxima-Nova-Black", 60, $config["text2_color"], "left", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("5", $l + 971, 88, 392, 50, $config["value2"], "Proxima-Nova-Black", 110, $config["text2_color"], "left", 20);
            } else if ($config["message_options"] == 4) {
                $w = $this->bannerService->get_font_metrics("SAVE " . $config["value1"], "Amazon-Ember", 95)["textWidth"];
                $w += $this->bannerService->get_font_metrics("¢", "Amazon-Ember", 60)["textWidth"];
                $l = (470 - $w) / 2 - 8;
                $json["layers"][] = $this->bannerService->get_text_layer("SAVE " . $config["value1"], $l + 642, 94, 392, 50, "SAVE " . $config["value1"], "Proxima-Nova-Black", 95, $config["text2_color"], "left", 0);
                $json["layers"][] = $this->bannerService->get_text_layer("cent", $l + $w + 632, 89, 392, 50, "¢", "Proxima-Nova-Black", 60, $config["text2_color"], "left", 20);
            }

            if ($config["message_options"] == 2) {
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 1", 642, 202, 468, 50, $config["text1"], "Proxima-Nova-Semibold", 27, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 2", 642, 237, 468, 50, $config["text2"], "Proxima-Nova-Semibold", 27, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 3", 642, 272, 468, 50, $config["text3"], "Proxima-Nova-Semibold", 27, $config["text3_color"], "center", 20);
            } else if ($config["message_options"] == 4) {
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 1", 642, 198, 468, 50, $config["text1"], "Proxima-Nova-Semibold", 27, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 2", 642, 233, 468, 50, $config["text2"], "Proxima-Nova-Semibold", 27, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 3", 642, 268, 468, 50, $config["text3"], "Proxima-Nova-Semibold", 27, $config["text3_color"], "center", 20);
            } else {
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 1", 642, 221, 468, 50, $config["text1"], "Proxima-Nova-Semibold", 27, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 2", 642, 256, 468, 50, $config["text2"], "Proxima-Nova-Semibold", 27, $config["text3_color"], "center", 20);
                $json["layers"][] = $this->bannerService->get_text_layer("Product Name Row 3", 642, 291, 468, 50, $config["text3"], "Proxima-Nova-Semibold", 27, $config["text3_color"], "center", 20);
            }
        }

        // Product Images
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

        $r = $config["product_dimensions"]["height"] < $max_height ? $config["product_dimensions"]["height"] / $max_height : 1;
        for ($i = 0; $i < $count; $i++) {
            $w = $products[$i]->getImageWidth() * $r;
            $h = $products[$i]->getImageHeight() * $r;
            $max_height = $max_height * $r;
            if ($r < 1) {
                $products[$i]->scaleImage($w, $h, true);
            }
        }

        $x = $config["product_dimensions"]["left"] + ($config["product_dimensions"]["width"] - $products_width * $r) / 2;
        $margin = 25;
        for ($i = 0; $i < $count; $i++) {
            $y = $config["product_dimensions"]["baseline"] - $products[$i]->getImageHeight();
            $w = $products[$i]->getImageWidth();
            $h = $products[$i]->getImageHeight();
            $json["layers"][] = $this->bannerService->get_smartobject_layer($files[$i]["name"], $product_filenames[$i], $x + intval($config["x_offset"][$i]), $y + intval($config["y_offset"][$i]), round($w * $config["scale"][$i]), round($h * $config["scale"][$i]), intval($config["angle"][$i]), $config["shadow"], null, null, 0);
            $x += $w;
            $x += $margin;
        }

        // Legal
        $legal_texts = $this->bannerService->get_multiline_text($config["legal"], "Amazon-Ember", 16, $config["width"] - 70);
        if ($config["legal"] != "") {
            if ($config["template"] == 0) {
                $json["layers"][] = $this->bannerService->get_pixel_layer("tc_background", 0, $config["height"] - 80, $config["width"], 80, "#E5E5E5");
                for ($i = 0; $i < count($legal_texts); $i++) {
                    if (!empty($legal_texts[$i])) {
                        $json["layers"][] = $this->bannerService->get_text_layer2("Legal " . ($i + 1), 37, $config["height"] - 80 + 25 * $i + 16, $config["width"] - 74, 50, array($legal_texts[$i]), "Proxima-Nova-Regular-It", "Mark Simonson - Proxima Nova Regular Italic.ttf", 16, "#000000", $config["template"] ? "center" : "left", 10);
                    }
                }
            } else if ($config["template"] == 1) {
                $json["layers"][] = $this->bannerService->get_pixel_layer("tc_background", 0, $config["height"] - 20 * count($legal_texts) - 11, $config["width"], 20 * count($legal_texts) + 11, "#E5E5E5");
                for ($i = 0; $i < count($legal_texts); $i++) {
                    if (!empty($legal_texts[$i])) {
                        $json["layers"][] = $this->bannerService->get_text_layer2("Legal " . ($i + 1), 35, $config["height"] - 20 * (count($legal_texts) - $i) - 4, $config["width"] - 70, 50, array($legal_texts[$i]), "Proxima-Nova-Regular-It", "Mark Simonson - Proxima Nova Regular Italic.ttf", 13, "#000000", $config["template"] ? "center" : "left", 10);
                    }
                }
            } else if ($config["template"] == 2) {
                $json["layers"][] = $this->bannerService->get_pixel_layer("tc_background", 0, $config["height"] - 25 * count($legal_texts) - 22, $config["width"], 25 * count($legal_texts) + 22, "#E5E5E5");
                for ($i = 0; $i < count($legal_texts); $i++) {
                    if (!empty($legal_texts[$i])) {
                        $json["layers"][] = $this->bannerService->get_text_layer2("Legal " . ($i + 1), 35, $config["height"] - 25 * (count($legal_texts) - $i) - 11, $config["width"] - 70, 50, array($legal_texts[$i]), "Proxima-Nova-Regular-It", "Mark Simonson - Proxima Nova Regular Italic.ttf", 21.67, "#000000", $config["template"] ? "center" : "left", 10);
                    }
                }
            }
        }

        /* Background */
        // if ($config["background_type"] == "solid") {
        //     array_unshift($json["layers"], $this->bannerService->get_pixel_layer("Background", 0, 0, $config["width"], $config["height"], $config["background_color"]));
        // } else if ($config["background_type"] == "upload") {
        //     if (!empty($config["background"])) {
        //         $background_layer = new \Imagick();
        //         $background_layer->readImage($config["background"]);
        //         $background_layer->scaleImage($config["width"], $config["height"], true);
        //         $background_layer->writeImage($config["background"]);
        //         $image_width = $background_layer->getImageWidth();
        //         $image_height = $background_layer->getImageHeight();
        //         array_unshift($json["layers"], $this->bannerService->get_smartobject_layer("Background", $config["background"], ($config["width"] - $image_width) / 2, ($config["height"] - $image_height) / 2, $image_width, $image_height, 0));
        //     }
        // } else if ($config["background_type"] == "gradient") {
        //     $gradient = new \Imagick();
        //     $gradient->newPseudoImage($config["width"], $config["height"], 'gradient:'.$config["g_start_color"].'-'.$config["g_end_color"]);
        //     $gradient->setImageFormat('png');
        //     $gradient->writeImage("background.png");
        //     array_unshift($json["layers"], $this->bannerService->get_smartobject_layer("Background", "background.png", 0, 0, $config["width"], $config["height"], 0));
        // }

        if (!empty($config["background"])) {
            array_unshift($json["layers"], $this->bannerService->get_smartobject_layer("Background", $config["background"], 0, 0, $config["width"], $config["height"], 0));
        } else {
            array_unshift($json["layers"], $this->bannerService->get_pixel_layer("Background", 0, 0, $config["width"], $config["height"], $config["background_color"]));
        }

        return base64_encode(json_encode($json));
    }

    private function get_history_settings($request)
    {
        $theme = $this->themeService->getById(intval($request->theme));
        $settings = array(
            "customer" => $request->customer,
            "output_dimensions" => $request->output_dimensions,
            "project_name" => $request->project_name,
            "file_ids" => $request->file_ids,
            "value1" => $request->value1,
            "value2" => $request->value2,
            "text1" => $request->text1,
            "text2" => $request->text2,
            "text3" => $request->text3,
            "x_offset" => $request->x_offset,
            "y_offset" => $request->y_offset,
            "angle" => $request->angle,
            "scale" => $request->scale,
            "theme" => strtolower($theme->name),
            "message_options" => $request->message_options,
            "circle_text_color" => $request->circle_text_color,
            "circle_color" => $request->circle_color,
            "text1_color" => $request->text1_color,
            "text2_color" => $request->text2_color,
            "burst_text" => $request->burst_text,
            "burst_color" => $request->burst_color,
            "burst_circle_color" => $request->burst_circle_color,
            "burst_text_color" => $request->burst_text_color,
            "legal" => $request->legal,
            "show_featured" => $request->show_featured,
            "show_button" => $request->show_button,
            "background" => $request->background,
            "export_all" => $request->export_all,
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
                $this->bannerService->save_project([
                    'name' => $request->project_name,
                    'customer' => $request->customer,
                    'output_dimensions' => $request->output_dimensions,
                    'projectname' => $request->project_name,
                    'url' => '',
                    'fileid' => $request->file_ids,
                    'headline' => "Kroger",
                    'size' => config("templates.Kroger.output_dimensions")[$request->output_dimensions],
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
                if ($request->proof_sheet) {
                    $templates = [0, 1, 2];
                    $jpegs = [];
                    foreach ($templates as $template) {
                        $config = $this->get_config($request, $template);

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
                        $jpegs[] = $jpeg_file;
                    }
                    $pdf_file = uniqid() . ".pdf";
                    $pdf = App::make("dompdf.wrapper");

                    $company = Company::find(auth()->user()->company_id);
                    dd($company);
                    exit;
                    $pdf->loadHTML($this->generate($company->name, $request->project_name, $jpegs));
                    $pdf->save($pdf_file);
                    
                    Storage::disk('s3')->put('outputs/' . $pdf_file, file_get_contents(public_path($pdf_file)));
                    $response = [
                        "url" => Storage::disk('s3')->temporaryUrl('outputs/' . $pdf_file, now()->addHours(1), [
                            'ResponseContentDisposition' => 'attachment; filename="' . $zip_filename . '.pdf"'
                        ]),
                        "projectname" => $request->project_name,
                        "log" => ''
                    ];
                } else {
                    $zip = new ZipArchive();
                    $log = "";
                    if ($zip->open(public_path($zip_file), ZipArchive::CREATE) === TRUE) {
                        $output_jpg_files = array();
                        $output_psd_files = array();
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
                                'fileid' => $request->file_ids,
                                'headline' => "Kroger",
                                'size' => config("templates.Kroger.output_dimensions")[$request->output_dimensions],
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
                                'fileid' => $request->file_ids,
                                'headline' => "Kroger",
                                'size' => config("templates.Kroger.output_dimensions")[$request->output_dimensions],
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
