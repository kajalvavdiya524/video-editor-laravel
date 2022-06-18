<?php

namespace App\Domains\Auth\Services\Templates;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use App\Domains\Auth\Services\BannerService;
use App\Domains\Auth\Services\ThemeService;
use App\Domains\Auth\Models\Setting;
use Exception;
use ZipArchive;

/**
 * Class WalmartTemplateService.
 */
class WalmartTemplateService extends BaseService
{
    protected $bannerService;
    protected $themeService;

    /**
     * WalmartTemplateService constructor.
     *
     */
    public function __construct(BannerService $bannerService, ThemeService $themeService)
    {
        $this->bannerService = $bannerService;
        $this->themeService = $themeService;
    }

    private function get_config($request, $template)
    {
        $config = array();
        // $template = $request->output_dimensions;
        $config["customer"] = $request->customer;
        $config["template"] = $template;
        $config["width"] = config("templates.Walmart.width")[$template];
        $config["height"] = config("templates.Walmart.height")[$template];
        $config["product_dimensions"] = config("templates.Walmart.product_dimensions")[$template];
        $config["theme"] = $request->theme;
        $config["x_offset"] = $request->x_offset;
        $config["y_offset"] = $request->y_offset;
        $config["angle"] = $request->angle;
        $config["scale"] = $request->scale;
        $config["show_stroke"] = $request->show_stroke;

        $config["headline"] = array();
        if ($request->headline1 != "") {
            array_push($config["headline"], $request->headline1);
        }
        if ($request->headline2 != "") {
            array_push($config["headline"], $request->headline2);
        }
        $config["subheadline"] = array();
        if ($request->subheadline1 != "") {
            array_push($config["subheadline"], $request->subheadline1);
        }
        if ($request->subheadline2 != "") {
            array_push($config["subheadline"], $request->subheadline2);
        }
        $config["cta"] = $request->cta;

        // Shadow
        $config["shadow"] = null;
        $theme = $this->themeService->getById(intval($request->theme));
        if (isset($theme)) {
            $attributes = json_decode($theme->attributes);
            $shadow_attrs = $attributes[0]->list;
            if (isset($shadow_attrs[0])) {
                $shadow = $shadow_attrs[0]->list;
                $config["shadow"] = array(
                    "opacity" => intval($shadow[0]->value),
                    "angle" => intval($shadow[1]->value),
                    "distance" => intval($shadow[2]->value),
                    "spread" => intval($shadow[3]->value),
                    "size" => intval($shadow[4]->value)
                );
            }
        }
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
        $output_dimension_str = config("templates.Walmart.output_dimensions")[$template];
        $filename = (!empty($project_name) ? $project_name : "output_") . $output_dimension_str;
        return $filename;
    }


