<?php

namespace App\Console\Commands;

use App\Http\Controllers\DocumentsUploadController;
use Illuminate\Console\Command;

class SendDocumentExpiryReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:documentexpirereminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminder for documents nearing expiry';

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
        DocumentsUploadController::sendDocumentExpiryReminder();
    }
}
