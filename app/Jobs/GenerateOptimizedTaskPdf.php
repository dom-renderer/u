<?php

namespace App\Jobs;

use App\Models\ChecklistTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateOptimizedTaskPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $task;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');

        try {

            $task = ChecklistTask::with(['parent.parent.checklist', 'parent.actstore', 'clist'])->find($this->task);
            $path = storage_path('app/public/task-pdf');

            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            // Generate PDF
                $json = $task->data ?? [];
                if (is_string($json)) {
                    $data = json_decode($json, true);
                } else if (is_array($json)) {
                    $data = $json;
                } else {
                    $data = [];
                }
                
                $groupedData = [];
                $namesToBeIgnored = array_combine(\App\Helpers\Helper::$namesToBeIgnored, \App\Helpers\Helper::$namesToBeIgnored);

                if (isset($task->parent->parent->checklist_id) && in_array($task->parent->parent->checklist_id, [106, 107])) {
                    foreach ($data as $item) {
                        if (!isset($namesToBeIgnored[$item->name])) {
                            if (!isset($groupedData[$item->className])) {
                                $groupedData[$item->className][] = $item->label;
                            }

                            $groupedData[$item->className][] = property_exists($item, 'value_label') ? (!empty($item->value_label) ? $item->value_label : $item->value) : $item->value;
                        }
                    }
                } else {
                    foreach ($data as $item) {
                        if (!isset($groupedData[$item->className])) {
                            $groupedData[$item->className][] = $item->label;
                        }

                        $groupedData[$item->className][] = property_exists($item, 'value_label') ? (!empty($item->value_label) ? $item->value_label : $item->value) : $item->value;
                    }
                }

                $groupedData = array_values($groupedData);

                $varients = \App\Helpers\Helper::categorizePoints($task->data ?? []);

                $total = count(\App\Helpers\Helper::selectPointsQuestions($task->data));
                $toBeCounted = $total - count($varients['na']);

                $failed = abs(count(array_column($varients['negative'], 'value')));
                $achieved = $toBeCounted - abs($failed);
                
                if ($failed <= 0) {
                    $achieved = array_sum(array_column($varients['positive'], 'value'));
                }
                
                if ($toBeCounted > 0) {
                    $percentage = number_format(($achieved / $toBeCounted) * 100, 2);
                } else {
                    $percentage = 0;
                }

                $finalResultData = [];

                $finalResultData['total_count'] = $total;
                $finalResultData['passed'] = $achieved;
                $finalResultData['failed'] = count($varients['negative']);
                $finalResultData['na'] = count($varients['na']);
                $finalResultData['percentage'] = "{$percentage}%";
                $finalResultData['final_result'] = $percentage > 80 ? "Pass" : "Fail";

                $toBeCounted = $total;
                // if (!\App\Helpers\Helper::isPointChecklist($task->form)) {
                //     $toBeCounted = collect($task->data)->flatten(1)->pluck('className')->filter()->unique()->count();
                // }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('tasks.pdf', ['data' => $groupedData, 'task' => $task, 'toBeCounted' => $toBeCounted, 'finalResultData' => $finalResultData])
            ->setPaper('A4', 'landscape');

            $pdf->save("{$path}/task-{$task->id}.pdf");
            // Generate PDF

            if (is_file("{$path}/task-{$task->id}.pdf")) {
                $input = "{$path}/task-{$task->id}.pdf";
                $output = "{$path}/task-compressed-{$task->id}.pdf";

                if (stripos(PHP_OS, 'WIN') === 0) {
                    \App\Helpers\Ghostscript::compressPdfWindows($input, $output);
                } else {
                    \App\Helpers\Ghostscript::compressPdfLinux($input, $output);
                }
            }


        } catch (\Exception $e) {
            \Log::error('GOT ERROR ON GENERATE PDF FOR TASK' . $e->getMessage() . ' ON LINE' . $e->getLine());
        }

    }
}
