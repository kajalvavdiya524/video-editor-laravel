<?php

namespace App\Domains\Auth\Http\Imports;

use App\Domains\Auth\Models\Template;
use App\Domains\Auth\Models\TemplateField;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TemplateUpdate implements ToCollection
{
    public function __construct($template_id)
    {
        $this->template_id = $template_id;
    }

    public function collection(Collection $rows)
    {
        $columns = array();
        $template = Template::find($this->template_id);
        $template->fields()->delete();
        foreach ($rows as $row) 
        {
            if ($row[0] == 'Field Type') {
                $columns = $row;
            } else if ($row[0] == 'Template Name') {
                $template->name = $row[1];
                $template->save();
            } else if ($row[0] == 'Dimensions') {
                $template->width = $row[8];
                $template->height = $row[9];
                $template->save();
            } else if (!empty($row[0])) {
                $options = array();
                for($i = 0; $i < count($columns); $i ++) {
                    // if (!empty($row[$i])) {
                        $options[$columns[$i]] = $row[$i];
                    // }
                }
                TemplateField::create([
                    'template_id' => $template->id,
                    'name' => $row[1],
                    'element_id' => $this->seoUrl($row[1]),
                    'type' => $row[0],
                    'order' => 0,
                    'grid_col' => $row[2],
                    'options' => json_encode($options)
                ]);
            }
        }
    }

    public function seoUrl($string) {
        //Lower case everything
        $str = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $str = preg_replace("/[^a-z0-9_\s-]/", "", $str);
        //Clean up multiple dashes or whitespaces
        $str = preg_replace("/[\s-]+/", " ", $str);
        //Convert whitespaces and underscore to dash
        $str = preg_replace("/[\s_]/", "_", $str);
        return $str;
    }
}