<?php

namespace App\Domains\Auth\Http\Exports;

use App\Domains\Auth\Models\Template;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Files\LocalTemporaryFile;

class TemplateXlsxExport implements FromCollection, WithEvents
{
    private $customer;
    
    private $calledByEvent;

    public function __construct($customer, $texts)
    {
        $this->customer = $customer;
        $this->texts = $texts;
        $this->calledByEvent = false;
    }

    public function collection()
    {
        if ($this->calledByEvent) { // flag
            collect([]);
        }

        return collect([]);
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function(BeforeWriting $event) {
                $templateFile = new LocalTemporaryFile(public_path($this->customer->xlsx_template_url));
                $event->writer->reopen($templateFile, Excel::XLSX);
                $cell_texts = [];
                foreach($this->customer->templates as $template) {
                    if (isset($this->texts->{'template_' . $template->id})) {
                        $texts = $this->texts->{'template_' . $template->id};
                        foreach($template->fields as $field) {
                            if (isset($texts->{$field->element_id})) {
                                $options = json_decode($field->options);
                                if (!empty($options->Cell)) {
                                    if (isset($cell_texts[$options->Cell])) {
                                        $cell_texts[$options->Cell] = $cell_texts[$options->Cell] . "\n" . $texts->{$field->element_id};
                                    } else {
                                        $cell_texts[$options->Cell] = $texts->{$field->element_id};
                                    }
                                }
                            }
                        }
                    }
                }
                foreach ($cell_texts as $key => $value) {
                    $cells_array = explode(",", $key);
                    foreach ($cells_array as $cells_value) {
                        $cells = explode("!", $cells_value);
                        $sheet = 0;
                        $cell = $cells[0];
                        if (count($cells) > 1) {
                            $sheet = intval($cells[0]);
                            $cell = $cells[1];
                        }
                        $event->writer->getSheetByIndex($sheet)->setCellValue($cell, $value);
                        $event->writer->getSheetByIndex($sheet)->getStyle($cell)->getAlignment()->setWrapText(true);
                    }
                }
            },
        ];
    }
}