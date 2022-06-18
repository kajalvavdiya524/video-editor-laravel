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
 * Class InstagramTemplateService.
 */
class InstagramTemplateService extends BaseService
{
    protected $bannerService;

    /**
     * InstagramTemplateService constructor.
     *
     */
    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    public function get_config($request, $background_filename = null, $button_filename = null, $logo_filename = null)
    {
        $config = array();
        $template = $request->output_dimensions;
        $config["customer"] = $request->customer;
        $config["template"] = $template;
        $config["width"] = config("templates.Instagram.width")[$template];
        $config["height"] = config("templates.Instagram.height")[$template];
        $config["product_dimensions"] = config("templates.Instagram.product_dimensions")[$template];

        if ($template < 3) {
            $config["product_layouts"] = $request->product_layouts;
            $config["headline"] = $request->headline;
            $config["multi_headline"] = $request->multi_headline;
            $config["headline_font"] = $request->headline_font;
            $config["headline_font_size"] = $request->headline_font_size;
            $config["subheadline_font_size"] = $request->subheadline_font_size;
            $config["headline_color"] = $request->headline_color;
            $config["headline_alignment"] = $request->headline_alignment;
            $config["headline_pos"] = config("templates.Instagram.headline_pos")[$template];
            $config["headline_space"] = config("templates.Instagram.headline_space")[$template];
            $config["subheadline"] = $request->subheadline;
            $config["subheadline_font"] = $request->subheadline_font;
            $config["subheadline_color"] = $request->subheadline_color;
            $config["subheadline_alignment"] = $request->subheadline_alignment;
            $config["subheadline_pos"] = config("templates.Instagram.subheadline_pos")[$template];
            $config["subheadline_space"] = config("templates.Instagram.subheadline_space")[$template];

            $config["CTA"] = $request->CTA;
            $config["CTA_font"] = $request->CTA_font;
            $config["CTA_font_size"] = $request->CTA_font_size;
            $config["CTA_color"] = $request->CTA_color;
            $config["CTA_pos"] = config("templates.Instagram.CTA_pos")[$template];

            $config["CTA_alignment"] = $request->CTA_alignment;
            $config["CTA_opaque"] = $request->CTA_opaque;
            $config["CTA_border_width"] = $request->CTA_border_width;
            $config["CTA_border_color"] = $request->CTA_border_color;
            $config["CTA_border_radius"] = $request->CTA_border_radius;
            $config["CTA_border_padding"] = $request->CTA_border_padding;
            $config["CTA_space"] = config("templates.Instagram.CTA_space")[$template];

            $config["product_space"] = $request->product_space;
            $config["product_layering"] = $request->product_layering;
            $config["background_color"] = $request->background_color;
            $config["drop_shadow"] = "none";
            $config["image_shadow"] = null;
            $config["fade"] = null;

            $config["drop_shadow"] = null;
            $config["text_tracking"] = isset($request->text_tracking) ? $request->text_tracking : 0;
            $config["product_custom_layering"] = $request->product_custom_layering;

            $config["background"] = $background_filename;
            $config["logo"] = $logo_filename;
            $config["logo_width"] = config("templates.Instagram.logo_width")[$template];
            $config["button"] = $button_filename;
            $config["button_space"] = config("templates.Instagram.button_space")[$template];
            $config["image_shadow"] = $request->image_shadow == "on" ? "bottom" : null;
            $config["fade"] = $request->fade == "on" ? 1 : 0;
            $config["border"] = $request->border;
            $config["border_color"] = $request->border_color;
            $config["compress_size"] = $request->compress_size;
        } else {
            $config["duration"] = $request->duration;
            $config["fade_type"] = $request->fade_type;
            $config["headlineData"] = json_decode($request->headlineData, true);
        }

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

    private function get_ffmpeg($files, $product_filenames, $config)
    {
        $headlineData = $config["headlineData"];
        $top_baseline = $config["height"] / 12;
        $bottom_baseline = $config["height"] * 11/12;
        $count = count($files);
        $script = "ffmpeg -y ";
        for ($i = 0; $i < $count; $i ++) {
            $script = $script."-loop 1 -t ".$config["duration"]." -i ".$product_filenames[$i]." ";
        }
        $script = $script." -filter_complex \"";
        for ($i = 0; $i < $count; $i ++) {
            $script = $script."[".$i."]"."scale=".$config["width"].":".($config["height"] * 2/3).":force_original_aspect_ratio=decrease,pad=".$config["width"].":".$config["height"].":(ow-iw)/2:(oh-ih)/2:color=white@1,format=yuva444p";
            if (isset($headlineData[$i])) {
                $data = $headlineData[$i];
                if ($headlineData[$i]["use_prev_text"]) {
                    $data = $headlineData[$i-1];
                }
                $script = $script.",drawtext=text='".$data["top_headline"]."':fontcolor='".$data["top_head_color"]."':fontsize=".$data["top_head_size"].": x=(w-text_w)/2: y=".$top_baseline."-text_h-10";
                $script = $script.",drawtext=text='".$data["top_subheadline"]."':fontcolor='".$data["top_subhead_color"]."':fontsize=".$data["top_subhead_size"].": x=(w-text_w)/2: y=".$top_baseline;
                $script = $script.",drawtext=text='".$data["bottom_headline"]."':fontcolor='".$data["bottom_head_color"]."':fontsize=".$data["bottom_head_size"].": x=(w-text_w)/2: y=".$bottom_baseline."-text_h-10";
                $script = $script.",drawtext=text='".$data["bottom_subheadline"]."':fontcolor='".$data["bottom_subhead_color"]."':fontsize=".$data["bottom_subhead_size"].": x=(w-text_w)/2: y=".$bottom_baseline;
            }
            if ($config["fade_type"] == "dissolve") {
                $script = $script.",fade=d=1:t=in:alpha=1";
            }
            if ($i == 0) {
                $script = $script.",setpts=PTS-STARTPTS/TB[f0]; ";
            } else {
                $script = $script.",setpts=PTS-STARTPTS+".($i*$config["duration"])."/TB[f".$i."]; ";
            }
        }
        for ($i = 0; $i < $count - 2; $i ++) {
            if ($i == 0) {
                $script = $script."[f0][f1]overlay[bg1]; ";
            } else {
                $script = $script."[bg".$i."][f".($i+1)."]overlay[bg".($i+1)."]; ";
            }
        }
        if ($count < 2) {
            $script = $script."[f0]concat=n=1:v=1:a=0";
        } else if ($count == 2) {
            $script = $script."[f0][f1]overlay";
        } else {
            $script = $script."[bg".($count-2)."][f".($count-1)."]overlay";
        }
        $script = $script.",format=yuv420p[v]\" -map \"[v]\" -movflags +faststart ";
        return $script;
    }

    public function get_psd($files, $product_filenames, $config)
    {
        $json = array(
            "width" => $config["width"],
            "height" => $config["height"],
            "layers" => array()
        );

        if ($config["product_layouts"] == 0) {
            /* Product */
            $count = count($files);
            $products = array();
            $sum_width = 0;
            $max_height = 0;
            $products_width = $config["product_dimensions"]["width"] - $config["product_space"] * ( $count - 1 );
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

            $max_width_product_index = 0;
            $r = $config["product_dimensions"]["height"] < $max_height ? $config["product_dimensions"]["height"] / $max_height : 1;
            for ($i = 0; $i < $count; $i++) {
                $w = $products[$i]->getImageWidth() * $r;
                $h = $products[$i]->getImageHeight() * $r;
                $max_height = $max_height * $r;
                if ($r < 1) {
                    $products[$i] ->scaleImage($w, $h, true);
                }

                if ($w >= $products[$max_width_product_index]->getImageWidth()) {
                    $max_width_product_index = $i;
                }
            }

            $product_side_orders = range(0, $count - 1);
            if ($config["product_layering"] == "2 Images - Larger Item Back-Right" || $config["product_layering"] == "3 Images - Largest Item Middle") {
                unset($product_side_orders[$max_width_product_index]);
                array_splice($product_side_orders, 1, 0, array($max_width_product_index));
            }

            $x = $config["product_dimensions"]["left"] + ($config["product_dimensions"]["width"] - ($products_width * $r + $config["product_space"] * ($count - 1))) / 2;

            for ($j = 0; $j < $count; $j++) {
                $i = $product_side_orders[$j];
                $margin = ($i != 0 ? $config["product_space"] : 0);
                $x += $margin;
                $y = $config["product_dimensions"]["baseline"] - $products[$i]->getImageHeight();
                $w = $products[$i]->getImageWidth();
                $h = $products[$i]->getImageHeight();
                $product_layers[] = $this->bannerService->get_smartobject_layer($files[$i]["name"], $product_filenames[$i], $x + $margin, $y, $w, $h, 0, null, $config["image_shadow"], $config["fade"], 0);
                $x += $w;
            }

            $product_layer_orders = range(0, $count - 1);
            if ($config["product_layering"] == "Front To Back" || $config["product_layering"] == "2 Images - Larger Item Back-Right") {
                $product_layer_orders = array_reverse($product_layer_orders);
            } else if ($config["product_layering"] == "Middle In Front") {
                $product_layer_orders = array(0, 2, 1);
            } else if ($config["product_layering"] == "Custom") {
                $product_layer_orders = array_map("intval", explode(" ", $config["product_custom_layering"]));
            }

            for ($j = 0; $j < $count; $j++) {
                $i = $product_layer_orders[$j] - ($config["product_layering"] == "Custom" ? 1 : 0);
                $json["layers"][] = $product_layers[$i];
            }

            /* Headline & Subheadline & Logo & Button */
            $y_pos = $config["headline_pos"]["top"] + 10;
            $x_pos = $config["headline_pos"]["left"];
            $headlines = $config["headline"];
            if ($config["multi_headline"]) {
                for ($i = 0; $i < count($headlines); $i++) {
                    if (!empty($headlines[$i])) {
                        $json["layers"][] = $this->bannerService->get_text_layer("Headline ".($i + 1), $x_pos, $y_pos, $config["headline_pos"]["right"] - $x_pos, $config["headline_font_size"][$i], array($headlines[$i]), $config["headline_font"][$i], $config["headline_font_size"][$i], $config["headline_color"][$i], $config["headline_alignment"][$i], $config["text_tracking"]);
                        $y_pos += $config["headline_font_size"][$i];
                    }
                }
            } else {
                if (!empty($config["headline"][0])) {
                    $headlines = array($config["headline"][0]);
                    $json["layers"][] = $this->bannerService->get_text_layer("Headline", $config["headline_pos"]["left"], $y_pos, $config["headline_pos"]["right"] - $config["headline_pos"]["left"], $config["headline_pos"]["bottom"] - $y_pos, $headlines, $config["headline_font"][0], $config["headline_font_size"][0], $config["headline_color"][0], $config["headline_alignment"][0], $config["text_tracking"]);
                }
            }    
            /* Sub-headline */
            $subheadlines = $config["subheadline"];
            if (!empty($config["subheadline"][0]) && $config["subheadline_pos"]["right"] - $config["subheadline_pos"]["left"] > 0) {
                $json["layers"][] = $this->bannerService->get_text_layer("Subheadline", $config["subheadline_pos"]["left"], $config["subheadline_pos"]["top"] + 10, $config["subheadline_pos"]["right"] - $config["subheadline_pos"]["left"], $config["subheadline_pos"]["bottom"] - $config["subheadline_pos"]["top"], $config["subheadline"][0], $config["subheadline_font"][0], $config["subheadline_font_size"][0], $config["subheadline_color"][0], $config["subheadline_alignment"][0], $config["text_tracking"]);
            }

            /* Button */
            $button_im = null;
            if (!empty($config["button"])) {
                $button_im = new \Imagick($config["button"]);
            }

            /* Call To Action */
            $y_pos = $config["CTA_pos"]["top"];
            $max_cta_width = 0;
            $max_width = 0;
            $cta_total_height = 0;
            if (!empty($config["CTA"])) {
                $cta_width_limit = $config["CTA_pos"]["right"] - $config["CTA_pos"]["left"];
                if ($config["CTA_border_width"] > 0) {
                    $cta_width_limit -= ($config["CTA_border_width"] + $config["CTA_border_padding"]) * 2;
                }
                $cta_texts = $this->bannerService->get_multiline_text($config["CTA"], $config["CTA_font"], $config["CTA_font_size"], $cta_width_limit);
                
                foreach ($cta_texts as $cta) {
                    $metrics = $this->bannerService->get_font_metrics($cta, $config["CTA_font"], $config["CTA_font_size"]);
                    $cta_total_height += $metrics["textHeight"];
                    $max_cta_width = $max_cta_width < $metrics["textWidth"] ? $metrics["textWidth"] : $max_cta_width;
                }

                if ($config["CTA_opaque"]) {
                    $json["layers"][] = $this->bannerService->get_color_layer("CTA background", 0, $config["CTA_pos"]["top"], $config["width"], $config["CTA_pos"]["top"] + $cta_total_height, "ffffff");
                }
                
                $x = $config["CTA_pos"]["left"];
                $max_width = $max_cta_width;
                if ($button_im) {
                    $max_width += ($config["button_space"] + $button_im->getImageWidth());
                }
                if ($config["CTA_alignment"] == "center") {
                    $x = ($config["CTA_pos"]["left"] + $config["CTA_pos"]["right"] - $max_width) / 2;
                } else if ($config["CTA_alignment"] == "right") {
                    $x = ($config["CTA_pos"]["right"] - $max_width);
                }
                $json["layers"][] = $this->bannerService->get_text_layer("CTA", $x, $y_pos, $max_cta_width + $config["CTA_font_size"], $cta_total_height, $cta_texts, $config["CTA_font"], $config["CTA_font_size"], $config["CTA_color"], "left", $config["text_tracking"], $config["CTA_border_width"], $config["CTA_border_color"], $config["CTA_border_padding"], $config["CTA_border_radius"], );
                $y_pos += $cta_total_height;
            }
            if ($button_im) {
                $w = $button_im->getImageWidth();
                $h = $button_im->getImageHeight();
                $x = 0;
                $y = $config["CTA_pos"]["top"] + ($y_pos - $config["CTA_pos"]["top"] - $config["button_space"] - $h) / 2;
                if ($config["CTA_alignment"] == "left") {
                    $x = $max_width > 0 ? $max_width + $config["CTA_pos"]["left"] : $config["CTA_pos"]["left"];
                } else if ($config["CTA_alignment"] == "center") {
                    $x = $max_width > 0 ? ($config["CTA_pos"]["left"] + $config["CTA_pos"]["right"] + $max_width) / 2 - $w : ($config["CTA_pos"]["left"] + $config["CTA_pos"]["right"] - $w) / 2;
                } else if ($config["CTA_alignment"] == "right") {
                    $x = $config["CTA_pos"]["right"] - $w;
                }
                $json["layers"][] = $this->bannerService->get_smartobject_layer("button", $config["button"], $x, $y, $w, $h, 0);

            }

            /* Retailer Logo */
            if ($config["logo"]) {
                $logo_im = new \Imagick($config["logo"]);
                $w = $logo_im->getImageWidth();
                $h = $logo_im->getImageHeight();
                if ($w > $config["logo_width"]) {
                    $w = $config["logo_width"];
                    $logo_im->thumbnailImage($w, null);
                    $h = $logo_im->getImageHeight();
                }
                $x = ($config["CTA_pos"]["left"] + $config["CTA_pos"]["right"] - $w) / 2;
                $json["layers"][] = $this->bannerService->get_smartobject_layer("retailer logo", $config["logo"], $x, $y_pos, $w, $h, 0);
            }
            if ($config["border"]) {
                $json["layers"][] = $this->bannerService->get_color_layer("psd border", 0, 0, $config["width"], $config["height"], $config["border_color"], 1);
            }
        } else if ($config["product_layouts"] == 1) {

        } else if ($config["product_layouts"] == 2) {
            $headlines = $config["headline"];
            $y_pos = $config["height"];
            for ($i = 0; $i < count($headlines); $i++) {
                if (!empty($headlines[$i])) {
                    $y_pos -= $config["headline_font_size"][$i];
                }
            }
            $y_pos /= 2;
            $x_pos = $config["headline_pos"]["left"];
            if ($config["multi_headline"]) {
                for ($i = 0; $i < count($headlines); $i++) {
                    if (!empty($headlines[$i])) {
                        $json["layers"][] = $this->bannerService->get_text_layer("Headline ".($i + 1), 0, $y_pos, $config["width"], $config["headline_font_size"][$i], array($headlines[$i]), $config["headline_font"][$i], $config["headline_font_size"][$i], $config["headline_color"][$i], $config["headline_alignment"][$i], $config["text_tracking"]);
                        $y_pos += $config["headline_font_size"][$i];
                    }
                }
            } else {
                if (!empty($config["headline"][0])) {
                    $y_pos = ($config["height"] - $config["headline_font_size"][0]) / 2;
                    $headlines = array($config["headline"][0]);
                    $json["layers"][] = $this->bannerService->get_text_layer("Headline", 0, $y_pos, $config["width"], 100, $headlines, $config["headline_font"][0], $config["headline_font_size"][0], $config["headline_color"][0], $config["headline_alignment"][0], $config["text_tracking"]);
                }
            }  
        } else if ($config["product_layouts"] == 3) {
            /* Product */
            $count = count($files);
            $count = $count > 4 ? 4 : $count;

            $products = array();
            $sum_width = 0;
            $max_height = 0;
            $products_width = $config["product_dimensions"]["width"];
            for ($i = 0; $i < $count; $i++) {
                $sum_width += $files[$i]["width"];
                $products[] = new \Imagick($product_filenames[$i]);
            }

            for ($i = 0; $i < $count; $i++) {
                $w = $products_width * $files[$i]["width"] / $sum_width * 1.5;
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
                    $products[$i] ->scaleImage($w, $h, true);
                }
            }

            $coordinate_x = [-$config["width"]/8, $config["width"]*2/5, $config["width"]/6, $config["width"]*2/3];
            $coordinate_y = [$config["height"]/5, -$config["height"]/5, $config["height"]*3/5, $config["height"]/6];

            for ($i = 0; $i < $count; $i++) {
                $w = $products[$i]->getImageWidth();
                $h = $products[$i]->getImageHeight();
                $json["layers"][] = $this->bannerService->get_smartobject_layer($files[$i]["name"], $product_filenames[$i], $coordinate_x[$i], $coordinate_y[$i], $w, $h, 45, null, $config["image_shadow"], $config["fade"], 0);
            }
            
        }
        
        /* Background */
        if (!empty($config["background"])) {
            $background_layer = new \Imagick();
            $background_layer->readImage($config["background"]);
            $background_layer->scaleImage($config["width"], $config["height"], true);
            $background_layer->writeImage($config["background"]);
            $image_width = $background_layer->getImageWidth();
            $image_height = $background_layer->getImageHeight();
            array_unshift($json["layers"], $this->bannerService->get_smartobject_layer("Background", $config["background"], ($config["width"] - $image_width) / 2, ($config["height"] - $image_height) / 2, $image_width, $image_height, 0));
        } else {
            array_unshift($json["layers"], $this->bannerService->get_pixel_layer("Background", 0, 0, $config["width"], $config["height"], $config["background_color"]));
        }

        return base64_encode(json_encode($json));
    }

