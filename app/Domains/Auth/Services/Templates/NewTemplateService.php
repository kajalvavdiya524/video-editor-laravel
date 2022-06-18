<?php

namespace App\Domains\Auth\Services\Templates;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Exceptions\GeneralException;
use App\Services\BaseService;
use App\Domains\Auth\Services\BannerService;
use App\Domains\Auth\Services\ThemeService;
use App\Domains\Auth\Services\GridLayoutService;
use App\Domains\Auth\Models\Setting;
use App\Domains\Auth\Models\Template;
use App\Domains\Auth\Models\TemplateField;
use Exception;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Class NewTemplateService.
 */
class NewTemplateService extends BaseService
{
    protected $bannerService;
    protected $themeService;
    protected $gridLayoutService;

    /**
     * NewTemplateService constructor.
     *
     */
    public function __construct(BannerService $bannerService, ThemeService $themeService, GridLayoutService $gridLayoutService)
    {
        $this->bannerService = $bannerService;
        $this->themeService = $themeService;
        $this->gridLayoutService = $gridLayoutService;
    }

    private function get_config($request)
    {
        $config = $request->all();
        $template_id = $request->template_id;

        $array_keys = ['x_offset', 'y_offset', 'angle', 'scale', 'moveable'];
        foreach ($array_keys as $key) {
            if (isset($config[$key]) && !is_array($config[$key])) {
                $config[$key] = explode(",", $config[$key]);
            }
        }

        $current_template = Template::where('id', $template_id)->first();
        $config["width"] = $current_template->width;
        $config["height"] = $current_template->height;
        $config["group_x"] = 0;
        $config["group_y"] = 0;

        if (isset($request->dpi)) {
            $config["dpi"] = intval($request->dpi);
        }
        if (isset($request->background)) {
            $config["background"] = [];
            $backgrounds = $request->background;
            if (!is_array($backgrounds)) {
                $backgrounds = explode(",", $backgrounds);
            }
            foreach ($backgrounds as $bg) {
                $background_filename = null;
                if (isset($bg) && !empty($bg)) {
                    $background_filename = uniqid() . ".png";
                    file_put_contents($background_filename, file_get_contents($bg));
                }
                $config["background"][] = $background_filename;
            }
        }
        if (isset($request->img_from_bk)) {
            $config["img_from_bk"] = [];
            $backgrounds = $request->img_from_bk;
            if (!is_array($backgrounds)) {
                $backgrounds = explode(",", $backgrounds);
            }
            foreach ($backgrounds as $bg) {
                $background_filename = null;
                if (isset($bg)) {
                    $background_filename = uniqid() . ".png";
                    file_put_contents($background_filename, file_get_contents($bg));
                }
                $config["img_from_bk"][] = $background_filename;
            }
        }

        $files = $request->file();
        foreach ($files as $key => $value) {
            $filename = null;
            if (isset($request->{$key}) && $request->{$key} != '') {
                $filename = uniqid() . "." . $request->{$key}->getClientOriginalExtension();
                file_put_contents($filename, file_get_contents($request->file($key)));
            } else if (isset($request->{$key . "_saved"})) {
                $filename = uniqid() . ".png";
                file_put_contents($filename, file_get_contents($request->{$key . "_saved"}));
            }
            $config[$key] = $filename;
        }

        $theme = $this->themeService->getById(intval($request->theme));
        $config["shadow"] = null;
        if (isset($theme)) {
            // Shadow (Amazon only)

            $attributes = json_decode($theme->attributes);
            foreach ($attributes as $key => $attr) {
                if ($attr->name == "Shadow Effects") {
                    $shadow_attrs = $attr->list;
                }
            }

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

        return $config;
    }

    private function get_layout_config($settings)
    {
        $config = $settings;
        $template_id = $settings['template_id'];

        $array_keys = ['x_offset', 'y_offset', 'angle', 'scale', 'moveable'];
        foreach ($array_keys as $key) {
            if (isset($config[$key]) && !is_array($config[$key])) {
                $config[$key] = explode(",", $config[$key]);
            }
        }

        $current_template = Template::where('id', $template_id)->first();
        $config["width"] = $current_template->width;
        $config["height"] = $current_template->height;
        $config["group_x"] = 0;
        $config["group_y"] = 0;
        if (isset($settings['dpi'])) {
            $config["dpi"] = intval($settings['dpi']);
        }

        if (isset($settings["background"])) {
            $config["background"] = [];
            $backgrounds = $settings["background"];
            if (!is_array($backgrounds)) {
                $backgrounds = explode(",", $backgrounds);
            }
            foreach ($backgrounds as $bg) {
                $background_filename = null;
                if (isset($bg)) {
                    $background_filename = uniqid() . ".png";
                    file_put_contents($background_filename, file_get_contents($bg));
                }
                $config["background"][] = $background_filename;
            }
        }
        if (isset($settings["img_from_bk"])) {
            $config["img_from_bk"] = [];
            $backgrounds = $settings["img_from_bk"];
            if (!is_array($backgrounds)) {
                $backgrounds = explode(",", $backgrounds);
            }
            foreach ($backgrounds as $bg) {
                $background_filename = null;
                if (isset($bg)) {
                    $background_filename = uniqid() . ".png";
                    file_put_contents($background_filename, file_get_contents($bg));
                }
                $config["img_from_bk"][] = $background_filename;
            }
        }

        $config["shadow"] = null;
        if (isset($settings['theme'])) {
            $theme = $this->themeService->getById(intval($settings['theme']));
            if (isset($theme)) {
                // Shadow (Amazon only)

                $attributes = json_decode($theme->attributes);
                foreach ($attributes as $key => $attr) {
                    if ($attr->name == "Shadow Effects") {
                        $shadow_attrs = $attr->list;
                    }
                }

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
        }

        return $config;
    }

    private function get_output_filename($request)
    {
        $filename = isset($request->project_name) ? $request->project_name : $request->template_name;
        if ($filename == 'null' && isset($request->instance_id)) {
            $layout = $this->gridLayoutService->getById($request->layout_id);
            $template = Template::find($request->template_id);
            $filename = $layout->name . '_' . $template->name;
        }
        return $filename;
    }

    private function check_in_group($group_field_names, $name)
    {
        $group_field = 0;
        if (!empty($group_field_names)) {
            $group_field = 1;
            $field_names = explode(",", $group_field_names);
            foreach ($field_names as $field_name) {
                if (trim($field_name) === $name) {
                    $group_field = 2;
                }
            }
        }

        return $group_field;
    }

    private function get_positioning_option($positioning_options, $data, $fields, $field_name)
    {
        $fieldFlags = [];

        foreach ($data as $key => $item) {
            if (str_starts_with($key, 'text_') && !str_ends_with($key, '_offset_x') && !str_ends_with($key, '_offset_y') && !str_ends_with($key, '_angle')) {
                $filter_items = array_values(array_filter($fields, function ($item) use ($key) {
                    return $item->element_id === $key;
                }));
                if (count($filter_items) > 0) {
                    $fieldFlags[$filter_items[0]->name] = !!$data[$key];
                }
            }
        }

        $parsedOptions = [];
        foreach ($positioning_options as $option) {
            $parsedOption = [];
            foreach ($option->fields as $optionField) {
                $parsedOption[$optionField->field_name] = [
                    'fields' => $optionField->fields,
                    'x' => $optionField->x,
                    'y' => $optionField->y,
                    'width' => $optionField->width,
                ];
            }
            $parsedOptions[] = $parsedOption;
        }

        foreach ($parsedOptions as $option) {
            $res = true;
            foreach ($option as $key => $item) {
                if (($option[$key]['fields'] == 1 && (!array_key_exists($key, $fieldFlags) || $fieldFlags[$key] !== true)) || ($option[$key]['fields'] == null && isset($fieldFlags[$key]) && $fieldFlags[$key] === true)) {
                    $res = false;
                    break;
                }
            }
            if ($res && isset($option[$field_name])) {
                return $option[$field_name];
            }
        }
        return null;
    }

    private function get_psd($files, $product_filenames, $config, $group_field_names = "")
    {
        $fonts = array(
            "Arial" => "arial.ttf",
            "Amazon-Ember-Bold" => "AmazonEmber_Bd.ttf",
            "Amazon-Ember" => "AmazonEmber_Rg.ttf",
            // "GothamNarrow-Ultra" => "Gotham Narrow Ultra.otf",
            // "MuseoSans-300Italic" => "MuseoSans_300_Italic.otf",
            "OpenSans-Bold" => "OpenSans-Bold.ttf",
            "Proxima-Nova-Black" => "Mark Simonson - Proxima Nova Black.ttf",
            "Proxima-Nova-Bold" => "Mark Simonson - Proxima Nova Bold.ttf",
            "Proxima-Nova-Extrabld" => "Mark Simonson - Proxima Nova Extrabold.ttf",
            "Proxima-Nova-Regular-It" => "ProximaNova-RegularItalic.ttf",
            "Proxima-Nova-Semibold" => "Mark Simonson - Proxima Nova Semibold.ttf",
            "Avenir-Next-Bold" => 'Avenir-Next-Bold.ttf',
            "Avenir-Next-Medium" => 'Avenir-Next-Medium.ttf',
            "Avenir-Black" => 'Avenir-Black.ttf',
            "Century-Gothic" => 'gothic.ttf',
            "Century-Gothic-Bold" => 'gothicb.ttf',
            "NotoSansSC-Regular" => 'NotoSansSC-Regular.otf',
            "PlexesPro-Medium" => "PlexesPro-Medium.ttf",
            "PlexesPro-Light" => "PlexesPro-Light.ttf",
            "PlexesPro-Book" => "PlexesPro-Book.ttf",
            "PlexesPro-Black" => "PlexesPro-Black.ttf",
            "Larsseit" => "Larsseit.ttf",
            "Larsseit-Bold" => "Larsseit-Bold.ttf",
            "Gotham-Black" => "Gotham-Black.ttf",
            "Gotham-Bold" => "GothamBold.ttf",
            "Gotham-Book" => "GothamBook.ttf",
            "Gotham-Light" => "GothamLight.ttf",
            "Gotham-Medium" => "GothamMedium.ttf",
            "Gotham-Thin" => "Gotham-Thin.ttf",
            "Gotham-XLight" => "Gotham-XLight.ttf",
            "Bogle-Regular" => "Bogle-Regular.ttf",
            "Bogle-Bold" => "Bogle-Bold.ttf",
            "National-2" => "National2-Regular.ttf",
            "National-2-Bold" => "National2-Bold.ttf",
            "NeueEinstellung-Medium" => "NeueEinstellung-Medium.ttf",
            "Neue-Einstellung-Bold" => "NeueEinstellung-Bold.ttf",
            "Avenir-Next-Condensed-Bold" => "AvenirNextCondensed-Bold.ttf",
            "Avenir-Next-Condensed-Demi-Bold" => "AvenirNextCondensed-DemiBold.ttf",
            "Avenir-Next-Condensed-Heavy" => "AvenirNextCondensed-Heavy.ttf",
            "Avenir-Next-Condensed-Medium" => "AvenirNextCondensed-Medium.ttf",
            "Avenir-Next-Condensed-Regular" => "AvenirNextCondensed-Regular.ttf",
            "Avenir-Next-Condensed-Ultra-Light" => "AvenirNextCondensed-UltraLight.ttf",
            "Avenir-Next-Demi-Bold" => "AvenirNext-DemiBold.ttf",
            "Avenir-Heavy" => "Avenir-Heavy.ttf",
            "Trade-Gothic-LT-Bold" => "Trade Gothic LT Bold.ttf",
            "Trade-Gothic-LT-Std-Bold-Cn" => "TradeGothicLTStd-BdCn20.ttf",
            "Trade-Gothic-LT" => "Trade Gothic LT.ttf",
            "Trade-Gothic-LT-Light" => "Trade Gothic LT Light.ttf",
            "Trade-Gothic-LT-Std-Cn" => "TradeGothicLTStd-Cn18.ttf",
            "TESCO-Modern" => "TESCO Modern.ttf",
            "TESCO-Modern-Bold" => "TESCO Modern Bold.ttf",
            "Arial-Bold" => "arialbd.ttf",
            "AlternateGothicATF-Black" => "AlternateGothicATF-Black.ttf",
            "AlternateGothicATF-Bold" => "AlternateGothicATF-Bold.ttf",
            "AlternateGothicATF-Heavy" => "AlternateGothicATF-Heavy.ttf",
            "AlternateGothicATF-Medium" => "AlternateGothicATF-Medium.ttf",
            "HP-Simplified" => "hp-simplified-265.ttf",
            "HP-Simplified-Bold" => "hp-simplified-556.ttf",
            "HP-Simplified-Bold-Italic" => "hp-simplified-990.ttf",
            "HP-Simplified-Italic" => "hp-simplified-652.ttf",
            "BarlowCondensed-Black" => "BarlowCondensed-Black.ttf",
            "BarlowCondensed-BlackItalic" => "BarlowCondensed-BlackItalic.ttf",
            "BarlowCondensed-Bold" => "BarlowCondensed-Bold.ttf", 
            "BarlowCondensed-BoldItalic" => "BarlowCondensed-BoldItalic.ttf",
            "BarlowCondensed-ExtraBold" => "BarlowCondensed-ExtraBold.ttf",
            "BarlowCondensed-ExtraBoldItalic" => "BarlowCondensed-ExtraBoldItalic.ttf", 
            "BarlowCondensed-ExtraLight" => "BarlowCondensed-ExtraLight.ttf", 
            "BarlowCondensed-ExtraLightItalic" => "BarlowCondensed-ExtraLightItalic.ttf", 
            "BarlowCondensed-Italic" => "BarlowCondensed-Italic.ttf", 
            "BarlowCondensed-Light" => "BarlowCondensed-Light.ttf", 
            "BarlowCondensed-LightItalic" => "BarlowCondensed-LightItalic.ttf", 
            "BarlowCondensed-Medium" => "BarlowCondensed-Medium.ttf", 
            "BarlowCondensed-MediumItalic" => "BarlowCondensed-MediumItalic.ttf", 
            "BarlowCondensed-Regular" => "BarlowCondensed-Regular.ttf",
            "BarlowCondensed-SemiBold" => "BarlowCondensed-SemiBold.ttf",
            "BarlowCondensed-SemiBoldItalic" => "BarlowCondensed-SemiBoldItalic.ttf",
            "BarlowCondensed-Thin" => "BarlowCondensed-Thin.ttf",
            "BarlowCondensed-ThinItalic" => "BarlowCondensed-ThinItalic.ttf",
            "Colfax-Bold" => "Colfax Bold.otf",
            "Colfax-Medium" => "Colfax Medium.otf",
            "Colfax-Regular" => "Colfax Regular.otf",
            "Futura-Bold" => "Futura Bold.ttf",
            "Futura-Condensed-Medium" => "Futura Condensed Medium.otf",
            "Futura-Extra-Bold" => "Futura Extra Bold.otf",
            "Futura-Medium" => "Futura Medium.ttf",
            "Helvetica-Neue-55-Roman" => "Helvetica Neue 55 Roman.otf",
            "Helvetica-Neue-65-Medium" => "Helvetica Neue 65 Medium.ttf",
            "Knockout-Regular" => "Knockout Regular.ttf",
            "Netto-OT-Bold" => "Netto OT Bold.ttf",
            "Playfair-Display-Black-Italic" => "Playfair Display Black Italic.ttf",
            "Playfair-Display-Black" => "Playfair Display Black.ttf",
            "SourceSansVariable-Roman" => "SourceSansVariable-Roman.ttf",
            "SourceSansVariable-Bold" => "SourceSansVariable-Bold.ttf"
        );

        $imagemagick_fonts = array(
            "Arial" => "ArialMT",
            "Amazon-Ember-Bold" => "Amazon-Ember-Bold",
            "Amazon-Ember" => "Amazon-Ember-Regular",
            "OpenSans-Bold" => "Open-Sans-Bold",
            "Proxima-Nova-Black" => "Proxima-Nova-Black",
            "Proxima-Nova-Bold" => "Proxima-Nova-Bold",
            "Proxima-Nova-Extrabld" => "Proxima-Nova-Extrabld",
            "Proxima-Nova-Regular-It" => "ProximaNova-RegularIt",
            "Proxima-Nova-Semibold" => "Proxima-Nova-Semibold",
            "Avenir-Next-Bold" => 'Avenir-Next-Bold',
            "Avenir-Next-Medium" => 'Avenir-Next-Medium',
            "Avenir-Black" => 'Avenir-Black',
            "Century-Gothic" => 'Century-Gothic',
            "Century-Gothic-Bold" => 'Century-Gothic-Bold',
            "NotoSansSC-Regular" => 'NotoSansSC-Regular',
            "PlexesPro-Medium" => "PlexesPro-Medium",
            "PlexesPro-Light" => "PlexesPro-Light",
            "PlexesPro-Book" => "PlexesPro-Book",
            "PlexesPro-Black" => "PlexesPro-Black",
            "Larsseit" => "Larsseit",
            "Larsseit-Bold" => "Larsseit-Bold",
            "Gotham-Black" => "Gotham-Black",
            "Gotham-Bold" => "Gotham-Bold",
            "Gotham-Book" => "Gotham-Book",
            "Gotham-Light" => "Gotham-Light",
            "Gotham-Medium" => "Gotham-Medium",
            "Gotham-Thin" => "Gotham-Thin",
            "Gotham-XLight" => "Gotham-XLight",
            "Bogle-Regular" => "Bogle-Regular",
            "Bogle-Bold" => "Bogle-Bold",
            "National-2" => "National2-Regular",
            "National-2-Bold" => "National2-Bold",
            "NeueEinstellung-Medium" => "NeueEinstellung-Medium",
            "Neue-Einstellung-Bold" => "NeueEinstellung-Bold",
            "Avenir-Next-Condensed-Bold" => "Avenir-Next-Condensed-Bold",
            "Avenir-Next-Condensed-Demi-Bold" => "Avenir-Next-Condensed-Demi-Bold",
            "Avenir-Next-Condensed-Heavy" => "Avenir-Next-Condensed-Heavy",
            "Avenir-Next-Condensed-Medium" => "Avenir-Next-Condensed-Medium",
            "Avenir-Next-Condensed-Regular" => "Avenir-Next-Condensed-Regular",
            "Avenir-Next-Condensed-Ultra-Light" => "Avenir-Next-Condensed-Ultra-Light",
            "Avenir-Next-Demi-Bold" => "Avenir-Next-Demi-Bold",
            "Avenir-Heavy" => "Avenir-Heavy",
            "Trade-Gothic-LT-Bold" => "Trade-Gothic-LT-Bold",
            "Trade-Gothic-LT-Std-Bold-Cn" => "Trade-Gothic-LT-Std-Bold-Cn",
            "Trade-Gothic-LT" => "Trade-Gothic-LT",
            "Trade-Gothic-LT-Light" => "Trade-Gothic-LT-Light",
            "Trade-Gothic-LT-Std-Cn" => "Trade-Gothic-LT-Std-Cn",
            "TESCO-Modern" => "TESCO-Modern-Regular",
            "TESCO-Modern-Bold" => "TESCO-Modern-Bold",
            "Arial" => "Arial",
            "Arial-Bold" => "Arial-Bold",
            "AlternateGothicATF-Black" => "AlternateGothicATF-Black",
            "AlternateGothicATF-Bold" => "AlternateGothicATF-Bold",
            "AlternateGothicATF-Heavy" => "AlternateGothicATF-Heavy",
            "AlternateGothicATF-Medium" => "AlternateGothicATF-Medium",
            "HP-Simplified" => "HP-Simplified-Regular",
            "HP-Simplified-Bold" => "HP-Simplified-Bold",
            "HP-Simplified-Bold-Italic" => "HP-Simplified-Bold-Italic",
            "HP-Simplified-Italic" => "HP-Simplified-Italic",
            "BarlowCondensed-Black" => "Barlow-Condensed-Black",
            "BarlowCondensed-BlackItalic" => "Barlow-Condensed-Black-Italic",
            "BarlowCondensed-Bold" => "Barlow-Condensed-Bold", 
            "BarlowCondensed-BoldItalic" => "Barlow-Condensed-Bold-Italic",
            "BarlowCondensed-ExtraBold" => "Barlow-Condensed-ExtraBold",
            "BarlowCondensed-ExtraBoldItalic" => "Barlow-Condensed-ExtraBold-Italic", 
            "BarlowCondensed-ExtraLight" => "Barlow-Condensed-ExtraLight", 
            "BarlowCondensed-ExtraLightItalic" => "Barlow-Condensed-ExtraLight-Italic", 
            "BarlowCondensed-Italic" => "Barlow-Condensed-Italic", 
            "BarlowCondensed-Light" => "Barlow-Condensed-Light", 
            "BarlowCondensed-LightItalic" => "Barlow-Condensed-Light-Italic", 
            "BarlowCondensed-Medium" => "Barlow-Condensed-Medium", 
            "BarlowCondensed-MediumItalic" => "Barlow-Condensed-Medium-Italic", 
            "BarlowCondensed-Regular" => "Barlow-Condensed-Regular",
            "BarlowCondensed-SemiBold" => "Barlow-Condensed-SemiBold",
            "BarlowCondensed-SemiBoldItalic" => "Barlow-Condensed-SemiBold-Italic",
            "BarlowCondensed-Thin" => "Barlow-Condensed-Thin",
            "BarlowCondensed-ThinItalic" => "Barlow-Condensed-Thin-Italic",
            "Colfax-Bold" => "Colfax-Bold",
            "Colfax-Medium" => "Colfax-Medium",
            "Colfax-Regular" => "Colfax-Regular",
            "Futura-Bold" => "Futura-Bold",
            "Futura-Condensed-Medium" => "Futura-Condensed-Medium",
            "Futura-Extra-Bold" => "Futura-Extra-Bold",
            "Futura-Medium" => "Futura-Medium",
            "Helvetica-Neue-55-Roman" => "HelveticaNeue-Roman",
            "Helvetica-Neue-65-Medium" => "Helvetica-66-Medium-Italic",
            "Knockout-Regular" => "Knockout-Regular",
            "Netto-OT-Bold" => "Netto-OT-Bold",
            "Playfair-Display-Black-Italic" => "Playfair-Display-Black-Italic",
            "Playfair-Display-Black" => "Playfair-Display-Black",
            "SourceSansVariable-Roman" => "SourceSansVariable-Roman",
            "SourceSansVariable-Bold" => "SourceSansVariable-Bold"
        );

        $json = array(
            "width" => $config["width"],
            "height" => $config["height"],
            "layers" => array(),
            "orders" => array()
        );

        $smartobject = array();

        if (isset($config["dpi"])) {
            $json["dpi"] = $config["dpi"];
        }
        $image_index = 0;
        $image_count = 0;
        $product_dimensions = array();
        $product_images = array();
        $group_colors = [];
        $group_fonts = [];
        $image_list_group = [];
        $template_fields = TemplateField::where('template_id', $config['template_id'])->get();
        $positioning_options = Template::find($config['template_id'])->positioning_options;
        foreach ($positioning_options as $positioning_option) {
            $positioning_option->fields;
        }
        $spacingFieldPosition = [];

        foreach ($template_fields as $field) {
            $options = json_decode($field->options, true);
            if ($field->type == "Product Image") {
                $image_count++;
                $product_images[] = $options;
            } else if ($field->type == "Product Dimensions") {
                $product_dimensions = array(
                    'X' => isset($options['X']) ? $options['X'] : 0,
                    'Y' => isset($options['Y']) ? $options['Y'] : 0,
                    'Width' => $options['Width'],
                    'Height' => $options['Height']
                );
            } else if ($field->type == "Smart Object" && !empty($options["Group Name"])) {
                $group_name = $options["Group Name"];
                $group_field = $this->check_in_group($group_field_names, $field->name);
                if (!empty($options["Option5"])) {
                    $w = intval(explode(',', $options["Option5"])[0]);
                    $h = intval(explode(',', $options["Option5"])[1]);
                }
                $smartobject[$group_name]["type"] = "smartobject";
                if ($group_field == 1) {
                    $smartobject[$group_name]["left"] = intval($options["X"]) - round($config['group_x']);
                    $smartobject[$group_name]["top"] = intval($options["Y"]) - round($config['group_y']);
                    $smartobject[$group_name]["width"] = $config['width'];
                    $smartobject[$group_name]["height"] = $config['height'];
                } else {
                    $smartobject[$group_name]["left"] = intval($options["X"]);
                    $smartobject[$group_name]["top"] = intval($options["Y"]);
                    $smartobject[$group_name]["width"] = intval($options["Width"]);
                    $smartobject[$group_name]["height"] = intval($options["Height"]);
                }
                $smartobject[$group_name]["name"] = $options["Name"];
                if ($group_field != 1) {
                    if ($options["Option5"]) {
                        $option5 = json_decode($options["Option5"], true);
                        if (isset($option5["mask"])) {
                            $smartobject[$group_name]["mask"] = $option5["mask"];
                        }
                        if (isset($option5["shadow"])) {
                            $smartobject[$group_name]["shadow"] = $option5["shadow"];
                        }
                    } else {
                        $smartobject[$group_name]["transform"] = array_map('intval', explode(',', $options["Option3"]));
                    }
                }
                $bk_color = empty($options["Option4"]) ? "#ffffff00" : $options["Option4"];
                $smartobject[$group_name]["psd"] = array(
                    "color" => $bk_color,
                    // "psd_width" => empty($options["Option5"]) ? intval($options["Width"]) : $w,
                    // "psd_height" => empty($options["Option5"]) ? intval($options["Height"]) : $h,
                    "psd_width" => intval($options["Width"]),
                    "psd_height" => intval($options["Height"]),
                    "layers" => array(),
                    "orders" => array()
                );
                $smartobject[$group_name]["order"] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
            } else if ($field->type == "Group Color") {
                $group_colors[] = [
                    "group" => $options['Option1'],
                    "element_id" => $field->element_id
                ];
            } else if ($field->type == "Group Font") {
                $group_fonts[] = [
                    "group" => $options['Option1'],
                    "element_id" => $field->element_id
                ];
            } else if ($field->type == "Image List Group") {
                $image_list_group[] = [
                    "group" => $options['Option1'],
                    "element_id" => $field->element_id
                ];
            } else if ($field->type == "Field Spacing") {
                $field_spacing_names = explode(",", $options["Option1"]);
                $spacingFieldValues = explode(",", $options["Option2"]);
                $spacingFieldX = intval($options["X"]);
                $spacingFieldWidth = intval($options["Width"]);
                if ($spacingFieldWidth == 0) {
                    $spacingFieldWidth = $config["width"];
                }
                $spacingFields = [];
                $spacingFieldAlignment = $options["Alignment"];
                for ($i = 0; $i < count($field_spacing_names); $i++) {
                    $spacing_field = $template_fields->first(function ($value, $key) use ($field_spacing_names, $i) {
                        return $value->name == trim($field_spacing_names[$i]);
                    });
                    if ($spacing_field) {
                        $spacing_field_options = json_decode($spacing_field->options, true);
                        $spacing_field_width = intval($spacing_field_options["Width"]);
                        if (str_contains($spacing_field->type, "Text") && $config[$spacing_field->element_id]) {
                            $metrics = $this->bannerService->get_font_metrics(
                                $config[$spacing_field->element_id],
                                $spacing_field_options["Font"],
                                intval($spacing_field_options["Font Size"])
                            );
                            $spacing_field_width = $metrics["textWidth"];
                        }
                        $spacingFields[] = [
                            'name' => $spacing_field->name,
                            'element_id' => $spacing_field->element_id,
                            'width' => $spacing_field_width,
                        ];
                    }
                }

                $group_width = 0;
                for ($i = 0; $i < count($spacingFields); $i++) {
                    $group_width += $spacingFields[$i]["width"];
                }

                $x = $spacingFieldX;
                if ($spacingFieldAlignment == "center") {
                    $x += ($spacingFieldWidth - $group_width) / 2;
                } else if ($spacingFieldAlignment == "right") {
                    $x += ($spacingFieldWidth - $group_width);
                }

                $spacingFieldPosition[$spacingFields[0]["name"]] = [
                    'x' => $x,
                    'width' => $spacingFields[0]["width"]
                ];
                for ($i = 1; $i < count($spacingFields); $i++) {
                    $x += $spacingFields[$i - 1]["width"];
                    if ($i < count($field_spacing_names) ) {
                        $x += intval($spacingFieldValues[$i - 1]);
                    }
                    $spacingFieldPosition[$spacingFields[$i]["name"]] = [
                        'x' => $x,
                        'width' => $spacingFields[$i]["width"]
                    ];
                }
            }
        }

        // Product Images
        $count = count($files);
        $count = $count > $image_count ? $image_count : $count;
        $sum_width = 0;
        $max_height = 0;
        $products = array();
        $left = 0;
        if ($count) {
            $margin = isset($config["product_space"]) ? intval($config["product_space"]) : 0;
            $margin_array = isset($config["product_space"]) ? array_map('intval', explode(',', $config["product_space"])) : [];
            $products_width = $product_dimensions["Width"] - array_sum($margin_array);
            for ($i = 0; $i < $count; $i++) {
                if ($product_images[$i]["Option1"] != "Hero") {
                    $sum_width += $files[$i]["width"];
                }
                $products[] = new \Imagick($product_filenames[$i]);
            }

            for ($i = 0; $i < $count; $i++) {
                if ($product_images[$i]["Option1"] != "Hero") {
                    if ($sum_width) {
                        $w = $products_width * $files[$i]["width"] / $sum_width;
                    } else {
                        $w = $products_width;
                    }
                    $products[$i]->thumbnailImage($w, null);
                    $h = $products[$i]->getImageHeight();
                    if ($max_height < $h) {
                        $max_height = $h;
                    }
                }
            }

            $total_width = 0;
            $r = $product_dimensions["Height"] < $max_height ? $product_dimensions["Height"] / $max_height : 1;
            for ($i = 0; $i < $count; $i++) {
                if ($product_images[$i]["Option1"] != "Hero") {
                    $w = $products[$i]->getImageWidth() * $r;
                    $h = $products[$i]->getImageHeight() * $r;
                    $max_height = $max_height * $r;
                    if ($r < 1) {
                        $products[$i]->scaleImage($w, $h, true);
                    }
                    $total_width += $products[$i]->getImageWidth();
                }
            }
            if ($config["product_image_alignment"] == "left") {
                $left = 0;
            } else if ($config["product_image_alignment"] == "center") {
                $left = ($products_width - $total_width) / 2;
            } else if ($config["product_image_alignment"] == "right") {
                $left = $products_width - $total_width;
            }
        }

        $background_pos = [];
        $smartobject_layers = [];
        $smartobject_orders = [];
        $background_img_pos = [];
        $img_from_bk_pos = [];
        $overlay_area = [];
        $idx = 0;

        $text_fields = [];
        foreach ($template_fields as $field) {
            if (($field->type == "Text" || $field->type == "Text Options" || $field->type == "Text from Spreadsheet") && isset($config['show_text'])) {
                $text_fields[] = $field;
            }
        }

        foreach ($template_fields as $field) {
            $options = json_decode($field->options, true);
            $group_field = $this->check_in_group($group_field_names, $field->name);

            if ($group_field === 1) continue;

            if ($options['Option5'] == 'image_exclude') continue;

            if ($field->type == "Product Image" && isset($products[$image_index])) {
                $w = $products[$image_index]->getImageWidth();
                $h = $products[$image_index]->getImageHeight();
                $scale = floatval($config["scale"][$image_index]);
                $angle = intval($config["angle"][$image_index]);
                if ($options["Option1"] == "Hero") {
                    $x = intval($options["X"]);
                    $y = intval($options["Y"]);
                    $ratio1 = intval($options["Width"]) / $w;
                    $w = intval($options["Width"]);
                    $h = $h * $ratio1;
                    $ratio2 = $h / intval($options["Height"]);
                    if ($ratio2 > 1) {
                        $h = intval($options["Height"]);
                        $w = $w / $ratio2;
                    }
                    $wr = $w * $scale;
                    $hr = $h * $scale;
                    $x = $x + $w / 2 - $wr / 2;
                    $y = $y + $h / 2 - $hr / 2;
                    $option2 = $options["Option2"];
                    if (str_starts_with($option2, "W-")) {
                        $offset = intval(explode("-", $option2)[1]);
                        // $cw = isset($config["canvas_dimension_width"]) ? intval($config["canvas_dimension_width"]) : $config["width"];
                        // $x = $cw - $offset - $w / 2 - $wr / 2;
                        $x = $config["width"] - $offset - $w / 2 - $wr / 2;
                    }
                    $x = $x + $wr / 2 - $hr * sin(deg2rad($angle)) / 2 - $wr * cos(deg2rad($angle)) / 2;
                    $y = $y + $hr / 2 - $hr * cos(deg2rad($angle)) / 2 - $wr * sin(deg2rad($angle)) / 2;
                    $x += intval($config["x_offset"][$image_index]);
                    $y += intval($config["y_offset"][$image_index]);
                } else {
                    $wr = $w * $scale;
                    $hr = $h * $scale;

                    $x = $left + $product_dimensions["X"] + intval($config["x_offset"][$image_index]) + $w / 2;
                    if (isset($config["p_width"])) {
                        $x = $x - intval($config["p_width"][$image_index]) / 2;
                    }
                    $y = ($product_dimensions["Height"] - $h) / 2 + $product_dimensions["Y"] + intval($config["y_offset"][$image_index]) + $h / 2;
                    if (isset($config["p_height"])) {
                        $y = $y - intval($config["p_height"][$image_index]) / 2;
                    }

                    $left += round($w / 2 + $wr / 2) + intval($config["x_offset"][$image_index]);
                    $left += isset($margin_array[$idx]) ? $margin_array[$idx] : (isset($margin_array[0]) ? $margin_array[0] : 0);
                    $idx++;
                }
                $l = $this->bannerService->get_smartobject_layer("Product Image" . $image_index, $product_filenames[$image_index], $x, $y, round($w * $scale), round($h * $scale), intval($config["angle"][$image_index]), $config["shadow"], null, null, 0);
                if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                    $group_name = $options["Group Name"];
                    $smartobject_layers[$group_name][] = $l;
                    $smartobject_orders[$group_name][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                } else {
                    $l['left'] -= $config['group_x'];
                    $l['top'] -= $config['group_y'];
                    $json["layers"][] = $l;
                    $json["orders"][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                }
                $image_index++;
            } else if (($field->type == "Text" || $field->type == "Text Options" || $field->type == "Text from Spreadsheet") && isset($config['show_text']) && (empty($options['Option2']) || $options['Option2'] != 'no_image')) {
                $fontname = !empty($options['Font']) ? $options['Font'] : "Proxima-Nova-Semibold";
                $text = !isset($config[$field->element_id]) || empty($config[$field->element_id]) ? " " : $config[$field->element_id];
                $fontsize = isset($config[$field->element_id . '_fontsize']) ? intval($config[$field->element_id . '_fontsize']) : (!empty($options['Font Size']) ? intval($options['Font Size']) : 20);
                if (isset($config[$field->element_id . '_font']) && isset($fonts[$config[$field->element_id . '_font']]) && isset($imagemagick_fonts[$config[$field->element_id . '_font']])) {
                    $fontname = $config[$field->element_id . '_font'];
                }
                $color = isset($config[$field->element_id . '_color']) ? $config[$field->element_id . '_color'] : (!empty($options['Font Color']) ? $options['Font Color'] : '#000000');

                foreach ($group_colors as $gc) {
                    $gc_arr = explode(",", $gc['group']);
                    if (in_array($options['Name'], $gc_arr)) {
                        $color = $config[$gc['element_id']];
                    }
                }

                foreach ($group_fonts as $gf) {
                    $gf_arr = explode(",", $gf['group']);
                    if (in_array($options['Name'], $gf_arr)) {
                        $fontname = $config[$gf['element_id']];
                    }
                }

                $p_option = $this->get_positioning_option($positioning_options, $config, $text_fields, $field->name);
                $y = intval($options['Y']);
                $x = intval($options['X']);
                $width = isset($config[$field->element_id . '_width']) ? intval($config[$field->element_id . '_width']) : 0;
                $width = !$width && !empty($options['Width']) ? intval($options['Width']) : $width;
                if ($p_option != null) {
                    $x = isset($p_option['x']) ? $p_option['x'] : $x;
                    $y = isset($p_option['y']) ? $p_option['y'] : $y;
                    $width = isset($p_option['width']) ? $p_option['width'] : $width;
                }
                $text_arr = $this->bannerService->get_multiline_text($text, "Proxima-Nova-Semibold", $fontsize, $width);
                $text_top = $y + (isset($config[$field->element_id . '_offset_y']) ? intval($config[$field->element_id . '_offset_y']) : 0);
                $angle = isset($config[$field->element_id . '_angle']) ? floatval($config[$field->element_id . '_angle']) : 0;

                if (strpos($text, '<u>') === false && strpos($text, '<c ') === false) {
                    for ($j = 0; $j < count($text_arr); $j++) {
                        if ($text_arr[$j] != "") {
                            if (!$angle) {
                                $l = $this->bannerService->get_text_layer2(
                                    $field->name,
                                    (isset($spacingFieldPosition[$field->name]) ? $spacingFieldPosition[$field->name]["x"] : $x) + intval($config[$field->element_id . '_offset_x']),
                                    $text_top,
                                    (isset($spacingFieldPosition[$field->name]) ? $spacingFieldPosition[$field->name]["width"] : $width),
                                    isset($options['Height']) ? intval($options['Height']) : 0,
                                    $text_arr[$j],
                                    $imagemagick_fonts[$fontname],
                                    $fonts[$fontname],
                                    $fontsize,
                                    $color ? $color : "#000000",
                                    $this->get_alignment_for_text_layer($options, $config, $field),
                                    !empty($options['Text Tracking']) ? intval($options['Text Tracking']) : 0,
                                    null,
                                    !empty($options['Kerning']) ? $options['Kerning'] : "none",
                                    isset($config["text_oversampling_value"]) ? intval($config["text_oversampling_value"]) : null
                                );
                            } else {
                                $img = $this->bannerService->get_text_image($text_arr[$j], $imagemagick_fonts[$fontname], $fontsize, isset($config[$field->element_id . '_color']) ? $config[$field->element_id . '_color'] : (!empty($options['Font Color']) ? $options['Font Color'] : '#000000'), true);
                                $file_name = uniqid() . '.png';
                                $img->writeImage($file_name);
                                $l = $this->bannerService->get_smartobject_layer(
                                    "Text Image",
                                    $file_name,
                                    (isset($spacingFieldPosition[$field->name]) ? $spacingFieldPosition[$field->name]["x"] : $x) + intval($config[$field->element_id . '_offset_x']),
                                    $text_top,
                                    $img->getImageWidth(),
                                    $img->getImageHeight(),
                                    $angle
                                );
                            }

                            $order = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                            if ($field->type == "Text Options") {
                                $order = $order - 1;
                            }
                            if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                                $group_name = $options["Group Name"];
                                $smartobject_layers[$group_name][] = $l;
                                $smartobject_orders[$group_name][] = $order;
                            } else {
                                if (isset($l['left'])) {
                                    $l['left'] -= $config['group_x'];
                                    $l['top'] -= $config['group_y'];
                                } else {
                                    if (isset($l['coordinates'])) {
                                        $l['coordinates'][0]['left'] -= $config['group_x'];
                                        $l['coordinates'][0]['top'] -= $config['group_y'];
                                    }
                                }
                                $json["layers"][] = $l;
                                $json["orders"][] = $order;
                            }

                            $lineSpacing = empty($options["Leading"]) ? 1 : floatval($options["Leading"]);
                            if ($lineSpacing < 2) {
                                $text_top += $fontsize * $lineSpacing;
                            } else {
                                $text_top += $lineSpacing;
                            }
                        }
                    }
                } else {
                    $l = $this->bannerService->get_text_layer2(
                        $field->name,
                        (isset($spacingFieldPosition[$field->name]) ? $spacingFieldPosition[$field->name]["x"] : $x) + intval($config[$field->element_id . '_offset_x']),
                        $text_top,
                        (isset($spacingFieldPosition[$field->name]) ? $spacingFieldPosition[$field->name]["width"] : $width),
                        isset($options['Height']) ? intval($options['Height']) : 0,
                        $text,
                        $imagemagick_fonts[$fontname],
                        $fonts[$fontname],
                        $fontsize,
                        $color ? $color : "#000000",
                        $this->get_alignment_for_text_layer($options, $config, $field),
                        !empty($options['Text Tracking']) ? intval($options['Text Tracking']) : 0,
                        null,
                        !empty($options['Kerning']) ? $options['Kerning'] : "none",
                        isset($config["text_oversampling_value"]) ? intval($config["text_oversampling_value"]) : null
                    );

                    $order = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    if ($field->type == "Text Options") {
                        $order = $order - 1;
                    }
                    if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                        $group_name = $options["Group Name"];
                        $smartobject_layers[$group_name][] = $l;
                        $smartobject_orders[$group_name][] = $order;
                    } else {
                        if (isset($l['left'])) {
                            $l['left'] -= $config['group_x'];
                            $l['top'] -= $config['group_y'];
                        } else {
                            if (isset($l['coordinates'])) {
                                $l['coordinates'][0]['left'] -= $config['group_x'];
                                $l['coordinates'][0]['top'] -= $config['group_y'];
                            }
                        }
                        $json["layers"][] = $l;
                        $json["orders"][] = $order;
                    }
                }
            } else if ($field->type == "Static Text" && isset($config['show_text'])) {
                $fontname = !empty($options['Font']) ? $options['Font'] : "Proxima-Nova-Semibold";
                $text = empty($options['Option1']) ? (empty($options['Placeholder']) ? " " : $options['Placeholder']) : $options['Option1'];
                $fontsize = !empty($options['Font Size']) ? intval($options['Font Size']) : 50;
                $width = isset($config[$field->element_id . '_width']) ? intval($config[$field->element_id . '_width']) : 0;
                $width = !$width && !empty($options['Width']) ? intval($options['Width']) : $width;
                if (!empty($options['Font']) && isset($fonts[$options['Font']]) && isset($imagemagick_fonts[$options['Font']])) {
                    $fontname = $options['Font'];
                }
                $text_arr = $this->bannerService->get_multiline_text($text, $fontname, $fontsize, $width);
                $text_top = intval($options['Y']) + intval($config[$field->element_id . '_offset_y']);
                if (strpos($text, '<u>') === false && strpos($text, '<c ') === false) {
                    for ($j = 0; $j < count($text_arr); $j++) {
                        if (!empty($text_arr[$j])) {
                            $l = $this->bannerService->get_text_layer2(
                                $field->name,
                                (isset($spacingFieldPosition[$field->name]) ? $spacingFieldPosition[$field->name]["x"] : intval($options['X'])) + intval($config[$field->element_id . '_offset_x']),
                                $text_top,
                                (isset($spacingFieldPosition[$field->name]) ? $spacingFieldPosition[$field->name]["width"] : $width),
                                !empty($options['Height']) ? intval($options['Height']) : 0,
                                $text_arr[$j],
                                $imagemagick_fonts[$fontname],
                                $fonts[$fontname],
                                $fontsize,
                                !empty($options['Font Color']) ? $options['Font Color'] : '#000000',
                                $this->get_alignment_for_text_layer($options, $config, $field),
                                !empty($options['Text Tracking']) ? intval($options['Text Tracking']) : 0,
                                null,
                                !empty($options['Kerning']) ? $options['Kerning'] : "none",
                                isset($config["text_oversampling_value"]) ? intval($config["text_oversampling_value"]) : null
                            );
                            if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                                $group_name = $options["Group Name"];
                                $smartobject_layers[$group_name][] = $l;
                                $smartobject_orders[$group_name][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                            } else {
                                if (isset($l['left'])) {
                                    $l['left'] -= $config['group_x'];
                                    $l['top'] -= $config['group_y'];
                                } else {
                                    if (isset($l['coordinates'])) {
                                        $l['coordinates'][0]['left'] -= $config['group_x'];
                                        $l['coordinates'][0]['top'] -= $config['group_y'];
				                    }
                                }
                                $json["layers"][] = $l;
                                $json["orders"][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                            }

                            $lineSpacing = empty($options["Leading"]) ? 1 : floatval($options["Leading"]);
                            if ($lineSpacing < 2) {
                                $text_top += $fontsize * $lineSpacing;
                            } else {
                                if (isset($l['coordinates'])) {
                                    $l['coordinates'][0]['left'] -= $config['group_x'];
                                    $l['coordinates'][0]['top'] -= $config['group_y'];
                                }
                            }
                        }
                    }
                } else {
                    $l = $this->bannerService->get_text_layer2(
                        $field->name,
                        (isset($spacingFieldPosition[$field->name]) ? $spacingFieldPosition[$field->name]["x"] : intval($options['X'])) + intval($config[$field->element_id . '_offset_x']),
                        $text_top,
                        (isset($spacingFieldPosition[$field->name]) ? $spacingFieldPosition[$field->name]["width"] : $width),
                        !empty($options['Height']) ? intval($options['Height']) : 0,
                        $text,
                        $imagemagick_fonts[$fontname],
                        $fonts[$fontname],
                        $fontsize,
                        !empty($options['Font Color']) ? $options['Font Color'] : '#000000',
                        $this->get_alignment_for_text_layer($options, $config, $field),
                        !empty($options['Text Tracking']) ? intval($options['Text Tracking']) : 0,
                        null,
                        !empty($options['Kerning']) ? $options['Kerning'] : "none",
                        isset($config["text_oversampling_value"]) ? intval($config["text_oversampling_value"]) : null
                    );
                    if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                        $group_name = $options["Group Name"];
                        $smartobject_layers[$group_name][] = $l;
                        $smartobject_orders[$group_name][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    } else {
                        if (isset($l['left'])) {
                            $l['left'] -= $config['group_x'];
                            $l['top'] -= $config['group_y'];
                        } else {
                            $l['coordinates'][0]['left'] -= $config['group_x'];
                            $l['coordinates'][0]['top'] -= $config['group_y'];
                        }
                        $json["layers"][] = $l;
                        $json["orders"][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    }
                }
            } else if ($field->type == "List Numbered Circle" || ($field->type == "List All" && $config["list_type"] == "circle")) {
                $strokeWidth = $options["Option3"] == "" ? 10 : intval($options["Option3"]);
                $text_color = isset($config['list_text_color']) ? $config['list_text_color'] : "#ffffff";
                $stroke = empty($options["Option2"]) ? '#ffffff' : $options["Option2"];
                $stroke = isset($config['list_stroke_color']) ? $config['list_stroke_color'] : $stroke;
                $fill = empty($options["Option4"]) || $options["Option4"] == '#00000000' ? null : $options["Option4"];
                $fill = isset($config['list_fill_color']) ? $config['list_fill_color'] : $fill;
                $l1 = $this->bannerService->get_pixel_layer("List Numbered Circle", intval($options['X']), intval($options['Y']), intval($options['Width']), intval($options['Width']), $fill, $strokeWidth, $stroke, intval($options['Width']) / 2 - $strokeWidth, [1, 1, 1, 1]);
                $l2 = $this->bannerService->get_text_layer2(
                    "List Numbered Circle", 
                    intval($options['X']), 
                    intval($options['Y']) - 5 + (intval($options['Width']) - intval($options['Font Size'])) / 2, 
                    isset($options['Width']) ? intval($options['Width']) : 0, 
                    isset($options['Width']) ? intval($options['Width']) : 0, 
                    isset($options['Option1']) ? $options['Option1'] : " ", 
                    "Proxima-Nova-Semibold", "Proxima-Nova-Semibold.ttf", 
                    isset($options['Font Size']) ? intval($options['Font Size']) : 20, 
                    $text_color, 
                    "center", 
                    0, false, "none", 
                    isset($config["text_oversampling_value"]) ? intval($config["text_oversampling_value"]) : null
                );
                if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                    $group_name = $options["Group Name"];
                    $smartobject_layers[$group_name][] = $l1;
                    $smartobject_layers[$group_name][] = $l2;
                    $smartobject_orders[$group_name][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    $smartobject_orders[$group_name][] = (empty($options["Order"]) ? 1000 : intval($options["Order"])) - 1;
                } else {
                    $l1['left'] -= $config['group_x'];
                    $l1['top'] -= $config['group_y'];
                    $l2['left'] -= $config['group_x'];
                    $l2['top'] -= $config['group_y'];
                    $json["layers"][] = $l1;
                    $json["layers"][] = $l2;
                    $json["orders"][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    $json["orders"][] = (empty($options["Order"]) ? 1000 : intval($options["Order"])) - 1;
                }
            } else if ($field->type == "List Numbered Square" || ($field->type == "List All" && $config["list_type"] == "square")) {
                $strokeWidth = $options["Option3"] == "" ? 10 : intval($options["Option3"]);
                $text_color = isset($config['list_text_color']) ? $config['list_text_color'] : "#ffffff";
                $stroke = empty($options["Option2"]) ? '#ffffff' : $options["Option2"];
                $stroke = isset($config['list_stroke_color']) ? $config['list_stroke_color'] : $stroke;
                $fill = empty($options["Option4"]) || $options["Option4"] == '#00000000' ? null : $options["Option4"];
                $fill = isset($config['list_fill_color']) ? $config['list_fill_color'] : $fill;
                $l1 = $this->bannerService->get_pixel_layer("List Numbered Square", intval($options['X']), intval($options['Y']), intval($options['Width']), intval($options['Height']), $fill, $strokeWidth, $stroke);
                $l2 = $this->bannerService->get_text_layer2(
                    "List Numbered Square", 
                    intval($options['X']), 
                    intval($options['Y']) - 5 + (intval($options['Height']) - intval($options['Font Size'])) / 2, 
                    isset($options['Width']) ? intval($options['Width']) : 0, 
                    isset($options['Height']) ? intval($options['Height']) : 0, 
                    isset($options['Option1']) ? $options['Option1'] : " ", 
                    "Proxima-Nova-Semibold", "Proxima-Nova-Semibold.ttf", 
                    isset($options['Font Size']) ? intval($options['Font Size']) : 20, 
                    $text_color, "center", 0, false, "none", 
                    isset($config["text_oversampling_value"]) ? intval($config["text_oversampling_value"]) : null
                );
                if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                    $group_name = $options["Group Name"];
                    $smartobject_layers[$group_name][] = $l1;
                    $smartobject_layers[$group_name][] = $l2;
                    $smartobject_orders[$group_name][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    $smartobject_orders[$group_name][] = (empty($options["Order"]) ? 1000 : intval($options["Order"])) - 1;
                } else {
                    $l1['left'] -= $config['group_x'];
                    $l1['top'] -= $config['group_y'];
                    $l2['left'] -= $config['group_x'];
                    $l2['top'] -= $config['group_y'];
                    $json["layers"][] = $l1;
                    $json["layers"][] = $l2;
                    $json["orders"][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    $json["orders"][] = (empty($options["Order"]) ? 1000 : intval($options["Order"])) - 1;
                }
            } else if ($field->type == "List Checkmark" || ($field->type == "List All" && $config["list_type"] == "checkmark")) {
                $strokeWidth = $options["Option3"] == "" ? 10 : intval($options["Option3"]);
                $text_color = isset($config['list_text_color']) ? $config['list_text_color'] : "#ffffff";
                $stroke = empty($options["Option2"]) ? '#ffffff' : $options["Option2"];
                $stroke = isset($config['list_stroke_color']) ? $config['list_stroke_color'] : $stroke;
                $fill = empty($options["Option4"]) || $options["Option4"] == '#00000000' ? null : $options["Option4"];
                $fill = isset($config['list_fill_color']) ? $config['list_fill_color'] : $fill;
                $l1 = $this->bannerService->get_pixel_layer("List Numbered Circle", intval($options['X']), intval($options['Y']), intval($options['Width']), intval($options['Width']), $fill, $strokeWidth, $stroke, intval($options['Width']) / 2 - $strokeWidth, [1, 1, 1, 1]);
                $l2 = $this->bannerService->get_text_layer2(
                    "List Checkmark", 
                    intval($options['X']), 
                    intval($options['Y']), 
                    isset($options['Width']) ? intval($options['Width']) : 0, 
                    isset($options['Height']) ? intval($options['Height']) : 0, 
                    "✓", "ArialUnicodeMS", "ARIALUNI.TTF", 
                    isset($options['Font Size']) ? intval($options['Font Size']) : 20, 
                    $text_color, "center", 0, false, "none", 
                    isset($config["text_oversampling_value"]) ? intval($config["text_oversampling_value"]) : null
                );
                if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                    $group_name = $options["Group Name"];
                    $smartobject_layers[$group_name][] = $l1;
                    $smartobject_layers[$group_name][] = $l2;
                    $smartobject_orders[$group_name][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    $smartobject_orders[$group_name][] = (empty($options["Order"]) ? 1000 : intval($options["Order"])) - 1;
                } else {
                    $l1['left'] -= $config['group_x'];
                    $l1['top'] -= $config['group_y'];
                    $l2['left'] -= $config['group_x'];
                    $l2['top'] -= $config['group_y'];
                    $json["layers"][] = $l1;
                    $json["layers"][] = $l2;
                    $json["orders"][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    $json["orders"][] = (empty($options["Order"]) ? 1000 : intval($options["Order"])) - 1;
                }
            } else if ($field->type == "List Star" || ($field->type == "List All" && $config["list_type"] == "star")) {
                $strokeWidth = $options["Option3"] == "" ? 10 : intval($options["Option3"]);
                $text_color = isset($config['list_text_color']) ? $config['list_text_color'] : "#ffffff";
                $stroke = empty($options["Option2"]) ? '#ffffff' : $options["Option2"];
                $stroke = isset($config['list_stroke_color']) ? $config['list_stroke_color'] : $stroke;
                $fill = empty($options["Option4"]) || $options["Option4"] == '#00000000' ? null : $options["Option4"];
                $fill = isset($config['list_fill_color']) ? $config['list_fill_color'] : $fill;
                $l1 = $this->bannerService->get_pixel_layer("List Numbered Circle", intval($options['X']), intval($options['Y']), intval($options['Width']), intval($options['Width']), $fill, $strokeWidth, $stroke, intval($options['Width']) / 2 - $strokeWidth, [1, 1, 1, 1]);
                $l2 = $this->bannerService->get_text_layer2(
                    "List Star", 
                    intval($options['X']), 
                    intval($options['Y']), 
                    isset($options['Width']) ? intval($options['Width']) : 0, 
                    isset($options['Height']) ? intval($options['Height']) : 0, 
                    "★", "ArialUnicodeMS", "ARIALUNI.TTF", 
                    isset($options['Font Size']) ? intval($options['Font Size']) : 20, 
                    $text_color, "center", 0, false, "none", 
                    isset($config["text_oversampling_value"]) ? intval($config["text_oversampling_value"]) : null
                );
                if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                    $group_name = $options["Group Name"];
                    $smartobject_layers[$group_name][] = $l1;
                    $smartobject_layers[$group_name][] = $l2;
                    $smartobject_orders[$group_name][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    $smartobject_orders[$group_name][] = (empty($options["Order"]) ? 1000 : intval($options["Order"])) - 1;
                } else {
                    $l1['left'] -= $config['group_x'];
                    $l1['top'] -= $config['group_y'];
                    $l2['left'] -= $config['group_x'];
                    $l2['top'] -= $config['group_y'];
                    $json["layers"][] = $l1;
                    $json["layers"][] = $l2;
                    $json["orders"][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    $json["orders"][] = (empty($options["Order"]) ? 1000 : intval($options["Order"])) - 1;
                }
            } else if ($field->type == "Background Theme Color") {
                $background_pos[] = $options;
            } else if ($field->type == "Rectangle") {
                $visible = $options['Color Selector'] == 'No' || (isset($config[$field->element_id . "_toggle_shape"]) && $config[$field->element_id . "_toggle_shape"] == 'on');
                if ($visible) {
                    $opacity = empty($options["Option5"]) ? 1 : floatval($options["Option5"]);
                    $fill_color = isset($config[$field->element_id . "_fill_color"]) ? $config[$field->element_id . "_fill_color"] : null;
                    $fill_color = $fill_color ? $fill_color : (empty($options["Option3"]) ? "#ffffff" : $options["Option3"]);
                    if ($fill_color && strlen($fill_color) > 7) {
                        $fill_color = null;
                    }
                    $stroke_color = isset($config[$field->element_id . "_stroke_color"]) ? $config[$field->element_id . "_stroke_color"] : null;
                    if (empty($options["Option4"])) {
                        $cornerOptions = [0, 0, 0, 0];
                        $radius = 10;
                    } else {
                        $cornerOptions = array_map('intval', explode(",", $options["Option4"]));
                        $radius = count($cornerOptions) < 5 ? 0 : $cornerOptions[4];
                        if (count($cornerOptions) == 1) {
                            $radius = $cornerOptions[0];
                            $cornerOptions = [1, 1, 1, 1];
                        } else {
                            $cornerOptions = $cornerOptions ? array_slice($cornerOptions, 0, 4) : [0, 0, 0, 0];
                        }
                    }
                    $offset_x = isset($config[$field->element_id . '_offset_x']) ? intval($config[$field->element_id . '_offset_x']) : 0;
                    $offset_y = isset($config[$field->element_id . '_offset_y']) ? intval($config[$field->element_id . '_offset_y']) : 0;
                    $angle = isset($config[$field->element_id . '_angle']) ? intval($config[$field->element_id . '_angle']) : 0;
                    $scaleX = isset($config[$field->element_id . '_scaleX']) ? floatval($config[$field->element_id . '_scaleX']) : 1;
                    $scaleY = isset($config[$field->element_id . '_scaleY']) ? floatval($config[$field->element_id . '_scaleY']) : 1;

                    $p_option = $this->get_positioning_option($positioning_options, $config, $text_fields, $field->name);
                    $y = intval($options['Y']);
                    $x = intval($options['X']);
                    $width = !empty($options['Width']) ? intval($options['Width']) : 1;
                    $height = !empty($options['Height']) ? intval($options['Height']) : 1;
                    if ($p_option != null) {
                        $x = isset($p_option['x']) ? $p_option['x'] : $x;
                        $y = isset($p_option['y']) ? $p_option['y'] : $y;
                        $width = isset($p_option['width']) ? $p_option['width'] : $width;
                    }
                    $l1 = $this->bannerService->get_pixel_layer("Rectangle", (isset($spacingFieldPosition[$field->name]) ? $spacingFieldPosition[$field->name]["x"] : $x) + $offset_x, $y + $offset_y, ceil((isset($spacingFieldPosition[$field->name]) ? $spacingFieldPosition[$field->name]["width"] : $width) * $scaleX), ceil($height* $scaleY), $fill_color, !empty($options["Option2"]) ? intval($options["Option2"]) : 0, $stroke_color ? $stroke_color : (empty($options["Option1"]) ? "#ffffff" : $options["Option1"]), $radius, $cornerOptions, null, $opacity);
                    if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                        $group_name = $options["Group Name"];
                        $smartobject_layers[$group_name][] = $l1;
                        $smartobject_orders[$group_name][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    } else {
                        $l1['left'] -= $config['group_x'];
                        $l1['top'] -= $config['group_y'];
                        $json["layers"][] = $l1;
                        $json["orders"][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    }
                }
            } else if ($field->type == "Circle") {
                if (!isset($config[$field->element_id . "_toggle_shape"]) || (isset($config[$field->element_id . "_toggle_shape"]) && $config[$field->element_id . "_toggle_shape"] == "on")) {
                    $opacity = empty($options["Option5"]) ? 1 : floatval($options["Option5"]);
                    $fill_color = isset($config[$field->element_id . "_fill_color"]) ? $config[$field->element_id . "_fill_color"] : null;
                    $fill_color = $fill_color ? $fill_color : (empty($options["Option3"]) ? "#ffffff" : $options["Option3"]);
                    if ($fill_color && strlen($fill_color) > 7) {
                        $fill_color = null;
                    }
                    $stroke_color = isset($config[$field->element_id . "_stroke_color"]) ? $config[$field->element_id . "_stroke_color"] : null;
                    $offset_x = intval($config[$field->element_id . '_offset_x']);
                    $offset_y = intval($config[$field->element_id . '_offset_y']);
                    $scale = floatval($config[$field->element_id . '_scaleX']);

                    $l1 = $this->bannerService->get_pixel_layer("Circle", intval($options['X']) + $offset_x, intval($options['Y']) + $offset_y, intval($options['Width']) * $scale, intval($options['Height']) * $scale, $fill_color, !empty($options["Option2"]) ? intval($options["Option2"]) : 0, $stroke_color ? $stroke_color : (empty($options["Option1"]) ? "#ffffff" : $options["Option1"]), intval($options['Width']) / 2, [1, 1, 1, 1], null, $opacity);
                    if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                        $group_name = $options["Group Name"];
                        $smartobject_layers[$group_name][] = $l1;
                        $smartobject_orders[$group_name][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    } else {
                        $l1['left'] -= $config['group_x'];
                        $l1['top'] -= $config['group_y'];
                        $json["layers"][] = $l1;
                        $json["orders"][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    }
                }
            } else if ($field->type == "Circle Type") {
                $fill_color = isset($config[$field->element_id . "_fill_color"]) ? $config[$field->element_id . "_fill_color"] : null;
                $offset_x = intval($config[$field->element_id . '_offset_x']);
                $offset_y = intval($config[$field->element_id . '_offset_y']);
                $scale = floatval($config[$field->element_id . '_scale']);

                $l1 = $this->bannerService->get_circle_layer("Circle", intval($options['X']) + $offset_x, intval($options['Y']) + $offset_y, intval($options['Width']) * $scale / 2, $fill_color ? $fill_color : (empty($options["Option1"]) ? "#ffffff" : $options["Option1"]));
                if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                    $group_name = $options["Group Name"];
                    $smartobject_layers[$group_name][] = $l1;
                    $smartobject_orders[$group_name][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                } else {
                    $l1['left'] -= $config['group_x'];
                    $l1['top'] -= $config['group_y'];
                    $json["layers"][] = $l1;
                    $json["orders"][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                }
            } else if ($field->type == "Line") {
                $width = intval($options['Width']);
                $height = intval($options['Height']);
                $offset_x = intval($config[$field->element_id . '_offset_x']);
                $offset_y = intval($config[$field->element_id . '_offset_y']);
                $scale = floatval($config[$field->element_id . '_scale']);
                $l1 = $this->bannerService->get_pixel_layer("Line", intval($options['X']) + $offset_x, intval($options['Y']) + $offset_y, $width * $scale, $height * $scale, $options['Option1']);
                if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                    $group_name = $options["Group Name"];
                    $smartobject_layers[$group_name][] = $l1;
                    $smartobject_orders[$group_name][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                } else {
                    $l1['left'] -= $config['group_x'];
                    $l1['top'] -= $config['group_y'];
                    $json["layers"][] = $l1;
                    $json["orders"][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                }
            } else if ($field->type == "Background Theme Image") {
                $data = $options;
                $data['name'] = $field->name;
                $background_img_pos[] = $data;
            } else if ($field->type == "Image From Background") {
                $data = $options;
                $data['name'] = $field->name;
                $img_from_bk_pos[] = $data;
            } else if ($field->type == "Background Image Upload" && (isset($config[$field->element_id]) || isset($config[$field->element_id."_saved"]))) {
                $path = isset($config[$field->element_id]) ? $config[$field->element_id] : $config[$field->element_id."_saved"];
                if (isset($config[$field->element_id."_saved"])) {
                    $filename = uniqid() . ".png";
                    file_put_contents($filename, file_get_contents($config[$field->element_id."_saved"]));
                    $path = $filename;
                }
                $img = new \Imagick($path);
                $img->scaleImage(intval($options['Width']), intval($options['Height']), true);
                $l1 = $this->bannerService->get_smartobject_layer($field->element_id, $path, (isset($spacingFieldPosition[$field->name]) ? $spacingFieldPosition[$field->name]["x"] : intval($options['X'])), intval($options['Y']), $img->getImageWidth(), $img->getImageHeight(), 0);
                if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                    $group_name = $options["Group Name"];
                    $smartobject_layers[$group_name][] = $l1;
                    $smartobject_orders[$group_name][] = 1001;
                } else {
                    $l1['left'] -= $config['group_x'];
                    $l1['top'] -= $config['group_y'];
                    $json["layers"][] = $l1;
                    $json["orders"][] = 1001;
                }
            } else if ($field->type == "Upload Image" && (isset($config[$field->element_id]) || isset($config[$field->element_id."_saved"]))) {
                $path = isset($config[$field->element_id]) ? $config[$field->element_id] : $config[$field->element_id."_saved"];
                if (isset($config[$field->element_id."_saved"])) {
                    $filename = uniqid() . ".png";
                    $config[$field->element_id."_saved"] = str_replace(" ", "%20", $config[$field->element_id."_saved"]);
                    file_put_contents($filename, file_get_contents($config[$field->element_id."_saved"]));
                    $path = $filename;
                }
                $ext = array_reverse(explode('.', $path))[0];
                $img = new \Imagick($path);
                if ($ext == "psd") {
                    $img->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                    $img->setImageFormat('png');
                }
                $w = intval($options['Width']);
                $h = intval($options['Height']);
                // $cw = isset($config["canvas_dimension_width"]) ? intval($config["canvas_dimension_width"]) : $config["width"];
                if ($options["Option1"]) {
                    // $w = $cw;
                    // $h = $cw / $img->getImageWidth() * $img->getImageHeight();
                    $w = $config["width"];
                    $h = $config["width"] / $img->getImageWidth() * $img->getImageHeight();
                }
                $img->scaleImage($w, $h + 1, true);
                $x = intval($options['X']) + intval($config[$field->element_id . '_offset_x']);
                if (isset($config[$field->element_id."_width"])) {
                    $x = $x + $img->getImageWidth() / 2 - intval($config[$field->element_id."_width"]) / 2;
                }
                $y = intval($options['Y']) + intval($config[$field->element_id . '_offset_y']);
                if (isset($config[$field->element_id."_height"])) {
                    $y = $y + $img->getImageHeight() / 2 - intval($config[$field->element_id."_height"]) / 2;
                }
                $l1 = $this->bannerService->get_smartobject_layer($field->element_id, $path, round($x), round($y), round($img->getImageWidth() * floatval($config[$field->element_id . '_scale'])), round($img->getImageHeight() * floatval($config[$field->element_id . '_scale'])), floatval($config[$field->element_id . '_angle']));
                if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                    $group_name = $options["Group Name"];
                    $smartobject_layers[$group_name][] = $l1;
                    $smartobject_orders[$group_name][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                } else {
                    $l1['left'] -= $config['group_x'];
                    $l1['top'] -= $config['group_y'];
                    $json["layers"][] = $l1;
                    $json["orders"][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                }
            } else if ($field->type == "Image List") {
                $value = isset($config[$field->element_id]) ? $config[$field->element_id] : '';
                foreach ($image_list_group as $ilg) {
                    $ilg_arr = explode(",", $ilg['group']);
                    if (in_array($options['Name'], $ilg_arr)) {
                        $value = $config[$ilg['element_id']];
                    }
                }
                if ($value == "none") continue;
                $url = siteUrl() . "/share?file=" . $value;
                $arr = explode(".", $value);
                $extension = end($arr);
                if ($extension == "svg") {
                    $arr = explode("/", $value);
                    $filename = "img/list/" . end($arr);
                } else {
                    $filename = uniqid() . ".png";
                    file_put_contents($filename, file_get_contents($url));
                }
                $img = new \Imagick($filename);
                $img->scaleImage(intval($options['Width']), intval($options['Height']), true);
                $l1 = $this->bannerService->get_smartobject_layer($field->element_id, $filename, intval($options['X']) + intval($config[$field->element_id . '_offset_x']), intval($options['Y']) + intval($config[$field->element_id . '_offset_y']), round($img->getImageWidth() * floatval($config[$field->element_id . '_scale'])), round($img->getImageHeight() * floatval($config[$field->element_id . '_scale'])), intval($config[$field->element_id . '_angle']));
                if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                    $group_name = $options["Group Name"];
                    $smartobject_layers[$group_name][] = $l1;
                    $smartobject_orders[$group_name][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                } else {
                    $l1['left'] -= $config['group_x'];
                    $l1['top'] -= $config['group_y'];
                    $json["layers"][] = $l1;
                    $json["orders"][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                }
            } else if ($field->type == "Static Image") {
                if (!empty($options['Filename'])) {
                    $arr = explode('.', $options['Filename']);
                    $ext = $arr[count($arr) - 1];
                    $filename = uniqid() . "." . $ext;
                    $path = siteUrl() . "/share?file=" . $options['Filename'];
                    file_put_contents($filename, file_get_contents($path));
                    $img = new \Imagick($filename);
                    $img->scaleImage(0, intval($options['Height']));
                    $l1 = $this->bannerService->get_smartobject_layer(
                        $field->element_id,
                        $filename,
                        intval($options['X']) + (isset($config[$field->element_id . '_offset_x']) ? intval($config[$field->element_id . '_offset_x']) : 0),
                        intval($options['Y']) + (isset($config[$field->element_id . '_offset_y']) ? intval($config[$field->element_id . '_offset_y']) : 0),
                        round($img->getImageWidth() * (isset($config[$field->element_id . '_scale']) ? floatval($config[$field->element_id . '_scale']) : 1)),
                        round($img->getImageHeight() * (isset($config[$field->element_id . '_scale']) ? floatval($config[$field->element_id . '_scale']) : 1)),
                        isset($config[$field->element_id . '_angle']) ? intval($config[$field->element_id . '_angle']) : 0
                    );
                    if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                        $group_name = $options["Group Name"];
                        $smartobject_layers[$group_name][] = $l1;
                        $smartobject_orders[$group_name][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    } else {
                        $l1['left'] -= $config['group_x'];
                        $l1['top'] -= $config['group_y'];
                        $json["layers"][] = $l1;
                        $json["orders"][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    }
                }
            } else if ($field->type == "Overlay Area") {
                if (!empty($options["Option1"])) {
                    $l1 = $this->bannerService->get_pixel_layer("Overlay Area", intval($options['X']), intval($options['Y']), intval($options['Width']), intval($options['Height']), $options["Option1"]);
                    if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                        $group_name = $options["Group Name"];
                        $smartobject_layers[$group_name][] = $l1;
                        $smartobject_orders[$group_name][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    } else {
                        $l1['left'] -= $config['group_x'];
                        $l1['top'] -= $config['group_y'];
                        $json["layers"][] = $l1;
                        $json["orders"][] = empty($options["Order"]) ? 1000 : intval($options["Order"]);
                    }
                }
                $overlay_area["x"] = intval($options["X"]);
                $overlay_area["y"] = intval($options["Y"]);
                $overlay_area["width"] = intval($options["Width"]);
                $overlay_area["height"] = intval($options["Height"]);
                $overlay_area["order"] = intval($options["Order"]);
            } else if ($field->type == "Canvas") {
                if (isset($config["show_canvas"])) {
                    $json["width"] = intval($options["Width"]);
                    $json["height"] = intval($options["Height"]);
                }
            }
        }

        $additional_texts = array_flip(preg_grep("/\badd_text_/", array_keys($config)));
        foreach ($additional_texts as $key => $value) {
            if (strlen($key) == 22) {
                $font = $config[$key . '_font'];
                $l = $this->bannerService->get_text_layer2(
                    $field->name,
                    intval($config[$key . '_offset_x']),
                    intval($config[$key . '_offset_y']),
                    intval($config[$key . '_width']),
                    0,
                    $config[$key],
                    $imagemagick_fonts[$font],
                    $fonts[$font],
                    intval($config[$key . '_fontsize']),
                    $config[$key . '_color'],
                    "center",
                    0, false, "none",
                    isset($config["text_oversampling_value"]) ? intval($config["text_oversampling_value"]) : null
                );
                $json["layers"][] = $l;
                $json["orders"][] = 1000;
            }
        }

        $additional_rectangles = array_flip(preg_grep("/\badd_rectangle_/", array_keys($config)));
        foreach ($additional_rectangles as $key => $value) {
            if (strlen($key) == 27) {
                $visible = isset($config[$key . "_toggle_shape"]) && $config[$key . "_toggle_shape"] == 'on';
                if ($visible) {
                    $fill_color = isset($config[$key . "_fill_color"]) ? $config[$key . "_fill_color"] : "#000000";
                    $stroke_color = isset($config[$key . "_stroke_color"]) ? $config[$key . "_stroke_color"] : "#000000";
                    $offset_x = isset($config[$key . '_offset_x']) ? intval($config[$key . '_offset_x']) : 0;
                    $offset_y = isset($config[$key . '_offset_y']) ? intval($config[$key . '_offset_y']) : 0;
                    $angle = isset($config[$key . '_angle']) ? intval($config[$key . '_angle']) : 0;
                    $width = isset($config[$key . '_width']) ? floatval($config[$key . '_width']) : 300;
                    $height = isset($config[$key . '_height']) ? floatval($config[$key . '_height']) : 300;
                    $l1 = $this->bannerService->get_pixel_layer(
                        "Rectangle", 
                        $offset_x, 
                        $offset_y, 
                        ceil($width), 
                        ceil($height), 
                        $fill_color, 
                        5, 
                        $stroke_color
                    );
                    $json["layers"][] = $l1;
                    $json["orders"][] = 1000;
                }
            }
        }

        $additional_circles = array_flip(preg_grep("/\badd_circle_/", array_keys($config)));
        foreach ($additional_circles as $key => $value) {
            if (strlen($key) == 24) {
                $visible = isset($config[$key . "_toggle_shape"]) && $config[$key . "_toggle_shape"] == 'on';
                if ($visible) {
                    $fill_color = isset($config[$key . "_fill_color"]) ? $config[$key . "_fill_color"] : "#000000";
                    $stroke_color = isset($config[$key . "_stroke_color"]) ? $config[$key . "_stroke_color"] : "#000000";
                    $offset_x = isset($config[$key . '_offset_x']) ? intval($config[$key . '_offset_x']) : 0;
                    $offset_y = isset($config[$key . '_offset_y']) ? intval($config[$key . '_offset_y']) : 0;
                    $angle = isset($config[$key . '_angle']) ? intval($config[$key . '_angle']) : 0;
                    $width = isset($config[$key . '_width']) ? floatval($config[$key . '_width']) : 300;
                    $height = isset($config[$key . '_height']) ? floatval($config[$key . '_height']) : 300;
                    $l1 = $this->bannerService->get_pixel_layer(
                        "Circle", 
                        $offset_x, 
                        $offset_y, 
                        ceil($width), 
                        ceil($height), 
                        $fill_color, 
                        5, 
                        $stroke_color, 
                        $width / 2, 
                        [1, 1, 1, 1]
                    );
                    $json["layers"][] = $l1;
                    $json["orders"][] = 1000;
                }
            }
        }

        if(isset($config["img_from_bk"])) {
            for ($key = count($config["img_from_bk"]) - 1; $key >= 0; $key--) {
                if (isset($config["img_from_bk"][$key]) && isset($img_from_bk_pos[$key])) {
                    $img = new \Imagick($config["img_from_bk"][$key]);
                    $x_offset = intval($config["img_from_bk_offset_x"][$key]);
                    $y_offset = intval($config["img_from_bk_offset_y"][$key]);
                    $scale = isset($config["img_from_bk_scale"]) ? floatval($config["img_from_bk_scale"][$key]) : 1;

                    $p_option = $this->get_positioning_option($positioning_options, $config, $text_fields, $img_from_bk_pos[$key]['Name']);
                    $x = intval($img_from_bk_pos[$key]['X']);
                    $y = intval($img_from_bk_pos[$key]['Y']);
                    $w = isset($img_from_bk_pos[$key]['Width']) ? intval($img_from_bk_pos[$key]['Width']) : $config["width"];
                    if ($p_option != null) {
                        $x = isset($p_option['x']) ? $p_option['x'] : $x;
                        $y = isset($p_option['y']) ? $p_option['y'] : $y;
                        $w = isset($p_option['width']) ? $p_option['width'] : $w;
                    }

                    $w = ceil($w * $scale);
                    $h = ceil($w / $img->getImageWidth() * $img->getImageHeight());
                    $img->scaleImage($w, $h, true);
                    $field_name = $img_from_bk_pos[$key]['name'];
                    $l = $this->bannerService->get_smartobject_layer("img_from_bk", $config["img_from_bk"][$key], (isset($spacingFieldPosition[$field_name]) ? $spacingFieldPosition[$field_name]["x"] : $x) + $x_offset - $config['group_x'], $y + $y_offset - $config['group_y'], (isset($spacingFieldPosition[$field_name]) ? $spacingFieldPosition[$field_name]["width"] : $w), $h, 0, null, null, 0, 0);

                    if (!empty($options["Group Name"]) && !empty($smartobject) && isset($smartobject[$options["Group Name"]])) {
                        $group_name = $options["Group Name"];
                        $smartobject_layers[$group_name][] = $l;
                        $smartobject_orders[$group_name][] = isset($img_from_bk_pos[$key]['Order']) ? intval($img_from_bk_pos[$key]['Order']) : 1000;
                    } else {
                        $l['left'] -= $config['group_x'];
                        $l['top'] -= $config['group_y'];
                        $json["layers"][] = $l;
                        $json["orders"][] = isset($img_from_bk_pos[$key]['Order']) ? intval($img_from_bk_pos[$key]['Order']) : 1000;
                    }
                }
            }
        }

        foreach ($smartobject as $key => $value) {
            $sl = isset($smartobject_layers[$key]) ? $smartobject_layers[$key] : [];
            $so = isset($smartobject_orders[$key]) ? $smartobject_orders[$key] : [];
            uasort($so, function ($a, $b) {
                return $b - $a;
            });
            $layers =  array();
            foreach ($so as $k => $value) {
                $layers[] = $sl[$k];
            }
            $smartobject[$key]["psd"]["layers"] = $layers;
            $smartobject[$key]["psd"]["orders"] = $so;
            $json["layers"][] = $smartobject[$key];
            $json["orders"][] = $smartobject[$key]["order"];
        }

        if(isset($config["background"])) {
            for ($key = count($config["background"]) - 1; $key >= 0; $key--) {
                if (isset($config["background"][$key]) && isset($background_img_pos[$key])) {
                    $img = new \Imagick($config["background"][$key]);

                    $p_option = $this->get_positioning_option($positioning_options, $config, $text_fields, $background_img_pos[$key]['Name']);
                    $x = intval($background_img_pos[$key]['X']);
                    $y = intval($background_img_pos[$key]['Y']);
                    $w = isset($background_img_pos[$key]['Width']) ? intval($background_img_pos[$key]['Width']) : 0;
                    if ($p_option != null) {
                        $x = isset($p_option['x']) ? $p_option['x'] : $x;
                        $y = isset($p_option['y']) ? $p_option['y'] : $y;
                        $w = isset($p_option['width']) ? $p_option['width'] : $w;
                    }
                    $field_name = $background_img_pos[$key]['name'];
                    $h = isset($background_img_pos[$key]['Height']) ? intval($background_img_pos[$key]['Height']) : 0;
                    // $w = min($w, $img->getImageWidth());
                    // $h = min($h, $img->getImageHeight());
                    $w = $w ? $w : $img->getImageWidth();
                    $h = $h ? $h : $img->getImageHeight();
                    $x_offset = isset($config["bk_img_offset_x"]) ? intval($config["bk_img_offset_x"][$key]) : 0;
                    $y_offset = isset($config["bk_img_offset_y"]) ? intval($config["bk_img_offset_y"][$key]) : 0;
                    $scale = isset($config["bk_img_scale"]) ? floatval($config["bk_img_scale"][$key]) : 1;
                    $crop = null;
                    if ($background_img_pos[$key]['Option5'] == 'crop') {
                        $crop = [0, 0, $w, $h];
                    }
                    $w = round((isset($spacingFieldPosition[$field_name]) ? $spacingFieldPosition[$field_name]["width"] : $w) * $scale);
                    $h = round($h * $scale);
                    array_unshift($json["layers"], $this->bannerService->get_smartobject_layer("Background", $config["background"][$key], (isset($spacingFieldPosition[$field_name]) ? $spacingFieldPosition[$field_name]["x"] : $x) + $x_offset - $config['group_x'], $y + $y_offset - $config['group_y'], $w, $h, 0, null, null, 0, 0, $crop));
                    array_unshift($json["orders"], isset($background_img_pos[$key]['Order']) ? intval($background_img_pos[$key]['Order']) : 1000);
                }
            }
        }

        foreach ($background_pos as $key => $pos) {
            if (isset($config["background_color"][$key])) {
                $colors = explode(",", $config["background_color"][$key]);
                if ($colors[0] == "solid") {
                    array_unshift($json["layers"], $this->bannerService->get_pixel_layer("Background", intval($pos['X']), intval($pos['Y']), intval($pos['Width']), intval($pos['Height']), isset($config["background_color"]) ? $colors[1] : '#ffffff'));
                } else if ($colors[0] == "gradient") {
                    $gradient = array(
                        "direction" => "down",
                        "start_color" => $colors[1],
                        "end_color" => $colors[2]
                    );
                    array_unshift($json["layers"], $this->bannerService->get_pixel_layer("Background", intval($pos['X']), intval($pos['Y']), intval($pos['Width']), intval($pos['Height']), null, 0, null, 0, [0, 0, 0, 0], $gradient));
                } else if ($colors[0] == "animation") {
                    $json["animation"] = [20, 40];
                    $json["output_filename"] = "result";
                    // $json["color"] = [$colors[1], $colors[2]];
                    array_unshift($json["layers"], $this->bannerService->get_pixel_layer("Background", intval($pos['X']), intval($pos['Y']), intval($pos['Width']), intval($pos['Height']), [$colors[1], $colors[2]]));
                }
                array_unshift($json["orders"], intval($pos['Order']));
            }
        }

        // Set Order
        uasort($json["orders"], function ($a, $b) {
            return $b - $a;
        });
        $layers =  array();
        foreach ($json["orders"] as $key => $value) {
            $layers[] = $json["layers"][$key];
        }
        $json["layers"] = $layers;

        if (!isset($config["overlay_area"])) {
            foreach ($json["layers"] as $key => $layer) {
                $left = intval(isset($layer['left']) ? $layer['left'] : (isset($layer['coordinates']) ? $layer['coordinates'][0]['left'] : '0'));
                $top = intval(isset($layer['top']) ? $layer['top'] : (isset($layer['coordinates']) ? $layer['coordinates'][0]['top'] : '0'));
                $width = isset($layer['width']) ? intval($layer['width']) : (isset($layer['radius']) ? intval($layer['radius']) * 2 : intval(isset($layer['coordinates']) ? $layer['coordinates'][0]['width'] : '0'));
                $height = isset($layer['height']) ? intval($layer['height']) : (isset($layer['radius']) ? intval($layer['radius']) * 2 : intval(isset($layer['coordinates']) ? $layer['coordinates'][0]['height'] : '0'));
                if (!empty($overlay_area) && ($left < 0 || $overlay_area["x"] <= $left) && ($top < 0 || $overlay_area["y"] <= $top)
                    && ($overlay_area["x"] + $overlay_area["width"]) >= ($left + $width)
                    && ($overlay_area["y"] + $overlay_area["height"]) >= ($top + $height)
                    && $overlay_area["order"] > $json["orders"][$key]) {
                    unset($json["layers"][$key]);
                    unset($json["orders"][$key]);
                }
            }
            $json["layers"] = array_values($json["layers"]);
        }

        if (isset($config["show_stroke"])) {
            $stroke_width = intval($config["stroke_width"]);
            // $cw = isset($config["canvas_dimension_width"]) ? intval($config["canvas_dimension_width"]) : $config["width"];
            // $ch = isset($config["canvas_dimension_height"]) ? intval($config["canvas_dimension_height"]) : $config["height"];
            $json["layers"][] = $this->bannerService->get_pixel_layer("Stroke", -$stroke_width / 2, -$stroke_width / 2, $json["width"] + $stroke_width, $json["height"] + $stroke_width, null, $stroke_width, $config["stroke_color"]);
        }

        Log::debug(json_encode($json));
        return base64_encode(json_encode($json));
    }

    public function get_history_settings($request)
    {
        $settings = $request->all();
        $files = $request->file();
        foreach ($files as $file) {
            $fname = $file->getClientOriginalName();
            $fname = explode(".", $fname);
            array_pop($fname);
            $fname = implode(".", $fname);
            if (isset($request->{$fname})) {
                $filename = $request->{$fname}->getClientOriginalName();
                $filepath = 'uploads/'. auth()->user()->company_id . '/' . uniqid() . "." . $request->{$fname}->getClientOriginalExtension();
                Storage::disk('s3')->put($filepath, file_get_contents($file));
            } else {
                $filename = $request->{$fname."_saved_name"};
                $filepath = $request->{$fname."_saved"};
            }
            $settings[$fname."_saved"] = url('/share?file='). $filepath;
            $settings[$fname."_saved_name"] = url('/share?file='). $filename;
        }
        return json_encode($settings);
    }

    public function run($request, $preview = false, $save = false, $publish = false)
    {
        // Map files from UTC/GTIN/ASIN/TCIN/WMT-ID
        $file_ids = preg_replace('/\s+/', " ", $request->file_ids);
        $result = $this->bannerService->map_files(explode(" ", $file_ids));
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
            $config = $this->get_config($request);

            $jpeg_file_id = uniqid();
            $jpeg_file = $jpeg_file_id . ".jpg";

            $input_arg = $this->get_psd($result["files"], $product_filenames, $config);
            $half_size = isset($config["half_size"]) ? " -half" : "";

            $command = escapeshellcmd("python3 /var/www/psd2/tool.py -j " . $input_arg . " -of result -p " . $jpeg_file_id . $half_size);
            $log = shell_exec($command . " 2>&1");
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
                $zip_filename = (!empty($request->output_filename) ? $request->output_filename : (!empty($request->project_name) ? $request->project_name : $request->template_name));
                if ($zip_filename  == 'null' && isset($request->instance_id)) {
                    $layout = $this->gridLayoutService->getById($request->layout_id);
                    $template = Template::find($request->template_id);
                    $zip_filename = $layout->name . '_' . $template->name;
                }
                $zip = new ZipArchive();
                $log = "";
                if ($zip->open(public_path($zip_file), ZipArchive::CREATE) === TRUE) {
                    $output_jpg_files = array();
                    $output_psd_files = array();
                    $config = $this->get_config($request);
                    $output_filename = $this->get_output_filename($request);

                    $psd_file_id = uniqid();
                    $psd_file = $psd_file_id . ".psd";
                    $jpeg_file_id = uniqid();
                    $jpeg_file = $jpeg_file_id . ".jpg";

                    $input_arg = $this->get_psd($result["files"], $product_filenames, $config);
                    $half_size = isset($config["half_size"]) ? " -half" : "";
                    $max_file_size = "";
                    if (isset($config["max_file_size"])) {
                        $max_file_size = " -fs " . $config['max_file_size'];
                    }
                    $command = escapeshellcmd("python3 /var/www/psd2/tool.py -j " . $input_arg . " -of " . $zip_file_id . " -o " . $psd_file_id . " -p " . $jpeg_file_id . $max_file_size . $half_size);
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
                    $zip->close();

                    if (file_exists($zip_file)) {
                        Storage::disk('s3')->put('outputs/' . $zip_file, file_get_contents(public_path($zip_file)));
                        $temp_files[] = $zip_file;
                    }

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
                            'customer' => $request->customer_id,
                            'output_dimensions' => $request->output_dimensions,
                            'projectname' => $request->project_name,
                            'url' => 'outputs/' . $zip_file,
                            'fileid' => $file_ids,
                            'headline' => "New Template",
                            'size' => $request->template_name,
                            'settings' => $this->get_history_settings($request),
                            'jpg_files' => implode(" ", $output_jpg_files),
                            'type' => $request->type,
                            'parent_id' => isset($request->parent_id) ? $request->parent_id : 0
                        ]);
                    }

                    if ($publish) {
                        $this->bannerService->save_project([
                            'name' => $zip_filename,
                            'customer' => $request->customer_id,
                            'output_dimensions' => $request->output_dimensions,
                            'projectname' => $request->project_name,
                            'url' => 'outputs/' . $zip_file,
                            'fileid' => $file_ids,
                            'headline' => "New Template",
                            'size' => $request->template_name,
                            'settings' => $this->get_history_settings($request),
                            'jpg_files' => implode(" ", $output_jpg_files),
                            'type' => $request->type,
                            'parent_id' => isset($request->parent_id) ? $request->parent_id : 0
                        ]);
                    }
                }

                if (file_exists($zip_file)) {
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
            $this->bannerService->save_exception(['file_id' => $file_ids, 'message' => $msg]);
        }

        foreach ($temp_files as $filename) {
            if (file_exists($filename)) {
                unlink($filename);
            }
        }

        return $response;
    }

    public function download_layout_assets($request)
    {
        $layout = $this->gridLayoutService->getById($request->layout_id);
        $options = json_decode($layout->options);
        $output_files = [];

        $logs = [];

        $zip_file_id = uniqid();
        $zip_file = $zip_file_id . ".zip";
        $zip = new ZipArchive();

        $output_filename_map = [];

        $layout_group = isset($options->group) ? $options->group : 'All';
        if ($zip->open(public_path($zip_file), ZipArchive::CREATE) === TRUE) {
            foreach ($layout->templates as $template) {
                if (
                    str_contains($layout->settings, $template->instance_id) &&
                    (
                        !isset($options->downloadable_templates) ||
                        count($options->downloadable_templates->{$layout_group}) === 0 ||
                        in_array($template->instance_id, $options->downloadable_templates->{$layout_group})
                    )
                ) {
                    $settings = json_decode($template->settings, true);
                    $settings['show_text'] = 'on';
                    if (isset($options->show_overlay) && boolval($options->show_overlay)) {
                        $settings["overlay_area"] = 'on';
                    } else {
                        unset($settings["overlay_area"]);
                    }

                    if (isset($options->show_canvas) && boolval($options->show_canvas)) {
                        $settings["show_canvas"] = "on";
                    }

                    if (!isset($settings['file_ids'])) {
                        $settings['file_ids'] = "";
                    }
                    $result = $this->bannerService->map_files(explode(" ", $settings['file_ids']));

                    $temp_files = [];
                    $product_filenames = [];
                    foreach ($result["files"] as $file) {
                        $filename = uniqid() . ".png";
                        $contents = Storage::disk('s3')->get($file["path"]);
                        Storage::disk('public')->put($filename, $contents);
                        $product_filenames[] = $filename;
                        $temp_files[] = $filename;
                    }

                    $config = $this->get_layout_config($settings);

                    $output_filename = '';
                    if (isset($options->use_custom_naming) && boolval($options->use_custom_naming)) {
                        $t = Template::find($template->template_id);
                        $layout_name = $layout->name;
                        $brand = isset($options->brand) ? $options->brand : '';
                        $project_name = isset($settings['project_name']) && !empty($settings['project_name']) ? $settings['project_name'] : '';
                        $output_filename = $options->custom_name;
                        $output_filename = str_replace('%Brand%', $brand, $output_filename);
                        $output_filename = str_replace('%LayoutName%', $layout_name, $output_filename);
                        $output_filename = str_replace('%TemplateName%', $t->name, $output_filename);
                        $output_filename = str_replace('%ProjectName%', $project_name, $output_filename);
                        $output_filename = str_replace('%TemplateWidth%', $t->width, $output_filename);
                        $output_filename = str_replace('%TemplateHeight%', $t->height, $output_filename);
                        $output_filename = str_replace('%LayoutTitle%', $options->title, $output_filename);
                        if (str_contains($output_filename, '%SpaceToUnderscore%')) {
                            $output_filename = str_replace('%SpaceToUnderscore%', '', $output_filename);
                            $output_filename = str_replace(' ', '_', $output_filename);
                        }
                    } else {
                        $output_filename = Template::find($template->template_id)->name;
                        if (isset($settings['project_name']) && !empty($settings['project_name'])) {
                            $output_filename = $settings['project_name'];
                        }
                    }

                    $group_field_names = '';
                    $t = Template::find($template->template_id);
                    foreach ($t->fields as $field) {
                        $field_options = json_decode($field->options, true);
                        if ($field->type == 'Group' && isset($options->group) && $field->name == $options->group) {
                            $group_field_names = $field_options['Option1'];
                            $config['width'] = intval($field_options['Width']);
                            $config['height'] = intval($field_options['Height']);
                            $config['group_x'] = intval($field_options['X']);
                            $config['group_y'] = intval($field_options['Y']);
                        }
                    }

                    $psd_file_id = uniqid();
                    $psd_file = $psd_file_id . ".psd";
                    $jpeg_file_id = uniqid();
                    $jpeg_file = $jpeg_file_id . ".jpg";

                    $input_arg = $this->get_psd($result["files"], $product_filenames, $config, $group_field_names);
                    $max_file_size = "";
                    if (isset($config["max_file_size"])) {
                        $max_file_size = " -fs " . $config['max_file_size'];
                    }
                    $command_str = "python3 /var/www/psd2/tool.py -j " . $input_arg . " -of " . $zip_file_id . " -o " . $psd_file_id . " -p " . $jpeg_file_id . $max_file_size;
                    if (isset($config["half_size"]) || (isset($options->resolution_size) && $options->resolution_size == '50')) {
                        $command_str = $command_str . " -half";
                    }
                    if (isset($options->resolution_size_suffix) && !empty($options->resolution_size_suffix)) {
                        $output_filename = $output_filename . $options->resolution_size_suffix;
                    }
                    $command = escapeshellcmd($command_str);
                    $log = shell_exec($command . " 2>&1");
                    $temp_files = array_merge($temp_files, array("tmp_result.psd", "shadow.png", "circle.png", "result.zip"));
                    $output_files[] = $psd_file;
                    $output_files[] = $jpeg_file;

                    $logs[] = $log;

                    if (isset($output_filename_map[$output_filename])) {
                        $output_filename_map[$output_filename] += 1;
                        $output_filename = $output_filename . '_' . $output_filename_map[$output_filename];
                    } else {
                        $output_filename_map[$output_filename] = 0;
                    }

                    $zip->addFile($jpeg_file, $output_filename . ".jpg");
                    if ($request->include_psd) {
                        $zip->addFile($psd_file, $output_filename . ".psd");
                    }

                    foreach ($temp_files as $filename) {
                        if (file_exists($filename)) {
                            unlink($filename);
                        }
                    }
                }
            }
        }
        $zip->close();

        Storage::disk('s3')->put('outputs/' . $zip_file, file_get_contents(public_path($zip_file)));
        $output_files[] = $zip_file;
        foreach ($output_files as $filename) {
            if (file_exists($filename)) {
                unlink($filename);
            }
        }

        $output_filename = $layout->name;
        if ($options != null && isset($options->prepend_to_filename) && $options->prepend_to_filename && isset($options->brand) && !empty($options->brand)) {
            $output_filename = $options->brand . '_' . $output_filename;
        }

        return [
            "url" => Storage::disk('s3')->temporaryUrl('outputs/' . $zip_file, now()->addHours(1), [
                'ResponseContentDisposition' => 'attachment; filename="' . $output_filename . '.zip"'
            ]),
            "projectname" => $layout->name,
            "logs" => $logs
        ];
    }

    private function replaceTextVariables($text, $options)
    {
        $result = $text;
        $result = str_replace('%ProjectName%', $options['project_name'], $result);
        $result = str_replace('%LayoutName%', $options['layout_name'], $result);
        $result = str_replace('%LayoutTitle%', $options['title'], $result);
        $result = str_replace('%Brand%', $options['brand'], $result);
        $result = str_replace('%TemplateName%', $options['template']->name, $result);
        $result = str_replace('%TemplateWidth%', $options['template']->width, $result);
        $result = str_replace('%TemplateHeight%', $options['template']->height, $result);
        if (str_contains($result, '%SpaceToUnderscore%')) {
            $result = str_replace('%SpaceToUnderscore%', '', $result);
            $result = str_replace(' ', '_', $result);
        }
        return $result;
    }

    public function download_layout_web($request)
    {
        $layout = $this->gridLayoutService->getById($request->layout_id);
        $options = json_decode($layout->options);
        $output_files = [];

        $logs = [];

        $zip_file_id = uniqid();
        $zip_file = $zip_file_id . ".zip";
        $zip = new ZipArchive();

        $output_filename_map = [];

        $web_page_content = Storage::disk('public')->get('web_pages/' . $options->web_page_file_path);

        $output_zip_filename = $layout->name;
        if ($options != null && isset($options->prepend_to_filename) && $options->prepend_to_filename && isset($options->brand) && !empty($options->brand)) {
            $output_zip_filename = $options->brand . '_' . $output_zip_filename;
        }

        if ($zip->open(public_path($zip_file), ZipArchive::CREATE) === TRUE) {
            foreach ($layout->templates as $template) {
                if (str_contains($layout->settings, $template->instance_id)) {
                    $settings = json_decode($template->settings, true);
                    $settings['show_text'] = 'on';
                    if (isset($options->show_overlay) && boolval($options->show_overlay)) {
                        $settings["overlay_area"] = 'on';
                    } else {
                        unset($settings["overlay_area"]);
                    }

                    if (isset($options->show_canvas) && boolval($options->show_canvas)) {
                        $settings["show_canvas"] = "on";
                    }

                    if (!isset($settings['file_ids'])) {
                        $settings['file_ids'] = "";
                    }
                    $result = $this->bannerService->map_files(explode(" ", $settings['file_ids']));

                    $temp_files = [];
                    $product_filenames = [];
                    foreach ($result["files"] as $file) {
                        $filename = uniqid() . ".png";
                        $contents = Storage::disk('s3')->get($file["path"]);
                        Storage::disk('public')->put($filename, $contents);
                        $product_filenames[] = $filename;
                        $temp_files[] = $filename;
                    }

                    $config = $this->get_layout_config($settings);

                    $output_filename = '';
                    if (isset($options->use_custom_naming) && boolval($options->use_custom_naming)) {
                        $t = Template::find($template->template_id);
                        $output_filename = $this->replaceTextVariables($options->custom_name, [
                            'project_name' => isset($settings['project_name']) && !empty($settings['project_name']) ? $settings['project_name'] : '',
                            'layout_name' => $layout->name,
                            'title' => $options->title,
                            'brand' => isset($options->brand) ? $options->brand : '',
                            'template' => $t
                        ]);
                    } else {
                        $output_filename = Template::find($template->template_id)->name;
                        if (isset($settings['project_name']) && !empty($settings['project_name'])) {
                            $output_filename = $settings['project_name'];
                        }
                    }
                    if (isset($options->resolution_size_suffix) && !empty($options->resolution_size_suffix)) {
                        $output_filename = $output_filename . $options->resolution_size_suffix;
                    }

                    if (isset($output_filename_map[$output_filename])) {
                        $output_filename_map[$output_filename] += 1;
                        $output_filename = $output_filename . '_' . $output_filename_map[$output_filename];
                    } else {
                        $output_filename_map[$output_filename] = 0;
                    }

                    $output_filename_ext = $output_filename . ".jpg";

                    $jpeg_file_id = uniqid();
                    $jpeg_file = $jpeg_file_id . ".jpg";

                    $group_field_names = '';
                    $t = Template::find($template->template_id);
                    foreach ($t->fields as $field) {
                        $field_options = json_decode($field->options, true);
                        if ($field->type == 'Group' && $field->name == $options->group) {
                            $group_field_names = $field_options['Option1'];
                            $config['width'] = intval($field_options['Width']);
                            $config['height'] = intval($field_options['Height']);
                            $config['group_x'] = intval($field_options['X']);
                            $config['group_y'] = intval($field_options['Y']);
                        }
                        if ($field->type == 'HTML') {
                            $html_text = $field_options['Option1'];
                            foreach ($t->fields as $ff) {
                                if (isset($config[$ff->element_id]) && is_string($config[$ff->element_id])) {
                                    $html_text = str_replace('%'.$ff->name.'%', $config[$ff->element_id], $html_text);
                                    $fontsize = isset($config[$ff->element_id . '_fontsize']) ? intval($config[$ff->element_id . '_fontsize']) : (!empty($field_options['Font Size']) ? intval($field_options['Font Size']) : 20);
                                    $alignment = isset($config[$ff->element_id . '_alignment']) ? $config[$ff->element_id . '_alignment'] : 'left';
                                    $html_text = str_replace('%'.$ff->name.'-font-size%', $fontsize, $html_text);
                                    $html_text = str_replace('%'.$ff->name.'-font-align%', $alignment, $html_text);
                                }
                            }
                            $html_text = str_replace('%image_filename%', $output_filename_ext, $html_text);
                            $web_page_content = Str::replaceFirst($field_options['Cell'], $html_text, $web_page_content);
                        } else {
                            if (!empty($field_options['Cell'])) {
                                if ($field->type == 'Filename Cell') {
                                    $web_page_content = Str::replaceFirst($field_options['Cell'], $output_filename_ext, $web_page_content);
                                } else {
                                    if (isset($config[$field->element_id])) {
                                        $web_page_content = Str::replaceFirst($field_options['Cell'], $config[$field->element_id], $web_page_content);
                                        $fontsize = isset($config[$field->element_id . '_fontsize']) ? intval($config[$field->element_id . '_fontsize']) : (!empty($field_options['Font Size']) ? intval($field_options['Font Size']) : 20);
                                        $web_page_content = str_replace('%'.str_replace('%', '', $field_options['Cell']).'-font-size%', $fontsize, $web_page_content);
                                        $alignment = isset($config[$field->element_id . '_alignment']) ? $config[$field->element_id . '_alignment'] : (!empty($field_options['Alignment']) ? intval($field_options['Alignment']) : 'left');
                                        $web_page_content = str_replace('%'.str_replace('%', '', $field_options['Cell']).'-font-align%', $alignment, $web_page_content);
                                    }
                                }
                            }
                        }
                    }

                    $input_arg = $this->get_psd($result["files"], $product_filenames, $config, $group_field_names);
                    $command_str = escapeshellcmd("python3 /var/www/psd2/tool.py -j " . $input_arg . " -of result -p " . $jpeg_file_id);
                    if (isset($config["half_size"]) || (isset($options->resolution_size) && $options->resolution_size == '50')) {
                        $command_str = $command_str . " -half";
                    }
                    $command = escapeshellcmd($command_str);
                    $log = shell_exec($command . " 2>&1");
                    $temp_files = array_merge($temp_files, array("tmp_result.psd", "shadow.png", "circle.png", "result.zip"));
                    $output_files[] = $jpeg_file;

                    $logs[] = $log;

                    $zip->addFile($jpeg_file, $output_filename_ext);

                    foreach ($temp_files as $filename) {
                        if (file_exists($filename)) {
                            unlink($filename);
                        }
                    }
                }
            }
            $html_file = uniqid() . ".html";
            Storage::disk('public')->put($html_file, $web_page_content);
            $output_files[] = $html_file;

            $zip->addFile($html_file, $output_zip_filename . ".html");
        }
        $zip->close();

        Storage::disk('s3')->put('outputs/' . $zip_file, file_get_contents(public_path($zip_file)));
        $output_files[] = $zip_file;
        foreach ($output_files as $filename) {
            if (file_exists($filename)) {
                unlink($filename);
            }
        }

        return [
            "url" => Storage::disk('s3')->temporaryUrl('outputs/' . $zip_file, now()->addHours(1), [
                'ResponseContentDisposition' => 'attachment; filename="' . $output_zip_filename . '.zip"'
            ]),
            "projectname" => $layout->name,
            "logs" => $logs
        ];
    }

    public function download_layout_logos($request)
    {
        $layout = $this->gridLayoutService->getById($request->layout_id);
        $options = json_decode($layout->options);

        $zip_file_id = uniqid();
        $zip_file = $zip_file_id . ".zip";
        $zip = new ZipArchive();

        $result = ['status' => 'error', 'logos' => []];
        $temp_files = [];
        if ($zip->open(public_path($zip_file), ZipArchive::CREATE) === TRUE) {
            foreach ($layout->templates as $template) {
                if (str_contains($layout->settings, $template->instance_id)) {
                    $t = Template::find($template->template_id);
                    $settings = json_decode($template->settings, true);
                    if (isset($settings['logos']) && !empty($settings['logos'])) {
                        $logos = json_decode($settings['logos'], true);
                        for ($i = 0; $i < count($logos); $i++) {
                            $url = $logos[$i];
                            if (!empty($url)) {
                                $arr = explode("/", $url);
                                $logo_name = end($arr);
                                $filename = uniqid() . ".png";
                                file_put_contents($filename, file_get_contents($url));

                                $zip->addFile($filename, $logo_name);
                                $temp_files[] = $filename;
                            }
                        }
                    }
                }
            }
            $zip->close();
        }

        $output_filename = $layout->name;

        if (file_exists(public_path($zip_file))) {
            Storage::disk('s3')->put('outputs/' . $zip_file, file_get_contents(public_path($zip_file)));
            $temp_files[] = $zip_file;
            $result = [
                "status" => "success",
                "url" => Storage::disk('s3')->temporaryUrl('outputs/' . $zip_file, now()->addHours(1), [
                    'ResponseContentDisposition' => 'attachment; filename="' . $output_filename . ' Logos.zip"'
                ])
            ];
        }

        foreach ($temp_files as $filename) {
            if (file_exists($filename)) {
                unlink($filename);
            }
        }

        return $result;
    }

    public function download_layout_proof($request)
    {
        $grid_rows = [];
        $layout = $this->gridLayoutService->getById($request->layout_id);
        $grid_templates = $layout->templates;

        foreach ($grid_templates as $template) {
            if (str_contains($layout->settings, $template->instance_id)) {
                $grid_settings = json_decode($layout->settings, true);
                $y = 0;
                for ($i = 0; $i < count($grid_settings); $i++) {
                    if (str_contains($grid_settings[$i]['content'], $template->instance_id)) {
                        $y = $grid_settings[$i]['y'];
                    }
                }
                if (!isset($grid_rows[$y])) {
                    $grid_rows[$y] = [];
                }

                $settings = json_decode($template->settings, true);
                $settings['show_text'] = 'on';
                if (!isset($settings['file_ids'])) {
                    $settings['file_ids'] = "";
                }
                $result = $this->bannerService->map_files(explode(" ", $settings['file_ids']));

                $temp_files = [];
                $product_filenames = [];
                foreach ($result["files"] as $file) {
                    $filename = uniqid() . ".png";
                    $contents = Storage::disk('s3')->get($file["path"]);
                    Storage::disk('public')->put($filename, $contents);
                    $product_filenames[] = $filename;
                    $temp_files[] = $filename;
                }

                $config = $this->get_layout_config($settings);

                $jpeg_file_id = uniqid();
                $jpeg_file = $jpeg_file_id . ".jpg";

                $input_arg = $this->get_psd($result["files"], $product_filenames, $config);
                $half_size = isset($config["half_size"]) ? " -half" : "";
                $command = escapeshellcmd("python3 /var/www/psd2/tool.py -j " . $input_arg . " -of result -p " . $jpeg_file_id . $half_size);

                shell_exec($command . " 2>&1");

                $temp_files = array_merge($temp_files, array("tmp_result.psd", "shadow.png", "circle.png", "result.zip"));
                $grid_rows[$y][] = $jpeg_file;

                foreach ($temp_files as $filename) {
                    if (file_exists($filename)) {
                        unlink($filename);
                    }
                }
            }
        }

        $canvas = new \Imagick();
        foreach ($grid_rows as $grid_row) {
            $imagick_row = new \Imagick();
            $imagick_row->setBackgroundColor('white');
            foreach ($grid_row as $image) {
                $imagick = new \Imagick($image);
                $imagick->setImageBackgroundColor("white");
                $imagick_row->addImage($imagick);
            }
            $imagick_row->resetIterator();
            $combined_row = $imagick_row->appendImages(false);
            $canvas->addImage($combined_row);
        }

        $canvas->resetIterator();
        $proof_image_name = uniqid() . '.jpg';
        $proof_image = $canvas->appendImages(true);
        $proof_image->setImageFormat('jpg');
        $proof_image->writeImage($proof_image_name);

        Storage::disk('s3')->put('outputs/' . $proof_image_name, file_get_contents(public_path($proof_image_name)));

        return [
            "url" => Storage::disk('s3')->temporaryUrl('outputs/' . $proof_image_name, now()->addHours(1), [
                'ResponseContentDisposition' => 'attachment; filename="' . 'layout_proof.jpg"'
            ])
        ];
    }

    /**
     * @param $options
     * @param $config
     * @param $field
     * @return string
     */
    private function get_alignment_for_text_layer($options, $config, $field)
    {
        if (array_key_exists($field->element_id.'_alignment', $config)) {
            return $config[$field->element_id.'_alignment'];
        }

        return ! empty($options['Alignment']) ? $options['Alignment'] : "left";
    }
}
