<?php

namespace App\Jobs;

use App\Models\ChecklistTask;
use App\Models\RedoAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DisapproveNoAnsweredFields implements ShouldQueue
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
        $id = $this->task;
        $task = ChecklistTask::find($id);

        try {
            if ($task) {

                $json = $task->data;
                $allTheClassNames = [];
                $allTheMailAction = [];

                if (!empty($json) && $json != '{}') {
                    foreach ($json as &$item) {
                        if (isset($item->name) && (\Str::contains($item->name, 'point-') || \Str::contains($item->name, 'points-'))) {
                            if ($item->value == 0) {
                                $item->approved = 'no';

                                $allTheClassNames[] = $item->className;

                                $redoActionExists = RedoAction::where('task_id', $id)
                                ->where('field_id', $item->className);

                                if ($redoActionExists->exists()) {
                                    $redoActionExists->update([
                                        'title' => $item->label,
                                        'remarks' => '',
                                        'page' => $item->page ?? 1,
                                        'status' => 0,
                                        'start_at' => date('Y-m-d H:i:s'),
                                        'completed_by' => date('Y-m-d H:i:s', strtotime('+10 day')),
                                        'do_not_allow_late_submission' => 0
                                    ]);

                                    $allTheMailAction[]  = [
                                        'action' => 'update',
                                        'status' => 'Re-Do',
                                        'title' => $item->label,
                                        'remarks' => '',
                                        'start_at' => date('Y-m-d H:i:s'),
                                        'completed_by' => date('Y-m-d H:i:s', strtotime('+10 day')),
                                        'do_not_allow_late_submission' => 0,
                                        'task_code' => $task->code,
                                        'task_date' => $task->date,
                                        'store_name' => $task->parent->actstore->name ?? '',
                                        'store_code' => $task->parent->actstore->code ?? '',
                                        'dom' => ($task->parent->user->name ?? '') . ' ' . ($task->parent->user->middle_name ?? '') . ' ' . ($task->parent->user->last_name ?? '')
                                    ];
                                } else {
                                    RedoAction::create([
                                        'task_id' => $id,
                                        'field_id' => $item->className,
                                        'page' => $item->page ?? 1,                        
                                        'title' => $item->label,
                                        'remarks' => '',
                                        'start_at' => date('Y-m-d H:i:s'),
                                        'completed_by' => date('Y-m-d H:i:s', strtotime('+10 day')),
                                        'do_not_allow_late_submission' => 0
                                    ]);

                                    $allTheMailAction[]  = [
                                        'action' => 'create',
                                        'status' => 'Re-Do',
                                        'title' => $item->label,
                                        'remarks' => '',
                                        'start_at' => date('Y-m-d H:i:s'),
                                        'completed_by' => date('Y-m-d H:i:s', strtotime('+10 day')),
                                        'do_not_allow_late_submission' => 0,
                                        'task_code' => $task->code,
                                        'task_date' => $task->date,
                                        'store_name' => $task->parent->actstore->name ?? '',
                                        'store_code' => $task->parent->actstore->code ?? '',
                                        'dom' => ($task->parent->user->name ?? '') . ' ' . ($task->parent->user->middle_name ?? '') . ' ' . ($task->parent->user->last_name ?? '')
                                    ];                                   
                                }

                            } else if ($item->value == 1) {
                                $redoActionExists = RedoAction::where('task_id', $id)
                                ->where('field_id', $item->className);

                                if ($redoActionExists->exists()) {
                                    $redoActionExists->update([
                                        'status' => 1
                                    ]);
                                }
                            }
                        }
                    }
                }

                if (!empty($allTheClassNames)) {
                    foreach ($allTheClassNames as $class) {
                        foreach ($json as &$item) {
                            if (isset($item->className) && $class == $item->className) {
                                $item->approved = 'no';
                            }
                        }
                    }
                }

                $task->data = $json;
                $task->save();

                if (!empty($allTheMailAction)) {
                    \App\Jobs\SendFieldRedoActionNotification::dispatch($allTheMailAction, $task->id);
                }                

            }
        } catch (\Exception $e) {
            \Log::error('ERROR OCCURED WHILE ADD FAILED TO REDOS ' . $e->getMessage() . ' on line ' . $e->getLine());
        }
    }
}
