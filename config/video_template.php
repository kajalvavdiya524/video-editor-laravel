<?php
$colors = [
    'black',
    'silver',
    'gray',
    'white',
    'maroon',
    'red',
    'purple',
    'fuchsia',
    'green',
    'lime',
    'olive',
    'yellow',
    'navy',
    'blue',
    'teal',
    'aqua'
];

$fonts = [
    'AlmaraiXb',
    'AvantGarde-Book',
    'AvantGarde-BookOblique',
    'AvantGarde-Demi',
    'AvantGarde-DemiOblique',
    'Bookman-Demi',
    'Bookman-DemiItalic',
    'Bookman-Light',
    'Bookman-LightItalic',
    'Courier',
    'Courier-Bold',
    'Courier-Oblique',
    'Courier-BoldOblique',
    "Colfax-Bold",
    "Colfax-Medium",
    "Colfax-Regular",
    'fixed',
    "Futura-Bold",
    "Futura-Condensed-Medium",
    "Futura-Extra-Bold",
    "Futura-Medium",
    'Helvetica',
    'Helvetica-Bold',
    'Helvetica-Oblique',
    'Helvetica-BoldOblique',
    'Helvetica-Narrow',
    'Helvetica-Narrow-Oblique',
    'Helvetica-Narrow-Bold',
    'Helvetica-Narrow-BoldOblique',
    "Helvetica-Neue-55-Roman",
    "Helvetica-Neue-65-Medium",
    "Knockout-Regular",
    'NewCenturySchlbk-Roman',
    'NewCenturySchlbk-Italic',
    'NewCenturySchlbk-Bold',
    'NewCenturySchlbk-BoldItalic',
    'Palatino-Roman',
    'Palatino-Italic',
    'Palatino-Bold',
    'Palatino-BoldItalic',
    "Playfair-Display-Black-Italic",
    "Playfair-Display-Black",
    'ShipporiMinchoB1Xb',
    'Times-Roman',
    'Times-Bold',
    'Times-Italic',
    'Times-BoldItalic',
    'Symbol',
    'Gotham-Bold',
    "Gotham-Book",
    "Gotham-Light",
    "Gotham-Medium",
    "Gotham-Thin",
    'OPTIRailroadGothic',
    'NeueHaasDisplay-Black',
    'NeueHaasDisplay-Light',
    'NeueHaasDisplay-Mediu',
    'NeueHaasDisplay-Roman',
    'NeueHaasDisplay-RomanItalic',
    'NeueHaasDisplay-Thin',
    'NeueHaasDisplay-ThinItalic',
    'NeueHaasDisplay-XXThinItalic',
    'NeueHaasDisplay-Bold',
    'NeueHaasDisplay-LightItalic',
    'NeueHaasDisplay-MediumItalic',
    'NeueHaasDisplay-XThin',
    'NeueHaasDisplay-XThinItalic',
    'NeueHaasDisplay-XXThin',
    'Netto-OT',
    'Netto-OT-Bold',

];
$audio_columns = [
    'Scene',
    'Subscene',
    'Name',
    'Type',
    'Duration',
    'Start',
    'End',
    'Filename',
    'Props'
];

$type_options = [
    [
        'value' => 'Text',
        'text' => 'Text'
    ], [
        'value' => 'Image',
        'text' => 'Image'
    ], [
        'value' => 'Music',
        'text' => 'Audio'
    ], [
        'value' => 'Video',
        'text' => 'Video'
    ], [
        'value' => 'VTT',
        'text' => 'Caption'
    ], [
        'value' => 'LH',
        'text' => 'Fade - Left Hand'
    ], [
        'value' => 'RH',
        'text' => 'Fade - Right Hand'
    ], [
        'value' => 'FF',
        'text' => 'Fade - Full Frame'
    ]
];

$animations = [
    'Text' => [
        'none',
        'fadein',
        'fadeout',
        'star',
        'star_reverse'
    ],
    'Video' => [
        'none',
        'fadein',
        'fadeout'
    ],
    'Image' => [
        'none',
        'fadein',
        'fadeout'
    ],
    'LH' => [
        'fadein',
        'fadeout',
        'bgr-c2c'
    ],
    'RH' => [
        'fadein',
        'fadeout',
        'bgr-c2c'
    ],
    'FF' => [
        'fadein',
        'fadeout',
        'bgr-c2c'
    ]
];

