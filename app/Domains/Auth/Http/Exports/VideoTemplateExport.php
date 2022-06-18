<?php

namespace App\Domains\Auth\Http\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VideoTemplateExport implements FromCollection, WithMapping, WithHeadings
{
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return config('video_template.xlsx_columns');
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function map($scene): array
    {
        return [
            isset($scene['Scene']) ? $scene['Scene'] : '',
            isset($scene['Subscene']) ? $scene['Subscene'] : '',
            isset($scene['Name']) ? $scene['Name'] : '',
            isset($scene['Type']) ? $scene['Type'] : '',
            isset($scene['Left_direction']) ? $scene['Left_direction'] : '',
            isset($scene['Left']) ? $scene['Left'] : '',
            isset($scene['Top']) ? $scene['Top'] : '',
            isset($scene['Width']) ? $scene['Width'] : '',
            isset($scene['Height']) ? $scene['Height'] : '',
            isset($scene['AlignH']) ? $scene['AlignH'] : '',
            isset($scene['AlignV']) ? $scene['AlignV'] : '',
            isset($scene['Duration']) ? $scene['Duration'] : '',
            isset($scene['Start']) ? $scene['Start'] : '',
            isset($scene['End']) ? $scene['End'] : '',
            isset($scene['Filename']) ? $scene['Filename'] : '',
            isset($scene['Text']) ? $scene['Text'] : '',
            isset($scene['Font_Name']) ? $scene['Font_Name'] : '',
            isset($scene['Line_Spacing']) ? $scene['Line_Spacing'] : '',
            isset($scene['Size']) ? $scene['Size'] : '',
            isset($scene['Color']) ? $scene['Color'] : '',
            isset($scene['Kerning']) ? $scene['Kerning'] : '',
            isset($scene['Background_Color']) ? $scene['Background_Color'] : '',
            isset($scene['Stroke_Width']) ? $scene['Stroke_Width'] : '',
            isset($scene['Stroke_Color']) ? $scene['Stroke_Color'] : '',
            isset($scene['Animation']) ? $scene['Animation'] : '',
            isset($scene['Animation_duration']) ? $scene['Animation_duration'] : '',
            isset($scene['Props']) ? $scene['Props'] : '',
            isset($scene['Original_File_Url']) ? $scene['Original_File_Url'] : '',
            isset($scene['Character_Count']) ? $scene['Character_Count'] : '',
        ];
    }
}
