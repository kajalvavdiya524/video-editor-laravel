<?php

namespace App\Domains\Auth\Models\Traits\Method;

use Illuminate\Support\Collection;
use App\Domains\Auth\Models\TemplateField;

/**
 * Trait TemplateMethod.
 */
trait TemplateMethod
{

    /*  
        this array contanis for each field type an array with all the required 
        data needed to generate that specific field, the key of the array is the "pretty" 
        name and the value is the actual input_name, in each case "ELEMENT_ID" should be 
        replaced with the actual element id.
        
        Note: Some fields need aditional info depending on the options defined, these extra
        fields are under the key: "Field Type_conditionals" along with the option that needs to be set
        its a multidimentional array for easier access to the options and values needed.

        Note 2: These extra fields are only hidden to the final user, so even if a text field has 
        the option "Font Selector" set to "No" this will have to be added later to create the banner
    
    */

    public $field_types_info = [
    
        "UPC/GTIN"	=> [
            "value" => "file_ids"
        ],
        
        "Product Space" => [
            "value" => "product_space"
        ],
        
        "Text"	=> [
            "value" => "ELEMENT_ID",
          ],

        "Text_conditionals"	=> [
            "ShowAlignment" =>  [true => ["alignment" => "ELEMENT_ID_alignment"] ],
            "Font Selector" =>  ["Yes" => [ "font"	=> "ELEMENT_ID_font", "font_size" => "ELEMENT_ID_size" ] ],
            "Color Selector" => ["Yes" => ["color"	=> "ELEMENT_ID_color"] ],
            "Moveable" => [ "Yes" => [ "offset_x" => "ELEMENT_ID_offset_x", "offset_y" => "ELEMENT_ID_offset_y", "width"	=> "ELEMENT_ID_width", "angle"	=> "ELEMENT_ID_angle" ] ],
        ],                
        
        "Text Options"	=> [
            "value" => "ELEMENT_ID",
        ],

        "Text Options_conditionals"	=> [
            "ShowAlignment" =>  [true => ["alignment" => "ELEMENT_ID_alignment"] ],
            "Font Selector" =>  ["Yes" => [ "font"	=> "ELEMENT_ID_font", "font_size" => "ELEMENT_ID_size" ] ],
            "Color Selector" => ["Yes" => ["color"	=> "ELEMENT_ID_color"]],
            "Moveable" => [ "Yes" => [ "offset_x" => "ELEMENT_ID_offset_x", "offset_y" => "ELEMENT_ID_offset_y", "width"=> "ELEMENT_ID_width",  "angle"	=> "ELEMENT_ID_angle", ] ],
        ],
        
        "Rectangle"	=> [
        ],

        "Rectangle_conditionals"	=> [
            "Color Selector" => ["Yes" => ["fill_color" => "ELEMENT_ID_fill_color","stroke_color"	=> "ELEMENT_ID_stroke_color" ]],
            "Moveable" => ["Yes" => [  "offset_x"	=> "ELEMENT_ID_offset_x", "offset_y"	=> "ELEMENT_ID_offset_y", "angle" => "ELEMENT_ID_angle", "scale_x"	=> "ELEMENT_ID_scaleX", "scale_y" => "ELEMENT_ID_scaleY"]]
        ],

        "Circle"	=> [
        ],
        
        "Circle_conditionals"	=> [
            "Color Selector" => ["Yes" => ["fill_color" => "ELEMENT_ID_fill_color","stroke_color"	=> "ELEMENT_ID_stroke_color" ]],
            "Moveable" => ["Yes" => [  "offset_x"	=> "ELEMENT_ID_offset_x", "offset_y"	=> "ELEMENT_ID_offset_y", "angle" => "ELEMENT_ID_angle", "scale_x"	=> "ELEMENT_ID_scaleX", "scale_y" => "ELEMENT_ID_scaleY"]]
        ],

        "Circle Type"	=> [
        ],
      
        "Circle Type_conditionals"	=> [
            "Color Selector" => ["Yes" => ["fill_color" => "ELEMENT_ID_fill_color","stroke_color"	=> "ELEMENT_ID_stroke_color" ]],
            "Moveable" => ["Yes" => [  "offset_x"	=> "ELEMENT_ID_offset_x", "offset_y"	=> "ELEMENT_ID_offset_y", "angle" => "ELEMENT_ID_angle", "scale_x"	=> "ELEMENT_ID_scaleX", "scale_y" => "ELEMENT_ID_scaleY"]]
        ],

        "Line"		=> [
        ],
        
        "Line_conditionals"=> [
            "Moveable" => ["Yes" => [ "offset_x" => "ELEMENT_ID_offset_x","offset_y" => "ELEMENT_ID_offset_y","angle" => "ELEMENT_ID_angle", "scale" => "ELEMENT_ID_scale"], ],
        ],

        "List All"	=> [ 
            "type"	    => "list_type",
            "fill_color"   => "list_fill_color",
            "stroke_color" => "list_stroke_color",
            "text_color"   => "list_text_color"
        ],

        /* uploaded images seems to be moveable even if moveable is set to no in the template 
         but i think this is a bug so I will put them as conditional */
         "Upload Image"	=> [
            "image_url" => "ELEMENT_ID_saved",
        ],
        
        "Upload Image_conditionals"	=> [
            "Moveable" => ["Yes" => [ "offset_x" => "ELEMENT_ID_offset_x", "offset_y" => "ELEMENT_ID_offset_y", "angle"  => "ELEMENT_ID_angle", "scale" => "ELEMENT_ID_scale", "width" => "ELEMENT_ID_width", "height" => "ELEMENT_ID_height"   ],],
        ],
    
        "Background Image Upload" => [
            "image_url" => "ELEMENT_ID_saved"
        ],
        
        "Image List"	=> [	
            "value" => "ELEMENT_ID",
        ],

        "Image List_conditionals"	=> [	
            "Moveable" => ["Yes" => ["offset_x" => "ELEMENT_ID_offset_x","offset_y" => "ELEMENT_ID_offset_y", "angle" => "ELEMENT_ID_angle", "scale"  => "ELEMENT_ID_scale"  ], ],
        ],

        "Product Image" => [	
        ],

        "Product Image_conditionals" => [	
            "Moveable" => ["Yes" => [ "offset_x" =>  "x_offset[]", "offset_y" =>  "y_offset[]","angle" =>  "angle[]",  "scale"  =>  "scale[]", "width" => "p_width[]", "height" => "p_height[]" ], ],
        ],

        "Background Theme Image" => [	
            "value" => "background[]",
        ],

        "Background Theme Image_conditionals" => [	
            "Moveable" => ["Yes" => [ "offset_x" => "bk_img_offset_x[]", "offset_y" => "bk_img_offset_y[]", "scale"    => "bk_img_scale[]"],],
        ],
        
        "Background Theme" => [
            "value" => "theme"
        ],
        
        "Background Theme Color" => [
            "value" =>  "background_color[]"
        ],
        
        "Background Color Picker" => [
            "value" => "background_color"
        ],
        
        "Image From Background" => [
            "value" => "img_from_bk[]",
         
        ],

        "Image From Background_conditionals" => [
            "Moveable" => ["Yes" => [ "offset_x" =>"img_from_bk_offset_x[]", "offset_x" =>"img_from_bk_offset_y[]", "scale"	=>"img_from_bk_scale[]" ], ],
        ],

        "DPI" => [
            "value" =>"dpi"
        ],

        "Filename Cell" => [
            "value" => "ELEMENT_ID"
        ],
        
        "Stroke" => [
            "value" => "show_stroke"
        ],

        "Overlay Area" => [
            "value" => "overlay_area"
        ],
        
        "Group Color" => [
            "value" => "ELEMENT_ID"
        ],
        
        "Group Font" => [
            "value" => "ELEMENT_ID"
        ],

        "Image List Group" => [
            "value" => "ELEMENT_ID"
        ],
       
    ];

                
    /* these are hardcoded fields that come from the template view and are sent in the payload
        commented out fields are going to be added automatically later, no need 
        to show them to the user to fill them as they can be filled automatically  */

