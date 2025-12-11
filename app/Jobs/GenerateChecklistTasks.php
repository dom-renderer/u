<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateChecklistTasks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $checklistScheduling;
    private $timestamps;
    private $maker;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($checklistScheduling, $timestamps, $maker)
    {
        $this->checklistScheduling = $checklistScheduling;
        $this->timestamps = $timestamps;
        $this->maker = $maker;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $hash = md5(json_encode($this->checklistScheduling->checklist->schema ?? []));

        foreach ($this->timestamps as $timestamp) {

            $checklistSchedulingExtra = \App\Models\ChecklistSchedulingExtra::create([
                'checklist_scheduling_id' => $this->checklistScheduling->id,
                'branch_id' => $this->maker['branch_id'],
                'store_id' => $this->maker['store_id'],
                'user_id' => $this->maker['user_id'],
                'branch_type' => $this->maker['branch_type']
            ]);

            \App\Models\ChecklistTask::create([
                'code' => \App\Helpers\Helper::generateTaskNumber($timestamp, $this->maker['user_id']),
                'checklist_scheduling_id' => $checklistSchedulingExtra->id,
                'form' => new \StdClass(),
                'version_id' => \App\Helpers\Helper::getFormVersion($this->checklistScheduling->checklist_id, $hash),
                'date' => $timestamp,
                'type' => 0
            ]);
        }
    }
}
