<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeletePDF extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:delete';

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

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        foreach (\App\Models\PdfGenerationLog::where('status', 1)->where('keep_till', '<=', date('Y-m-d H:i:s'))->get() as $row) {
            \DB::beginTransaction();
            try {

                if (\App\Models\PdfGenerationLog::where('task_id', $row->task_id)->where('keep_till', '>', date('Y-m-d H:i:s'))->doesntExist()) {
                    if (file_exists(storage_path("app/public/task-pdf/task-{$row->task_id}.pdf")) && is_file(storage_path("app/public/task-pdf/task-{$row->task_id}.pdf"))) {
                        @unlink(storage_path("app/public/task-pdf/task-{$row->task_id}.pdf"));
                    }

                    if (file_exists(storage_path("app/public/task-pdf/task-compressed-{$row->task_id}.pdf")) && is_file(storage_path("app/public/task-pdf/task-compressed-{$row->task_id}.pdf"))) {
                        @unlink(storage_path("app/public/task-pdf/task-compressed-{$row->task_id}.pdf"));
                    }

                    $row->update(['status' => 2]);
                }

                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollBack();
                Log::error('Requested report ' . $row->task_id . ' not generated due to' . ' : ' . $e->getLine() . ' line ' . $e->getLine());
            }
        }
    }
}
