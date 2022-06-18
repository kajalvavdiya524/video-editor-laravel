<?php

namespace App\Domains\Auth\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use App\Domains\Auth\Models\File;
use App\Domains\Auth\Models\Setting;
use App\Domains\Auth\Models\Mapping;
use App\Domains\Auth\Models\NewMapping;
use App\Domains\Auth\Models\Dimension;
use App\Domains\Auth\Models\ParentChild;
use Exception;
use ZipArchive;

/**
 * Class BannerService.
 */
class BannerService extends BaseService
{
    protected $draftService;
    protected $projectService;
    protected $exceptionService;
    protected $settingsService;

    /**
     * BannerService constructor.
     *
     */
    public function __construct(HistoryService $draftService, ProjectService $projectService, ExceptionService $exceptionService, SettingsService $settingsService)
    {
        ini_set('memory_limit', -1);

        $this->draftService = $draftService;
        $this->projectService = $projectService;
        $this->exceptionService = $exceptionService;
        $this->settingsService = $settingsService;
    }

    public function get_file_with_dimension($file, $file_ids, &$result, &$log, $show_warning = true)
    {
        $found_dimension = false;
        $child_list = [];
        foreach ($file_ids as $file_id) {
            $new_mapping = NewMapping::getNewMapping($file_id);
            if ($new_mapping) {
                $dimension = $new_mapping;
                $log[] = "Child links from new mappings: $file_id";
                $child_list = NewMapping::getChildLinks($file_id);
            } else {
                $dimension = Dimension::getDimension($file_id);
                $log[] = "Child links from dimension table: $file_id";
                $child_list[] = explode('.', $file["name"])[0];
            }
            if ($dimension) {
                $result["files"][] = [
                    'id' => $file->id,
                    'name' => $file->name,
                    'product_name' => isset($dimension->product_name) ? $dimension->product_name : '',
                    'brand' => isset($dimension->brand) ? $dimension->brand : '',
                    'company_id' => $file->company_id,
                    'path' => $file->path,
                    'thumbnail' => $file->thumbnail,
                    'nf_url' => $dimension->nf_url,
                    'ingredient_url' => $dimension->ingredient_url,
                    'width' => $dimension->width,
                    'height' => $dimension->height,
                    'depth' => isset($dimension->depth) ? $dimension->depth : '5', 
                    'child_list' => $child_list
                ];
                $found_dimension = true;
                break;
            }
        }
        if (!$found_dimension) {
            $result["files"][] = [
                'id' => $file->id,
                'name' => $file->name,
                'path' => $file->path,
                'thumbnail' => $file->thumbnail,
                'company_id' => $file->company_id,
                'nf_url' => '',
                'ingredient_url' => '',
                'width' => $file->width ? $file->width : 5,
                'height' => $file->height ? $file->height : 5,
                'depth' => $file->depth ? $file->depth : 5, 
                'child_list' => $child_list
            ];

            if ($file->width == 0 || $file->height == 0) {
                $msg = "[$file_ids[0]] - Dimensions not found. Just used a preset size: 5, 5, 5";
                $log[] = "Dimension Not Found. Just used a preset size: 5, 5, 5";
                // Save Exception Message
                if ($show_warning) {
                    $result["messages"][] = $msg;
                    $this->save_exception(['file_id' => $file_ids[0], 'message' => $msg]);
                }
            }
        } else {
            $log[] = "Dimension Found: $dimension->width, $dimension->height";
        }
    }

    public function check_file($file, $file_ids, &$result, &$log, $show_warning = true)
    {
        if ($file) {
            $log[] = "File Found";
            $this->get_file_with_dimension($file, $file_ids, $result, $log, $show_warning);
        } else {
            $msg = "[$file_ids[0]] - Image Not Found";
            $log[] = "File Not Found";
            $result["messages"][] = $msg;

            // Save Exception Message
            if ($show_warning) {
                $this->save_exception(['file_id' => $file_ids[0], 'message' => $msg]);
            }
        }
    }

    public function fileId_to_gtin($file_id)
    {
        $result = array();
        if (!empty($file_id)) {
            $mappings = Mapping::getMappings($file_id);
            foreach ($mappings as $mapping) {
                $gtins = Dimension::getGTINs($mapping->UPC);
                foreach ($gtins as $gtin) {
                    $parent_mappings  = ParentChild::getByParents($gtin);
                    foreach ($parent_mappings as $parent_mapping) {
                        $result[] = $parent_mapping->child;
                    }
                }
            }
            $result = array_unique($result);
        }
        return $result;
    }

    public function get_file_map_info($file_id)
    {
        $result = [
            'has_child' => 0,
            'has_dimension' => 0,
            'ASIN' => '',
            'UPC' => '',
            'parent_gtin' => '',
            'child_gtin' => '',
            'product_name' => '',
            'brand' => '',
            'width' => 0,
            'height' => 0,
            'depth' => 0
        ];
        $mapping = Mapping::getMapping($file_id);
        if ($mapping) {
            $gtin = Dimension::getGTIN($mapping->UPC);
            $result['ASIN'] = $mapping->ASIN;
            $result['UPC'] = $mapping->UPC;
            if ($gtin) {
                $result['parent_gtin'] = $gtin;
                $parent_mapping  = ParentChild::getByParent($gtin);
                if ($parent_mapping) {
                    $result['has_child'] = 1;
                    $unit_gtin = $parent_mapping->child;
                    $result['child_gtin'] = $unit_gtin;
                    $new_mapping = NewMapping::getNewMapping($unit_gtin);
                    if ($new_mapping) {
                        $unit_gtin = $new_mapping->GTIN;
                        $dimension = $new_mapping;
                    } else {
                        $dimension = Dimension::getDimension($unit_gtin);
                    }
                    $file = File::getFile($unit_gtin);
                    if ($file && $dimension) {
                        $result['has_dimension'] = 1;
                        $result['product_name'] = isset($dimension->product_name) ? $dimension->product_name : '';
                        $result['brand'] = isset($dimension->brand) ? $dimension->brand : '';
                        $result['width'] = $dimension->width;
                        $result['height'] = $dimension->height;
                        $result['depth'] = isset($dimension->depth) ? $dimension->depth : '0';
                    } else {
                        $mapping = Mapping::getMapping($unit_gtin);
                        if ($mapping) {
                            $result['ASIN'] = $mapping->ASIN;
                            $result['UPC'] = $mapping->UPC;
                            $file = File::getFile($mapping->UPC);
                            if (!$file)
                                $file = File::getFile($mapping->ASIN);

                            if ($file && $dimension) {
                                $result['has_dimension'] = 1;
                                $result['product_name'] = isset($dimension->product_name) ? $dimension->product_name : '';
                                $result['brand'] = isset($dimension->brand) ? $dimension->brand : '';
                                $result['width'] = $dimension->width;
                                $result['height'] = $dimension->height;
                                $result['depth'] = isset($dimension->depth) ? $dimension->depth : '0';
                            }
                        }
                    }
                } else {
                    $result['has_child'] = 0;
                    $new_mapping = NewMapping::getNewMapping($gtin);
                    if ($new_mapping) {
                        $dimension = $new_mapping;
                    } else {
                        $dimension = Dimension::getDimension($gtin);
                    }
                    if ($dimension) {
                        $result['has_dimension'] = 1;
                        $result['product_name'] = isset($dimension->product_name) ? $dimension->product_name : '';
                        $result['brand'] = isset($dimension->brand) ? $dimension->brand : '';
                        $result['width'] = $dimension->width;
                        $result['height'] = $dimension->height;
                        $result['depth'] = isset($dimension->depth) ? $dimension->depth : '0';
                    }
                }
            }
        } else {
            $new_mapping = NewMapping::getNewMapping($file_id);
            if ($new_mapping) {
                $dimension = $new_mapping;
            } else {
                $dimension = Dimension::getDimension($file_id);
            }
            if ($dimension) {
                $result['has_dimension'] = 1;
                $result['product_name'] = isset($dimension->product_name) ? $dimension->product_name : '';
                $result['brand'] = isset($dimension->brand) ? $dimension->brand : '';
                $result['width'] = $dimension->width;
                $result['height'] = $dimension->height;
                $result['depth'] = isset($dimension->depth) ? $dimension->depth : '0';
            }
        }

        return $result;
    }

