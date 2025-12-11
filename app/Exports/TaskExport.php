<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TaskExport implements FromArray, WithStyles, ShouldAutoSize
{
    protected $data;
    protected $task;

    public function __construct(array $data, $task = null)
    {
        $this->data = $data;
        $this->task = $task;
    }

    public function array(): array
    {
        $date1 = \Carbon\Carbon::parse($this->task->started_at);
        $date2 = \Carbon\Carbon::parse($this->task->completion_date);
        $diff = $date1->diff($date2);

        $formattedData = [
            ["TASK - " . ($this->task->code ?? ""), "", ""],
            ["", "", ""],
            ["", "", ""],

            ["STORE NAME", $this->task->parent->actstore->name ?? '', ""],
            ["STORE CODE", $this->task->parent->actstore->code ?? '', ""],
            ["DOM", ($this->task->parent->user->name ?? '') . ' ' . $this->task->parent->user->middle_name ?? '' . ' ' . $this->task->parent->user->last_name ?? '', ""],
            ["START TIME", date('d-m-Y H:i', strtotime($this->task->started_at)), ""],
            ["END TIME", date('d-m-Y H:i', strtotime($this->task->completion_date)), ""],
            ["OPS TIME", "{$diff->d} days, {$diff->h} hours, {$diff->i} minutes", ""],
            ["", "", ""]            
        ];

        return array_merge($formattedData,  $this->data);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:C2'); 
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFE599');

        $highlightRows = [4, 5, 6, 7, 8, 9];
        foreach ($highlightRows as $row) {
            $range = "A{$row}:C{$row}";
            $sheet->getStyle($range)->getFont()->setBold(true);
            $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID);
        }

        $lastRow = count($this->data) + 2;
        for ($i = 10; $i <= $lastRow; $i++) {
            $sheet->getStyle("A{$i}")->getFont()->setBold(true);
        }
    }

}