$align_h_options = [
    'Left',
    'Left-FF',
    'Left-LH',
    'Left-RH',
    'Center',
    'Center-FF',
    'Center-LH',
    'Center-RH',
    'Right',
    'Right-FF',
    'Right-LH',
    'Right-RH',
    'FF',
    'LH',
    'RH',
];
$align_v_options = ['Top', 'Center', 'Bottom' ];
$top_options = [
    [
        'value' => 0,
        'text' => '0%'
    ], [
        'value' => 1,
        'text' => '100%'
    ]
];

$left_options = [
    [
        'value' => 0,
        'text' => '0%'
    ], [
        'value' => 0.5,
        'text' => '50%'
    ]
];

return [
    'fonts' => $fonts,

    'text_animations' => ['none', 'fadein', 'fadeout', 'star', 'star_reverse'],

    'video_animations' => ['none', 'fadein', 'fadeout'],

    'colors' => $colors,

    'xlsx_columns' => [
        'Scene',
        'Subscene',
        'Name',
        'Type',
        'Left_direction',
        'Left',
        'Top',
        'Width',
        'Height',
        'AlignH',
        'AlignV',
        'Duration',
        'Start',
        'End',
        'Filename',
        'Text',
        'Font_Name',
        'Line_Spacing',
        'Size',
        'Color',
        'Kerning',
        'Background_Color',
        'Stroke_Width',
        'Stroke_Color',
        'Animation',
        'Animation_duration',
        'Props',
        'Original_File_Url',
        'Character_Count'
    ],

    'all_columns' => [
        [
            'value' => 'Actions',
            'text' => 'Actions',
            'label' => 'Actions',
            'field' => 'Actions',
            'input_type' => 'custom',
            'width' => '80px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => false,
            'sticky' => true,
            'default' => ''
        ], [
            'value' => 'Scene',
            'text' => 'Scene',
            'label' => 'Scene',
            'field' => 'Scene',
            'input_type' => 'number',
            'width' => '80px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => 1
        ], [
            'value' => 'Subscene',
            'text' => 'Subscene',
            'label' => 'Subscene',
            'field' => 'Subscene',
            'input_type' => 'number',
            'width' => '80px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT'],
            'group_editable' => true,
            'sticky' => true,
            'default' => 1
        ], [
            'value' => 'Type',
            'text' => 'Type',
            'label' => 'Type',
            'field' => 'Type',
            'input_type' => 'select',
            'width' => '110px',
            'options' => $type_options,
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => false,
            'sticky' => true,
            'default' => 'Text'
        ], [
            'value' => 'Name',
            'text' => 'Name',
            'label' => 'Name',
            'field' => 'Name',
            'input_type' => 'text',
            'width' => '140px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Media',
            'text' => 'Media/Text',
            'label' => 'Media/Text',
            'field' => 'Media',
            'input_type' => 'custom',
            'width' => '260px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT'],
            'group_editable' => false,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Character_Count',
            'text' => 'Character Count',
            'label' => 'Character Count',
            'field' => 'Character_Count',
            'input_type' => 'number',
            'width' => '112px',
            'for' => ['text'],
            'visible_for' => ['Text'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Left_direction',
            'text' => 'Left Direction',
            'label' => 'Left Direction',
            'field' => 'Left_direction',
            'input_type' => 'text',
            'for' => [],
            'visible_for' => [],
            'group_editable' => false,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Left',
            'text' => 'Left',
            'label' => 'Left',
            'field' => 'Left',
            'input_type' => 'percent',
            'width' => '100px',
            'options' => $left_options,
            'visible_for' => ['Text', 'Image', 'Video'],
            'for' => ['media', 'text'],
            'group_editable' => true,
            'sticky' => false,
            'default' => 0
        ], [
            'value' => 'Top',
            'text' => 'Top',
            'label' => 'Top',
            'field' => 'Top',
            'input_type' => 'percent',
            'width' => '100px',
            'options' => $top_options,
            'for' => [],
            'visible_for' => ['Text', 'Image', 'Video'],
            'group_editable' => true,
            'sticky' => false,
            'default' => 0
        ], [
            'value' => 'Width',
            'text' => 'Width',
            'label' => 'Width',
            'field' => 'Width',
            'input_type' => 'percent',
            'width' => '100px',
            'options' => [],
            'for' => [],
            'visible_for' => ['Text', 'Image', 'Video'],
            'group_editable' => true,
            'sticky' => false,
            'default' => 1.0
        ], [
            'value' => 'Height',
            'text' => 'Height',
            'label' => 'Height',
            'field' => 'Height',
            'input_type' => 'percent',
            'width' => '100px',
            'options' => [],
            'for' => [],
            'visible_for' => ['Text', 'Image', 'Video'],
            'group_editable' => true,
            'sticky' => false,
            'default' => 1.0
        ], [
            'value' => 'AlignH',
            'text' => 'Horiz Align',
            'label' => 'Horiz Align',
            'field' => 'AlignH',
            'input_type' => 'select',
            'width' => '100px',
            'options' => $align_h_options,
            'for' => [],
            'visible_for' => ['Text', 'Image', 'Video'],
            'group_editable' => true,
            'sticky' => false,
            'default' => 'Center'
        ], [
            'value' => 'AlignV',
            'text' => 'Vert Align',
            'label' => 'Vert Align',
            'field' => 'AlignV',
            'input_type' => 'select',
            'width' => '100px',
            'options' => $align_v_options,
            'for' => [],
            'visible_for' => ['Text', 'Image', 'Video'],
            'group_editable' => true,
            'sticky' => false,
            'default' => 'Center'
        ], [
            'value' => 'Duration',
            'text' => 'Duration',
            'label' => 'Duration',
            'field' => 'Duration',
            'input_type' => 'number',
            'width' => '70px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => '5'
        ], [
            'value' => 'Start',
            'text' => 'Start',
            'label' => 'Start',
            'field' => 'Start',
            'input_type' => 'formatted-time',
            'width' => '80px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => '0'
        ], [
            'value' => 'End',
            'text' => 'End',
            'label' => 'End',
            'field' => 'End',
            'input_type' => 'formatted-time',
            'width' => '80px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Timeline_Start',
            'text' => 'Timeline Start',
            'label' => 'Timeline Start',
            'field' => 'Timeline_Start',
            'input_type' => 'formatted-time',
            'width' => '100px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => '0'
        ], [
            'value' => 'Timeline_End',
            'text' => 'Timeline End',
            'label' => 'Timeline End',
            'field' => 'Timeline_End',
            'input_type' => 'formatted-time',
            'width' => '100px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Font_Name',
            'text' => 'Font',
            'label' => 'Font',
            'field' => 'Font_Name',
            'input_type' => 'select',
            'width' => '160px',
            'options' => $fonts,
            'for' => ['text'],
            'visible_for' => ['Text', 'VTT'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Line_Spacing',
            'text' => 'Line Spacing',
            'label' => 'Line Spacing',
            'field' => 'Line_Spacing',
            'input_type' => 'line_spacing_type',
            'width' => '100px',
            'for' => ['text'],
            'visible_for' => ['Text'],
            'group_editable' => true,
            'sticky' => false,
            'default' => 1
        ], [
            'value' => 'Size',
            'text' => 'Size',
            'label' => 'Size',
            'field' => 'Size',
            'input_type' => 'number',
            'width' => '100px',
            'for' => ['text'],
            'visible_for' => ['Text', 'VTT'],
            'group_editable' => true,
            'sticky' => false,
            'default' => 1
        ], [
            'value' => 'Color',
            'text' => 'Color',
            'label' => 'Color',
            'field' => 'Color',
            'input_type' => 'select',
            'width' => '120px',
            'options' => $colors,
            'for' => ['text'],
            'visible_for' => ['Text', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
        'value' => 'Kerning',
        'text' => 'Kerning',
        'label' => 'Kerning',
        'field' => 'Kerning',
        'input_type' => 'number',
        'width' => '120px',
        'for' => ['text'],
        'visible_for' => ['Text'],
        'group_editable' => false,
        'sticky' => false,
        'default' => '#FFFFFF'
        ], [
        'value' => 'Background_Color',
        'text' => 'Background Color',
        'label' => 'Background Color',
        'field' => 'Background_Color',
        'input_type' => 'color',
        'width' => '120px',
        'for' => [],
        'visible_for' => ['Text', 'Image', 'VTT', 'LH', 'RH', 'FF'],
        'group_editable' => true,
        'sticky' => false,
        'default' => '#FFFFFF'
        ], [
            'value' => 'Stroke_Width',
            'text' => 'Stroke Width',
            'label' => 'Stroke Width',
            'field' => 'Stroke_Width',
            'input_type' => 'number',
            'width' => '100px',
            'for' => ['text'],
            'visible_for' => ['Text', 'VTT'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Stroke_Color',
            'text' => 'Stroke Color',
            'label' => 'Stroke Color',
            'field' => 'Stroke_Color',
            'input_type' => 'select',
            'width' => '120px',
            'options' => $colors,
            'for' => ['text'],
            'visible_for' => ['Text', 'Image', 'VTT'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Animation',
            'text' => 'Animation',
            'label' => 'Animation',
            'field' => 'Animation',
            'input_type' => 'select',
            'width' => '130px',
            'options' => $animations,
            'for' => ['text'],
            'visible_for' => ['Text', 'Image', 'Video', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Animation_duration',
            'text' => 'Animation Duration',
            'label' => 'Animation Duration',
            'field' => 'Animation_duration',
            'input_type' => 'number',
            'for' => [],
            'visible_for' => ['Text', 'Image', 'Video', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Props',
            'text' => 'Props',
            'label' => 'Props',
            'field' => 'Props',
            'input_type' => 'text',
            'for' => [],
            'visible_for' => ['Music'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ]
    ],

    'all_timeframe_columns' => [
        
        [
            'value' => 'Actions',
            'text' => 'Actions',
            'label' => 'Actions',
            'field' => 'Actions',
            'input_type' => 'custom',
            'width' => '80px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => false,
            'sticky' => true,
            'default' => ''
        ], 
        [
            'value' => 'Start',
            'text' => 'Start',
            'label' => 'Start',
            'field' => 'Start',
            'input_type' => 'formatted-time',
            'width' => '80px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => '0'
        ], [
            'value' => 'End',
            'text' => 'End',
            'label' => 'End',
            'field' => 'End',
            'input_type' => 'formatted-time',
            'width' => '80px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Timeline_Start',
            'text' => 'Timeline Start',
            'label' => 'Timeline Start',
            'field' => 'Timeline_Start',
            'input_type' => 'formatted-time',
            'width' => '100px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => '0'
        ], [
            'value' => 'Timeline_End',
            'text' => 'Timeline End',
            'label' => 'Timeline End',
            'field' => 'Timeline_End',
            'input_type' => 'formatted-time',
            'width' => '100px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Scene',
            'text' => 'Scene',
            'label' => 'Scene',
            'field' => 'Scene',
            'input_type' => 'number',
            'width' => '80px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => 1
        ], [
            'value' => 'Subscene',
            'text' => 'Subscene',
            'label' => 'Subscene',
            'field' => 'Subscene',
            'input_type' => 'number',
            'width' => '80px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT'],
            'group_editable' => true,
            'sticky' => true,
            'default' => 1
        ], [
            'value' => 'Type',
            'text' => 'Type',
            'label' => 'Type',
            'field' => 'Type',
            'input_type' => 'select',
            'width' => '110px',
            'options' => $type_options,
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => false,
            'sticky' => true,
            'default' => 'Text'
        ], [
            'value' => 'Name',
            'text' => 'Name',
            'label' => 'Name',
            'field' => 'Name',
            'input_type' => 'text',
            'width' => '140px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Media',
            'text' => 'Media/Text',
            'label' => 'Media/Text',
            'field' => 'Media',
            'input_type' => 'custom',
            'width' => '260px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT'],
            'group_editable' => false,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Character_Count',
            'text' => 'Character Count',
            'label' => 'Character Count',
            'field' => 'Character_Count',
            'input_type' => 'number',
            'width' => '112px',
            'for' => ['text'],
            'visible_for' => ['Text'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Left_direction',
            'text' => 'Left Direction',
            'label' => 'Left Direction',
            'field' => 'Left_direction',
            'input_type' => 'text',
            'for' => [],
            'visible_for' => [],
            'group_editable' => false,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Left',
            'text' => 'Left',
            'label' => 'Left',
            'field' => 'Left',
            'input_type' => 'percent',
            'width' => '100px',
            'options' => $left_options,
            'visible_for' => ['Text', 'Image', 'Video'],
            'for' => ['media', 'text'],
            'group_editable' => true,
            'sticky' => false,
            'default' => 0
        ], [
            'value' => 'Top',
            'text' => 'Top',
            'label' => 'Top',
            'field' => 'Top',
            'input_type' => 'percent',
            'width' => '100px',
            'options' => $top_options,
            'for' => [],
            'visible_for' => ['Text', 'Image', 'Video'],
            'group_editable' => true,
            'sticky' => false,
            'default' => 0
        ], [
            'value' => 'Width',
            'text' => 'Width',
            'label' => 'Width',
            'field' => 'Width',
            'input_type' => 'percent',
            'width' => '100px',
            'options' => [],
            'for' => [],
            'visible_for' => ['Text', 'Image', 'Video'],
            'group_editable' => true,
            'sticky' => false,
            'default' => 1.0
        ], [
            'value' => 'Height',
            'text' => 'Height',
            'label' => 'Height',
            'field' => 'Height',
            'input_type' => 'percent',
            'width' => '100px',
            'options' => [],
            'for' => [],
            'visible_for' => ['Text', 'Image', 'Video'],
            'group_editable' => true,
            'sticky' => false,
            'default' => 1.0
        ], [
            'value' => 'AlignH',
            'text' => 'Horiz Align',
            'label' => 'Horiz Align',
            'field' => 'AlignH',
            'input_type' => 'select',
            'width' => '100px',
            'options' => $align_h_options,
            'for' => [],
            'visible_for' => ['Text', 'Image', 'Video'],
            'group_editable' => true,
            'sticky' => false,
            'default' => 'Center'
        ], [
            'value' => 'AlignV',
            'text' => 'Vert Align',
            'label' => 'Vert Align',
            'field' => 'AlignV',
            'input_type' => 'select',
            'width' => '100px',
            'options' => $align_v_options,
            'for' => [],
            'visible_for' => ['Text', 'Image', 'Video'],
            'group_editable' => true,
            'sticky' => false,
            'default' => 'Center'
        ], [
            'value' => 'Duration',
            'text' => 'Duration',
            'label' => 'Duration',
            'field' => 'Duration',
            'input_type' => 'number',
            'width' => '70px',
            'for' => ['media', 'text'],
            'visible_for' => ['Text', 'Image', 'Music', 'Video', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => '5'
        ], [
            'value' => 'Font_Name',
            'text' => 'Font',
            'label' => 'Font',
            'field' => 'Font_Name',
            'input_type' => 'select',
            'width' => '160px',
            'options' => $fonts,
            'for' => ['text'],
            'visible_for' => ['Text', 'VTT'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Size',
            'text' => 'Size',
            'label' => 'Size',
            'field' => 'Size',
            'input_type' => 'number',
            'width' => '100px',
            'for' => ['text'],
            'visible_for' => ['Text', 'VTT'],
            'group_editable' => true,
            'sticky' => false,
            'default' => 1
        ], [
            'value' => 'Color',
            'text' => 'Color',
            'label' => 'Color',
            'field' => 'Color',
            'input_type' => 'select',
            'width' => '120px',
            'options' => $colors,
            'for' => ['text'],
            'visible_for' => ['Text', 'VTT', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Kerning',
            'text' => 'Kerning',
            'label' => 'Kerning',
            'field' => 'Kerning',
            'input_type' => 'number',
            'width' => '120px',
            'for' => ['text'],
            'visible_for' => ['Text'],
            'group_editable' => false,
            'sticky' => false,
            'default' => '#FFFFFF'
            ], [
        'value' => 'Background_Color',
        'text' => 'Background Color',
        'label' => 'Background Color',
        'field' => 'Background_Color',
        'input_type' => 'color',
        'width' => '120px',
        'for' => [],
        'visible_for' => ['Text', 'Image', 'VTT', 'LH', 'RH', 'FF'],
        'group_editable' => true,
        'sticky' => false,
        'default' => '#FFFFFF'
        ], [
            'value' => 'Stroke_Width',
            'text' => 'Stroke Width',
            'label' => 'Stroke Width',
            'field' => 'Stroke_Width',
            'input_type' => 'number',
            'width' => '100px',
            'for' => ['text'],
            'visible_for' => ['Text', 'VTT'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Stroke_Color',
            'text' => 'Stroke Color',
            'label' => 'Stroke Color',
            'field' => 'Stroke_Color',
            'input_type' => 'select',
            'width' => '120px',
            'options' => $colors,
            'for' => ['text'],
            'visible_for' => ['Text', 'Image', 'VTT'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Animation',
            'text' => 'Animation',
            'label' => 'Animation',
            'field' => 'Animation',
            'input_type' => 'select',
            'width' => '130px',
            'options' => $animations,
            'for' => ['text'],
            'visible_for' => ['Text', 'Image', 'Video', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Animation_duration',
            'text' => 'Animation Duration',
            'label' => 'Animation Duration',
            'field' => 'Animation_duration',
            'input_type' => 'number',
            'for' => [],
            'visible_for' => ['Text', 'Image', 'Video', 'LH', 'RH', 'FF'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ], [
            'value' => 'Props',
            'text' => 'Props',
            'label' => 'Props',
            'field' => 'Props',
            'input_type' => 'text',
            'for' => [],
            'visible_for' => ['Music'],
            'group_editable' => true,
            'sticky' => false,
            'default' => ''
        ]
    ],
    'default_visible_columns' => [
        'Actions', 'Type', 'Media', 'Left', 'Top', 'Width', 'Height', 'Start', 'End'
    ],
    'default_visible_timeframe_columns' => [
        'Start', 'End', 'Actions', 'Type', 'Media', 'Left', 'Top', 'Width', 'Height'
    ],
    'column_types' => [
        [
            'value' => 'All',
            'text' => 'All'
        ], [
            'value' => 'Text',
            'text' => 'Text'
        ], [
            'value' => 'Image',
            'text' => 'Image'
        ], [
            'value' => 'Music',
            'text' => 'Audio'
        ], [
            'value' => 'Video',
            'text' => 'Video'
        ], [
            'value' => 'Caption',
            'text' => 'Caption'
        ]
    ],

    // column visibiity types users can select
    'column_visibility_types' => [
        [
            'value' => 'all',
            'text' => 'All'
        ], [
            'value' => 'text',
            'text' => 'Text'
        ], [
            'value' => 'media',
            'text' => 'Media'
        ]
    ],
    /*
    options: 'all', 'media', 'text', 'custom'
    */
    'selected_column_visibility_name' => 'custom',

    'preview_sizes' => [
        [
            'value' => 0,
            'width' => 426,
            'height' => 240
        ], [
            'value' => 1,
            'width' => 640,
            'height' => 360
        ], [
            'value' => 2,
            'width' => 854,
            'height' => 480
        ], [
            'value' => 3,
            'width' => 1920,
            'height' => 1080
        ],[
            'value' => 4,
            'width' => 500,
            'height' => 500
        ],[
            'value' => 5,
            'width' => 1500,
            'height' => 1500
        ],[
            'value' => 6,
            'width' => 540,
            'height' => 960
        ],[
            'value' => 7,
            'width' => 1080,
            'height' => 1920
        ]
    ]
];