    public function map_files($file_ids, $show_warning = true)
    {
        $result = [
            'files' => [],
            'messages' => [],
            'logs' => [],
        ];
        $fileIds = [];
        for ($i = 0; $i < count($file_ids); $i++) {
            if (substr($file_ids[$i], -2) == '_p') {
                $fileIds[] = substr($file_ids[$i], 0, -2);
            } else {
                $fileIds[] = $file_ids[$i];
            }
        }

        foreach ($fileIds as $file_id) {
            if (!empty($file_id)) {
                $log = [];
                $child_list = [];
                $log[] = "Original ASIN/UPC : $file_id";
                $mapping = Mapping::getMapping($file_id);
                if ($mapping) {
                    $log[] = "Corresponding ASIN and UPC: $mapping->ASIN, $mapping->UPC";
                    $gtin = Dimension::getGTIN($mapping->UPC);
                    if ($gtin) {
                        $log[] = "Corresponding GTIN: $gtin";
                        $parent_mapping  = ParentChild::getByParent($gtin);
                        if ($parent_mapping) {
                            $log[] = "Correspnding Child: $parent_mapping->child";
                            $unit_gtin = $parent_mapping->child;
                            $new_mapping = NewMapping::getNewMapping($unit_gtin);
                            if ($new_mapping) {
                                $unit_gtin = $new_mapping->GTIN;
                                $child_list = NewMapping::getChildLinks($gtin);
                                $dimension = $new_mapping;
                            } else {
                                $dimension = Dimension::getDimension($unit_gtin);
                                $child_list = [$gtin, $unit_gtin];
                            }
                            $file = File::getFile($unit_gtin);
                            if ($file) {
                                $log[] = "File Found: $unit_gtin";
                                if ($dimension) {
                                    $log[] = "Dimension Found: $dimension->width, $dimension->height";
                                    $result["files"][] = [
                                        'id' => $file->id,
                                        'name' => $file->name,
                                        'product_name' => isset($dimension->product_name) ? $dimension->product_name : '',
                                        'brand' => isset($dimension->brand) ? $dimension->brand : '',
                                        'company_id' => $file->company_id,
                                        'path' => $file->path,
                                        'thumbnail' => $file->thumbnail,
                                        'nf_url' => $dimension->nf_url,
                                        'ingredient_url' => $dimension->ingredient_url,
                                        'width' => $dimension->width,
                                        'height' => $dimension->height,
                                        'depth' => isset($dimension->depth) ? $dimension->depth : '5', 
                                        'child_list' => $child_list
                                    ];
                                } else {
                                    $result["files"][] = [
                                        'id' => $file->id,
                                        'name' => $file->name,
                                        'path' => $file->path,
                                        'thumbnail' => $file->thumbnail,
                                        'company_id' => $file->company_id,
                                        'nf_url' => '',
                                        'ingredient_url' => '',
                                        'width' => $file->width ? $file->width : 5,
                                        'height' => $file->height ? $file->height : 5,
                                        'depth' => $file->depth ? $file->depth : 5, 
                                        'child_list' => $child_list
                                    ];

                                    if ($file->width == 0 || $file->height == 0) {
                                        $msg = "[$file_id] - Dimensions not found. Just used a preset size: 5, 5, 5";
                                        $log[] = "Dimension Not Found. Just used preset size: 5, 5, 5";
                                        // Save Exception Message
                                        if ($show_warning) {
                                            $result["messages"][] = $msg;
                                            $this->save_exception(['file_id' => $file_id, 'message' => $msg]);
                                        }
                                    }
                                }
                            } else {
                                $log[] = "File Not Found: $unit_gtin, Find corresponding mapping";
                                $mapping = Mapping::getMapping($unit_gtin);
                                if ($mapping) {
                                    $log[] = "Mapping Found, corresponding ASIN and UPC: $mapping->ASIN, $mapping->UPC";
                                    $file = File::getFile($mapping->UPC);
                                    $id = $mapping->UPC;
                                    if (!$file) {
                                        $file = File::getFile($mapping->ASIN);
                                        $id = $mapping->ASIN;
                                    }
                                    if ($file) {
                                        $log[] = "File Found, $id";
                                        if ($dimension) {
                                            $log[] = "Dimension Found: $dimension->width, $dimension->height";
                                            $result["files"][] = [
                                                'id' => $file->id,
                                                'name' => $file->name,
                                                'product_name' => isset($dimension->product_name) ? $dimension->product_name : '',
                                                'brand' => isset($dimension->brand) ? $dimension->brand : '',
                                                'company_id' => $file->company_id,
                                                'path' => $file->path,
                                                'thumbnail' => $file->thumbnail,
                                                'nf_url' => $dimension->nf_url,
                                                'ingredient_url' => $dimension->ingredient_url,
                                                'width' => $dimension->width,
                                                'height' => $dimension->height,
                                                'depth' => isset($dimension->depth) ? $dimension->depth : '5', 
                                                'child_list' => $child_list
                                            ];
                                        } else {
                                            $result["files"][] = [
                                                'id' => $file->id,
                                                'name' => $file->name,
                                                'path' => $file->path,
                                                'thumbnail' => $file->thumbnail,
                                                'company_id' => $file->company_id,
                                                'nf_url' => '',
                                                'ingredient_url' => '',
                                                'width' => $file->width ? $file->width : 5,
                                                'height' => $file->height ? $file->height : 5,
                                                'depth' => $file->depth ? $file->depth : 5, 
                                                'child_list' => $child_list
                                            ];
                                            if ($file->width == 0 || $file->height == 0) {
                                                $msg = "[$file_id] - Dimensions not found. Just used a preset size: 5, 5, 5";
                                                $log[] = "Dimension Not Found. Just used preset size: 5, 5, 5";
                                                // Save Exception Message
                                                if ($show_warning) {
                                                    $result["messages"][] = $msg;
                                                    $this->save_exception(['file_id' => $file_id, 'message' => $msg]);
                                                }
                                            }
                                        }
                                    } else {
                                        $msg = "[$file_id] - Image Not Found";
                                        $log[] = "File Not Found with $mapping->ASIN/$mapping->UPC, Image Not Found";
                                        $result["messages"][] = $msg;

                                        // Save Exception Message
                                        if ($show_warning) {
                                            $this->save_exception(['file_id' => $file_id, 'message' => $msg]);
                                        }
                                    }
                                } else {
                                    $msg = "[$file_id] - Mapping Not Found: $unit_gtin, Invalid UPC/GTIN";
                                    $log[] = "Mapping Not Found: $unit_gtin, Invalid UPC/GTIN";

                                    // Save Exception Message
                                    if ($show_warning) {
                                        $result["messages"][] = $msg;
                                        $this->save_exception(['file_id' => $file_id, 'message' => $msg]);
                                    }
                                }
                            }
                        } else {
                            $log[] = "Not related to Parent/Child";
                            $new_mapping = NewMapping::getNewMapping($gtin);
                            if ($new_mapping) {
                                $file = File::getFile($new_mapping->GTIN);
                            }
                            else {
                                $file = File::getFile($gtin);
                            }

                            if ($file) {
                                $log[] = "File Found, $gtin";
                                $this->get_file_with_dimension($file, [$gtin], $result, $log, $show_warning);
                            } else {
                                $log[] = "File Not Found by $gtin, Use mapping to find file";
                                $file = File::getFile($mapping->UPC);
                                $id = $mapping->UPC;
                                if (!$file) {
                                    $file = File::getFile($mapping->ASIN);
                                    $id = $mapping->ASIN;
                                }
                                $this->check_file($file, [$gtin], $result, $log, $show_warning);
                            }
                        }
                    } else {
                        $log[] = "Corresponding GTIN Not Found";
                        $result["messages"][] = "[$file_id] - Corresponding GTIN not found";
                        $file = File::getFile($mapping->UPC);
                        if (!$file) {
                            $file = File::getFile($mapping->ASIN);
                        }
                        if ($file) {
                            $result["files"][] = [
                                'id' => $file->id,
                                'name' => $file->name,
                                'path' => $file->path,
                                'thumbnail' => $file->thumbnail,
                                'company_id' => $file->company_id,
                                'nf_url' => '',
                                'ingredient_url' => '',
                                'width' => $file->width ? $file->width : 5,
                                'height' => $file->height ? $file->height : 5,
                                'depth' => $file->depth ? $file->depth : 5, 
                                'child_list' => $child_list
                            ];

                            if ($file->width == 0 || $file->height == 0) {
                                $msg = "[$file_id] - Dimensions not found. Just used a preset size: 5, 5, 5";
                                $log[] = "Dimension Not Found. Just used preset size: 5, 5, 5";
                                // Save Exception Message
                                if ($show_warning) {
                                    $result["messages"][] = $msg;
                                    $this->save_exception(['file_id' => $file_id, 'message' => $msg]);
                                }
                            }
                        } else {
                            $msg = "[$file_id] - Image Not Found";
                            $log[] = "File Not Found with $mapping->ASIN/$mapping->UPC, Image Not Found";
                            $result["messages"][] = $msg;

                            // Save Exception Message
                            if ($show_warning) {
                                $this->save_exception(['file_id' => $file_id, 'message' => $msg]);
                            }
                        }
                    }
                } else {
                    $log[] = "Mapping Not found, check $file_id.png on the system";
                    $new_mapping = NewMapping::getNewMapping($file_id);
                    if ($new_mapping) {
                        $file = File::getFile($new_mapping->GTIN);
                    }
                    else {
                        $file = File::getFile($file_id);
                    }
                    $this->check_file($file, [$new_mapping ? $new_mapping->GTIN : $file_id], $result, $log, $show_warning);
                }
                $result['logs'][] = $log;
            }
        }

        for ($i = 0; $i < count($file_ids); $i++) {
            if (substr($file_ids[$i], -2) == '_p') {
                $file = File::getFile($fileIds[$i]);
                if ($file) {
                    $result["files"][$i] = [
                        'id' => $file->id,
                        'name' => $file->name,
                        'path' => $file->path,
                        'thumbnail' => $file->thumbnail,
                        'company_id' => $file->company_id,
                        'nf_url' => '',
                        'ingredient_url' => '',
                        'width' => $file->width ? $file->width : 5,
                        'height' => $file->height ? $file->height : 5,
                        'depth' => $file->depth ? $file->depth : 5, 
                        'child_list' => $result["files"][$i]["child_list"]
                    ];
                } else {
                    $msg = "[$fileIds[$i]] - Image Not Found";
                    $log[] = "File Not Found";
                    $result["messages"][] = $msg;

                    // Save Exception Message
                    if ($show_warning) {
                        $this->save_exception(['file_id' => $fileIds[$i], 'message' => $msg]);
                    }
                }
            }
        }

        if (count($file_ids) == count($result["files"])) {
            if (count($result["messages"]) && $show_warning) {
                return [
                    "status" => "warning",
                    "files" => $result["files"],
                    "messages" => $result["messages"],
                    "logs" => $result["logs"],
                ];
            }
            return [
                "status" => "success",
                "files" => $result["files"],
                "logs" => $result["logs"]
            ];
        }

        if (count($result["messages"]) == 0) {
            $result["messages"][] = "Empty UPC/GTIN";

            // for dev only
            // remove required validation for file ids
            return [
                "status" => "success",
                "files" => [],
                "logs" => []
            ];
        }

        return [
            "status" => "error",
            "messages" => $result["messages"],
            "logs" => $result["logs"],
        ];
    }