    public $hardcoded_fields = [
     
        "Project Name" => [     
            "value" => "project_name"
        ], 
        
        "Country" => [     
            "value" => "country_id"
        ], 
        
        "Language" => [     
            "value" => "language_id"
        ], 
        
        /*             
        "customer" => ["value" =>  $this->customer->name], 
        "customer_id" =>  ["value" =>$this->customer->id], 
        "output_dimensions" =>  ["value" =>$this->id], 
        "template_name" => ["value" => $this->name], 
        "type" =>  ["value" =>"3"], 
        "template_id" =>  ["value" =>$this->id], 
        */

    ];
        
    public $field_types_info_hidden = [
        "Product Dimensions" => [
            "value" => "product_image_alignment"
        ],
        "Max File Size" => [
            "value" =>"max_file_size"
        ],
        "Static Image"	=> [
            "offset_x" => "ELEMENT_ID_offset_x",
            "offset_y" => "ELEMENT_ID_offset_y",
            "angle"    => "ELEMENT_ID_angle",
            "scale"    => "ELEMENT_ID_scale"
        ],
    ];
    
    public function getFieldsArray($get_ids = true, $get_hardcoded_fields = false){
        $array_fields = array();

            $fields = $this->fields;
            if ($fields){
                $array_fields_collection = collect($fields)->map(function ($field) use ($get_ids) {
                    if ($get_ids)
                        return ($field->element_id);
                    else
                        return ($field->name);
                    });
                   
                    $array_of_fields = $array_fields_collection->all();

                    if ($get_hardcoded_fields){
                        $hardcoded_fields_collection = collect($this->hardcoded_fields)->map(function ($field, $key) use ($get_ids) {
                            if ($get_ids)
                                return ($field['value']);
                            else
                                return ($key);
                        });

                        $hardcoded_fields = $hardcoded_fields_collection->all();

                        $hardcoded_fields = array_flip( $hardcoded_fields);
                        $j = 0;
                        foreach ($hardcoded_fields as $key => $value){
                            $hardcoded_fields[$key] = $j;
                            $j++;

                        }

                        $array_of_fields = array_merge(  array_flip($hardcoded_fields), $array_of_fields );
                    }

                if (count ($array_fields_collection))
                    $array_fields = array_merge( $array_fields, array_flip( $array_of_fields));

            }
                        
            return $array_fields;
    
    }

