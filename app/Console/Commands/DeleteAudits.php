<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeleteAudits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:audits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete audits older than 2 days';

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
        $twoDaysAgo = now()->subDays(2)->startOfDay()->format('Y-m-d H:i:s');
        \Illuminate\Support\Facades\DB::table('audits')->where('created_at', '<', $twoDaysAgo)->delete();
        $this->info('Old audits deleted successfully.');
    }
}
