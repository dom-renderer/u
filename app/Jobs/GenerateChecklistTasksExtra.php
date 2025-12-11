<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\ChecklistSchedulingExtra;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ChecklistTask;
use Illuminate\Bus\Queueable;
use \App\Helpers\Helper;

class GenerateChecklistTasksExtra implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $userLocationMatrix;
    private $checklistScheduling;
    private $timestamps;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($checklistScheduling, $timestamps, $userLocationMatrix)
    {
        $this->userLocationMatrix = $userLocationMatrix;
        $this->checklistScheduling = $checklistScheduling;
        $this->timestamps = $timestamps;
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

            foreach ($this->userLocationMatrix as $finalCreationArrayRow) {
                $mkrRole = $finalCreationArrayRow['role_id'];

                if (in_array($mkrRole, [Helper::$roles['divisional-operations-manager'], Helper::$roles['operations-manager'], Helper::$roles['head-of-department']])) {
                    $mkrRole = 3;
                } else if (in_array($mkrRole, [Helper::$roles['store-phone'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier']])) {
                    $mkrRole = 1;
                } else {
                    $mkrRole = 2;
                }
        
                $makerInfo = Helper::getFirstBranch($finalCreationArrayRow['user_id'], $mkrRole);

                foreach ($finalCreationArrayRow['locations'] as $finaLocation) {
                    $checklistSchedulingExtra = ChecklistSchedulingExtra::create([
                        'checklist_scheduling_id' => $this->checklistScheduling->id,
                        'branch_id' => $makerInfo['branch_id'],
                        'store_id' => $finaLocation,
                        'user_id' => $makerInfo['user_id'],
                        'branch_type' => $makerInfo['branch_type']
                    ]);

                    ChecklistTask::create([
                        'code' => Helper::generateTaskNumber($timestamp, $makerInfo['user_id']),
                        'checklist_scheduling_id' => $checklistSchedulingExtra->id,
                        'form' => new \StdClass(),
                        'version_id' => Helper::getFormVersion($this->checklistScheduling->checklist_id, $hash),
                        'date' => $timestamp,
                        'type' => 0
                    ]);
                }
            }
        }
    }
}
