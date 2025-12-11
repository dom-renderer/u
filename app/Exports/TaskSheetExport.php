<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class TaskSheetExport implements FromArray, ShouldAutoSize, WithTitle
{
    protected $title;
    protected $data;

    public function __construct($data, $title)
    {
        $this->data = $data;
        $this->title = $title;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function title(): string
    {
        return is_string($this->title) ? $this->title : '';
    }
}
