<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class StyledTaskExport extends DefaultValueBinder implements FromArray, WithStyles, WithCustomValueBinder
{
    protected $data;
    protected $styleData;

    public function __construct(array $data, array $styleData)
    {
        $this->data = $data;
        $this->styleData = $styleData;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function bindValue(Cell $cell, $value)
    {
        return parent::bindValue($cell, $value);
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];

        foreach ($this->styleData as $styleInfo) {
            switch ($styleInfo['type']) {
                case 'header_row':
                    $styles[$styleInfo['row']] = [
                        'font' => [
                            'bold' => true,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => 'c8e6c9'
                            ],
                        ],
                    ];
                    break;

                case 'section_header':
                    $styles[$styleInfo['row']] = [
                        'font' => [
                            'bold' => true,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => 'c8e6c9'
                            ],
                        ],
                    ];
                    break;

                case 'percentage_rows':
                    foreach ($styleInfo['data'] as $rowData) {
                        $row = $rowData['row'];
                        $values = $rowData['values'];
                        $startCol = $rowData['start_col'];

                        $styles['A' . $row] = [
                            'font' => [
                                'bold' => true,
                            ],
                        ];

                        foreach ($values as $index => $value) {
                            $col = chr(65 + $startCol - 1 + $index);
                            $cellRef = $col . $row;

                            if ($value > 80) {
                                $styles[$cellRef] = [
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'startColor' => [
                                            'rgb' => 'c8e6c9'
                                        ],
                                    ],
                                ];
                            } else {
                                $styles[$cellRef] = [
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'startColor' => [
                                            'rgb' => 'ffccbc',
                                        ],
                                    ],
                                ];
                            }
                        }
                    }
                    break;
            }
        }

        return $styles;
    }
}