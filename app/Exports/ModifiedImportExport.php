<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class ModifiedImportExport implements FromCollection
{
    protected $inputPath;
    protected $data;

    public function __construct(string $inputPath, array $data)
    {
        $this->inputPath = $inputPath;
        $this->data = $data;
    }

    public function collection()
    {
        $rows = [];

        if (($handle = fopen($this->inputPath, 'r')) !== false) {
            $header = fgetcsv($handle);
            $newHeader = array_merge($header, ['Status', 'Message']);
            $rows[] = $newHeader;

            $iteration = 0;
            while (($row = fgetcsv($handle)) !== false) {
                if (isset($this->data['response'][$iteration])) {
                    $newRow = array_merge($row, ['Error', $this->data['response'][$iteration]]);
                } elseif (isset($this->data['leave_blank'][$iteration])) {
                    $newRow = array_merge($row, ['', '']);
                } else {
                    $newRow = array_merge($row, ['Success', '']);
                }

                $rows[] = $newRow;
                $iteration++;
            }

            fclose($handle);
        }

        return new Collection($rows);
    }
}