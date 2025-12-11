<?php

namespace App\Console\Commands;

use App\Models\ChecklistSchedulingExtra;
use Illuminate\Console\Command;
use App\Models\ChecklistTask;
use Carbon\Carbon;

class RemoveDuplicateTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:duplicate';

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
        ini_set('memory_limit','-1');

        $toBeNotRemoved = [];

        \DB::beginTransaction();

        try {
            $tasks = ChecklistTask::with(['parent.parent'])->whereDate('date', '>=', Carbon::today()->startOfMonth()->format('Y-m-d'))
            ->whereDate('date', '<=', Carbon::today()->endOfMonth()->format('Y-m-d'))
            ->where('cancelled', 0)
            ->get();

            foreach ($tasks as $task) {
                if (isset($task->parent->parent->id)) {
                    $otherDuplicates = ChecklistTask::select('id')
                    ->where('id', '!=', $task->id)
                    ->when(!empty($toBeNotRemoved), function ($query) use ($toBeNotRemoved) {
                        $query->whereNotIn('id', $toBeNotRemoved);
                    }) 
                    ->whereDate('date', date('Y-m-d', strtotime($task->date)))
                    ->where('cancelled', 0)
                    ->whereHas('parent', function ($builder) use ($task) {
                        $builder->where('user_id', $task->parent->user_id)->where('store_id', $task->parent->store_id);
                    })
                    ->whereHas('parent.parent', function ($builder) use ($task) {
                        $builder->where('checker_user_id', $task->parent->parent->checker_user_id)->where('checklist_id', $task->parent->parent->checklist_id);
                    })
                    ->where('status', 0)
                    ->pluck('id')
                    ->toArray();

                    if (!empty($otherDuplicates)) {
                        $toBeNotRemoved[] = $task->id;
                        ChecklistTask::whereIn('id', $otherDuplicates)->update(['deleted_at' => '2025-10-01 00:00:00']);

                        $implode = implode(',', $otherDuplicates);
                        echo "{$task->id} Removed duplicates DELETED: $implode\n";
                    }
                }
            }

            \DB::commit();

            echo "ALL DUPLICATE REMOVED SUCCESSFULLY";
        } catch (\Exception $e) {
            \DB::rollBack();

            echo "FAILED DUE TO " . $e->getMessage() . ' on line ' . $e->getLine();
        }
    }
}
