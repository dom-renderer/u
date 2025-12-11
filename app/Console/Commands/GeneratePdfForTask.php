<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GeneratePdfForTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:pdf';

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
        \App\Models\ChecklistTask::select('id')->whereIn('status', [1, 2, 3])->chunk(200, function ($tasks) {
            foreach ($tasks as $task) {
                if (!is_file(storage_path("app/public/task-pdf/task-{$task->id}.pdf"))) {
                    \App\Jobs\GenerateOptimizedTaskPdf::dispatch($task->id);
                }
            }
        });
    }
}
