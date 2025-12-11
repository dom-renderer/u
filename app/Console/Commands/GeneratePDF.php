<?php

namespace App\Console\Commands;

use App\Models\DeviceToken;
use App\Models\SystemNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GeneratePDF extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        ini_set('memory_limit', '-1');

        foreach (\App\Models\PdfGenerationLog::with('task')->where('status', 0)->get() as $row) {
            \DB::beginTransaction();
            try {

                if (!is_file(storage_path("app/public/task-pdf/task-{$row->task_id}.pdf"))) {
                    // GENERATE HERE
                        $task = \App\Models\ChecklistTask::with(['parent.parent.checklist', 'parent.actstore', 'clist'])->find($row->task_id);
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
                            
                            $namesToBeIgnored = array_combine(\App\Helpers\Helper::$namesToBeIgnored, \App\Helpers\Helper::$namesToBeIgnored);

                            $groupedData = [];

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
                    // GENERATE HERE
                }

                $row->update(['keep_till' => date('Y-m-d H:i:s', strtotime('+2 days')), 'status' => 1]);

                $title = 'Task ' . (isset($row->task->code) ? $row->task->code : '') . ' report has been generated';
                $description = 'Your requested task report has been generated. this link will be valid for 2 days.';

                SystemNotification::create([
                    'title' => $title,
                    'description' => $description,
                    'link' => asset("storage/task-pdf/task-compressed-{$row->task_id}.pdf"),
                    'user_id' => $row->user_id
                ]);

                \DB::commit();

                if (file_exists(storage_path("app/public/task-pdf/task-{$row->task_id}.pdf")) && is_file(storage_path("app/public/task-pdf/task-{$row->task_id}.pdf"))) {
                    $deviceTokens = DeviceToken::select('token')->where('user_id', $row->user_id)->pluck('token')->toArray();
                    if (!empty($deviceTokens)) {
                        \App\Helpers\Helper::sendPushNotification($deviceTokens, [
                            'title' => $title,
                            'description' => $description
                        ]);
                    }
                }

            } catch (\Exception $e) {
                \DB::rollBack();
                Log::error('Requested report ' . $row->task_id . ' not generated due to' . ' : ' . $e->getLine() . ' line ' . $e->getLine());
            }
        }
    }
}
