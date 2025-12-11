<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AllTaskExport implements WithMultipleSheets
{
    protected $arrays;

    public function __construct(array $arrays)
    {
        $this->arrays = $arrays;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->arrays as $index => $array) {
            $sheets[$index] = new TaskSheetExport($array, $index);
        }

        return $sheets;
    }
}
