<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class EmptyTaskJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'empty:json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Empty Task Json';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle() {

        DB::table('checklist_tasks')
            ->where(function ($builder) {
                $builder->whereNotNull('form')
                ->where('form', '!=', '{}')
                ->where('form', '!=', '[]')
                ->where('form', '!=', '');
            })
            ->where(function ($builder) {
                $builder->whereNull('deleted_at')
                ->orWhere('deleted_at', '');
            })
            ->whereNotNull('version_id')
            ->where('version_id', '!=', '')
            ->where('version_id', '>', '0')
            ->orderBy('id')
            ->chunk(1000, function ($tasks) {
                foreach ($tasks as $task) {
                    if (!empty($task->form)) {

                        DB::table('checklist_tasks')->where('id', $task->id)->update([
                            'form' => '{}'
                        ]);

                        $this->info("Versioned {$task->id} Task");
                    }
                }
            });
    }
}
