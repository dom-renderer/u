<?php

namespace App\Console\Commands;

use App\Helpers\Helper;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskExceptionMail;
use Illuminate\Console\Command;
use App\Models\ChecklistTask;

class SendTaskException extends Command
{
    protected static $staticEmails = ['mahesh.thakkar@teapost.in', 'puneet.tibrewala@teapost.in'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:exception-fields';

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
        $from = now()->subDay()->format('Y-m-d');
        $to = now()->subDay()->format('Y-m-d');
        $emails = self::$staticEmails;

        $exceptionFieldData = [];

        foreach (ChecklistTask::with(['parent.parent', 'parent.parent.checker', 'parent.actstore', 'parent.user', 'parent.actstore.thecity'])
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->where('type', 0)
            ->whereIn('status', [2, 3])
            ->get() as $task) {

                $falsyValues = Helper::getBooleanFields($task->data)['falsy'];

                if (count($falsyValues) > 0) {
                    foreach ($falsyValues as $falsyValue) {
                        $exceptionFieldData[] = [
                            'item_name' => html_entity_decode($falsyValue['label']),
                            'dom_name' => isset($task->parent->user->id) ? ($task->parent->user->employee_id . ' - ' . $task->parent->user->name . ' ' .  $task->parent->user->middle_name . ' '  . $task->parent->user->last_name) : '',
                            'location_name' => isset($task->parent->actstore->id) ? ($task->parent->actstore->code . ' - ' . $task->parent->actstore->name) : '',
                            'city_name' => $task->parent->actstore->thecity->city_name ?? '',
                            'state_name' => $task->parent->actstore->thecity->city_state ?? '',
                            'initial_status_name' => 'Pending',
                            'latest_status_name' => Helper::getLatestStatus($task->id, $falsyValue['className']),
                            'last_updated' => date('d-m-Y H:i', strtotime($task->updated_at)),
                        ];
                    }
                }                
        }

        foreach ($emails as $email) {
            Mail::to($email)->send(new TaskExceptionMail($exceptionFieldData));
            $this->info("Sent mail to {$email} for exception field data.");
        }

        $makers = $checkers = $stores = [];

        foreach (ChecklistTask::with(['parent.parent', 'parent.parent.checker', 'parent.actstore', 'parent.user', 'parent.actstore.thecity'])
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->where('type', 0)
            ->whereIn('status', [2, 3])
            ->get() as $task) {
                
            if (isset($task->parent->parent->id)) {
                //store
                if (isset($task->parent->actstore->email) && self::validateEmail($task->parent->actstore->email)) {
                    if (!empty($stores)) {
                        $stores[$task->parent->actstore->email][] = $task->id;
                    } else {
                        $stores = [
                            $task->parent->actstore->email = [$task->id]
                        ];
                    }
                }

                //maker
                if (isset($task->parent->user->email) && self::validateEmail($task->parent->user->email)) {
                    if (!empty($makers)) {
                        $makers[$task->parent->user->email][] = $task->id;
                    } else {
                        $makers = [
                            $task->parent->user->email = [$task->id]
                        ];
                    }
                }

                //checker
                if (isset($task->parent->parent->checker->email) && self::validateEmail($task->parent->parent->checker->email)) {
                    if (!empty($checkers)) {
                        $checkers[$task->parent->parent->checker->email][] = $task->id;
                    } else {
                        $checkers = [
                            $task->parent->parent->checker->email = [$task->id]
                        ];
                    }
                }
            }
        }

        foreach ($makers as $email => $task) {
            if (!in_array($email, self::$staticEmails) && !empty($task)) {
                $exceptionFieldData = [];

                foreach (ChecklistTask::with(['parent.parent', 'parent.parent.checker', 'parent.actstore', 'parent.user', 'parent.actstore.thecity'])
                    ->whereIn('id', $task)
                    ->get() as $task) {

                        $falsyValues = Helper::getBooleanFields($task->data)['falsy'];

                        if (count($falsyValues) > 0) {
                            foreach ($falsyValues as $falsyValue) {
                                $exceptionFieldData[] = [
                                    'item_name' => html_entity_decode($falsyValue['label']),
                                    'dom_name' => isset($task->parent->user->id) ? ($task->parent->user->employee_id . ' - ' . $task->parent->user->name . ' ' .  $task->parent->user->middle_name . ' '  . $task->parent->user->last_name) : '',
                                    'location_name' => isset($task->parent->actstore->id) ? ($task->parent->actstore->code . ' - ' . $task->parent->actstore->name) : '',
                                    'city_name' => $task->parent->actstore->thecity->city_name ?? '',
                                    'state_name' => $task->parent->actstore->thecity->city_state ?? '',
                                    'initial_status_name' => 'Pending',
                                    'latest_status_name' => Helper::getLatestStatus($task->id, $falsyValue['className']),
                                    'last_updated' => date('d-m-Y H:i', strtotime($task->updated_at)),
                                ];
                            }
                        }                
                }

                Mail::to($email)->send(new TaskExceptionMail($exceptionFieldData));
                $this->info("Sent mail to MAKER: {$email} for exception field data.");
            }
        }

        foreach ($checkers as $email => $task) {
            if (!in_array($email, self::$staticEmails) && !empty($task)) {
                $exceptionFieldData = [];

                foreach (ChecklistTask::with(['parent.parent', 'parent.parent.checker', 'parent.actstore', 'parent.user', 'parent.actstore.thecity'])
                    ->whereIn('id', $task)
                    ->get() as $task) {

                        $falsyValues = Helper::getBooleanFields($task->data)['falsy'];

                        if (count($falsyValues) > 0) {
                            foreach ($falsyValues as $falsyValue) {
                                $exceptionFieldData[] = [
                                    'item_name' => html_entity_decode($falsyValue['label']),
                                    'dom_name' => isset($task->parent->user->id) ? ($task->parent->user->employee_id . ' - ' . $task->parent->user->name . ' ' .  $task->parent->user->middle_name . ' '  . $task->parent->user->last_name) : '',
                                    'location_name' => isset($task->parent->actstore->id) ? ($task->parent->actstore->code . ' - ' . $task->parent->actstore->name) : '',
                                    'city_name' => $task->parent->actstore->thecity->city_name ?? '',
                                    'state_name' => $task->parent->actstore->thecity->city_state ?? '',
                                    'initial_status_name' => 'Pending',
                                    'latest_status_name' => Helper::getLatestStatus($task->id, $falsyValue['className']),
                                    'last_updated' => date('d-m-Y H:i', strtotime($task->updated_at)),
                                ];
                            }
                        }                
                }

                Mail::to($email)->send(new TaskExceptionMail($exceptionFieldData));
                $this->info("Sent mail to CHECKER: {$email} for exception field data.");
            }
        }

        foreach ($stores as $email => $task) {
            if (!in_array($email, self::$staticEmails) && !empty($task)) {
                $exceptionFieldData = [];

                foreach (ChecklistTask::with(['parent.parent', 'parent.parent.checker', 'parent.actstore', 'parent.user', 'parent.actstore.thecity'])
                    ->whereIn('id', $task)
                    ->get() as $task) {

                        $falsyValues = Helper::getBooleanFields($task->data)['falsy'];

                        if (count($falsyValues) > 0) {
                            foreach ($falsyValues as $falsyValue) {
                                $exceptionFieldData[] = [
                                    'item_name' => html_entity_decode($falsyValue['label']),
                                    'dom_name' => isset($task->parent->user->id) ? ($task->parent->user->employee_id . ' - ' . $task->parent->user->name . ' ' .  $task->parent->user->middle_name . ' '  . $task->parent->user->last_name) : '',
                                    'location_name' => isset($task->parent->actstore->id) ? ($task->parent->actstore->code . ' - ' . $task->parent->actstore->name) : '',
                                    'city_name' => $task->parent->actstore->thecity->city_name ?? '',
                                    'state_name' => $task->parent->actstore->thecity->city_state ?? '',
                                    'initial_status_name' => 'Pending',
                                    'latest_status_name' => Helper::getLatestStatus($task->id, $falsyValue['className']),
                                    'last_updated' => date('d-m-Y H:i', strtotime($task->updated_at)),
                                ];
                            }
                        }                
                }

                Mail::to($email)->send(new TaskExceptionMail($exceptionFieldData));
                $this->info("Sent mail to STORE: {$email} for exception field data.");
            }
        }        
    }

    private static function validateEmail($email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $domain = substr(strrchr($email, "@"), 1);
            
            if (checkdnsrr($domain, "MX")) {
                return true;
            }
        }

        return false;
    }
}
