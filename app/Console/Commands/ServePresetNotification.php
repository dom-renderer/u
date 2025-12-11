<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ServePresetNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:preset-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Preset Notification';

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
        $currentTime = now()->addHour()->format('Y-m-d H:i:00');
        $nextHour = now()->addHour()->addMinute()->format('Y-m-d H:i:00');
        
        $tasks = \App\Models\ChecklistTask::with(['parent.parent.checklist.checklist.presetemplates'])
            ->pending()
            ->scheduling()
            ->whereBetween('date', [$currentTime, $nextHour])
            ->get();

        foreach ($tasks as $task) {
            if (isset($task->parent->parent->checklist->presetemplates) && is_iterable($task->parent->parent->checklist->presetemplates)) {
                foreach ($task->parent->parent->checklist->presetemplates()->with('ntemp')->where('type', 1)->get() as $notification) {
                    if (isset($notification->ntemp->id)) {
                        if ($notification->ntemp->type === 0) {

                            if (isset($task->parent)) {
                                $extra = $task->parent;

                                $user = \App\Models\User::find($extra->user_id);
                                $location = \App\Models\Store::find($extra->store_id);
                                $checklist = \App\Models\DynamicForm::find($task->parent->parent->checklist_id);

                                $content = str_replace(array_keys(\App\Helpers\Helper::$notificationTemplatePlaceholders), [
                                    isset($user->id) ? ("{$user->name} {$user->middle_name} {$user->last_name}") : 'N/A',
                                    $user->username ?? 'N/A',
                                    $user->phone_number ?? 'N/A',
                                    $user->email ?? 'N/A',
                                    $location->name ?? 'N/A',
                                    $checklist->name ?? 'N/A',
                                    'N/A'
                                ], $notification->ntemp->content);

                                $title = str_replace(array_keys(\App\Helpers\Helper::$notificationTemplatePlaceholders), [
                                    isset($user->id) ? ("{$user->name} {$user->middle_name} {$user->last_name}") : 'N/A',
                                    $user->username ?? 'N/A',
                                    $user->phone_number ?? 'N/A',
                                    $user->email ?? 'N/A',
                                    $location->name ?? 'N/A',
                                    $checklist->name ?? 'N/A',
                                    'N/A'
                                ], $notification->ntemp->title);

                                \Illuminate\Support\Facades\Mail::to($user->email)
                                ->send(new \App\Mail\EscalationMail($title, $content));
                            }

                        } else if ($notification->ntemp->type == 1) {

                            if (isset($task->parent)) {
                                $extra = $task->parent;

                                $user = \App\Models\User::find($extra->user_id);
                                $location = \App\Models\Store::find($extra->store_id);
                                $checklist = \App\Models\DynamicForm::find($task->parent->parent->checklist_id);

                                $content = str_replace(array_keys(\App\Helpers\Helper::$notificationTemplatePlaceholders), [
                                    isset($user->id) ? ("{$user->name} {$user->middle_name} {$user->last_name}") : 'N/A',
                                    $user->username ?? 'N/A',
                                    $user->phone_number ?? 'N/A',
                                    $user->email ?? 'N/A',
                                    $location->name ?? 'N/A',
                                    $checklist->name ?? 'N/A',
                                    'N/A'
                                ], $notification->ntemp->content);

                                $title = str_replace(array_keys(\App\Helpers\Helper::$notificationTemplatePlaceholders), [
                                    isset($user->id) ? ("{$user->name} {$user->middle_name} {$user->last_name}") : 'N/A',
                                    $user->username ?? 'N/A',
                                    $user->phone_number ?? 'N/A',
                                    $user->email ?? 'N/A',
                                    $location->name ?? 'N/A',
                                    $checklist->name ?? 'N/A',
                                    'N/A'
                                ], $notification->ntemp->title);

                                $deviceTokens = [];
                                if (isset($user->id)) {
                                    $deviceTokens = \App\Models\DeviceToken::where('user_id', $user->id)->pluck('token')->toArray();
                                }
                
                                if (!empty($deviceTokens)) {
                                    \App\Helpers\Helper::sendPushNotification($deviceTokens, [
                                        'title' => $title,
                                        'description' => $content
                                    ]);
                                }
                            }

                        }
                    }
                }
            }
        }
    }
}
