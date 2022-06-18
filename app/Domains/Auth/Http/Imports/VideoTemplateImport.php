<?php

namespace App\Domains\Auth\Http\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VideoTemplateImport implements ToCollection, WithHeadingRow, WithMapping
{
    /**
     * @param array $row
     *
     * @return User|null
     */
    public function collection(Collection $rows)
    {
        return $rows;
    }

    public function map($scene): array
    {
        return [
            'Scene' => isset($scene['Scene']) ? $scene['Scene'] : '',
            'Subscene' => isset($scene['Subscene']) ? $scene['Subscene'] : '',
            'Name' => isset($scene['Name']) ? $scene['Name'] : '',
            'Type' => isset($scene['Type']) ? $scene['Type'] : '',
            'Left_direction' => isset($scene['Left_direction']) ? $scene['Left_direction'] : '',
            'Left' => isset($scene['Left']) ? $scene['Left'] : '',
            'Top' => isset($scene['Top']) ? $scene['Top'] : '',
            'Width' => isset($scene['Width']) ? $scene['Width'] : '',
            'Height' => isset($scene['Height']) ? $scene['Height'] : '',
            'AlignH' => isset($scene['AlignH']) ? $scene['AlignH'] : '',
            'AlignV' => isset($scene['AlignV']) ? $scene['AlignV'] : '',
            'Duration' => isset($scene['Duration']) ? $scene['Duration'] : '',
            'Start' => isset($scene['Start']) ? $scene['Start'] : '',
            'End' => isset($scene['End']) ? $scene['End'] : '',
            'Filename' => isset($scene['Filename']) ? $scene['Filename'] : '',
            'Text' => isset($scene['Text']) ? $scene['Text'] : '',
            'Font_Name' => isset($scene['Font_Name']) ? $scene['Font_Name'] : '',
            'Line_Spacing' => isset($scene['Line_Spacing']) ? $scene['Line_Spacing'] : '',
            'Size' => isset($scene['Size']) ? $scene['Size'] : '',
            'Color' => isset($scene['Color']) ? $scene['Color'] : '',
            'Kerning' => isset($scene['Kerning']) ? $scene['Kerning'] : '',
            'Background_Color' => isset($scene['Background_Color']) ? $scene['Background_Color'] : '',
            'Stroke_Color' => isset($scene['Stroke_Color']) ? $scene['Stroke_Color'] : '',
            'Stroke_Width' => isset($scene['Stroke_Width']) ? $scene['Stroke_Width'] : '',
            'Animation' => isset($scene['Animation']) ? $scene['Animation'] : '',
            'Animation_duration' => isset($scene['Animation_duration']) ? $scene['Animation_duration'] : '',
            'Props' => isset($scene['Props']) ? $scene['Props'] : '',
            'Original_File_Url' => isset($scene['Original_File_Url']) && ($scene['Type'] === 'Video' || $scene['Type'] === 'Image')
                ? $scene['Original_File_Url'] : ($scene['Type'] === 'Video' || $scene['Type'] === 'Image' ? $scene['Filename'] : ''),
            'Character_Count' => isset($scene['Character_Count']) ? $scene['Character_Count'] : '',
        ];
    }

    public function headingRow(): int
    {
        return 1;
    }
}
