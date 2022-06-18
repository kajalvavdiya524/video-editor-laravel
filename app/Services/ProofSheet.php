<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

trait ProofSheet
{
    public function generate($company_name, $project_name, $jpegs)
    {
        $rapidads_logo = Storage::url("/img/icons/Itsrapid_logo.png");
        $styles = '';
        $footer = "
            <div>
                <img src='$rapidads_logo' />
            </div>
        ";
        $html = "
            <html>
                <head>
                    $styles
                </head>
                <body>
                    <header>
                    </header>
                    <footer>
                        $footer
                    </footer>
                </body>
            </html>
        ";

        return $html;
    }
}
