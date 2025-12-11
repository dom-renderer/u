<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class RemoveTaskMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:media';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Checklist Media';

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
        $this->warn('Removal of media started');

        $folderPath = storage_path('app/public/workflow-task-uploads');

        DB::table('checklist_tasks')
            ->join('checklist_scheduling_extras', 'checklist_tasks.checklist_scheduling_id', '=', 'checklist_scheduling_extras.id')
            ->join('checklist_schedulings', 'checklist_scheduling_extras.checklist_scheduling_id', '=', 'checklist_schedulings.id')
            ->join('dynamic_forms', 'checklist_schedulings.checklist_id', '=', 'dynamic_forms.id')
            ->where(function ($builder) {
                $builder->whereNull('checklist_tasks.deleted_at')
                ->orWhere('checklist_tasks.deleted_at', '');
            })
            ->orderBy('checklist_tasks.id')
            ->selectRaw('checklist_tasks.id, checklist_schedulings.checklist_id, dynamic_forms.remove_media_frequency, dynamic_forms.remove_media_frequency_after_n_day, checklist_tasks.completion_date')
            ->chunk(1000, function ($tasks) use ($folderPath) {
                foreach ($tasks as $task) {
                    if ($task->remove_media_frequency == 'every_n_day' && is_numeric($task->remove_media_frequency_after_n_day) && $task->remove_media_frequency_after_n_day > 0 &&
                    Carbon::parse($task->completion_date)->addDays($task->remove_media_frequency_after_n_day)->lt(Carbon::now())) {
                        $files = File::files($folderPath);

                        foreach ($files as $file) {
                            $fileName = $file->getFilename();

                            if (str_starts_with($fileName, 'SIGN-20') && str_ends_with($fileName, "-{$task->checklist_id}-{$task->id}.webp")) {

                                $logPath = storage_path('logs/media-removal.log');

                                if (!file_exists($logPath)) {
                                    File::put($logPath, "");
                                    chmod($logPath, 0777);
                                }

                                $logEntry = 'REAL-[' . now() . '] ' . $file->getRealPath() . PHP_EOL;
                                File::append($logPath, $logEntry);
                                File::delete($file->getRealPath());

                                continue;
                            }
                        }
                    }                    
                }
            });

        $this->warn('Removal of media completed');
    }
}
