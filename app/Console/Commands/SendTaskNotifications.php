<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendTaskNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Task Notification';

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
        $oneMinuteAgo = now()->subMinute()->format('Y-m-d H:i:00');
        $currentTime = now()->format('Y-m-d H:i:00');

        $tasks = \App\Models\ChecklistTask::with(['parent.user'])
                 ->pending()
                 ->whereBetween('date', [$oneMinuteAgo, $currentTime])
                 ->get();

        if ($tasks) {
            foreach ($tasks as $task) {

                if ($task->type == 0) {
                    $deviceTokens = \App\Models\DeviceToken::select('token')
                    ->where('user_id', $task->parent->user_id)
                    ->pluck('token')
                    ->toArray();

                    if (!empty($deviceTokens)) {
                        \App\Helpers\Helper::sendPushNotification($deviceTokens, [
                            'title' => isset($task->parent->parent->notification_title) ? $task->parent->parent->notification_title : $task->code,
                            'description' => isset($task->parent->parent->notification_description) ? $task->parent->parent->notification_description : ''
                        ]);
                    }

                    if (isset($task->parent->user->email)) {
                        \Illuminate\Support\Facades\Mail::to($task->parent->user->email)->send(new \App\Mail\EscalationMail(isset($task->parent->parent->notification_title) ? $task->parent->parent->notification_title : $task->code, isset($task->parent->parent->notification_description) ? $task->parent->parent->notification_description : ''));
                    }

                }
            }
        }
    }
}