    public function save_exception($data)
    {
        $subject = 'RapidAds - Create Ads Exception';
        $this->settingsService->send_email('backend.includes.text_mail', $subject, array('msg' => $data["message"]));
        return $this->exceptionService->store($data);
    }

    public function get_config($request, $template, $background_filename, $button_filename, $logo_filename)
    {
        $config = array();

        $config["customer"] = $request->customer;
        $config["template"] = $template;
        $config["width"] = $request->customer == "amazon_fresh" ? config("templates.AmazonFresh.width") : config("templates.Generic.width")[$template];
        $config["height"] = $request->customer == "amazon_fresh" ? config("templates.AmazonFresh.height") : config("templates.Generic.height")[$template];

        if ($template != 5 && $template != 6) {
            $config["headline"] = $request->headline;
            if ($request->customer != "amazon_fresh") {
                $config["multi_headline"] = $request->multi_headline;
                $config["headline_font"] = $request->headline_font;
                $config["headline_font_size"] = array();
                foreach ($request->headline_font_size as $headline_font_size) {
                    $size_array = array_map("intval", explode(",", $headline_font_size));
                    $config["headline_font_size"][] = $size_array[count($size_array) > 1 ? $template : 0];
                }
                $config["headline_color"] = $request->headline_color;
                $config["headline_alignment"] = $request->headline_alignment;
                $config["headline_pos"] = config("templates.Generic.headline_pos")[$template];
                $config["headline_space"] = config("templates.Generic.headline_space")[$template];
            }

            $config["subheadline"] = $request->subheadline;
            if ($request->customer != "amazon_fresh") {
                $config["subheadline_font"] = $request->subheadline_font;
                $size_array = array_map("intval", explode(",", $request->subheadline_font_size));
                $config["subheadline_font_size"] = $size_array[count($size_array) > 1 ? $template : 0];
                $config["subheadline_color"] = $request->subheadline_color;
                $config["subheadline_alignment"] = $request->subheadline_alignment;
                $config["subheadline_pos"] = config("templates.Generic.subheadline_pos")[$template];
                $config["subheadline_space"] = config("templates.Generic.subheadline_space")[$template];
            }

            $config["CTA"] = $request->CTA;
            $config["CTA_font"] = $request->customer == "amazon_fresh" ? "Amazon-Ember-Light" : $request->CTA_font;
            if ($request->customer == "amazon_fresh") {
                $config["CTA_font_size"] = 17;
            } else {
                $size_array = array_map("intval", explode(",", $request->CTA_font_size));
                $config["CTA_font_size"] = $size_array[count($size_array) > 1 ? $template : 0];
            }
            $config["CTA_color"] = $request->customer == "amazon_fresh" ? "#000000" : $request->CTA_color;

            if ($request->customer != "amazon_fresh") {
                $config["CTA_pos"] = config("templates.Generic.CTA_pos")[$template];
            } else {
                $output_dimension = config("templates.AmazonFresh.output_dimensions")[$template];
                $cta_pos_id = "AmazonFresh_" . $output_dimension . "_CTA_pos";
                $config["CTA_pos"] = [
                    'left' => intval(Setting::where("key", $cta_pos_id . "_Left")->first()->value),
                    'top' => intval(Setting::where("key", $cta_pos_id . "_Top")->first()->value),
                    'right' => intval(Setting::where("key", $cta_pos_id . "_Right")->first()->value)
                ];
            }

            if ($request->customer != "amazon_fresh") {
                $config["CTA_alignment"] = $request->CTA_alignment;
                $config["CTA_opaque"] = $request->CTA_opaque;
                $config["CTA_border_width"] = $request->CTA_border_width;
                $config["CTA_border_color"] = $request->CTA_border_color;
                $config["CTA_border_radius"] = $request->CTA_border_radius;
                $config["CTA_border_padding"] = $request->CTA_border_padding;
                $config["CTA_space"] = config("templates.Generic.CTA_space")[$template];
            }
        }

        if ($request->customer == "amazon_fresh") {
            $top = intval(Setting::where("key", "AmazonFresh_Products_Top")->first()->value);
            $left = intval(Setting::where("key", "AmazonFresh_Products_Left")->first()->value);
            $bottom = intval(Setting::where("key", "AmazonFresh_Products_Bottom")->first()->value);
            $right = intval(Setting::where("key", "AmazonFresh_Products_Right")->first()->value);
            $config["product_dimensions"] =  [
                'width' => $right - $left, 'height' => $bottom - $top, 'baseline' => $bottom, 'left' => $left
            ];
        } else {
            $config["product_dimensions"] = config("templates.Generic.product_dimensions")[$template];
        }

        $config["product_space"] = $request->product_space;
        $config["product_layering"] = $request->product_layering;
        $config["background_color"] = $request->background_color;
        $config["drop_shadow"] = isset($request->drop_shadow) ? $request->drop_shadow : "left";
        $config["image_shadow"] = null;
        $config["fade"] = null;

        if ($template != 5 && $template != 6) {
            $config["drop_shadow"] = isset($request->drop_shadow) ? $request->drop_shadow : "left";
            $config["text_tracking"] = isset($request->text_tracking) ? $request->text_tracking : 0;
            $config["product_custom_layering"] = $request->product_custom_layering;

            if ($request->customer != "amazon_fresh") {
                $config["background"] = $background_filename;
                $config["logo"] = $logo_filename;
                $config["logo_width"] = config("templates.Generic.logo_width")[$template];
                $config["button"] = $button_filename;
                $config["button_space"] = config("templates.Generic.button_space")[$template];
                $config["image_shadow"] = $request->image_shadow == "on" ? "bottom" : null;
                $config["fade"] = $request->fade == "on" ? 1 : 0;
                $config["border"] = $request->border;
                $config["border_color"] = $request->border_color;
                $size_array = array_map("intval", explode(",", $request->compress_size));
                $config["compress_size"] = $size_array[count($size_array) > 1 ? $template : 0];
            } else {
                $config["text_tracking"] = -10;
                $config["text_pos"] = config("templates.AmazonFresh.text_pos");
                $config["bottom_position"] = $request->bottom_position;
                $config["images_position"] = $request->images_position;
                if ($request->circle_position != "none") {
                    $config["circle_pos"] = config("templates.AmazonFresh.circle_pos")[$request->circle_position];
                    $config["circle_color"] = $request->circle_color;
                }
                $config["compress_size"] = 120;
            }
        }

        return $config;
    }

