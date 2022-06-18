<?php

namespace App\Domains\Auth\Http\Imports;

use App\Domains\Auth\Models\PositioningOption;
use App\Domains\Auth\Models\PositioningOptionField;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class PositioningUpdate implements ToArray, WithCalculatedFormulas
{
    public function __construct()
    {
    }

    public function array(array $array)
    {
        if (count($array) > 3) {
            $template_ids = explode(',', $array[1][0]);
            $template_id = 0;
            if (config('app.env') == 'dev') {
                $template_id = isset($template_ids[1]) ? intval($template_ids[1]) : 0;
            } else {
                $template_id = intval($template_ids[0]);
            }
            if ($template_id != 0) {
                for ($i = 1; $i < count($array[1]) && !empty($array[1][$i]); $i += 4) {
                    $positioning_option = new PositioningOption([
                        'template_id' => $template_id,
                        'name' => $array[1][$i]
                    ]);
                    $positioning_option->save();
    
                    $option_fields = [];
                    for ($j = 3; $j < count($array) && !empty($array[$j][0]); $j++) {
                        $option_fields[] = [
                            'option_id' => $positioning_option->id,
                            'field_name' => $array[$j][0],
                            'fields' => empty($array[$j][$i]) && $array[$j][$i] != '0' ? null : intval($array[$j][$i]),
                            'x' => empty($array[$j][$i + 1]) && $array[$j][$i + 1] != '0' ? null : intval($array[$j][$i + 1]),
                            'y' => empty($array[$j][$i + 2]) && $array[$j][$i + 2] != '0' ? null : intval($array[$j][$i + 2]),
                            'width' => empty($array[$j][$i + 3]) && $array[$j][$i + 3] != '0' ? null : intval($array[$j][$i + 3])
                        ];
                    }
                    PositioningOptionField::insert($option_fields);
                }
            }
        }
    }
}