    private function get_history_settings($request)
    {
        if ($request->output_dimensions < 3) {
            $settings = array(
                "customer" => $request->customer,
                "output_dimensions" => $request->output_dimensions,
                "project_name" => $request->project_name,
                "file_ids" => $request->file_ids,
                "product_space" => $request->product_space,
                "headline" => $request->headline,
                "subheadline" => $request->subheadline,
                "CTA" => $request->CTA,
                "compress" => $request->compress,
                "compress_size" => $request->compress_size,
                "include_psd" => $request->include_psd,
                "background_color" => $request->background_color,
                "drop_shadow" => $request->drop_shadow,
                "text_tracking" => isset($request->text_tracking) ? $request->text_tracking : 0,
                "product_layering" => $request->product_layering,
                "product_custom_layering" => $request->product_custom_layering,
                "type" => $request->type,
                "product_texts" => $request->product_texts
            );
            $settings["multi_headline"] = $request->multi_headline;
            $settings["headline_alignment"] = $request->headline_alignment;
            $settings["headline_font"] = $request->headline_font;
            $settings["headline_font_size"] = $request->headline_font_size;
            $settings["headline_color"] = $request->headline_color;
            $settings["subheadline_alignment"] = $request->subheadline_alignment;
            $settings["subheadline_font"] = $request->subheadline_font;
            $settings["subheadline_font_size"] = $request->subheadline_font_size;
            $settings["subheadline_color"] = $request->subheadline_color;
            $settings["CTA_alignment"] = $request->CTA_alignment;
            $settings["CTA_opaque"] = $request->CTA_opaque;
            $settings["CTA_font"] = $request->CTA_font;
            $settings["CTA_font_size"] = $request->CTA_font_size;
            $settings["CTA_color"] = $request->CTA_color;
            $settings["CTA_border_width"] = $request->CTA_border_width;
            $settings["CTA_border_color"] = $request->CTA_border_color;
            $settings["CTA_border_radius"] = $request->CTA_border_radius;
            $settings["CTA_border_padding"] = $request->CTA_border_padding;
            $settings["output_filename"] = $request->output_filename;
            $settings["image_shadow"] = $request->image_shadow;
            $settings["fade"] = $request->fade;
            $settings["border"] = $request->border;
            $settings["border_color"] = $request->border_color;
        } else {
            $settings = array(
                "customer" => $request->customer,
                "output_dimensions" => $request->output_dimensions,
                "project_name" => $request->project_name,
                "file_ids" => $request->file_ids,
                "duration" => $request->duration,
                "fade_type" => $request->fade_type
            );
        }

        return json_encode($settings);
    }

