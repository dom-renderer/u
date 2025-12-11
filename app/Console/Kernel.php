<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /**
         * Task Scheduling
        */
            $schedule->command('perpetualtask:run')->everyMinute();
        /**
         * Task Scheduling
        */

        /**
         * Task Action Notifcations
        */
            $schedule->command('send:notification')->everyMinute();
            $schedule->command('hour:before')->everyMinute();
            $schedule->command('half:past')->everyMinute();
            $schedule->command('quarter:end')->everyMinute();
        /**
         * Task Action Notifcations
        */

        
        /**
         * LMS Disable Content
        */
            $schedule->command('disable:content')->everyMinute();
        /**
         * LMS Disable Content
        */


        /**
         * Write Timestamp on Task Image
        */
            $schedule->command('rewrite:mark')->everyMinute()->withoutOverlapping();
        /**
         * Write Timestamp on Task Image
        */


        /**
         * Task PDF Generation & Deletion
        */
            $schedule->command('pdf:generate')->everyMinute()->withoutOverlapping();
            $schedule->command('pdf:delete')->everyMinute()->withoutOverlapping();
        /**
         * Task PDF Generation & Deletion
        */


        /**
         * Document Expiration Notificaiton
        */
            $schedule->command( 'send:documentexpirereminder' )->dailyAt( '08:00' );
        /**
         * Document Expiration Notificaiton
        */

        /**
         * Delete Audits Older than 2 Days
        */
            $schedule->command('delete:audits')->dailyAt( '04:00' );
        /**
         * Delete Audits Older than 2 Days
        */

        /**
         * Execute Ticket Escalation Checks
        */
            $schedule->command('execute:ticket-escalation')->everyMinute()->withoutOverlapping();
        /**
         * Execute Ticket Escalation Checks
        */

        /**
         * Calculate Percentage
        */
            $schedule->command('calcualte:percentage')->everyThreeMinutes()->withoutOverlapping();
        /**
         * Calculate Percentage
        */

        /**
         * Write Version Id
        */
            $schedule->command('write:version')->everyMinute()->withoutOverlapping();
        /**
         * Write Version Id
        */

        /**
         * Delete Media of Task
        */
            $schedule->command('remove:media')->dailyAt( '00:00' )->withoutOverlapping();
        /**
         * Delete Media of Task
        */
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