    public function getApiFields($get_all = false){
        
            //$get_all = true;

            $fields = $this->fields;

            $fields_info = $this->field_types_info;

            if ($get_all){
                $fields_info = array_merge ($fields_info, $this->field_types_info_hidden);
            }
            
            $array_fields = $this->hardcoded_fields;

            // load some default data onto hardcoded fields
            $array_fields['Project Name'] = '';
            $array_fields['Country'] = 'United States';
            $array_fields['Language'] = 'English';

            foreach ($fields as $field){

                if (isset($fields_info[$field->type])){
                    
                   /*if ($field->type == "Rectangle")
                        print_r ($field->options);*/

                    $a = $fields_info[$field->type];
                    $template_fields = array_map(function($val) { return ""; }, $a);
                    
                    $options = json_decode($field->options,1);
                    
                    // check for conditional fields and options
                    if (isset($fields_info[$field->type."_conditionals"])){
                        $extra_options = array();
                        foreach ( $options as $key => $value ){
                          
                            if (!$get_all){
                                if (isset($fields_info[$field->type."_conditionals"][$key][$value])){
                                    $extra_options = array_merge($extra_options,$fields_info[$field->type."_conditionals"][$key][$value]);
                                }
                            }else{
                                if (isset($fields_info[$field->type."_conditionals"][$key])){
                                    foreach ($fields_info[$field->type."_conditionals"][$key] as $conditional_options){
                                        $extra_options = array_merge($extra_options,$conditional_options);
                                    }
                                }
                            }
                        
                        }
                        $extra_fields = array_map(function($val) { return ""; }, $extra_options);
                        $template_fields = array_merge($template_fields, $extra_fields);

                    }

                    $array_fields[$field->name] = $template_fields;

                    // special treatment for some fields will go in this block

                    if ($field->type == "Max File Size")
                        $array_fields[$field->name]["value"] = $options["Option1"];

                    // END special treatment 

                    // default value
                    if ($options['Placeholder'] != '' &&  
                        isset ($array_fields[$field->name]) &&  
                        array_key_exists("value",$array_fields[$field->name]) )
                    {

                        $array_fields[$field->name]['value'] = $options['Placeholder'] ;
                    }

                    // default alignment
                    if ( isset($options['ShowAlignment'])  && 
                         isset ($array_fields[$field->name]) &&  
                         array_key_exists("alignment",$array_fields[$field->name])  )
                    {
                        $array_fields[$field->name]['alignment'] = $options['Alignment'] ;
                    }

                    // default width
                    if ( isset($options['Width'])  && 
                    isset ($array_fields[$field->name]) &&  
                    array_key_exists("width",$array_fields[$field->name])  )
                    {
                       $array_fields[$field->name]['width'] = $options['Width'] ;
                    }
                                      
                    // default height
                    if ( isset($options['Height'])  && 
                    isset ($array_fields[$field->name]) &&  
                    array_key_exists("height",$array_fields[$field->name])  )
                    {
                        $array_fields[$field->name]['height'] = $options['Height'] ;
                    }

                    // default X
                    if ( isset($options['X'])  && 
                    isset ($array_fields[$field->name]) &&  
                    array_key_exists("offset_x",$array_fields[$field->name])  )
                    {
                        $array_fields[$field->name]['offset_x'] = $options['X'] ;
                    }

                    // default Y
                    if ( isset($options['Y'])  && 
                    isset ($array_fields[$field->name]) &&  
                    array_key_exists("offset_y",$array_fields[$field->name])  )
                    {
                        $array_fields[$field->name]['offset_y'] = $options['Y'] ;
                    }
                
                    // if theres nothing for the user to enter, remove the key from the array
                    // this happens for example with fixed rectangles or circles

                    if (! count($array_fields[$field->name]) && !$get_all){
                        unset ($array_fields[$field->name]);
                    }
                
                }

              

            }

            return $array_fields;
    
    }
    
}