    private function get_psd($files, $product_filenames, $config)
    {
        $json = array(
            "width" => $config["width"],
            "height" => $config["height"],
            "layers" => array()
        );

        if ($config["template"] == 0) {
            // Headline
            // $headline_top = $config["product_dimensions"]["top"] + (count($config["subheadline"]) ? 0 : 20);
            $headline_top = $config["product_dimensions"]["top"];
            for ($i = 0; $i < count($config["headline"]); $i++) {
                $text_arr = $this->bannerService->get_multiline_text($config["headline"][$i], "Bogle-Regular", 24, 130);
                if ($config["headline"][$i] != "") {
                    for ($j = 0; $j < count($text_arr); $j++) {
                        if (!empty($text_arr[$j])) {
                            $json["layers"][] = $this->bannerService->get_text_layer2("headline" . ($i + 1) . ($j + 1), 0, $headline_top, $config["product_dimensions"]["width"], 50, $text_arr[$j], "Bogle-Regular", "Bogle-Regular.ttf", 24, "#2f2f2f", "center", 0);
                            $headline_top += 26;
                        }
                    }
                }
            }
            // Subheadline
            $subheadline_top = $headline_top + 10;
            for ($i = 0; $i < count($config["subheadline"]); $i++) {
                $json["layers"][] = $this->bannerService->get_text_layer2("subheadline" . ($i + 1), 0, $subheadline_top + 16 * $i, $config["product_dimensions"]["width"], 50, $config["subheadline"][$i], "Bogle-Regular", "Bogle-Regular.ttf", 14, "#2f2f2f", "center", 0);
            }
            // Product Image
            $count = count($files);
            $count = $count > 3 ? 3 : $count;
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
    
            $x = [40, 100, -5];
            $y = [210, 250, 300];
            for ($i = 0; $i < $count; $i++) {
                $w = $products[$i]->getImageWidth();
                $h = $products[$i]->getImageHeight();
                $json["layers"][] = $this->bannerService->get_smartobject_layer($files[$i]["name"], $product_filenames[$i], $x[$i] + intval($config["x_offset"][$i]), $y[$i] + intval($config["y_offset"][$i]), round($w * $config["scale"][$i]), round($h * $config["scale"][$i]), intval($config["angle"][$i]), $config["shadow"], null, null, 0);
            }

            // CTA
            $json["layers"][] = $this->bannerService->get_pixel_layer("cta_background", ($config["width"] - 84) / 2, $config["product_dimensions"]["baseline"] - 50, 84, 20, "#0070dc", 0, null, 10, [1, 1, 1, 1]);
            $json["layers"][] = $this->bannerService->get_text_layer2("cta_text", ($config["width"] - 84) / 2, $config["product_dimensions"]["baseline"] - 49, 84, 20, $config["cta"], "Bogle-Bold", "Bogle-Bold.ttf", 13, "#ffffff", "center", 0);
            
            // Logo
            $json["layers"][] = $this->bannerService->get_smartobject_layer("logo", "img/backgrounds/Walmart/walmart_burst.png", ($config["width"] - 80) / 2 + 4, $config["product_dimensions"]["baseline"] - 23, 80, 19);
        } else if ($config["template"] == 1) {
            $headline_height = 0;
            $text_array = [];
            for ($i = 0; $i < count($config["headline"]); $i++) {
                $text_array[] = $this->bannerService->get_multiline_text($config["headline"][$i], "Bogle-Regular", 24, 220);
                if ($config["headline"][$i] != "") {
                    for ($j = 0; $j < count($text_array[$i]); $j++) {
                        if (!empty($text_array[$i][$j])) {
                            $headline_height += 26;
                        }
                    }
                }
            }

            $subheadline_height = 0;
            for ($i = 0; $i < count($config["subheadline"]); $i++) {
                if ($config["subheadline"] != "") {
                    $subheadline_height += 16;
                }
            }

            $total_height = $headline_height + $subheadline_height + 77;
            $top = ($config["height"] - $total_height) / 2;
            // Headline
            for ($i = 0; $i < count($text_array); $i++) {
                for ($j = 0; $j < count($text_array[$i]); $j++) {
                    if ($text_array[$i][$j] != "") {
                        $json["layers"][] = $this->bannerService->get_text_layer2("headline" . ($i + 1) . ($j + 1), $config["product_dimensions"]["left"], $top, $config["product_dimensions"]["width"], 50, $text_array[$i][$j], "Bogle-Regular", "Bogle-Regular.ttf", 24, "#2f2f2f", "left", 0);
                        $top += 26;
                    }
                }
            }
            // Subheadline
            $top = $top + 8;
            for ($i = 0; $i < count($config["subheadline"]); $i++) {
                if ($config["subheadline"][$i] != "") {
                    $json["layers"][] = $this->bannerService->get_text_layer2("subheadline" . ($i + 1), $config["product_dimensions"]["left"], $top, $config["product_dimensions"]["width"], 50, $config["subheadline"][$i], "Bogle-Regular", "Bogle-Regular.ttf", 14, "#2f2f2f", "left", 0);
                    $top += 16;
                }
            }
            // Product Image
            $count = count($files);
            $count = $count > 3 ? 3 : $count;
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
    
            $x = [230, 128, 180];
            $y = [0, 105, 90];
            for ($i = 0; $i < $count; $i++) {
                $w = $products[$i]->getImageWidth();
                $h = $products[$i]->getImageHeight();
                $index = $config["template"] * 3 + $i;
                $json["layers"][] = $this->bannerService->get_smartobject_layer($files[$i]["name"], $product_filenames[$i], $x[$i] + intval($config["x_offset"][$index]), $y[$i] + intval($config["y_offset"][$index]), round($w * $config["scale"][$index]), round($h * $config["scale"][$index]), intval($config["angle"][$index]), $config["shadow"], null, null, 0);
            }

            // CTA
            $top = $top + 17;
            $json["layers"][] = $this->bannerService->get_pixel_layer("cta_background", $config["product_dimensions"]["left"], $top, 84, 20, "#0070dc", 0, null, 10, [1, 1, 1, 1]);
            $json["layers"][] = $this->bannerService->get_text_layer2("cta_text", $config["product_dimensions"]["left"], $top + 1, 84, 20, $config["cta"], "Bogle-Bold", "Bogle-Bold.ttf", 13, "#ffffff", "center", 0);
            
            // Logo
            $top = $top + 27;
            $json["layers"][] = $this->bannerService->get_smartobject_layer("logo", "img/backgrounds/Walmart/walmart_burst.png", $config["product_dimensions"]["left"] + 4, $top, 80, 19);
        } else if ($config["template"] == 2) {
            // Headline
            $headline_top = $config["product_dimensions"]["top"];
            // if (count($config["subheadline"]) == 0) {
            //     $headline_top += 20;
            // }
            for ($i = 0; $i < count($config["headline"]); $i++) {
                $text_arr = $this->bannerService->get_multiline_text($config["headline"][$i], "Bogle-Regular", 36, 250);
                if ($config["headline"][$i] != "") {
                    for ($j = 0; $j < count($text_arr); $j++) {
                        if (!empty($text_arr[$j])) {
                            $json["layers"][] = $this->bannerService->get_text_layer2("headline" . ($i + 1) . ($j + 1), 0, $headline_top, $config["width"], 50, $text_arr[$j], "Bogle-Regular", "Bogle-Regular.ttf", 36, "#2f2f2f", "center", 0);
                            $headline_top += 37;
                        }
                    }
                }
            }
            // Subheadline
            $subheadline_top = $headline_top + 10;
            for ($i = 0; $i < count($config["subheadline"]); $i++) {
                $json["layers"][] = $this->bannerService->get_text_layer2("subheadline" . ($i + 1), 0, $subheadline_top + 22 * $i, $config["width"], 50, $config["subheadline"][$i], "Bogle-Regular", "Bogle-Regular.ttf", 21, "#2f2f2f", "center", 0);
            }
            // Product Image
            $count = count($files);
            $count = $count > 3 ? 3 : $count;
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
    
            $x = [70, 0, 120];
            $y = [220, 325, 250];
            for ($i = 0; $i < $count; $i++) {
                $w = $products[$i]->getImageWidth();
                $h = $products[$i]->getImageHeight();
                $index = $config["template"] * 3 + $i;
                $json["layers"][] = $this->bannerService->get_smartobject_layer($files[$i]["name"], $product_filenames[$i], $x[$i] + intval($config["x_offset"][$index]), $y[$i] + intval($config["y_offset"][$index]), round($w * $config["scale"][$index]), round($h * $config["scale"][$index]), intval($config["angle"][$index]), $config["shadow"], null, null, 0);
            }

            // CTA
            $json["layers"][] = $this->bannerService->get_pixel_layer("cta_background", ($config["width"] - 84) / 2, $config["product_dimensions"]["baseline"] - 50, 84, 20, "#0070dc", 0, null, 10, [1, 1, 1, 1]);
            $json["layers"][] = $this->bannerService->get_text_layer2("cta_text", ($config["width"] - 84) / 2, $config["product_dimensions"]["baseline"] - 49, 84, 20, $config["cta"], "Bogle-Bold", "Bogle-Bold.ttf", 13, "#ffffff", "center", 0);
            
            // Logo
            $json["layers"][] = $this->bannerService->get_smartobject_layer("logo", "img/backgrounds/Walmart/walmart_burst.png", ($config["width"] - 84) / 2 + 4, $config["product_dimensions"]["baseline"] - 23, 80, 19);
        } else if ($config["template"] == 3) {
            // Headline
            $total_height = 0;
            $text_array = [];
            for ($i = 0; $i < count($config["headline"]); $i++) {
                $text_array[] = $this->bannerService->get_multiline_text($config["headline"][$i], "Bogle-Regular", 18, 220);
                if ($config["headline"][$i] != "") {
                    for ($j = 0; $j < count($text_array[$i]); $j++) {
                        if (!empty($text_array[$i][$j])) {
                            $total_height += 18;
                        }
                    }
                }
            }
            $top = ($config["height"] - $total_height) / 2 - 3;
            // Headline
            for ($i = 0; $i < count($text_array); $i++) {
                for ($j = 0; $j < count($text_array[$i]); $j++) {
                    if ($text_array[$i][$j] != "") {
                        $json["layers"][] = $this->bannerService->get_text_layer2("headline" . ($i + 1) . ($j + 1), 65, $top, $config["product_dimensions"]["width"], 50, $text_array[$i][$j], "Bogle-Regular", "Bogle-Regular.ttf", 17, "#2f2f2f", "left", 0);
                        $top += 18;
                    }
                }
            }
            // Product Image
            $count = count($files);
            $count = $count > 3 ? 3 : $count;
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
                    $products[$i]->scaleImage($w, 0);
                }
            }
    
