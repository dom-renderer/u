<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TemporaryMappingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temp:exec';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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

        $allForms = DB::table('dynamic_forms')->where('type', 0)->where(function ($innerBuilder) {
            $innerBuilder->whereNull('deleted_at')->orWhere('deleted_at', '');
        })->get();

        foreach ($allForms as $form) {
            $hash = md5($form->schema);

            $existingRecord = DB::table('form_versions')
                ->where('checklist_id', $form->id)
                ->where('hash', $hash)
                ->first();

            if ($existingRecord) {
                DB::table('form_versions')
                    ->where('id', $existingRecord->id)
                    ->update([
                        'checklist_id' => $form->id,
                        'form' => $form->schema,
                        'hash' => $hash
                    ]);
            } else {
                DB::table('form_versions')
                    ->insert([
                        'checklist_id' => $form->id,
                        'form' => $form->schema,
                        'hash' => $hash
                    ]);
            }

            $this->info("Versioned {$form->id} checklist");
        }

        $this->warn('Task Started');

        DB::table('checklist_tasks')
            ->join('checklist_scheduling_extras', 'checklist_tasks.checklist_scheduling_id', '=', 'checklist_scheduling_extras.id')
            ->join('checklist_schedulings', 'checklist_scheduling_extras.checklist_scheduling_id', '=', 'checklist_schedulings.id')
            ->where(function ($builder) {
                $builder->whereNull('checklist_tasks.deleted_at')
                ->orWhere('checklist_tasks.deleted_at', '');
            })
            ->whereNull('checklist_tasks.version_id')
            ->orderBy('checklist_tasks.id')
            ->selectRaw('checklist_tasks.id, checklist_tasks.form, checklist_tasks.version_id, checklist_schedulings.checklist_id')
            ->chunk(1000, function ($tasks) {
                foreach ($tasks as $task) {
                    if (!empty($task->form)) {
                        $hash = md5($task->form);

                        $existingRecord = DB::table('form_versions')
                            ->where('checklist_id', $task->checklist_id)
                            ->where('hash', $hash)
                            ->first();

                        if (!$existingRecord) {
                            DB::table('form_versions')
                                ->insert([
                                    'checklist_id' => $task->checklist_id,
                                    'form' => $task->form,
                                    'hash' => $hash
                                ]);

                            $existingRecord = DB::table('form_versions')
                                ->where('checklist_id', $task->checklist_id)
                                ->where('hash', $hash)
                                ->first();
                        }

                        DB::table('checklist_tasks')->where('id', $task->id)->update([
                            'version_id' => $existingRecord->id
                        ]);

                        $this->info("Versioned {$task->id} Task");
                    }
                }
            });
    }
}
