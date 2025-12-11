<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class ReformatCodeOfTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reformat:code';

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

        DB::beginTransaction();

        try {

            \App\Models\ChecklistTask::query()->chunk(500, function ($rows) {
                foreach ($rows as $row) {
                    $index = sprintf('%02d', \App\Models\ChecklistTask::withTrashed()->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), date('Y-m-d', strtotime($row->date)))->count() + 1);
                    $sequence = "WO{$index}";

                    $employeeId = \App\Models\User::withTrashed()->select('employee_id')->where('id', $row->parent->user_id ?? '')->first()->employee_id ?? '';
                    if (!empty($employeeId)) {
                        $sequence .= "-{$employeeId}";
                    }

                    $sequence .= ('-' . date('d-m-y', strtotime($row->date)));

                    $row->update([
                        'code' => $sequence
                    ]);

                    echo "$sequence \n";
                }
            });

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }
}