            $x = [145, 222, 245];
            $y = [-20, 20, 0];
            for ($i = 0; $i < $count; $i++) {
                $w = $products[$i]->getImageWidth();
                $h = $products[$i]->getImageHeight();
                $index = $config["template"] * 3 + $i;
                $json["layers"][] = $this->bannerService->get_smartobject_layer($files[$i]["name"], $product_filenames[$i], $x[$i] + intval($config["x_offset"][$index]), $y[$i] + intval($config["y_offset"][$index]), round($w * $config["scale"][$index]), round($h * $config["scale"][$index]), intval($config["angle"][$index]), $config["shadow"], null, null, 0);
            }

            // CTA
            $logo = new \Imagick("img/backgrounds/Walmart/arrow.png");
            $logo->scaleImage(25, null);
            $w = $logo->getImageWidth();
            $h = $logo->getImageHeight();
            $json["layers"][] = $this->bannerService->get_smartobject_layer("arrow", "img/backgrounds/Walmart/arrow.png", $config["width"] - 15 - $w, ($config["height"] - $h) / 2, $w, $h);
            
            // Logo
            $logo = new \Imagick("img/backgrounds/Walmart/burst.png");
            $logo->scaleImage(35, null);
            $w = $logo->getImageWidth();
            $h = $logo->getImageHeight();
            $json["layers"][] = $this->bannerService->get_smartobject_layer("logo", "img/backgrounds/Walmart/burst.png", 15, ($config["height"] - $h) / 2, $w, $h);
        } else if ($config["template"] == 4) {
            $total_height = 0;
            $text_array = [];
            for ($i = 0; $i < count($config["headline"]); $i++) {
                $text_array[] = $this->bannerService->get_multiline_text($config["headline"][$i], "Bogle-Regular", 24, 220);
                if ($config["headline"][$i] != "") {
                    for ($j = 0; $j < count($text_array[$i]); $j++) {
                        if (!empty($text_array[$i][$j])) {
                            $total_height += 26;
                        }
                    }
                }
            }
            for ($i = 0; $i < count($config["subheadline"]); $i++) {
                if ($config["subheadline"] != "") {
                    $total_height += 16;
                }
            }
            $top = ($config["height"] - $total_height) / 2 - 4;
            // Headline
            for ($i = 0; $i < count($text_array); $i++) {
                for ($j = 0; $j < count($text_array[$i]); $j++) {
                    if ($text_array[$i][$j] != "") {
                        $json["layers"][] = $this->bannerService->get_text_layer2("headline" . ($i + 1) . ($j + 1), 119, $top, $config["product_dimensions"]["width"], 50, $text_array[$i][$j], "Bogle-Regular", "Bogle-Regular.ttf", 24, "#2f2f2f", "left", 0);
                        $top += 26;
                    }
                }
            }
            // Subheadline
            $top = $top + 8;
            for ($i = 0; $i < count($config["subheadline"]); $i++) {
                if ($config["subheadline"][$i] != "") {
                    $json["layers"][] = $this->bannerService->get_text_layer2("subheadline" . ($i + 1), 119, $top, $config["product_dimensions"]["width"], 50, $config["subheadline"][$i], "Bogle-Regular", "Bogle-Regular.ttf", 14, "#2f2f2f", "left", 0);
                    $top += 16;
                }
            }
            
            // Product Image
            $count = count($files);
            $count = $count > 3 ? 3 : $count;
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
    
            $x = [333, 480, 530];
            $y = [-15, 45, 0];
            for ($i = 0; $i < $count; $i++) {
                $w = $products[$i]->getImageWidth();
                $h = $products[$i]->getImageHeight();
                $index = $config["template"] * 3 + $i;
                $json["layers"][] = $this->bannerService->get_smartobject_layer($files[$i]["name"], $product_filenames[$i], $x[$i] + intval($config["x_offset"][$index]), $y[$i] + intval($config["y_offset"][$index]), round($w * $config["scale"][$index]), round($h * $config["scale"][$index]), intval($config["angle"][$index]), $config["shadow"], null, null, 0);
            }

            // CTA
            $json["layers"][] = $this->bannerService->get_pixel_layer("cta_background", $config["width"] - 117, ($config["height"] - 25) / 2, 90, 25, "#0070dc", 0, null, 13, [1, 1, 1, 1]);
            $json["layers"][] = $this->bannerService->get_text_layer2("cta_text", $config["width"] - 117, ($config["height"] - 20) / 2, 90, 25, $config["cta"], "Bogle-Bold", "Bogle-Bold.ttf", 13, "#ffffff", "center", 0);
            
            // Logo
            $logo = new \Imagick("img/backgrounds/Walmart/burst.png");
            $logo->scaleImage(65, null);
            $w = $logo->getImageWidth();
            $h = $logo->getImageHeight();
            $json["layers"][] = $this->bannerService->get_smartobject_layer("logo", "img/backgrounds/Walmart/burst.png", 27, ($config["height"] - $h) / 2, $w, $h);
        }

        if (!empty($config["background"])) {
            $background = new \Imagick($config["background"]);
            $w = $background->getImageWidth();
            $h = $background->getImageHeight();
            array_unshift($json["layers"], $this->bannerService->get_smartobject_layer("Background", $config["background"], 0, 0, $w, $h, 0));
        } else {
            array_unshift($json["layers"], $this->bannerService->get_pixel_layer("Background", 0, 0, $config["width"], $config["height"], "#f4f4f4"));
        }
        if (isset($config["show_stroke"])) {
            $json["layers"][] = $this->bannerService->get_pixel_layer("Stroke", 0, 0, $config["width"] + 1, $config["height"] + 1, null, 1, "#6d6d6d");
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
            "theme" => strtolower($theme->name),
            "file_ids" => $request->file_ids,
            "headline1" => $request->headline1,
            "headline2" => $request->headline2,
            "subheadline1" => $request->subheadline1,
            "subheadline2" => $request->subheadline2,
            "cta" => $request->cta,
            "x_offset" => $request->x_offset,
            "y_offset" => $request->y_offset,
            "angle" => $request->angle,
            "scale" => $request->scale,
            "background" => $request->background,
            "export_all" => $request->export_all,
            "include_psd" => $request->include_psd,
            "type" => $request->type,
            "product_texts" => $request->product_texts
        );

        return json_encode($settings);
    }

    public function run($request, $preview = false, $save = false, $publish = false)
    {
        // Map files from UTC/GTIN/ASIN/TCIN/WMT-ID
        $input_files = array_map('trim', explode(",", $request->file_ids));
        
        $results = [];
        $fileIds = explode(" ", $input_files[$request->output_dimensions]);
        $fileIds = array_filter($fileIds);
        $results[] = $this->bannerService->map_files($fileIds);

        if ($results[0]["status"] == "error") {
            return $results[0];
        }
        if ($request->export_all == 'on') {
            for ($i = 0; $i < 5; $i++) {
                if ($i != $request->output_dimensions) {
                    $t = $this->bannerService->map_files(explode(" ", $input_files[$i]));
                    if ($t["status"] == "error") {
                        return $t;
                    }
                    $results[] = $t;
                }
            }
        }

        // Get trimmed product image files
        $temp_files = [];
        $product_filenames = [];
        foreach ($results as $result) {
            $pf = [];
            foreach ($result["files"] as $file) {
                $filename = uniqid() . ".png";
                $contents = Storage::disk('s3')->get($file["path"]);
                Storage::disk('public')->put($filename, $contents);
                $pf[] = $filename;
                $temp_files[] = $filename;
            }
            $product_filenames[] = $pf;
        }

        $response = [];
        $jpeg_file = "";

        if ($preview) {
            $response = ["files" => [], "log" => []];
            $templates = [];
            $templates[] = $request->output_dimensions;
            if ($request->export_all == "on") {
                for ($i = 0; $i < 5; $i++) {
                    if ($i != $request->output_dimensions) {
                        $templates[] = $i;
                    }
                }
            }

            for ($i = 0; $i < count($templates); $i++) {
                $config = $this->get_config($request, $templates[$i]);
                $jpeg_file_id = uniqid();
                $jpeg_file = $jpeg_file_id . ".jpg";

                $input_arg = $this->get_psd($results[$i]["files"], $product_filenames[$i], $config);
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
                    $templates[] = $request->output_dimensions;
                    if ($request->export_all == "on") {
                        for ($i = 0; $i < 5; $i++) {
                            if ($i != $request->output_dimensions) {
                                $templates[] = $i;
                            }
                        }
                    }

                    for ($i = 0; $i < count($templates); $i++) {
                        $config = $this->get_config($request, $templates[$i]);
                        $output_filename = $this->get_output_filename($request, $templates[$i]);

                        $psd_file_id = uniqid();
                        $psd_file = $psd_file_id . ".psd";
                        $jpeg_file_id = uniqid();
                        $jpeg_file = $jpeg_file_id . ".jpg";

                        $input_arg = $this->get_psd($results[$i]["files"], $product_filenames[$i], $config);
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
                            'headline' => "Walmart",
                            'size' => config("templates.Walmart.output_dimensions")[$request->output_dimensions],
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
                            'headline' => "Walmart",
                            'size' => config("templates.Walmart.output_dimensions")[$request->output_dimensions],
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
