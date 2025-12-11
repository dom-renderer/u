<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;
use App\Models\ChecklistTask;
use Carbon\Carbon;

class DeleteOldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:old-data';

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
        // $cutoffDate = Carbon::create(2025, 5, 31)->endOfDay();
        // $taskIds = ChecklistTask::where('created_at', '<=', $cutoffDate)->pluck('id')->toArray();

        // foreach ($taskIds as $id) {
        //     $filePath = "public/task-pdf/task-{$id}.pdf";

        //     if (Storage::exists($filePath)) {
        //         Storage::delete($filePath);
        //         echo "PDF DELETED $filePath \n";
        //     }
        // }

        $folderPath = storage_path('app/public/workflow-task-uploads');

        $prefixesToDelete = [
            'SIGN-202501',
            'SIGN-202502',
            'SIGN-202503',
            'SIGN-202504',
            'SIGN-202505',
            'SIGN-202506',
            'SIGN-202507',
        ];

        $files = File::files($folderPath);

        foreach ($files as $file) {
            $fileName = $file->getFilename();

            foreach ($prefixesToDelete as $prefix) {
                if (str_starts_with($fileName, $prefix)) {
                    File::delete($file->getRealPath());
                    break;
                }
            }
        }        
        

    }
}