    public function get_output_filename($request, $template)
    {
        $output_filename = $request->output_filename;
        $project_name = $request->project_name;
        $customer = $request->customer;

        $filename = !empty($output_filename) ? $output_filename : (!empty($project_name) ? $project_name : "output");
        // Add template name in output filename
        $index = ($customer == "amazon_fresh") ? $template : $template + 1;
        $config = config("templates." . (($customer == "amazon_fresh") ? "AmazonFresh." : "Generic.") . "output_dimensions");
        $filename = $filename . "_" . $config[$index];
        return $filename;
    }

    public function get_font_metrics($text, $font, $font_size)
    {
        $im = new \Imagick();
        $draw = new \ImagickDraw();
        $draw->setFont($font);
        $draw->setFontSize($font_size);
        $draw->setTextAlignment(\Imagick::ALIGN_LEFT);
        $metrics = $im->queryFontMetrics($draw, $text, true);
        return $metrics;
    }

    public function get_multiline_text($text, $font, $font_size, $max_width)
    {
        $result = array();
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $list = array(" " => " ;", "." => ".;", "," => ",;", "!" => "!;", "*" => "*;", "]" => "];", ")" =>  ");", "&" => "&;");
            $find = array_keys($list);
            $replace = array_values($list);
            $array = explode(";", str_ireplace($find, $replace, $line));
            $line_width = 0;
            $line_str = "";
            foreach ($array as $t) {
                if ($t != "") {
                    $list = array("<sup>" => ";<sup>", "<sub>" => ";<sub>", "</sup>" => "</sup>;", "</sub>" => "</sub>;");
                    $find = array_keys($list);
                    $replace = array_values($list);
                    $subarray = explode(";", str_ireplace($find, $replace, $t));
                    $w = 0;
                    foreach ($subarray as $st) {
                        if ($st != "") {
                            if (strpos($st, "<sup>") !== false) {
                                $metrics = $this->get_font_metrics(str_replace(array("<sup>", "</sup>"), "", $st), $font, $font_size / 2);
                            } else if (strpos($st, "<sub>") !== false) {
                                $metrics = $this->get_font_metrics(str_replace(array("<sub>", "</sub>"), "", $st), $font, $font_size / 2);
                            } else {
                                $metrics = $this->get_font_metrics($st, $font, $font_size);
                            }
                            $w += $metrics["textWidth"];
                        }
                    }
                    if ($line_width + $w < $max_width) {
                        $line_width += $w;
                        $line_str = $line_str . $t;
                    } else {
                        if ($line_str != "")
                            $result[] = trim($line_str);
                        $line_width = $w;
                        $line_str = $t;
                    }
                }
            }
            $result[] = trim($line_str);
        }
        return $result;
    }

    public function get_text_image($text, $font, $font_size, $font_color, $trim = null)
    {
        $im = new \Imagick();
        $draw = new \ImagickDraw();
        $draw->setFont($font);
        $draw->setFontSize($font_size);
        $draw->setFillColor($font_color);
        $metrics = $im->queryFontMetrics($draw, $text, true);

        $total_width = 0;
        $total_height = $metrics["textHeight"];
        $baseline = $metrics["ascender"];
        $subscript_font_size = $font_size / 2;
        $subscript_baseline = $baseline + $subscript_font_size / 3;
        $superscript_font_size = $font_size / 2;
        $superscript_baseline = abs($metrics["descender"]) + $superscript_font_size / 2;

        // superscript & subscript

        $splitters = array("<sup>" => ";<sup>", "<sub>" => ";<sub>", "</sup>" => "</sup>;", "</sub>" => "</sub>;");
        $find = array_keys($splitters);
        $replace = array_values($splitters);
        $texts = explode(";", str_ireplace($find, $replace, $text));
        foreach ($texts as $t) {
            if (!empty($t)) {
                if (strpos($t, "<sup>") !== false) {
                    $superscript = str_replace(array("<sup>", "</sup>"), "", $t);
                    $draw->setFontSize($superscript_font_size);
                    $metrics = $im->queryFontMetrics($draw, $superscript);
                    $draw->annotation($total_width, $superscript_baseline, $superscript);
                    $total_width += $metrics["textWidth"];
                } else if (strpos($t, "<sub>") !== false) {
                    $subscript = str_replace(array("<sub>", "</sub>"), "", $t);
                    $draw->setFontSize($subscript_font_size);
                    $metrics = $im->queryFontMetrics($draw, $subscript);
                    $draw->annotation($total_width, $subscript_baseline, $subscript);
                    $total_width += $metrics["textWidth"];
                } else {
                    // period issue
                    $splitters = ["P." => "P;.", "F." => "F;.", "T." => "T;.", "V." => "V;.", "W." => "W;.", "Y." => "Y;.", "r." => "r;.", "v." => "v;.", "w." => "w;.", "y." => "y;."];
                    $find = array_keys($splitters);
                    $replace = array_values($splitters);
                    $array = explode(";", str_ireplace($find, $replace, $t));
                    $draw->setFontSize($font_size);
                    foreach ($array as $x) {
                        $metrics = $im->queryFontMetrics($draw, $x);
                        $margin = strpos($x, ".") === 0 ? $font_size / 8 : 0;
                        $draw->annotation($total_width - $margin, $baseline, $x);
                        $total_width += $metrics["textWidth"] - $margin;
                    }
                }
            }
        }

        $im->newImage($total_width, $total_height, "transparent");
        $im->drawImage($draw);
        if ($trim) {
            $im->trimImage(0);
            $im->setImagePage(0, 0, 0, 0);
        }
        return $im;
    }

    public function get_circle_image($r, $c)
    {
        $draw = new \ImagickDraw();
        $fillColor = new \ImagickPixel($c);
        $backgroundColor = new \ImagickPixel("transparent");

        $draw->setFillColor($fillColor);
        $draw->circle($r, $r, $r + $r, $r);

        $im = new \Imagick();
        $im->newImage($r * 2 + 1, $r * 2 + 1, $backgroundColor);
        $im->setImageFormat("png");
        $im->drawImage($draw);

        return $im;
    }

    public function set_image_position($image, $x, $y)
    {
        $w = $image->getImageWidth();
        $h = $image->getImageHeight();
        $image->setImagePage($w, $h, $x, $y);
        return $image;
    }

    public function get_image_layer($layername, $left, $top, $right, $bottom, $filename, $shadow = null, $mirror = null, $fade = null)
    {
        $layer = array(
            "type" => "image",
            "name" => $layername,
            "left" => $left,
            "top" => $top,
            "right" => $right,
            "bottom" => $bottom,
            "image" => $filename
        );

        if ($shadow && $shadow != "none") {
            $layer["shadow"] = 1;
            $layer["shadow_color_argb_hex"] = ltrim(Setting::where("key", "DropShadow_Color")->first()->value, "#");
            $layer["shadow_radius"] = intval(Setting::where("key", "DropShadow_Radius")->first()->value);
            $layer["shadow_x_offset"] = intval(Setting::where("key", "DropShadow_X")->first()->value) * ($shadow == "left" ? -1 : 1);
            $layer["shadow_y_offset"] = intval(Setting::where("key", "DropShadow_Y")->first()->value);
        }

        if ($mirror) {
            $layer["mirror"] = 1;
        }

        if ($fade) {
            $layer["mirror_fade"] = 1;
        }

        return $layer;
    }

    public function get_color_layer($layername, $left, $top, $right, $bottom, $color, $border = null)
    {
        $layer = array(
            "type" => "image",
            "name" => $layername,
            "left" => $left,
            "top" => $top,
            "right" => $right,
            "bottom" => $bottom,
            "color_argb_hex" => ltrim($color, "#"),
            "border" => -1,
        );
        if ($border) {
            $layer["border"] = $border;
        }

        return $layer;
    }

    public function get_text_layer($layername, $left, $top, $width, $height, $text, $font, $font_size, $color, $alignment, $text_tracking, $direction = "horizontal", $border = null, $border_color = null, $border_padding = null, $border_radius = null)
    {
        $layer = array(
            "type" => "text",
            "name" => $layername,
            "left" => round($left),
            "top" => round($top),
            "width" => round($width),
            "height" => round($height),
            "text" => $text,
            "font_family_and_style" => $this->get_psd_font($font),
            "size" => $font_size,
            "color_argb_hex" => ltrim($color, "#"),
            "vertical_distance_between_lines" => $font_size,
            "horizontal_aligment" => $alignment,
            "text_tracking" => $text_tracking,
            "direction" => $direction
        );

        if ($layername != 'CTA') {
            $layer["kerning"] = "optical";
        } else {
            $layer["text_tracking"] = 0;
        }

        if ($border > 0) {
            $layer["border"] = $border;
            $layer["border_color"] = $border_color;
            if ($border_padding > 0) {
                $layer["border_padding"] = $border_padding;
            }
            if ($border_radius > 0) {
                $layer["border_radius"] = $border_radius;
            }
        }

        if (strpos($text[0], "<sup>") !== false) {
            $layer["superscript_size"] = $font_size / 2;
            $pos = strpos($text[0], "<sup>");
            $w = $this->get_font_metrics(preg_replace("/<sup>.*<\/sup>/", "", $text[0]), $font, $font_size)["textWidth"];
            $offset = $w / $font_size * 100;
            if ($alignment == "left") {
                $layer["superscript_offset_x_in_percent_of_size"] = $pos == 0 ? -1 * $font_size / 2 : $offset;
            } else if ($alignment == "center") {
                $layer["superscript_offset_x_in_percent_of_size"] = $pos == 0 ? -1 * $offset / 2 : $offset / 2;
            } else if ($alignment == "right") {
                $layer["superscript_offset_x_in_percent_of_size"] = $pos == 0 ? -1 * $offset : $font_size / 2;
            }
            $layer["superscript_offset_y_in_percent_of_size"] = -1 * $font_size / 2;
        }
        return $layer;
    }

    public function get_text_layer2($layername, $left, $top, $width, $height, $text, $font, $font_file, $font_size, 
                                    $color, $alignment, $text_tracking, $shadow = false, $kerning = "none", $oversampling = null)
    {
        $layer = array(
            "name" => $layername,
            "type" => "text",
            "text" => $text,
            "align" => $alignment,
            "left" => round($left),
            "top" => round($top),
            "width" => round($width),
            "height" => round($height),
            "color" => $color,
            "font" => $font,
            "fontsize" => $font_size,
            "font_file" => $font_file,
            "tracking" => $text_tracking,
            "kerning"=> $kerning,
            "shadow"=> !$shadow ? null : [
                "angle" => 145,
                "distance" => 10,
                "opacity" => 50,
                "size" => 1,
                "spread" => 5
            ]
        );

        if ($oversampling) {
            $layer["oversampling"] = $oversampling;
        }

        return $layer;
    }

    public function get_text_layer3($layername, $left, $top, $width, $height, $text, $font, $font_file, $font_size, $color, $alignment, $text_tracking, $shadow = false, $kerning = "none")
    {
        $layer = array(
            "name" => $layername,
            "type" => "multilinetext",
            "text" => $text,
            "align" => $alignment,
            "coordinates" => [
                [
                    "left" => $left,
                    "top" => $top,
                    "width" => $width,
                    "height" => $height,
                ]
            ],
            "color" => $color,
            "font" => $font,
            "fontsize" => $font_size,
            "font_file" => $font_file,
            "tracking" => $text_tracking,
            "kerning"=> $kerning,
            "shadow"=> !$shadow ? null : [
                "angle" => 145,
                "distance" => 10,
                "opacity" => 50,
                "size" => 1,
                "spread" => 5
            ]
        );

        return $layer;
    }

    public function get_circle_layer($layername, $left, $top, $r, $color)
    {
        return [
            "type" => "circle",
            "name" => $layername,
            "top" => $top,
            "left" => $left,
            "radius" => $r,
            "color" => $color
        ];
    }

    /* PSD2 json */
    public function get_pixel_layer($layername, $left, $top, $width, $height, $fill_color, $border_width = 0, 
                                    $border_color = "#000000", $round_radius = 10, $round_corners = [0, 0, 0, 0], 
                                    $gradient = null, $opacity = 1)
    {
        $layer = array(
            "type" => "pixel",
            "name" => $layername,
            "left" => $left,
            "top" => $top,
            "width" => $width,
            "height" => $height,
            "corners" => array(
                "round_corners" => $round_corners,
                "round_radius" => $round_radius
            ), 
            "opacity" => $opacity
        );
        if ($fill_color != null) {
            $layer["fill_color"] = $fill_color;
        } else if ($gradient) {
            $layer["gradient"] = $gradient;
        }

        if ($border_width) {
            $layer["border_width"] = $border_width;
            $layer["border_color"] = $border_color;
        }

        return $layer;
    }

    public function get_smartobject_layer($layername, $filename, $left, $top, $width, $height, $rotation = 0, $shadow = null, $mirror = null, $mirror_fade = 0, $mirror_offset = 0, $crop = null)
    {
        $shadow_array = null;
        if ($shadow == "left" || $shadow == "center" || $shadow == "right" || $shadow == "none") {
            $shadow_array = array(
                "angle" => ($shadow == "left") ? 35 : 145,
                "opacity" => 36,
                "distance" => ($shadow == "center") ? 10 : 35,
                "spread" => 5,
                "size" => 5
            );
        } else {
            $shadow_array = $shadow;
        }

        $layer = array(
            "type" => "smartobject",
            "name" => $layername,
            "file_name" => $filename,
            "left" => $left,
            "top" => $top,
            "width" => $width,
            "height" => $height,
            "rotation" => $rotation,
            "mirror" => $mirror,
            "mirror_fade" => $mirror_fade,
            "mirror_offset" => $mirror_offset,
            "shadow" => $shadow_array
        );

        if ($crop !== null) {
            $layer["crop"] = $crop;
        }

        return $layer;
    }

    
    public function get_multiline_text_layer($layername, $left, $top, $width, $height, $coordinates, $text, $font, $font_file, $font_size, $color, $alignment, $text_tracking, $shadow = false, $kerning = "none")
    {
        $layer = array(
            "type" => "text",
            "name" => $layername,
            "left" => $left,
            "top" => $top,
            "width" => $width,
            "height" => $height,
            "text" => $text,
            "font" => $font,
            "font_file" => $font_file,
            "fontsize" => $font_size,
            "color" => $color,
            "align" => $alignment,
            "tracking" => $text_tracking,
            "kerning"=> $kerning,
            "shadow"=> !$shadow ? null : [
                "angle" => 145,
                "distance" => 10,
                "opacity" => 50,
                "size" => 1,
                "spread" => 5
            ], 
            "coordinates" => $coordinates
        );

        return $layer;
    }

    // --------------------------

    public function get_psd_font($fontname)
    {
        $fonts = array(
            "Amazon-Ember" => "AmazonEmber-Regular", "Amazon-Ember-Bold" => "AmazonEmber-Bold", "Amazon-Ember-Bold-Italic" => "AmazonEmber-BoldItailc",
            "Amazon-Ember-Heavy" => "AmazonEmber-Heavy", "Amazon-Ember-Heavy-Italic" => "AmazonEmber-HeavyItalic", "Amazon-Ember-Light" => "AmazonEmber-Light",
            "Amazon-Ember-Light-Italic" => "AmazonEmber-LightItalic", "Amazon-Ember-Medium" => "AmazonEmber-Medium", "Amazon-Ember-Medium-Italic" => "AmazonEmber-MediumItalic",
            "Arial" => "ArialMT", "Arial-Bold" => "Arial-BoldMT", "Arial-Bold-Italic" => "Arial-BoldItailcMT", "Arial-Italic" => "Arial-ItalicMT",
            "Helvetica-Narrow" => "Helvetica-Light", "Helvetica-Narrow-Oblique" => "Helvetica-LightOblique",
            "Noto-Sans-Bold" => "NotoSans-Bold", "Noto-Sans-Regular" => "NotoSans-Regular", "Proxima-Nova-Black" => "ProximaNova-Black",
            "Proxima-Nova-Bold" => "ProximaNova-Bold", "Proxima-Nova-Extrabld" => "ProximaNova-Extrabld", "Proxima-Nova-Regular-It" => "ProximaNova-RegularIt",
            "Proxima-Nova-Semibold" => "ProximaNova-Semibold",
        );

        if (isset($fonts[$fontname])) {
            return $fonts[$fontname];
        }
        return $fontname;
    }

    public function get_psd($files, $product_filenames, $config)
    {
        $json = array(
            "width" => $config["width"],
            "height" => $config["height"],
            "layers" => array()
        );

        /* Circle */
        if ($config["customer"] == "amazon_fresh" && isset($config["circle_pos"])) {
            $r = $config["circle_pos"]["radius"];
            $json["layers"][] = $this->get_circle_layer("circle", $config["circle_pos"]["x"], $config["circle_pos"]["y"], $r, $config["circle_color"]);
        }

        /* Product */
        $count = count($files);
        $products = array();
        $sum_width = 0;
        $max_height = 0;
        $products_width = $config["product_dimensions"]["width"] - $config["product_space"] * ($count - 1);
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
                $products[$i]->scaleImage($w, $h, true);
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
        if ($config["customer"] == "amazon_fresh") {
            $x += $config["images_position"];
        }

        for ($j = 0; $j < $count; $j++) {
            $i = $product_side_orders[$j];
            $margin = ($i != 0 ? $config["product_space"] : 0);
            $x += $margin;
            $y = $config["product_dimensions"]["baseline"] - $products[$i]->getImageHeight() - ($config["customer"] == "amazon_fresh" ? $config["bottom_position"] : 0);
            $w = $products[$i]->getImageWidth();
            $h = $products[$i]->getImageHeight();
            if ($config["customer"] == "amazon_fresh") {
                $product_layers[] = $this->get_smartobject_layer($files[$i]["name"], $product_filenames[$i], $x + $margin, $y, $w, $h, 0, $config["drop_shadow"], $config["image_shadow"], $config["fade"], 0);
            } else {
                $product_layers[] = $this->get_smartobject_layer($files[$i]["name"], $product_filenames[$i], $x + $margin, $y, $w, $h, 0, $config["drop_shadow"], $config["image_shadow"], $config["fade"], 0);
            }
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
        if ($config["customer"] == "amazon_fresh") {
            $text_width = 420;
            $dimension = config("templates.AmazonFresh.output_dimensions")[$config["template"]];
            $rows = config("templates.AmazonFresh.dimensions_map")[$dimension];
            $default_texts = config("templates.AmazonFresh.default_texts")[$dimension];
            foreach ($rows as $row) {
                $left = intval(Setting::where("key", "AmazonFresh_" . $dimension . "_" . $row . "_Left")->first()->value);
                $top = intval(Setting::where("key", "AmazonFresh_" . $dimension . "_" . $row . "_Top")->first()->value);
                $fontsize = intval(Setting::where("key", "AmazonFresh_" . $dimension . "_" . $row . "_FontSize")->first()->value);
                $fontfamily = Setting::where("key", "AmazonFresh_" . $dimension . "_" . $row . "_FontFamily")->first()->value;
                $text = array();
                if ($row == "NEW") {
                    $text[] = isset($config["headline"][0]) ? $config["headline"][0] : $default_texts[$row];
                } else if ($row == "1H") {
                    $text[] = $dimension == "NEW+1H" ? (isset($config["subheadline"][0]) ? $config["subheadline"][0] : $default_texts[$row]) : (isset($config["headline"][0]) ? $config["headline"][0] : $default_texts[$row]);
                } else if ($row == "2H") {
                    $text[] = isset($config["headline"][1]) ? $config["headline"][1] : $default_texts[$row];
                } else if ($row == "3H") {
                    $text[] = isset($config["headline"][2]) ? $config["headline"][2] : $default_texts[$row];
                } else if ($row == "1S") {
                    $text[] = isset($config["subheadline"][0]) ? $config["subheadline"][0] : $default_texts[$row];
                } else if ($row == "2S") {
                    $text[] = isset($config["subheadline"][1]) ? $config["subheadline"][1] : $default_texts[$row];
                }
                if (!empty($text[0])) {
                    $json["layers"][] = $this->get_text_layer($row, $left, $top, $text_width, $fontsize, $text, $fontfamily, $fontsize, "#000000", "left", $config["text_tracking"]);
                }
            }

            /* Legal */
            if (!empty($config["CTA"])) {
                $json["layers"][] = $this->get_text_layer("CTA", $config["CTA_pos"]["left"], $config["CTA_pos"]["top"], $config["CTA_pos"]["right"] - $config["CTA_pos"]["left"], $config["CTA_font_size"], array($config["CTA"]), $config["CTA_font"], $config["CTA_font_size"], $config["CTA_color"], "center", $config["text_tracking"]);
            }
        } else {
            /* Headline */
            $y_pos = $config["headline_pos"]["top"] + 10;
            $x_pos = $config["headline_pos"]["left"];
            $headlines = $config["headline"];
            if ($config["multi_headline"]) {
                if ($config["template"] == 3 || $config["template"] == 4) {
                    for ($i = 0; $i < count($headlines); $i++) {
                        if (!empty($headlines[$i])) {
                            $t = $headlines[$i] . " ";
                            $w = $this->get_font_metrics($t, $config["headline_font"][$i], $config["headline_font_size"][$i])["textWidth"];
                            $json["layers"][] = $this->get_text_layer("Headline " . ($i + 1), $x_pos, $y_pos, $w, $config["headline_font_size"][$i], array($t), $config["headline_font"][$i], $config["headline_font_size"][$i], $config["headline_color"][$i], $config["headline_alignment"][$i], $config["text_tracking"]);
                            $x_pos += $w;
                        }
                    }
                } else {
                    for ($i = 0; $i < count($headlines); $i++) {
                        if (!empty($headlines[$i])) {
                            $json["layers"][] = $this->get_text_layer("Headline " . ($i + 1), $x_pos, $y_pos, $config["headline_pos"]["right"] - $x_pos, $config["headline_font_size"][$i], array($headlines[$i]), $config["headline_font"][$i], $config["headline_font_size"][$i], $config["headline_color"][$i], $config["headline_alignment"][$i], $config["text_tracking"]);
                            $y_pos += $config["headline_font_size"][$i];
                        }
                    }
                }
            } else {
                if (($config["template"] == 3 || $config["template"] == 4) && !empty($config["headline"][0])) {
                    $headlines = array($config["headline"][0]);
                    $json["layers"][] = $this->get_text_layer("Headline", $config["headline_pos"]["left"], $y_pos, $config["headline_pos"]["right"] - $config["headline_pos"]["left"], $config["headline_pos"]["bottom"] - $y_pos, $headlines, $config["headline_font"][0], $config["headline_font_size"][0], $config["headline_color"][0], $config["headline_alignment"][0], $config["text_tracking"]);
                } else {
                    $headlines = $this->get_multiline_text($config["headline"][0], $config["headline_font"][0], $config["headline_font_size"][0], $config["headline_pos"]["right"] - $config["headline_pos"]["left"]);
                    for ($i = 0; $i < count($headlines); $i++) {
                        if (!empty($headlines[$i])) {
                            $json["layers"][] = $this->get_text_layer("Headline " . ($i + 1), $config["headline_pos"]["left"], $y_pos, $config["headline_pos"]["right"] - $config["headline_pos"]["left"], $config["headline_pos"]["bottom"] - $y_pos, array($headlines[$i]), $config["headline_font"][0], $config["headline_font_size"][0], $config["headline_color"][0], $config["headline_alignment"][0], $config["text_tracking"]);
                            $y_pos += $config["headline_font_size"][0];
                        }
                    }
                }
            }

            /* Sub-headline */
            if (!empty($config["subheadline"][0]) && $config["subheadline_pos"]["right"] - $config["subheadline_pos"]["left"] > 0) {
                $subheadline_texts = $this->get_multiline_text($config["subheadline"][0], $config["subheadline_font"], $config["subheadline_font_size"], $config["subheadline_pos"]["right"] - $config["subheadline_pos"]["left"]);
                $json["layers"][] = $this->get_text_layer("Subheadline", $config["subheadline_pos"]["left"], $config["subheadline_pos"]["top"] + 10, $config["subheadline_pos"]["right"] - $config["subheadline_pos"]["left"], $config["subheadline_pos"]["bottom"] - $config["subheadline_pos"]["top"], $subheadline_texts, $config["subheadline_font"], $config["subheadline_font_size"], $config["subheadline_color"], $config["subheadline_alignment"], $config["text_tracking"]);
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
                $cta_texts = $this->get_multiline_text($config["CTA"], $config["CTA_font"], $config["CTA_font_size"], $cta_width_limit);

                foreach ($cta_texts as $cta) {
                    $metrics = $this->get_font_metrics($cta, $config["CTA_font"], $config["CTA_font_size"]);
                    $cta_total_height += $metrics["textHeight"];
                    $max_cta_width = $max_cta_width < $metrics["textWidth"] ? $metrics["textWidth"] : $max_cta_width;
                }

                if ($config["CTA_opaque"]) {
                    $json["layers"][] = $this->get_color_layer("CTA background", 0, $config["CTA_pos"]["top"], $config["width"], $config["CTA_pos"]["top"] + $cta_total_height, "ffffff");
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
                $json["layers"][] = $this->get_text_layer("CTA", $x, $y_pos, $max_cta_width + $config["CTA_font_size"], $cta_total_height, $cta_texts, $config["CTA_font"], $config["CTA_font_size"], $config["CTA_color"], "left", $config["text_tracking"], $config["CTA_border_width"], $config["CTA_border_color"], $config["CTA_border_padding"], $config["CTA_border_radius"],);
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
                // $json["layers"][] = $this->get_image_layer("button", $x, $y, $x + $w, $y + $h, $config["button"], "none");
                $json["layers"][] = $this->get_smartobject_layer("button", $config["button"], $x, $y, $w, $h, 0);
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
                // $json["layers"][] = $this->get_image_layer("retailer logo", $x, $y_pos, $x + $w, $y_pos + $h, $config["logo"], "none");
                $json["layers"][] = $this->get_smartobject_layer("retailer logo", $config["logo"], $x, $y_pos, $w, $h, 0);
            }
            if ($config["border"]) {
                $json["layers"][] = $this->get_color_layer("psd border", 0, 0, $config["width"], $config["height"], $config["border_color"], 1);
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
            array_unshift($json["layers"], $this->get_smartobject_layer("Background", $config["background"], ($config["width"] - $image_width) / 2, ($config["height"] - $image_height) / 2, $image_width, $image_height, 0));
        } else {
            array_unshift($json["layers"], $this->get_pixel_layer("Background", 0, 0, $config["width"], $config["height"], $config["background_color"]));
        }

        return base64_encode(json_encode($json));
    }

    public function get_history_settings($request)
    {
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

        if ($request->customer == "amazon_fresh") {
            $settings["circle_color"] = $request->circle_color;
            $settings["circle_position"] = $request->circle_position;
            $settings["color_name"] = $request->color_name;
            $settings["bottom_position"] = $request->bottom_position;
            $settings["images_position"] = $request->images_position;
        } else {
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
        }

        return json_encode($settings);
    }

    public function get_nf_image_path($file)
    {
        $file_id = explode('.', $file["name"])[0];
        $strArr = explode('.', $file["nf_url"]);
        $file_extension = end($strArr);
        // $nf_image_path = "files/".$file["company_id"]."/Nutrition_Facts_Images/".$file_id.".".$file_extension;
        if (auth()->user()->isMasterAdmin()) {
            $nf_image_path = "files/" . $file["company_id"] . "/Nutrition_Facts_Images/" . $file_id . ".jpg";
        } else {
            $nf_image_path = "files/" . auth()->user()->company_id . "/Nutrition_Facts_Images/" . $file_id . ".jpg";
        }
        if (Storage::disk('s3')->exists($nf_image_path)) return $nf_image_path;
        return null;
    }

    public function get_ingredient_image_path($file)
    {
        if ($file['ingredient_url'] == "" || $file['nf_url'] == $file['ingredient_url']) return null;
        $file_id = explode('.', $file["name"])[0];
        $strArr = explode('.', $file["ingredient_url"]);
        $file_extension = end($strArr);
        // $ingredient_image_path = "files/".$file["company_id"]."/Ingredients_Images/".$file_id.".".$file_extension;
        if (auth()->user()->isMasterAdmin()) {
            $ingredient_image_path = "files/" . $file["company_id"] . "/Ingredients_Images/" . $file_id . ".jpg";
        } else {
            $ingredient_image_path = "files/" . auth()->user()->company_id . "/Ingredients_Images/" . $file_id . ".jpg";
        }
        if (Storage::disk('s3')->exists($ingredient_image_path)) return $ingredient_image_path;
        return null;
    }

    public function canRun($request)
    {
        $file_ids = preg_replace('/\s+/', " ", $request->file_ids);
        $result = $this->map_files(explode(" ", $file_ids));
        if ($result["status"] == "error") {
            return $result;
        }

        $count = count($result["files"]);
        // $count += count($request->files('products'));
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
        return $result;
    }

    public function run($request, $preview = false, $save = false, $publish = false)
    {
        // Map files from UTC/GTIN/ASIN/TCIN/WMT-ID
        $file_ids = preg_replace('/\s+/', " ", $request->file_ids);
        $result = $this->map_files(explode(" ", $file_ids));

        if ($result["status"] == "error") {
            return $result;
        }

        $count = count($result["files"]);
        // $count += count($request->files('products'));
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
            $filename = uniqid() . ".png";
            $contents = Storage::disk('s3')->get($file["path"]);
            Storage::disk('public')->put($filename, $contents);
            $product_filenames[] = $filename;
            $temp_files[] = $filename;
        }

        /*
        foreach($request->files('products') as $product_file) {
            $product = new \Imagick();
            $product->readImageBlob(file_get_contents($product_file));
            $product->trimImage(0);
            $product->setImagePage(0, 0, 0, 0);
            $product->setImageFormat("png");
            $product->writeImage($filename);
            $product_filenames[] = $filename;
            $temp_files[] = $filename;
        }
        */

        // Save background image if exists in request.
        $background_filename = null;
        if (isset($request->background)) {
            $background_filename = uniqid() . "." . $request->background->getClientOriginalExtension();
            $temp_files[] = $background_filename;
            file_put_contents($background_filename, file_get_contents($request->file("background")));
        }

        // Save button image if exists in request.
        $button_filename = null;
        if (isset($request->button)) {
            $button_filename = uniqid() . "." . $request->button->getClientOriginalExtension();
            $temp_files[] = $button_filename;
            file_put_contents($button_filename, file_get_contents($request->file("button")));
        }

        // Save logo image if exists in request.
        $logo_filename = null;
        if (isset($request->logo)) {
            $logo_filename = uniqid() . "." . $request->logo->getClientOriginalExtension();
            $temp_files[] = $logo_filename;
            file_put_contents($logo_filename, file_get_contents($request->file("logo")));
        }

        $response = [];

        if ($preview) {
            $templates = [];
            $response = ["files" => [], "log" => []];
            if ($request->customer == "amazon_fresh") {
                $templates[] = $request->output_dimensions;
            } else {
                if ($request->output_dimensions >= 0) {
                    $templates[] = $request->output_dimensions;
                } else {
                    $templates = array(0, 1, 2, 3, 4);
                }
            }
            foreach ($templates as $template) {
                $config = $this->get_config($request, $template, $background_filename, $button_filename, $logo_filename);
                $jpeg_file_id = uniqid();
                $jpeg_file = $jpeg_file_id . ".jpg";
                $log = "";

                $input_arg = $this->get_psd($result["files"], $product_filenames, $config);
                $pil_font = Setting::where('key', 'pil_font')->first()->value;
                if ($pil_font == "on") {
                    $command = escapeshellcmd("python3 /var/www/psd2/tool.py --pil-font -j " . $input_arg . " -of result -p " . $jpeg_file_id);
                } else {
                    $command = escapeshellcmd("python3 /var/www/psd2/tool.py -j " . $input_arg . " -of result -p " . $jpeg_file_id);
                }
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
                    'fileid' => $file_ids,
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
                    $templates = array();
                    $output_jpg_files = array();
                    if ($request->customer == "amazon_fresh") {
                        $templates[] = $request->output_dimensions;
                    } else {
                        if ($request->output_dimensions >= 0) {
                            $templates[] = $request->output_dimensions;
                        } else {
                            $templates = array(0, 1, 2, 3, 4);
                        }
                    }
    
                    foreach ($templates as $template) {
                        $config = $this->get_config($request, $template, $background_filename, $button_filename, $logo_filename);
                        $output_filename = $this->get_output_filename($request, $template);
    
                        $psd_file_id = uniqid();
                        $psd_file = $psd_file_id . ".psd";
                        $jpeg_file_id = uniqid();
                        $jpeg_file = $jpeg_file_id . ".jpg";
    
                        $input_arg = $this->get_psd($result["files"], $product_filenames, $config);
                        $includ_psd_cmd = "";
                        if ($request->include_psd) {
                            $includ_psd_cmd = " -o " . $psd_file_id;
                        }
                        $pil_font = Setting::where('key', 'pil_font')->first()->value;
                        if ($pil_font == "on") {
                            $command = escapeshellcmd("python3 /var/www/psd2/tool.py --pil-font -j " . $input_arg . " -of " . $zip_file_id . $includ_psd_cmd . " -p " . $jpeg_file_id);
                        } else {
                            $command = escapeshellcmd("python3 /var/www/psd2/tool.py -j " . $input_arg . " -of " . $zip_file_id . $includ_psd_cmd . " -p " . $jpeg_file_id);
                        }
                        $log = shell_exec($command . " 2>&1");
                        $temp_files = array_merge($temp_files, array("tmp_result.psd", "shadow.png", "circle.png"));
                        $temp_files[] = $psd_file;
                        $temp_files[] = $jpeg_file;
    
                        $zip->addFile($jpeg_file, $output_filename . ".jpg");
                        $output_jpg_files[] = $jpeg_file;
                        if ($request->include_psd) {
                            $zip->addFile($psd_file, $output_filename . ".psd");
                        }
                    }
    
                    $zip->close();
    
                    Storage::disk('s3')->put('outputs/' . $zip_file, file_get_contents(public_path($zip_file)));
                    $temp_files[] = $zip_file;
    
                    foreach ($output_jpg_files as $filename) {
                        Storage::disk('s3')->put('outputs/jpg/' . $filename, file_get_contents(public_path($filename)));
                    };
    
                    if ($save) {
                        $this->draftService->store([
                            'name' => $zip_filename,
                            'customer' => $request->customer,
                            'output_dimensions' => $request->output_dimensions,
                            'projectname' => $request->project_name,
                            'url' => 'outputs/' . $zip_file,
                            'fileid' => $file_ids,
                            'headline' => implode(" ", $request->headline),
                            'size' => $request->customer == "amazon_fresh" ? config("templates.AmazonFresh.output_dimensions")[$request->output_dimensions] : config("templates.Generic.output_dimensions")[$request->output_dimensions + 1],
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
                            'url' => 'outputs/' . $zip_file,
                            'fileid' => $file_ids,
                            'headline' => implode(" ", $request->headline),
                            'size' => $request->customer == "amazon_fresh" ? config("templates.AmazonFresh.output_dimensions")[$request->output_dimensions] : config("templates.Generic.output_dimensions")[$request->output_dimensions + 1],
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

    public function save_draft($data)
    {
        $this->draftService->store($data);
    }

    public function save_project($data)
    {
        $this->projectService->store($data);
    }

    public function generate_thumbnail(bool $again = false)
    {
        $files = File::all();
        $count = 0;
        if ($again) {
            if (Storage::disk('s3')->exists('files/thumbnails/128x128')) {
                Storage::disk('s3')->deleteDirectory('files/thumbnails/128x128');
            }
        }
        foreach ($files as $file) {
            if ($again || $file->thumbnail == '') {
                $file->thumbnail = 'files/thumbnails/128x128/' . $file->company_id . '/' . $file->name;
                $file->save();
                $count++;
            }
        }

        return $count;
    }
}
