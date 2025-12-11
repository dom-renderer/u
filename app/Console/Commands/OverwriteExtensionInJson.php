<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ChecklistTask;
use Carbon\Carbon;

class OverwriteExtensionInJson extends Command
{
    protected $signature = 'overwrite:path';
    protected $description = 'Replace .png with .webp in JSON fields without losing data';

    public function handle()
    {
        $months = range(1, 12);

        foreach ($months as $month) {
            $startOfMonth = Carbon::create(2025, $month, 1)->startOfMonth();
            $endOfMonth = Carbon::create(2025, $month, 1)->endOfMonth();

            ChecklistTask::where('status', '>', 0)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->cursor()
                ->each(function ($task) {
                    $task->data = $this->replacePngWithWebp($task->data);
                    $task->save();

                    $this->info("Task ID {$task->id} processed successfully.");
                });
        }

        $this->info("All tasks processed!");
    }

    protected function replacePngWithWebp($data)
    {
        if (is_array($data)) {
            foreach ($data as &$item) {
                $item = $this->replacePngWithWebp($item);
            }
        } elseif (is_object($data)) {
            foreach ($data as $key => &$value) {
                if ($key === 'value' && isset($data->isFile) && $data->isFile) {
                    if (is_array($value)) {
                        $value = array_map(fn($v) => str_replace('.png', '.webp', $v), $value);
                    } elseif (is_string($value)) {
                        $value = str_replace('.png', '.webp', $value);
                    }
                } else {
                    $value = $this->replacePngWithWebp($value);
                }
            }
        }

        return $data;
    }
}