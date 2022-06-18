<?php
require(base_path().'/resources/lib/fonts.php');
$return = [
    'AmazonFresh' => [
        'width' => 1460,
        'height' => 300,
        'output_dimensions' => ['1H+1S', 'NEW+1H', '2H', '2H+1S', '1H+2S', '3H'],
        'dimensions_map' => [
            "1H+1S"     => ["1H", "1S"],
            "NEW+1H"    => ["NEW", "1H"],
            "2H"        => ["1H", "2H"],
            "2H+1S"     => ["1H", "2H", "1S"],
            "1H+2S"     => ["1H", "1S", "2S"],
            "3H"        => ["1H", "2H", "3H"]
        ],
        'default_texts' => [
            "1H+1S"     => ["1H" => "Headline", "1S" => "Subheadline"],
            "NEW+1H"    => ["NEW" => "New / Discount", "1H" => "Headline"],
            "2H"        => ["1H" => "Headline1", "2H" => "Headline2"],
            "2H+1S"     => ["1H" => "Headline1", "2H" => "Headline2", "1S" => "Subheadline"],
            "1H+2S"     => ["1H" => "Headline", "1S" => "Subheadline1", "2S" => "Subheadline2"],
            "3H"        => ["1H" => "Headline1", "2H" => "Headline2", "3H" => "Headline3"]
        ],
        'CTA_pos' => [
            "1H+1S"     => ['left' => 0, 'top' => 273, 'right' => 800], 
            "NEW+1H"    => ['left' => 0, 'top' => 273, 'right' => 800], 
            "2H"        => ['left' => 0, 'top' => 273, 'right' => 800], 
            "2H+1S"     => ['left' => 0, 'top' => 273, 'right' => 800], 
            "1H+2S"     => ['left' => 0, 'top' => 273, 'right' => 800], 
            "3H"        => ['left' => 0, 'top' => 273, 'right' => 800]
        ],
        'circle_pos' => [
            'top' => ['radius' => 240, 'x' => 715, 'y' => -240],
            'bottom' => ['radius' => 240, 'x' => 715, 'y' => 60],
            'center' => ['radius' => 240, 'x' => 715, 'y' => -90]
        ],
    ],
    'Generic' => [
        'width' => [160, 300, 300, 320, 728],
        'height' => [600, 250, 600, 50, 90],
        'output_dimensions' => ['All Ad Units', '160 x 600', '300 x 250', '300 x 600', '320 x 50', '728 x 90'],
        'fonts' => ['Amazon-Ember', 'Amazon-Ember-Bold', 'Amazon-Ember-Bold-Italic', 'Amazon-Ember-Heavy', 'Amazon-Ember-Heavy-Italic', 'Amazon-Ember-Light', 
            'Amazon-Ember-Light-Italic', 'Amazon-Ember-Medium', 'Amazon-Ember-Medium-Italic', 'Arial', 'Arial-Black', 'Arial-Bold', 'Arial-Bold-Italic', 'Arial-Italic',
            'Courier', 'Courier-Bold', 'Courier-BoldOblique', 'Courier-Oblique', 'Helvetica', 'Helvetica-Bold', 'Helvetica-BoldOblique', 'Helvetica-Narrow',
            'Helvetica-Narrow-Bold', 'Helvetica-Narrow-BoldOblique', 'Helvetica-Narrow-Oblique', 'Helvetica-Oblique', 'Noto-Sans-Bold', 'Noto-Sans-Regular',
            'Palatino-Bold', 'Palatino-BoldItalic', 'Palatino-Italic', 'Palatino-Roman', 'Symbol', 'Times-Bold', 'Times-BoldItalic', 'Times-Italic', 'Times-Roman'],
        'headline_pos' => [
            ['left' => 5, 'top' => 60, 'right' => 155, 'bottom' => 200],
            ['left' => 15, 'top' => 35, 'right' => 145, 'bottom' => 155],
            ['left' => 30, 'top' => 30, 'right' => 270, 'bottom' => 230],
            ['left' => 7, 'top' => 10, 'right' => 205, 'bottom' => 35],
            ['left' => 30, 'top' => 15, 'right' => 445, 'bottom' => 65]
        ],
        'headline_space' => [10, 8, 15, 0, 0],
        'subheadline_pos' => [
            ['left' => 5, 'top' => 190, 'right' => 155, 'bottom' => 260],
            ['left' => 15, 'top' => 160, 'right' => 120, 'bottom' => 200],
            ['left' => 30, 'top' => 235, 'right' => 270, 'bottom' => 290],
            ['left' => 0, 'top' => 0, 'right' => 0, 'bottom' => 0],
            ['left' => 0, 'top' => 0, 'right' => 0, 'bottom' => 0]
        ],
        'subheadline_space' => [5, 4, 7, 0, 0],
        'product_dimensions' => [
            ['width' => 150, 'height' => 150, 'baseline' => 410, 'left' => 5],
            ['width' => 165, 'height' => 160, 'baseline' => 195, 'left' => 125],
            ['width' => 210, 'height' => 205, 'baseline' => 500, 'left' => 45],
            ['width' => 55, 'height' => 50, 'baseline' => 50, 'left' => 210],
            ['width' => 105, 'height' => 90, 'baseline' => 90, 'left' => 470]
        ],
        'CTA_pos' => [
            ['left' => 5, 'right' => 155, 'top' => 505],
            ['left' => 15, 'right' => 285, 'top' => 205],
            ['left' => 30, 'right' => 270, 'top' => 530],
            ['left' => 265, 'right' => 320, 'top' => 8],
            ['left' => 575, 'right' => 728, 'top' => 25]
        ],
        'CTA_space' => [5, 4, 7, 3, 5],
        'button_space' => [5, 4, 7, 3, 5],
        'logo_width' => [70, 70, 70, 30, 65]
    ],
    'Amazon' => [
        'width' => [3000, 3000, 3000, 1500, 3000, 3000],
        'height' => [3000, 3000, 3000, 1500, 3000, 3000],
        'output_dimensions' => ['Variety Pack - 1 Row', 'Variety Pack - 2 Rows', 'Nutrition Facts', 'Nutrition Facts Horizontal', 'Product Video', 'Virtual Bundle'],
        'product_dimensions' => [
            ['width' => 1900, 'height' => 1750, 'baseline' => 1300, 'left' => 0],
            ['width' => 1900, 'height' => 1750, 'baseline' => 1300, 'left' => 0],
            ['width' => 3000, 'height' => 3000, 'baseline' => 500, 'left' => 10],
            ['width' => 1500, 'height' => 1500, 'baseline' => 0, 'left' => 10],
            ['width' => 3000, 'height' => 3000, 'baseline' => 500, 'left' => 10],
            ['width' => 3000, 'height' => 3000, 'baseline' => 500, 'left' => 10]
        ]
    ],
    'Kroger' => [
        'width' => [624, 1280, 3200],
        'height' => [1132, 300, 400],
        'output_dimensions' => ['624 x 1132', '1280 x 300', '3200 x 400'],
        'product_dimensions' => [
            ['width' => 624, 'height' => 510, 'baseline' => 1070, 'left' => -15],
            ['width' => 410, 'height' => 300, 'baseline' => 270, 'left' => 590],
            ['width' => 650, 'height' => 400, 'baseline' => 370, 'left' => 1350]
        ]
    ],
    'Superama' => [
        'width' => [1680],
        'height' => [320], 
        'output_dimensions' => ['1680 x 320'],
        'product_dimensions' => [
            ['width' => 1680, 'height' => 320, 'baseline' => [120, 490, 940, 1120, 1430, 1560], 'left' => 0]
        ]
    ],
    'Walmart' => [
        'width' => [160, 300, 300, 320, 728],
        'height' => [600, 250, 600, 50, 90], 
        'output_dimensions' => ['160x600', '300x250', '300x600', '320x50', '728x90'], 
        'product_dimensions' => [
            ['width' => 160, 'height' => 250, 'baseline' => 555, 'left' => 10, 'top' => 72],
            ['width' => 150, 'height' => 250, 'baseline' => 250, 'left' => 21, 'top' => 0],
            ['width' => 200, 'height' => 250, 'baseline' => 555, 'left' => 40, 'top' => 72],
            ['width' => 120, 'height' => 90, 'baseline' => 0, 'left' => 15, 'top' => 0],
            ['width' => 300, 'height' => 150, 'baseline' => 0, 'left' => 27, 'top' => 0]
        ]
    ],
    'MRHI' => [
        'width' => [3000, 3000, 3000, 3000, 1500, 1500, 1500, 1500],
        'height' => [3000, 3000, 3000, 3000, 1500, 1500, 1500, 1500],
        'output_dimensions' => ['3000 x 3000 Vertical Strip, Units Bottom', '3000 x 3000 Vertical Strip, Units Top', '3000 x 3000 Horizontal Strip', '3000 x 3000 No Strip',
                                '1500 x 1500 Vertical Strip, Units Bottom', '1500 x 1500 Vertical Strip, Units Top', '1500 x 1500 Horizontal Strip', '1500 x 1500 No Strip'],
        'product_dimensions' => [
            ['width' => 3000, 'height' => 3000, 'baseline' => 0, 'left' => 0],
            ['width' => 3000, 'height' => 3000, 'baseline' => 0, 'left' => 0],
            ['width' => 3000, 'height' => 3000, 'baseline' => 0, 'left' => 0],
            ['width' => 3000, 'height' => 3000, 'baseline' => 0, 'left' => 0],
            ['width' => 1500, 'height' => 1500, 'baseline' => 0, 'left' => 0],
            ['width' => 1500, 'height' => 1500, 'baseline' => 0, 'left' => 0],
            ['width' => 1500, 'height' => 1500, 'baseline' => 0, 'left' => 0], 
            ['width' => 1500, 'height' => 1500, 'baseline' => 0, 'left' => 0]
        ],
    ], 
    'Instagram' => [
        'width' => [1080, 1080, 1080, 1080, 1080, 1080],
        'height' => [1080, 608, 1920, 1080, 608, 1350],
        'output_dimensions' => ['Square (1080 x 1080)', 'Landscape (1080 x 608)', 'Story (1080 x 1920)', 'Square Video (1080 x 1080)', 'Landscape Video (1080 x 608)', 'Portrait Video (1080 x 1350)'],
        'product_dimensions' => [
            ['width' => 1080, 'height' => 640, 'baseline' => 840, 'left' => 0],
            ['width' => 1080, 'height' => 300, 'baseline' => 475, 'left' => 0],
            ['width' => 1080, 'height' => 1200, 'baseline' => 1495, 'left' => 0],
            ['width' => 1080, 'height' => 1080, 'baseline' => 840, 'left' => 0],
            ['width' => 1080, 'height' => 608, 'baseline' => 475, 'left' => 0],
            ['width' => 1080, 'height' => 1350, 'baseline' => 1050, 'left' => 0]
        ],
        'headline_pos' => [
            ['left' => 0, 'top' => 60, 'right' => 1080, 'bottom' => 200],
            ['left' => 0, 'top' => 35, 'right' => 1080, 'bottom' => 155],
            ['left' => 0, 'top' => 30, 'right' => 1080, 'bottom' => 230]
        ],
        'headline_space' => [10, 8, 15],
        'subheadline_pos' => [
            ['left' => 0, 'top' => 260, 'right' => 1080, 'bottom' => 330],
            ['left' => 0, 'top' => 200, 'right' => 1080, 'bottom' => 240],
            ['left' => 0, 'top' => 290, 'right' => 1080, 'bottom' => 345]
        ],
        'subheadline_space' => [5, 4, 7],
        'CTA_pos' => [
            ['left' => 0, 'right' => 1080, 'top' => 505],
            ['left' => 0, 'right' => 1080, 'top' => 205],
            ['left' => 0, 'right' => 1080, 'top' => 530]
        ],
        'CTA_space' => [5, 4, 7],
        'button_space' => [5, 4, 7],
        'logo_width' => [70, 70, 70]
    ], 
    'Pilot' => [
        'width' => [1250, 3033, 3033],
        'height' => [1042, 375, 474],
        'output_dimensions' => ['Template 1', 'Template 2', 'Template 3'],
        'product_dimensions' => [
            ['width' => 1080, 'height' => 640, 'baseline' => 840, 'left' => 0],
            ['width' => 1080, 'height' => 300, 'baseline' => 475, 'left' => 0],
            ['width' => 1080, 'height' => 1200, 'baseline' => 1495, 'left' => 0]
        ]
    ], 
    'Sam' => [
        'width' => [1140], 
        'height' => [40], 
        'output_dimensions' => ['Template 1'],
        'product_dimensions' => [
            ['width' => 180, 'height' => 40, 'baseline' => 40, 'left' => 42]
        ]
    ],
    'product_layering' => ['Back To Front', 'Front To Back', 'Middle In Front', '2 Images - Larger Item Back-Right', '3 Images - Largest Item Middle', 'Custom'],
    'xlsx_columns' => ['Field Type', 'Name', 'Grid Column', 'Order', 'Placeholder', 'Font Selector', 'Color Selector', 'X', 'Y', 'Width', 'Height', 'Angle', 'Scale', 'Moveable', 'Font', 'Font Size', "Size To Fit", 'Font Color', 'Alignment', 'Kerning', 'Text Tracking', 'Leading', 'Group Name', 'Max Chars', 'Filename', 'Cell', 'Option1', 'Option2', 'Option3', 'Option4', 'Option5'],
    'xlsx_column_widths' => [250, 150, 110, 120, 150, 120, 120, 90, 90, 90, 90, 90, 90, 150, 200, 90, 90, 200, 150, 120, 120, 120, 120, 95, 350, 150, 250, 250, 250, 250, 250],
    'field_types' => [
        'Product Dimensions', 'UPC/GTIN', 'Product Image', 'Product Space', 'Show Text', 'Text', 'Static Text', 
        'Text Options', 'Text from Spreadsheet', 'Background Theme', 'Background Theme Image', 'Background Theme None', 
        'Background Theme Color', 'Background Image Upload', 'Circle', 'Circle Type', 'Rectangle', 'Static Image', 
        'Upload Image', 'Image From Background', 'Image List', 'List Numbered Circle', 'List Numbered Square', 'List Checkmark', 
        'List Star', 'List All', 'Line', 'DPI', 'Canvas', 'Download All', 'Smart Object', 'Filename Cell', 
        'Stroke', 'Save Image Position', 'Overlay Area', 'Max File Size', 'Group', 'Field Spacing', 'HTML', 'Safe Zone', 
        'Editable Template', 'Background Mockup', 'Group Color', 'Group Font', 'Image List Group', "Additional Fields", 
        "Text Oversampling", "Half Size"
    ],
    'fonts' => $fonts,

];

return $return;