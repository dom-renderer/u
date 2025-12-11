<?php

namespace App\Console\Commands;

use App\Models\Content;
use Illuminate\Console\Command;

class MakeContentDisable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'disable:content';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make content disable on expiry date';

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
        Content::whereNotNull('expiry_date')->where(\DB::raw("DATE_FORMAT(expiry_date, '%Y-%m-%d')"), '<=', date('Y-m-d'))->update([
            'status' => 0
        ]);
    }
}
