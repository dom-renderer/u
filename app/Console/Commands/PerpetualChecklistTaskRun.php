<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PerpetualChecklistTaskRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'perpetualtask:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run perpetual checklist scheduling tasks';

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
        $futureDate = \Carbon\Carbon::now();

        $checklistSchedules = \App\Models\ChecklistScheduling::with(['checklist'])->where('perpetual', 1)->where('import_type', 0)->get();

        foreach ($checklistSchedules as $checklistSchedule) {
            $allExtra = \App\Models\ChecklistSchedulingExtra::where('checklist_scheduling_id', $checklistSchedule->id)->get();

            foreach ($allExtra as $extraRow) {
                $checklistTasks = \App\Models\ChecklistTask::where('checklist_scheduling_id', $extraRow->id)->scheduling()->orderBy('date', 'DESC')->first();

                if (empty($checklistTasks) || \Carbon\Carbon::parse($checklistTasks->date)->lt($futureDate)) {
    
                    $startDate = date('Y-m-d H:i:s', strtotime($checklistSchedule->start));
                    $typeSlug = 'hourly';
                    $weekdayTime = null;
                    $allDays = null;
                    $lastDate = date('Y-m-d H:i:s', strtotime("{$startDate} +10 days"));
        
                    if ($checklistTasks) {
                        $startDate = $checklistTasks->date;
    
                        if ($checklistSchedule->frequency_type == 0) {
                            $startDate = date('Y-m-d H:i:s', strtotime("$startDate +1 hour"));
                        } else if ($checklistSchedule->frequency_type == 1) {
                            $startDate = date('Y-m-d H:i:s', strtotime("$startDate +$checklistSchedule->interval hours"));
                        } else if ($checklistSchedule->frequency_type == 2) {
                            $startDate = date('Y-m-d H:i:s', strtotime("$startDate +1 days"));
                        } else if ($checklistSchedule->frequency_type == 3) {
                            $startDate = date('Y-m-d H:i:s', strtotime("$startDate +$checklistSchedule->interval days"));
                        } else if ($checklistSchedule->frequency_type == 4) {
                            $startDate = date('Y-m-d H:i:s', strtotime("$startDate +7 days"));
                        } else if ($checklistSchedule->frequency_type == 5) {
                            $startDate = date('Y-m-d H:i:s', strtotime("$startDate +14 days"));
                        } else if ($checklistSchedule->frequency_type == 6) {
                            $startDate = date('Y-m-d H:i:s', strtotime("$startDate +30 days"));
                        } else if ($checklistSchedule->frequency_type == 7) {
                            $startDate = date('Y-m-d H:i:s', strtotime("$startDate +60 days"));
                        } else if ($checklistSchedule->frequency_type == 8) {
                            $startDate = date('Y-m-d H:i:s', strtotime("$startDate +90 days"));
                        } else if ($checklistSchedule->frequency_type == 9) {
                            $startDate = date('Y-m-d H:i:s', strtotime("$startDate +180 days"));
                        } else if ($checklistSchedule->frequency_type == 10) {
                            $startDate = date('Y-m-d H:i:s', strtotime("$startDate +365 days"));
                        } else if ($checklistSchedule->frequency_type == 11) {
                            $allDays = explode(',', $checklistSchedule->weekdays);
                            $dt = new \DateTime($startDate);
                            $dt->modify('next ' . $allDays[0]);
                            $startDate = $dt->format('Y-m-d H:i:s');
                        }
                    }
    
                    if ($checklistSchedule->frequency_type == 0) {
                        $typeSlug = 'hourly';
                        $lastDate = date('Y-m-d H:i:s', strtotime("$startDate +1 hour"));
                    } else if ($checklistSchedule->frequency_type == 1) {
                        $typeSlug = $checklistSchedule->interval . ' hour';
                        $lastDate = date('Y-m-d H:i:s', strtotime("$startDate +$checklistSchedule->interval hours"));
                    } else if ($checklistSchedule->frequency_type == 2) {
                        $typeSlug = 'daily';
                        $lastDate = date('Y-m-d H:i:s', strtotime("$startDate +1 day"));
                    } else if ($checklistSchedule->frequency_type == 3) {
                        $typeSlug = $checklistSchedule->interval . ' day';
                        $lastDate = date('Y-m-d H:i:s', strtotime("$startDate +$checklistSchedule->interval days"));
                    } else if ($checklistSchedule->frequency_type == 4) {
                        $typeSlug = 'weekly';
                        $lastDate = date('Y-m-d H:i:s', strtotime("$startDate +7 days"));
                    } else if ($checklistSchedule->frequency_type == 5) {
                        $typeSlug = 'biweekly';
                        $lastDate = date('Y-m-d H:i:s', strtotime("$startDate +14 days"));
                    } else if ($checklistSchedule->frequency_type == 6) {
                        $typeSlug = 'monthly';
                        $lastDate = date('Y-m-d H:i:s', strtotime("$startDate +30 days"));
                    } else if ($checklistSchedule->frequency_type == 7) {
                        $typeSlug = 'bimonthly';
                        $lastDate = date('Y-m-d H:i:s', strtotime("$startDate +60 days"));
                    } else if ($checklistSchedule->frequency_type == 8) {
                        $typeSlug = 'quarterly';
                        $lastDate = date('Y-m-d H:i:s', strtotime("$startDate +90 days"));
                    } else if ($checklistSchedule->frequency_type == 9) {
                        $typeSlug = 'semiannually';
                        $lastDate = date('Y-m-d H:i:s', strtotime("$startDate +180 days"));
                    } else if ($checklistSchedule->frequency_type == 10) {
                        $typeSlug = 'annually';
                        $lastDate = date('Y-m-d H:i:s', strtotime("$startDate +365 days"));
                    } else if ($checklistSchedule->frequency_type == 11) {
                        $typeSlug = 'specific_days';
                        $allDays = explode(',', $checklistSchedule->weekdays);
                        $weekdayTime = $checklistSchedule->weekday_time;
                    }
    
                    $allTimestampts = \App\Helpers\Frequency::generate($startDate, $lastDate, $typeSlug, $allDays, $weekdayTime);
                    $hash = md5(json_encode($checklistSchedule->checklist->schema ?? []));
    
                    foreach ($allTimestampts as $timestamp) {
                        \App\Models\ChecklistTask::create([
                            'code' => \App\Helpers\Helper::generateTaskNumber($timestamp, $extraRow->user_id),
                            'checklist_scheduling_id' => $extraRow->id,
                            'form' => new \StdClass(),
                            'version_id' => \App\Helpers\Helper::getFormVersion($checklistSchedule->checklist_id, $hash),
                            'date' => $timestamp,
                            'type' => 0
                        ]);
                    }
                }
            }
        }
    }
}
