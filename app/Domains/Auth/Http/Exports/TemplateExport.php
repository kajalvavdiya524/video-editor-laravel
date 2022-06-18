<?php

namespace App\Domains\Auth\Http\Exports;

use App\Domains\Auth\Models\Template;
use App\Domains\Auth\Models\TemplateField;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class TemplateExport implements FromCollection
{
    public function __construct($template_id)
    {
        $this->template_id = $template_id;
    }

    public function collection()
    {
        $columns = config('templates.xlsx_columns');
        $data = [$columns];
        
        $template = Template::find($this->template_id);
        $template_name_row = [];
        $template_dimensions_row = [];
        foreach ($columns as $column) {
            if ($column == 'Field Type') {
                $template_name_row[] = 'Template Name';
                $template_dimensions_row[] = 'Dimensions';
            } else if ($column == 'Name') {
                $template_name_row[] = $template->name;
                $template_dimensions_row[] = '';
            } else if ($column == 'Width') {
                $template_name_row[] = '';
                $template_dimensions_row[] = $template->width;
            } else if ($column == 'Height') {
                $template_name_row[] = '';
                $template_dimensions_row[] = $template->height;
            } else {
                $template_name_row[] = '';
                $template_dimensions_row[] = '';
            }
        }
        $data[] = $template_name_row;
        $data[] = $template_dimensions_row;

        foreach ($template->fields as $field) {
            $row = [];
            $options = json_decode($field->options);
            foreach ($columns as $column) {
                $row[] = isset($options->{$column}) ? $options->{$column} : "";
            }
            $data[] = $row;
        }

        return collect($data);
    }
}