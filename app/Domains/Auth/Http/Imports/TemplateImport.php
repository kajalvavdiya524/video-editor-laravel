<?php

namespace App\Domains\Auth\Http\Imports;

use App\Domains\Auth\Models\Template;
use App\Domains\Auth\Models\TemplateField;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TemplateImport implements ToCollection
{
    public function __construct($customer_id, $filename)
    {
        $this->customer_id = $customer_id;
        $this->filename = $filename;
    }

    public function collection(Collection $rows)
    {
        $columns = array();
        $company_id = auth()->user()->company_id;
        $template = new Template;
        $template->company_id = $company_id;
        $template->customer_id = $this->customer_id;
        $template->order = 0;
        $template->image_url = $this->filename ? 'img/templates/' . $this->filename : '';
        foreach ($rows as $row)
        {
            if ($row[0] == 'Field Type') {
                $columns = $row;
            } else if ($row[0] == 'Template Name') {
                $template->name = $row[1];
                $template->save();
            } else if ($row[0] == 'Dimensions') {
                $template->width = $row[9];
                $template->height = $row[10];
                $template->save();
            } else if (isset($row[0]) && !empty($row[0])) {
                $options = array();
                for($i = 0; $i < count($columns); $i ++) {
                    $options[$columns[$i]] = $row[$i];
                }
                TemplateField::create([
                    'template_id' => $template->id,
                    'name' => $row[1],
                    'element_id' => $this->seoUrl($row[0] . ' ' . $row[1]),
                    'type' => $row[0],
                    'order' => empty($row[3]) ? 0 : $row[3],
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