    public function run_video($request, $preview = false, $save = false, $publish = false)
    {
        $result = $this->bannerService->map_files(explode(" ", $request->file_ids), false);
        if ($result["status"] == "error") {
            return $result;
        }

        // Get trimmed product image files
        $temp_files = array();
        $product_filenames = array();
        foreach ($result["files"] as $file) {
            $filename = uniqid().".jpg";
            $product = new \Imagick();
            $product->readImageBlob(Storage::disk('s3')->get($file["path"]));
            $product->setImageBackgroundColor(new \ImagickPixel('white'));
            $product = $product->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            // $product->trimImage(0);
            // $product->setImagePage(0, 0, 0, 0);
            $product->setImageFormat("jpg");
            $product->writeImage($filename);
            $product_filenames[] = $filename;
            $temp_files[] = $filename;
        }

        $response = null;
        $mp4_file = "";

        if ($preview) {
            $response = ["files" => [], "log" => []];
            $config = $this->get_config($request);
            $mp4_file = uniqid().".mp4";

            $command = $this->get_ffmpeg($result["files"], $product_filenames, $config);
            $command = $command.$mp4_file;

            $log = shell_exec($command." 2>&1");

            $response['files'][] = $mp4_file;
            $response['log'][] = $log;
        } else {
            $zip_file_id = uniqid();
            $zip_file = $zip_file_id.".zip";
            $zip_filename = (!empty($request->output_filename) ? $request->output_filename : (!empty($request->project_name) ? $request->project_name : "output"));
            $zip = new ZipArchive();
            $log = "";
            if ($zip->open(public_path($zip_file), ZipArchive::CREATE) === TRUE) {
                $config = $this->get_config($request);
                $output_filename = $this->get_output_filename($request);
                
                $mp4_file = uniqid().".mp4";

                $command = $this->get_ffmpeg($result["files"], $product_filenames, $config);
                $command = $command.$mp4_file;
                $log = shell_exec($command." 2>&1");

                $zip->addFile($mp4_file, $output_filename.".mp4");
                $zip->close();

                Storage::disk('s3')->put('outputs/'.$zip_file, file_get_contents(public_path($zip_file)));
                $temp_files[] = $zip_file;

                if ($save) {
                    $this->bannerService->save_draft([
                        'name' => $zip_filename,
                        'customer' => $request->customer,
                        'output_dimensions' => $request->output_dimensions,
                        'projectname' => $request->project_name,
                        'url' => 'outputs/'.$zip_file,
                        'fileid' => $request->file_ids,
                        'headline' => "Instagram",
                        'size' => config("templates.Instagram.output_dimensions")[$request->output_dimensions],
                        'settings' => $this->get_history_settings($request),
                        'jpg_files' => $zip_file,
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
                        'headline' => "Instagram",
                        'size' => config("templates.Instagram.output_dimensions")[$request->output_dimensions],
                        'settings' => $this->get_history_settings($request),
                        'jpg_files' => $zip_file,
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

        if ($result["status"] == "warning") {
            $response["status"] = "warning";
            $response["messages"] = $result["messages"];
        } else if ($result["status"] == "success") {
            $response["status"] = "success";
        }

        if (!file_exists($mp4_file)) {
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

    public function run_image($request, $preview = false, $save = false, $publish = false)
    {
        // Map files from UTC/GTIN/ASIN/TCIN/WMT-ID
        $result = $this->bannerService->map_files(explode(" ", $request->file_ids), false);

        if ($result["status"] == "error") {
            return $result;
        }
        
        $count = count($result["files"]);
        if ($request->product_layering == "2 Images - Larger Item Back-Right" && $count != 2) {
            return [
                "status" => "error",
                "messages" => ["$request->product_layering only supported for 2 images"]
            ];
        }
        if (($request->product_layering == "3 Images - Largest Item Middle" || $request->product_layering == "Middle In Front") && $count != 3) {
            return [
                "status" => "error",
                "messages" => ["$request->product_layering only supported for 3 images"]
            ];
        }
        if ($request->product_layering == "Custom" && $count != count(explode(" ", $request->product_custom_layering))) {
            return [
                "status" => "error",
                "messages" => ["Product custom layering doesn't match with image count."]
            ];
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

        // Save background image if exists in request.
        $background_filename = null;
        if (isset($request->background)) {
            $background_filename = uniqid().".".$request->background->getClientOriginalExtension();
            $temp_files[] = $background_filename;
            file_put_contents($background_filename, file_get_contents($request->file("background")));
        }

        // Save button image if exists in request.
        $button_filename = null;
        if (isset($request->button)) {
            $button_filename = uniqid().".".$request->button->getClientOriginalExtension();
            $temp_files[] = $button_filename;
            file_put_contents($button_filename, file_get_contents($request->file("button")));
        }

        // Save logo image if exists in request.
        $logo_filename = null;
        if (isset($request->logo)) {
            $logo_filename = uniqid().".".$request->logo->getClientOriginalExtension();
            $temp_files[] = $logo_filename;
            file_put_contents($logo_filename, file_get_contents($request->file("logo")));
        }

        $response = [];

        if ($preview) {
            $response = ["files" => [], "log" => []];
            $template = $request->output_dimensions;
            
            $config = $this->get_config($request, $background_filename, $button_filename, $logo_filename);
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
                    'size' => config("templates.Instagram.output_dimensions")[$request->output_dimensions],
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
                    $template = $request->output_dimensions;
                
                    $config = $this->get_config($request, $background_filename, $button_filename, $logo_filename);
                    $output_filename = $this->get_output_filename($request, $template);
    
                    $psd_file_id = uniqid();
                    $psd_file = $psd_file_id.".psd";
                    $jpeg_file_id = uniqid();
                    $jpeg_file = $jpeg_file_id.".jpg";
                    
                    $input_arg = $this->get_psd($result["files"], $product_filenames, $config);
                    $includ_psd_cmd = "";
                    if ($request->include_psd) {
                        $includ_psd_cmd = " -o ".$psd_file_id;
                    }
                    $pil_font = Setting::where('key', 'pil_font')->first()->value;
                    if ($pil_font == "on") {
                        $command = escapeshellcmd("python3 /var/www/psd2/tool.py --pil-font -j ".$input_arg." -of ".$zip_file_id.$includ_psd_cmd." -p ".$jpeg_file_id);
                    } else {
                        $command = escapeshellcmd("python3 /var/www/psd2/tool.py -j ".$input_arg." -of ".$zip_file_id.$includ_psd_cmd." -p ".$jpeg_file_id);
                    }
                    $log = shell_exec($command." 2>&1");
                    $temp_files = array_merge($temp_files, array("tmp_result.psd", "shadow.png", "circle.png"));
                    $temp_files[] = $psd_file;
                    $temp_files[] = $jpeg_file;
    
                    $zip->addFile($jpeg_file, $output_filename.".jpg");
                    $output_jpg_files[] = $jpeg_file;
                    if ($request->include_psd) {
                        $zip->addFile($psd_file, $output_filename.".psd");
                        $output_jpg_files[] = $psd_file;
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
                        $this->draftService->store([
                            'name' => $zip_filename,
                            'customer' => $request->customer,
                            'output_dimensions' => $request->output_dimensions,
                            'projectname' => $request->project_name,
                            'url' => 'outputs/'.$zip_file,
                            'fileid' => $request->file_ids,
                            'headline' => implode(" ", $request->headline),
                            'size' => config("templates.Instagram.output_dimensions")[$request->output_dimensions],
                            'settings' => $this->get_history_settings($request),
                            'jpg_files' => implode(" ", $output_jpg_files),
                            'type' => $request->type,
                            'parent_id' => isset($request->parent_id) ? $request->parent_id : 0
                        ]);
                    }
    
                    if ($publish) {
                        $this->projectService->store([
                            'name' => $zip_filename,
                            'customer' => $request->customer,
                            'output_dimensions' => $request->output_dimensions,
                            'projectname' => $request->project_name,
                            'url' => 'outputs/'.$zip_file,
                            'fileid' => $request->file_ids,
                            'headline' => implode(" ", $request->headline),
                            'size' => config("templates.Instagram.output_dimensions")[$request->output_dimensions],
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

        foreach ($temp_files as $filename) {
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
        if ($result["status"] == "warning") {
            $response["status"] = "warning";
            $response["messages"] = $result["messages"];
        } else if ($result["status"] == "success") {
            $response["status"] = "success";
        }

        return $response;
    }
}
