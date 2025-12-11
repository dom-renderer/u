<?php

namespace App\Http\Controllers\API\v2;

use App\Http\Controllers\API\ApiController as v1;
use App\Models\NewTicketEscalationExecution;
use Illuminate\Support\Facades\Validator;
use App\Models\TaskDeviceInformation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SystemNotification;
use App\Models\TicketAttachment;
use App\Models\NewTicketHistory;
use App\Models\PdfGenerationLog;
use App\Models\RescheduledTask;
use Illuminate\Validation\Rule;
use App\Models\TicketRoleUser;
use App\Models\NewTicketOwner;
use App\Models\ChecklistTask;
use \Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\DynamicForm;
use App\Models\RedoAction;
use App\Models\Particular;
use App\Models\NewTicket;
use App\Models\UserIssue;
use App\Helpers\Helper;
use App\Models\Ticket;
use App\Models\Issue;
use App\Models\User;
use Carbon\Carbon;

class ApiController extends \App\Http\Controllers\Controller
{
    public function submission(Request $request) {
        TaskDeviceInformation::create([
            'eloquent' => ChecklistTask::class,
            'eloquent_id' => $request->task_id,
            'user_id' => auth()->check() ? auth()->user()->id : null,
            'device_model' => $request->device_model,
            'network_speed' => $request->network_speed,
            'device_version' => $request->device_version
        ]);

        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:checklist_tasks,id',
            'status' => 'required|in:1,2',
            'type' => 'required|in:1,2',//1 = Full JSON | 2 = Partial JSON
            'data' => 'required'
        ]);

        if ($validator->fails()) { 
            $errorString = implode(",",$validator->messages()->all());
            return response()->json(['error' => $errorString], 401);
        }

        $task = ChecklistTask::findOrFail($request->task_id);
        $forImageChecklistId = $task->parent->parent->checklist_id ?? 'NA';
        $forImageTaskId = $task->id ?? 'NA';

        if ($task->status == Helper::$status['in-verification']) {
            return response()->json(['error' => 'This Checklist already submitted.']);
        }

        if (!file_exists(storage_path('app/public/workflow-task-uploads'))) {
            mkdir(storage_path('app/public/workflow-task-uploads'), 0777, true);
        }

        if (is_string($request->data) && $request->data != "NONE") {
            $data = json_decode($request->data, true);
        } else {
            $data = $request->data;
        }

        $filesToBeRemoved = [];
        $currentJson = $task->data;

        if ($request->data != "NONE") {
            if ($request->type == 2) {
                if (empty($currentJson)) {
                    $currentJson = [];
                }

                foreach ($data as $row) {
                    if (self::hasValueByName($currentJson, $row['name'])) {
                        foreach ($currentJson as &$item) {
                            if (isset($item->name) && $item->name === $row['name']) {

                                if (property_exists($item, 'isFile') &&  $item->isFile) {
                                    if (is_array($item->value)) {
                                        foreach ($item->value as $fileVal) {
                                            if (!empty($fileVal) && !Str::contains($fileVal, '|to_be_generated.png') && is_file(storage_path("app/public/workflow-task-uploads/{$fileVal}"))) {
                                                $fileDoesExists = false;
                                                if (is_array($row['value'])) {
                                                    foreach ($row['value'] as $rw) {
                                                        if ($rw == $fileVal) {
                                                            $fileDoesExists = true;
                                                            continue;
                                                        }
                                                    }
                                                } else if (is_string($row['value'])) {
                                                    if ($row['value'] == $item->value) {
                                                            $fileDoesExists = true;
                                                    }
                                                }

                                                if ($fileDoesExists === false) {
                                                    $filesToBeRemoved[] = storage_path("app/public/workflow-task-uploads/{$fileVal}");
                                                }
                                            }
                                        }
                                    } else if (is_string($item->value)) {
                                        if (!empty($item->value) && !Str::contains($item->value, '|to_be_generated.png') && is_file(storage_path("app/public/workflow-task-uploads/{$item->value}"))) {
                                            $fileDoesExists = false;
                                            
                                            if (is_array($row['value'])) {
                                                foreach ($row['value'] as $rw) {
                                                    if ($rw == $item->value) {
                                                        $fileDoesExists = true;
                                                        continue;
                                                    }
                                                }
                                            } else if (is_string($row['value'])) {
                                                if ($row['value'] == $item->value) {
                                                        $fileDoesExists = true;
                                                }
                                            }

                                            if ($fileDoesExists === false) {
                                                $filesToBeRemoved[] = storage_path("app/public/workflow-task-uploads/{$item->value}");
                                            }
                                        }
                                    }
                                }

                                if ($row['isFile'] && is_array($row['value'])) {
                                    $finalImgArrObj = [];
                                    foreach ($row['value'] as $tfov) {
                                        if (!Str::contains($tfov, '|to_be_generated.png')) {
                                            $finalImgArrObj[] = $tfov;
                                        }
                                    }

                                    $row['value'] = $finalImgArrObj;
                                } else if ($row['isFile'] && is_string($row['value'])) {
                                    if (!Str::contains($row['value'], '|to_be_generated.png')) {
                                        $finalImgArrObj = [$row['value']];
                                    }

                                    $row['value'] = $finalImgArrObj;
                                }

                                $item->value = $row['value'];

                                if (property_exists($item, 'value_label') && isset($row['value_label'])) {
                                    $item->value_label = $row['value_label'];
                                }

                                continue;
                            }
                        }
                    } else {
                        if (!is_array($currentJson) && ($currentJson == '{}' || empty($currentJson))) {
                            $currentJson = [];
                        } else if (is_object($currentJson)) {
                            $currentJson = (array) $currentJson;
                        }

                        if (isset($row) && array_key_exists('isFile', $row)) {
                            if (is_array($row['value'])) {
                                $finalImgArr = [];
                                foreach ($row['value'] as $thisFileRow) {
                                    if (!Str::contains($thisFileRow, '|to_be_generated.png')) {
                                        $finalImgArr[] = $thisFileRow;
                                    }
                                }

                                $row['value'] = $finalImgArr;
                            } else if (is_string($row['value'])) {
                                if (Str::contains($row['value'], '|to_be_generated.png')) {
                                    $row['value'] = [];
                                }
                            }
                        }

                        array_push($currentJson, $row);
                    }
                }

                foreach ($currentJson as &$item) {
                    if (is_array($item)) {
                        $item = (object) $item;
                    }
                }
                unset($item);

                usort($currentJson, function ($a, $b) {
                    $pageComparison = (int)$a->page <=> (int)$b->page;
                    
                    if ($pageComparison === 0) {
                        $aIndex = isset($a->index) ? (int)$a->index : PHP_INT_MAX;
                        $bIndex = isset($b->index) ? (int)$b->index : PHP_INT_MAX;
                        
                        return $aIndex <=> $bIndex;
                    }

                    return $pageComparison;
                });

                $task->data = $currentJson;

            } else {
                foreach ($data as &$dt) {
                    if (array_key_exists('isFile', $dt) && $dt['isFile'] == true) {
                        if (is_array($dt['value'])) {
                            foreach ($dt['value'] as &$tempRow) {
                                if (strpos($tempRow, 'SIGN-20') !== false) {
                                    continue;
                                }
                                $tempRow = Helper::downloadBase64FileWebp($tempRow, ('SIGN-' . date('YmdHis') . uniqid() . '-' . $forImageChecklistId . '-' . $forImageTaskId), storage_path('app/public/workflow-task-uploads'));
                            }
                        } else {
                            if (strpos($dt['value'], 'SIGN-20') !== false) {
                                continue;
                            }
                            $dt['value'] = Helper::downloadBase64FileWebp($dt['value'], ('SIGN-' . date('YmdHis') . uniqid() . '-' . $forImageChecklistId . '-' . $forImageTaskId), storage_path('app/public/workflow-task-uploads'));
                        }
                    }
                }

                $task->data = $data;
            }
        }

        if ($task->type == 0 && isset($task->parent->parent->checker_user_id)) {
            $task->status = $request->status;

            if ($request->status == Helper::$status['in-verification']) {
                \App\Jobs\DisapproveNoAnsweredFields::dispatch($task->id);
            }
        } else {
            if ($request->status == Helper::$status['in-verification']) {
                $task->status = Helper::$status['completed'];
                $task->completion_date = now();
            } else {
                $task->status = $request->status;
            }
        }

        if ($task->type == 0 && $request->status == Helper::$status['in-verification']) {
            v1::dispatchNotifications($task);
        }

        if (empty($task->started_at)) {
            if (!empty($request->starting_date)) {
                $task->started_at = date('Y-m-d H:i:s', strtotime($request->starting_date));
            } else {
                $task->started_at = now();
            }
        }

        $task->save();

        if ($request->status == Helper::$status['in-verification']) {
            $task = ChecklistTask::find($request->task_id);
            $task->completion_date = now();

            if ($task->type == 1) {
                $task->status = Helper::$status['completed'];
            }

            $task->save();
        }

        if ($task->status == 2 || $task->status == 3) {
            //generating from notification center
            // \App\Jobs\GenerateOptimizedTaskPdf::dispatch($task->id);
        }

        if (!empty($filesToBeRemoved)) {
            foreach ($filesToBeRemoved as $filesToBeRemovedFile) {
                if (is_file($filesToBeRemovedFile)) {
                    //keep for logs
                    // unlink($filesToBeRemovedFile);
                }
            }            
        }

        return response()->json(['success' => 'Checklist submitted successfully.', 'data' => $data]);
    }

    public static function hasValueByName($items, $targetName) {
        foreach ($items as $item) {
            if (isset($item->name) && $item->name === $targetName) {
                return !empty($item->name);
            }
        }
        return false;
    }

    public function tasks(Request $request) {
        $page = $request->page > -1 ? $request->page : 0;
        $perPage = $request->record_per_page > 0 ? $request->record_per_page : 5;
        $skip = $page * $perPage;

        $filterCompending = $request->status;
        $filterFrom = date('Y-m-d H:i:s', strtotime($request->from));
        $filterTo = date('Y-m-d H:i:s', strtotime($request->to));

        $tasks = ChecklistTask::with(['restasks', 'submissionentries', 'redos', 'parent.parent', 'parent.user' => function ($builder) {
            return $builder->withTrashed();
        }])
        ->when($request->has('task_id') && !empty($request->task_id), function ($innerBuilder) {
            $innerBuilder->where('id', request('task_id'));
        })
        ->when(auth()->check(), function ($inBldr) {
            $inBldr->where(function ($innerBuilder) {
                $innerBuilder->whereHas('parent.parent', function ($query) {
                    $query->where('checker_user_id', auth()->user()->id);
                })
                ->orWhereHas('parent', function ($query) {
                    $query->where('user_id', auth()->user()->id);
                });
            })
            ->when(is_numeric(request('current_store_id')) && request('current_store_id') > 0, function ($builder) {
                $builder->whereHas('parent.actstore', function ($query) {
                    $query->where('id', request('current_store_id'));
                });
            });
        })
        ->when($request->showCancelled == 1, function ($builder) {
            return $builder->where('cancelled', 1);
        })
        ->when($request->showCancelled == 2, function ($builder) {
            return $builder->where('cancelled', 0);
        })
        ->when($filterCompending == 1, function ($builder) {
            return $builder->where('status', 0);
        })
        ->when($filterCompending == 2, function ($builder) {
            return $builder->where('status', 1);
        })
        ->when($filterCompending == 3, function ($builder) {
            return $builder->where('status', 2);
        })
        ->when($filterCompending == 4, function ($builder) {
            return $builder->where('status', 3);
        })
        
        ->when($request->task_type == 1, function ($builder) {
            $builder->whereHas('parent', function ($query) {
                $query->where('user_id', auth()->user()->id);
            });
        })
        ->when($request->task_type == 1 && in_array(request('filter_status'), ['PENDING', 'IN_PROGRESS', 'PENDING_VERIFICATION', 'VERIFIED', 'COMPLETED']), function ($builder) {
            if (request('filter_status') == 'PENDING') {
                $builder->where('status', 0);
            } else if (request('filter_status') == 'IN_PROGRESS') {
                $builder->where('status', 1);
            } else if (request('filter_status') == 'PENDING_VERIFICATION') {
                $builder->where('status', 2);
            } else if (request('filter_status') == 'VERIFIED') {
                $builder->where('status', 3)
                ->whereHas('parent.parent', function ($query) {
                    $query->where('checker_user_id', '>', 0);
                });
            } else if (request('filter_status') == 'COMPLETED') {
                $builder->where('status', 3)
                ->whereHas('parent.parent', function ($query) {
                    $query->whereNull('checker_user_id');
                });
            }
        })


        ->when($request->task_type == 2, function ($builder) {
            $builder->whereHas('parent.parent', function ($query) {
                $query->where('checker_user_id', auth()->user()->id);
            });
        })

        ->when($request->task_type == 2 && in_array(request('filter_status'), ['PENDING_VERIFICATION', 'REASSIGNED', 'VERIFYING', 'VERIFIED']), function ($builder) {
            if (request('filter_status') == 'PENDING_VERIFICATION') {
                $builder->where('status', 2)
                ->whereDoesntHave('redos', function ($query) {
                    $query->where('status', [0,1]);
                });
            } else if (request('filter_status') == 'REASSIGNED') {
                $builder->where('status', 2)
                ->whereDoesntHave('redos', function ($query) {
                    $query->where('status', 1);
                })
                ->whereHas('redos', function ($query) {
                    $query->where('status', 0);
                });                
            } else if (request('filter_status') == 'VERIFYING') {
                $builder->where('status', 2)
                ->whereHas('redos', function ($query) {
                    $query->where('status', 1);
                });
            } else if (request('filter_status') == 'VERIFIED') {
                $builder->where('status', 3);
            }
        })

        ->when($request->task_type == 2 && !in_array(request('filter_status'), ['PENDING_VERIFICATION', 'REASSIGNED', 'VERIFYING', 'VERIFIED']), function ($builder) {
            $builder->where('status', 2);
        })
        ->when(is_numeric($request->checklist_template_id) && $request->checklist_template_id > 0, function ($builder) {
            $builder->whereHas('parent.parent', function ($query) {
                $query->where('checklist_id', request('checklist_template_id'));
            });
        })

        ->scheduling();

        if (!empty($request->from) && !empty($request->to)) {
            $tasks = $tasks->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($filterFrom)))
            ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($filterTo)));
        } else if (!empty($request->from) && empty($request->to)) {
            $tasks = $tasks->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($filterFrom)));
        } else if (empty($request->from) && !empty($request->to)) {
            $tasks = $tasks->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($filterTo)));
        } else {
            $tasks = $tasks->where(function ($where) {
                $where->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), date('Y-m-d'))
                ->orWhere(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), date('Y-m-d'));
            });
        }

        $taskCount = $tasks->clone()->count();

        $tasks = $tasks
        ->orderBy('date', 'ASC')
        ->skip($skip)
        ->take($perPage)
        ->get()
        ->map(function ($el) {
            if ($el->status == 0) {
                $statusLabel = 'PENDING';
            } else if ($el->status == 1) {
                $statusLabel = 'IN-PROGRESS';
            } else if ($el->status == 2) {

                if (request('task_type') == 1 && request('filter_status') == 'PENDING_VERIFICATION') {
                    $statusLabel = 'PENDING-VERIFICATION';
                } else {
                    if (isset($el->parent->parent->checker_user_id)) {
                        if ($el->redos()->where('status', 1)->count() == 0 && $el->redos()->where('status', 0)->count() > 0) {
                            $statusLabel = 'REASSIGNED';
                        } else if ($el->redos()->where('status', 0)->count() == 0 && $el->redos()->where('status', 1)->count() == 0) {
                            $statusLabel = 'PENDING-VERIFICATION';
                        } else {
                            $statusLabel = 'VERIFYING';
                        }
                    } else {
                        $statusLabel = 'COMPLETED';
                    }
                }

            } else {
                if (isset($el->parent->parent->checker_user_id)) {
                    $statusLabel = 'VERIFIED';
                } else {
                    $statusLabel = 'COMPLETED';
                }
            }

            $tempTime = Helper::calculateTotalTime($el->id);

            $versionedForm = Helper::getVersionForm($el->version_id);
            $theFulfilledJson = [];
            if (isset($versionedForm)) {
                $theFulfilledJson = $versionedForm;
                if (!empty($el->data)) {
                    foreach ($el->data as $row) {
                        if (isset($theFulfilledJson[$row->page - 1]) && is_array($theFulfilledJson[$row->page - 1])) {
                            foreach ($theFulfilledJson[$row->page - 1] as $thisRowKey => $thisRow) {
                                if (property_exists($thisRow, 'name') && $thisRow->name == $row->name) {
                                    $theFulfilledJson[$row->page - 1][$thisRowKey]->value = $row->value;
                                }
                            }
                        }
                    }
                }
            }

            $isStoreClist = false;

            if (isset($el->parent->parent->checklist_id) && in_array($el->parent->parent->checklist_id, Helper::$storeCheckLists)) {
                $isStoreClist = true;
            }

            if ($isStoreClist) {
                $shoutStartAtTime = date('d-m-Y H:i ', strtotime($el->date));
                //$shoutCompletedByTime = date('d-m-Y H:i', strtotime($el->date));
                $shoutCompletedByTime = date('d-m-Y H:i', strtotime($el->date . ' +23 hours'));


                $theStartTime = (isset($el->parent->parent->start_grace_time) ? date('d-m-Y H:i', strtotime(Helper::addGraceTime(date('d-m-Y H:i:s', strtotime(date('d-m-Y H:i:s', strtotime($el->date)))), $el->parent->parent->start_grace_time))) : null);
                //$theEndTime = isset($el->parent->parent->end_grace_time) ? date('d-m-Y H:i', strtotime(Helper::addGraceTime(date('d-m-Y H:i:s', strtotime(date('d-m-Y H:i:s', strtotime($theStartTime)))), $el->parent->parent->end_grace_time))) : null;
                $theEndTime = date('d-m-Y H:i', strtotime($el->date . ' +23 hours'));
               
            } else {
                $shoutStartAtTime = date('d-m-Y ', strtotime($el->date)) . (isset($el->parent->parent->start_at) ? date('H:i', strtotime($el->parent->parent->start_at)) : '00:00');
                $shoutCompletedByTime = date('d-m-Y ', strtotime($el->date)) . (isset($el->parent->parent->completed_by) ? date('H:i', strtotime($el->parent->parent->completed_by)) : '23:59');

                $theStartTime = (isset($el->parent->parent->start_grace_time) ? date('d-m-Y H:i', strtotime(Helper::addGraceTime(date('d-m-Y H:i:s', strtotime(date('d-m-Y', strtotime($el->date)) . ' ' . (isset($el->parent->parent->start_at) ? $el->parent->parent->start_at : '23:59:59'))), $el->parent->parent->start_grace_time))) : null);
                $theEndTime = isset($el->parent->parent->end_grace_time) ? date('d-m-Y H:i', strtotime(Helper::addGraceTime(date('d-m-Y H:i:s', strtotime(date('d-m-Y', strtotime($el->date)) . ' ' . (isset($el->parent->parent->completed_by) ? $el->parent->parent->completed_by : '23:59:59'))), $el->parent->parent->end_grace_time))) : null;
            }

            return [
                'checklist_task_id' => $el->id,
                'checklist_id' => $el->parent->parent->checklist_id,
                'branch_type' => isset($el->parent->actstore->name) ? $el->parent->actstore->name : '',
                'store_name' => isset($el->parent->actstore->name) ? $el->parent->actstore->name : '',
                'store_code' => isset($el->parent->actstore->code) ? $el->parent->actstore->code : '',
                'store_latitude' => isset($el->parent->actstore->latitude) ? $el->parent->actstore->latitude : '',
                'store_longitude' => isset($el->parent->actstore->longitude) ? $el->parent->actstore->longitude : '',
                'store_id' => isset($el->parent->actstore->id) ? $el->parent->actstore->id : '',
                'branch_id' => $el->parent->branch_id,
                'user' => $el->parent->user,
                'checklist_title' => $el->parent->parent->checklist->name ?? '',
                'code' => $el->code,
                'schema_encoded' => $theFulfilledJson,
                'data' => isset($el->data) ? $el->data : null,
                'status' => $el->status,
                'cancelled' => $el->cancelled,
                'cancellation_reason' => $el->cancellation_reason,
                'is_point_checklist' => Helper::isPointChecklist($versionedForm),
                'status_label' => $statusLabel,
                'check_inout' => $el->submissionentries()->latest()->get()->toArray(),
                
                'reschedulings' => RescheduledTask::where('task_id', $el->id)->latest()->first(),                
                'do_not_allow_late_submission' => boolval($el->parent->parent->do_not_allow_late_submission),
                
                'date' => date('d-m-Y H:i', strtotime($el->date)),
                'should_start_at' => $shoutStartAtTime,
                'should_completed_by' => $shoutCompletedByTime,
                'grace_start_time' => $theStartTime,
                'grace_end_time' => $theEndTime,

                'grace_start' => isset($el->parent->parent->start_grace_time) ? $el->parent->parent->start_grace_time : null,
                'grace_end' => isset($el->parent->parent->end_grace_time) ? $el->parent->parent->end_grace_time : null,

                'should_complete_in' => isset($el->parent->parent->hours_required) ? $el->parent->parent->hours_required : null,
                'time_spent' => $tempTime,
                'remaining_time' => Helper::calculateRemainingTime(isset($el->parent->parent->hours_required) ? $el->parent->parent->hours_required : '00:00:00', $tempTime),
                
                'allow_rescheduling' => intval(isset($el->parent->parent) ? $el->parent->parent->allow_rescheduling : 0),
                'can_reschedule_on_working_day' => boolval($el->parent->parent->allow_double_rescheduling),

                'excel_export' => route('task-export-excel', $el->id),
                'is_checker' => $el->parent->parent->checker_user_id == auth()->user()->id,
                'redo_action' => RedoAction::where('task_id', $el->id)->where('status', 0)->get()->toArray(),
                'pdf_export' => route('task-export-compressed-pdf', $el->id),
                'should_show_generation_button' => PdfGenerationLog::where('status', 1)->where('task_id', $el->id)->doesntExist(),
                'already_requested' => PdfGenerationLog::where('status', 0)->where('task_id', $el->id)->where('user_id', auth()->user()->id)->exists(),
                'pdf_report_link' => asset("storage/task-pdf/task-compressed-{$el->id}.pdf"),

                'new_tickets' => NewTicket::with(['department', 'particular', 'issue', 'creator', 'store', 'owners.user', 'histories.author'])->where('task_id', $el->id)->latest()->get()->map(function ($ticketEl) {
                    return $this->formatTicketResponse($ticketEl);
                }),

                'is_geofencing_enabled' => isset($el->parent->parent->checklist->is_geofencing_enabled) && $el->parent->parent->checklist->is_geofencing_enabled == 1 ? true : false,
                'geofencing_range' => env('GEOFENCE_RANGE', 300)
            ];
        });
        
        $tasks = $tasks->toArray();

        return response()->json(['success' => $tasks, 'total_records' => $taskCount, 'page' => intval($page), 'record_per_page' => $perPage], 200); 
    }

    public function submitImages(Request $request) {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:checklist_tasks,id',
            'data' => 'required'
        ]);

        if ($validator->fails()) { 
            $errorString = implode(",",$validator->messages()->all());
            return response()->json(['error' => $errorString], 401);
        }

        $task = ChecklistTask::find($request->task_id);
        $existingJson = $task->data;
        $originals = $thumbnails = [];
        $mergerArray = [];

        $forImageChecklistId = $task->parent->parent->checklist_id ?? 'NA';
        $forImageTaskId = $task->id ?? 'NA';

        try {

            if (is_string($request->data)) {
                $data = json_decode($request->data, true);
            } else {
                $data = $request->data;
            }
            
            if (!empty($data)) {

                $allOfTheFields = [];
                foreach ($data as $dataKey => $dataRow) {
                    $allOfTheFields[$dataRow['field_id']] = $dataRow;
                }

                foreach ($data as $dataKey => $dataRow) {
                    if (!empty($existingJson)) {
                        foreach ($existingJson as &$row) {
                            if (property_exists($row, 'name') && $row->name == $dataRow['field_id']) {
                                if (isset($allOfTheFields[$dataRow['field_id']])) {
                                    unset($allOfTheFields[$dataRow['field_id']]);
                                }

                                $tmpArr = [];
                                $iterationForLatLong = -1;
                                foreach ($dataRow['values'] as $dt) {
                                    $iterationForLatLong++;
                                    $tempName = ('SIGN-' . date('YmdHis') . uniqid() . '-' . $forImageChecklistId . '-' . $forImageTaskId);
                                    $image = Helper::downloadBase64FileWebp($dt, $tempName, storage_path('app/public/workflow-task-uploads'));

                                    if (is_file(storage_path("app/public/workflow-task-uploads/{$image}"))) {

                                        $loc = isset($dataRow['location'][$iterationForLatLong]) ? $dataRow['location'][$iterationForLatLong] : '';
                                        $loc = explode(',', is_string($loc) ? $loc : '');

                                        $latitude = isset($loc[0]) ? $loc[0] : '';
                                        $longitude = isset($loc[1]) ? $loc[1] : '';

                                        \App\Jobs\AddMetaDataToImage::dispatch([
                                            'task_id' => $request->task_id,
                                            'timestamp' => isset($dataRow['timestamp'][$iterationForLatLong]) ? $dataRow['timestamp'][$iterationForLatLong] : null,
                                            'latitude' => $latitude,
                                            'longitude' => $longitude,
                                            'field_name' => $dataRow['field_id'] ?? '',
                                            'path' => storage_path("app/public/workflow-task-uploads/{$image}")
                                        ]);

                                        $imagePngWebpThumb = str_replace(".webp", ".png", $image);
                                        $img2 = Helper::createImageThumbnail(storage_path("app/public/workflow-task-uploads/{$image}"), storage_path("app/public/workflow-task-uploads-thumbnails/{$imagePngWebpThumb}"), 200, 200);
                                        if ($img2 && is_file(storage_path("app/public/workflow-task-uploads-thumbnails/{$imagePngWebpThumb}"))) {

                                            if (isset($thumbnails[$row->name])) {
                                                $thumbnails[$row->name]['values'][] = $image;
                                            } else {
                                                $thumbnails[$row->name]['field_name'] = $row->name;
                                                $thumbnails[$row->name]['values'][] = $image;
                                            }

                                        } else {
                                            return response()->json(['error' => "Error occured while generating thumbnail"]);
                                        }
                                    } else {
                                        return response()->json(['error' => "Error occured while generating image"]);
                                    }

                                    $tmpArr[] = $image;
                                }

                                if (is_string($row->value) && !empty($row->value)) {
                                    array_push($tmpArr, $row->value);
                                } else if (is_array($row->value) && !empty($row->value)) {
                                    $tmpArr = array_merge($tmpArr, $row->value);
                                }
                                
                                $row->value = $tmpArr;

                                if (isset($dataRow['timestamp'])) {
                                    $row->timestamp = $dataRow['timestamp'];
                                }

                                if (isset($dataRow['location'])) {
                                    $row->location = $dataRow['location'];
                                }

                                $row->value = $tmpArr;
                            }
                        }
                    }
                }

                $allOfTheFields = array_filter($allOfTheFields);
                if (!empty($allOfTheFields)) {

                    foreach ($allOfTheFields as $line) {

                        $tmpArr = [];
                        $iterationForLatLong = -1;
                        foreach ($line['values'] as $dt) {
                            $iterationForLatLong++;
                            $tempName = ('SIGN-' . date('YmdHis') . uniqid() . '-' . $forImageChecklistId . '-' . $forImageTaskId);
                            $image = Helper::downloadBase64FileWebp($dt, $tempName, storage_path('app/public/workflow-task-uploads'));

                            if (is_file(storage_path("app/public/workflow-task-uploads/{$image}"))) {

                                $loc = isset($line['location'][$iterationForLatLong]) ? $line['location'][$iterationForLatLong] : '';
                                $loc = explode(',', is_string($loc) ? $loc : '');

                                $latitude = isset($loc[0]) ? $loc[0] : '';
                                $longitude = isset($loc[1]) ? $loc[1] : '';

                                \App\Jobs\AddMetaDataToImage::dispatch([
                                    'task_id' => $request->task_id,
                                    'timestamp' => isset($line['timestamp'][$iterationForLatLong]) ? $line['timestamp'][$iterationForLatLong] : null,
                                    'latitude' => $latitude,
                                    'longitude' => $longitude,
                                    'field_name' => $line['field_id'] ?? '',
                                    'path' => storage_path("app/public/workflow-task-uploads/{$image}")
                                ]);

                                $imagePngWebpThumb = str_replace(".webp", ".png", $image);
                                $img2 = Helper::createImageThumbnail(storage_path("app/public/workflow-task-uploads/{$image}"), storage_path("app/public/workflow-task-uploads-thumbnails/{$imagePngWebpThumb}"), 200, 200);
                                if ($img2 && is_file(storage_path("app/public/workflow-task-uploads-thumbnails/{$imagePngWebpThumb}"))) {
                                    if (isset($thumbnails[$line['field_id']])) {
                                        $thumbnails[$line['field_id']]['values'][] = $image;
                                    } else {
                                        $thumbnails[$line['field_id']]['field_name'] = $line['field_id'];
                                        $thumbnails[$line['field_id']]['values'][] = $image;
                                    }

                                } else {
                                    return response()->json(['error' => "Error occured while generating thumbnail"]);
                                }
                            } else {
                                return response()->json(['error' => "Error occured while generating image"]);
                            }

                            $tmpArr[] = $image;
                        }

                        $mergerArray[] = (object)[
                            "className" => $line['className'],
                            "page" => $line['page'],
                            "index" => $line['index'],
                            "label" => $line['label'],
                            "timestamp" => isset($line['timestamp']) ? $line['timestamp'] : null,
                            "location" => isset($line['location']) ? $line['location'] : null,
                            "name" => $line['field_id'],
                            "value" => $tmpArr,
                            "isFile" => true
                        ];
                    }
                }

                if (empty($existingJson) || $existingJson == '{}') {
                    $task->data = $mergerArray;
                } else {
                    if (!is_array($existingJson)) {
                        $existingJson = (array) $existingJson;
                    }

                    $tmpAr = array_merge($mergerArray, $existingJson);
                    $task->data = array_filter($tmpAr);
                }

                $task->status = $task->status == 0 ? 1 : $task->status;
                $task->save();

                $tempJsonForReindexing = $task->data;

                usort($tempJsonForReindexing, function ($a, $b) {
                    $pageComparison = (int)$a->page <=> (int)$b->page;
                    
                    if ($pageComparison === 0) {
                        $aIndex = isset($a->index) ? (int)$a->index : PHP_INT_MAX;
                        $bIndex = isset($b->index) ? (int)$b->index : PHP_INT_MAX;

                        return $aIndex <=> $bIndex;
                    }

                    return $pageComparison;
                });

                $task->data = $tempJsonForReindexing;
                $task->save();

                return response()->json(['success' => "Image uploaded successfully", "thumbnails" => array_values($thumbnails)]);
            } else {
                return response()->json(['error' => "No JSON Found"]);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'line' => $e->getLine()]);
        }
    }

    public function refreshTaskListing(Request $request) {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:checklist_tasks,id'
        ]);

        if ($validator->fails()) { 
            $errorString = implode(",",$validator->messages()->all());
            return response()->json(['error' => $errorString], 401);
        }
        $data = [];

        foreach ($request->ids as $task) {
            $taskELoquent = ChecklistTask::withTrashed()->selectRaw('id, cancelled, status, deleted_at')->where('id', $task)->first();
            $shouldKeep = true;

            if (!empty($taskELoquent->deleted_at)) {
                $shouldKeep = false;
            } else if ($taskELoquent->status == 3) {
                $shouldKeep = false;
            } else if ($taskELoquent->cancelled == 1) {
                $shouldKeep = false;
            }

            $data[] = [
                'task_id' => intval($task),
                'status' => intval($shouldKeep)
            ];
        }

        return response()->json(['success' => $data]);
    }

    public function generatePdfReport(Request $request) {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:checklist_tasks,id',
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) { 
            $errorString = implode(",",$validator->messages()->all());
            return response()->json(['error' => $errorString], 401);
        }

        PdfGenerationLog::create([
            'user_id' => $request->user_id,
            'task_id' => $request->task_id,
            'keep_till' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'status' => 0
        ]);

        return response()->json(['success' => 'You task report generation request has been sent, you will get a notification once it\'s genrated']);
    }

    public function getNotifications(Request $request) {
        return response()->json([
            'success' => SystemNotification::where('user_id', auth()->user()->id)->limit(25)->get()
        ]);
    }

    public function particulars(Request $request) {
        $query = Particular::query();

        $status = strtolower((string) $request->status);
        if (in_array($status, ['active', 'inactive'])) {
            $query->where('status', $status === 'active' ? 1 : 0);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $data = $query->orderBy('name')->get();
        return response()->json(['success' => $data]);
    }

    public function issues(Request $request) {
        $query = Issue::query();

        $status = strtolower((string) $request->status);
        if (in_array($status, ['active', 'inactive'])) {
            $query->where('status', $status === 'active' ? 1 : 0);
        }

        if ($request->filled('particular_id')) {
            $query->where('particular_id', $request->particular_id);
        }

        $data = $query->orderBy('name')->get();
        return response()->json(['success' => $data]);
    }

    public function createTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_id' => ['required', 'exists:departments,id'],
            'particular_id' => [
                'required',
                Rule::exists('particulars', 'id')->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department_id);
                }),
            ],
            'issue_id' => [
                'required',
                Rule::exists('issues', 'id')->where(function ($query) use ($request) {
                    return $query->where('particular_id', $request->particular_id);
                }),
            ],
            'store_id' => ['required', 'exists:stores,id'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'attachments.*' => ['nullable'],
            'extra_users' => ['nullable', 'array'],
            'extra_users.*' => ['exists:users,id'],
        ]);

        if ($validator->fails()) {
            $errorString = implode(", ", $validator->messages()->all());
            return response()->json(['error' => $errorString], 422);
        }

        try {
            DB::beginTransaction();

            $attachments = $this->storeTicketAttachments($request->file('attachments', []));

            $ticket = NewTicket::create([
                'department_id' => $request->department_id,
                'particular_id' => $request->particular_id,
                'issue_id' => $request->issue_id,
                'store_id' => $request->store_id,
                'subject' => $request->subject,
                'description' => $request->description,
                'attachments' => $attachments,
                'priority' => $request->priority,
                'task_id' => $request->task_id,
                'field_name' => $request->field_name,
                'created_by' => auth()->id(),
            ]);

            foreach (UserIssue::where('issue_id', $request->issue_id)->get() as $userIssue) {
                NewTicketOwner::create([
                    'new_ticket_id' => $ticket->id,
                    'owner_id' => $userIssue->user_id,
                    'assigned_by' => auth()->id(),
                    'is_primary' => true,
                ]);
            }

            if (!empty($request->extra_users) && is_array($request->extra_users)) {
                foreach ($request->extra_users as $extraUserId) {
                    NewTicketOwner::create([
                        'new_ticket_id' => $ticket->id,
                        'owner_id' => $extraUserId,
                        'is_primary' => false,
                        'assigned_by' => auth()->id(),
                    ]);
                }
            }

            NewTicketHistory::create([
                'new_ticket_id' => $ticket->id,
                'description' => 'Ticket created',
                'data' => [
                    'type' => 'created',
                    'user_id' => auth()->id(),
                    'priority' => $request->priority,
                    'description' => $request->description,
                    'owners_included' => $request->extra_users ?? []
                ],
                'attachments' => $attachments,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            $ticket->load(['department', 'particular', 'issue', 'store', 'creator', 'owners.user', 'histories.author']);

            return response()->json([
                'success' => 'Ticket created successfully.',
                'data' => $this->formatTicketResponse($ticket)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create ticket: ' . $e->getMessage()], 500);
        }
    }

    public function tickets(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);
        $tab = $request->get('status');

        $query = NewTicket::query()
            ->with(['department', 'particular', 'issue', 'creator', 'store', 'owners.user', 'histories.author'])
            ->when(!empty($tab), function ($builder) use ($tab) {
                $builder->where('status', $tab);
            })
            ->when($request->filled('ticket_id'), function ($builder) {
                $builder->where('id', request('ticket_id'));
            })
            ->when($request->filled('department_id'), function ($builder) {
                $builder->where('department_id', request('department_id'));
            })
            ->when($request->filled('particular_id'), function ($builder) {
                $builder->where('particular_id', request('particular_id'));
            })
            ->when($request->filled('issue_id'), function ($builder) {
                $builder->where('issue_id', request('issue_id'));
            })
            ->when($request->filled('location'), function ($builder) {
                $builder->where('store_id', request('location'));
            })
            ->when($request->filled('assigned'), function ($builder) {
                $builder->whereHas('owners', function ($innerBuilder) {
                    $innerBuilder->where('owner_id', request('assigned'));
                });
            })
            ->when($request->filled('created_from'), function ($builder) {
                $builder->whereDate('created_at', '>=', date('Y-m-d', strtotime(request('created_from'))));
            })
            ->when($request->filled('created_to'), function ($builder) {
                $builder->whereDate('created_at', '<=', date('Y-m-d', strtotime(request('created_to'))));
            })
            ->when($request->filled('created_by'), function ($builder) {
                $builder->where('created_by', request('created_by'));
            })
            ->when($request->filled('status'), function ($builder) {
                $builder->where('status', request('status'));
            })
            ->when($request->filled('priority'), function ($builder) {
                $builder->where('priority', request('priority'));
            })
            ->orderBy('created_at', 'desc');

        $total = $query->count();
        $tickets = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        $formattedTickets = $tickets->map(function ($ticket) {
            return $this->formatTicketResponse($ticket);
        });

        return response()->json([
            'success' => true,
            'data' => $formattedTickets,
            'pagination' => [
                'current_page' => (int) $page,
                'per_page' => (int) $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ]
        ], 200);
    }

    public function acceptTicket(Request $request, $id)
    {
        try {
            $ticket = NewTicket::findOrFail($id);

            if ($ticket->status !== NewTicket::STATUS_PENDING) {
                return response()->json([
                    'error' => 'Only pending tickets can be accepted.'
                ], 422);
            }

            DB::beginTransaction();

            $ticket->update([
                'status' => NewTicket::STATUS_ACCEPTED,
            ]);

            if (is_array($request->extra_users)) {
                $toKeepIds = [];

                foreach ($request->extra_users as $userId) {
                    $record = NewTicketOwner::withTrashed()->updateOrCreate([
                        'new_ticket_id' => $ticket->id,
                        'owner_id' => $userId,
                        'is_primary' => false,
                    ], [
                        'assigned_by' => auth()->id(),
                    ]);

                    if (method_exists($record, 'trashed') && $record->trashed()) {
                        $record->restore();
                    }

                    $toKeepIds[] = $record->id;
                }

                if (!empty($toKeepIds)) {
                    NewTicketOwner::where('new_ticket_id', $ticket->id)
                        ->where('is_primary', false)
                        ->whereNotIn('id', $toKeepIds)
                        ->delete();
                } else {
                    NewTicketOwner::where('new_ticket_id', $ticket->id)
                        ->where('is_primary', false)
                        ->delete();
                }
            }

            NewTicketHistory::create([
                'new_ticket_id' => $ticket->id,
                'description' => 'Ticket accepted.',
                'data' => [
                    'type' => 'accepted',
                    'user_id' => auth()->id(),
                    'owners_included' => $request->extra_users ?? []
                ],
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            $ticket->load(['department', 'particular', 'issue', 'creator', 'store', 'owners.user', 'histories.author']);

            return response()->json([
                'success' => 'Ticket accepted successfully.',
                'data' => $this->formatTicketResponse($ticket)
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Ticket not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to accept ticket: ' . $e->getMessage()], 500);
        }
    }

    public function inprogressTicket(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reply' => ['required', 'string'],
            'attachments.*' => ['nullable'],
            'extra_users' => ['nullable', 'array'],
            'extra_users.*' => ['exists:users,id'],
        ]);

        if ($validator->fails()) {
            $errorString = implode(", ", $validator->messages()->all());
            return response()->json(['error' => $errorString], 422);
        }

        try {
            $ticket = NewTicket::findOrFail($id);
            $oldStatus = $ticket->status;

            if ($ticket->status == NewTicket::STATUS_ACCEPTED) {

                DB::beginTransaction();

                $attachments = $this->storeTicketAttachments($request->file('attachments', []));

                $ticket->update([
                    'status' => NewTicket::STATUS_IN_PROGRESS,
                    'is_reopened' => true,
                    'in_progress_at' => now(),
                ]);

                $toKeepEU = [];
                if (!empty($request->extra_users)) {
                    foreach ($request->extra_users as $extraUserId) {
                        $toKeepEU[] = NewTicketOwner::updateOrCreate([
                            'new_ticket_id' => $ticket->id,
                            'owner_id' => $extraUserId,
                            'is_primary' => false
                        ], [
                            'assigned_by' => auth()->id(),
                        ])->id;
                    }
                }

                if (NewTicket::STATUS_IN_PROGRESS != $oldStatus) {
                    NewTicketEscalationExecution::where('ticket_id', $ticket->id)->delete();

                    $ticket->update([
                        'in_progress_at' => now(),
                    ]);
                }

                if (!empty($toKeepEU)) {
                    NewTicketOwner::where('is_primary', false)
                        ->where('new_ticket_id', $ticket->id)
                        ->whereNotIn('id', $toKeepEU)
                        ->delete();
                } else {
                    NewTicketOwner::where('is_primary', false)
                        ->where('new_ticket_id', $ticket->id)
                        ->delete();
                }

                NewTicketHistory::create([
                    'new_ticket_id' => $ticket->id,
                    'description' => 'Ticket status changed to in-progress',
                    'data' => [
                        'type' => 'reply',
                        'user_id' => auth()->id(),
                        'status' => NewTicket::STATUS_IN_PROGRESS,
                        'description' => $request->reply,
                        'owners_included' => $request->extra_users ?? []
                    ],
                    'attachments' => $attachments,
                    'created_by' => auth()->id(),
                ]);

                DB::commit();

            } else if ($ticket->status == NewTicket::STATUS_CLOSED && $request->make_inprogress == 1) {

                DB::beginTransaction();

                $attachments = $this->storeTicketAttachments($request->file('attachments', []));

                $ticket->update([
                    'status' => NewTicket::STATUS_IN_PROGRESS,
                    'is_reopened' => true,
                    'in_progress_at' => now(),
                ]);

                $toKeepEU = [];
                if (!empty($request->extra_users)) {
                    foreach ($request->extra_users as $extraUserId) {
                        $toKeepEU[] = NewTicketOwner::updateOrCreate([
                            'new_ticket_id' => $ticket->id,
                            'owner_id' => $extraUserId,
                            'is_primary' => false
                        ], [
                            'assigned_by' => auth()->id(),
                        ])->id;
                    }
                }

                if (!empty($toKeepEU)) {
                    NewTicketOwner::where('is_primary', false)
                        ->where('new_ticket_id', $ticket->id)
                        ->whereNotIn('id', $toKeepEU)
                        ->delete();
                } else {
                    NewTicketOwner::where('is_primary', false)
                        ->where('new_ticket_id', $ticket->id)
                        ->delete();
                }

                NewTicketHistory::create([
                    'new_ticket_id' => $ticket->id,
                    'description' => 'Ticket re-opened and set to in_progress',
                    'data' => [
                        'type' => 'reply',
                        'user_id' => auth()->id(),
                        'status' => NewTicket::STATUS_IN_PROGRESS,
                        'description' => $request->reply,
                        'reopened' => true,
                        'owners_included' => $request->extra_users ?? []
                    ],
                    'attachments' => $attachments,
                    'created_by' => auth()->id(),
                ]);

                DB::commit();

            } else if ($ticket->status == NewTicket::STATUS_IN_PROGRESS) {

                DB::beginTransaction();

                $attachments = $this->storeTicketAttachments($request->file('attachments', []));

                $toKeepEU = [];
                if (!empty($request->extra_users)) {
                    foreach ($request->extra_users as $extraUserId) {
                        $toKeepEU[] = NewTicketOwner::updateOrCreate([
                            'new_ticket_id' => $ticket->id,
                            'owner_id' => $extraUserId,
                            'is_primary' => false
                        ], [
                            'assigned_by' => auth()->id(),
                        ])->id;
                    }
                }

                if (!empty($toKeepEU)) {
                    NewTicketOwner::where('is_primary', false)
                        ->where('new_ticket_id', $ticket->id)
                        ->whereNotIn('id', $toKeepEU)
                        ->delete();
                } else {
                    NewTicketOwner::where('is_primary', false)
                        ->where('new_ticket_id', $ticket->id)
                        ->delete();
                }

                NewTicketHistory::create([
                    'new_ticket_id' => $ticket->id,
                    'description' => 'Comment added to ticket',
                    'data' => [
                        'type' => 'reply',
                        'user_id' => auth()->id(),
                        'status' => NewTicket::STATUS_IN_PROGRESS,
                        'description' => $request->reply,
                        'owners_included' => $request->extra_users ?? []
                    ],
                    'attachments' => $attachments,
                    'created_by' => auth()->id(),
                ]);

                DB::commit();

            } else {
                return response()->json([
                    'error' => 'Something went wrong.'
                ], 422);
            }

            $ticket->load(['department', 'particular', 'issue', 'creator', 'store', 'owners.user', 'histories.author']);

            return response()->json([
                'success' => 'Ticket In-Progress successfully.',
                'data' => $this->formatTicketResponse($ticket)
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Ticket not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to reopen ticket: ' . $e->getMessage()], 500);
        }
    }

    public function closeTicket(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reply' => ['required', 'string'],
            'attachments.*' => ['nullable'],
            'extra_users' => ['nullable', 'array'],
            'extra_users.*' => ['exists:users,id'],
        ]);

        if ($validator->fails()) {
            $errorString = implode(", ", $validator->messages()->all());
            return response()->json(['error' => $errorString], 422);
        }

        try {
            $ticket = NewTicket::findOrFail($id);

            if ($ticket->status == NewTicket::STATUS_IN_PROGRESS) {

                DB::beginTransaction();

                $attachments = $this->storeTicketAttachments($request->file('attachments', []));

                $ticket->update([
                    'status' => NewTicket::STATUS_CLOSED
                ]);

                $toKeepEU = [];
                if (!empty($request->extra_users)) {
                    foreach ($request->extra_users as $extraUserId) {
                        $toKeepEU[] = NewTicketOwner::updateOrCreate([
                            'new_ticket_id' => $ticket->id,
                            'owner_id' => $extraUserId,
                            'is_primary' => false
                        ], [
                            'assigned_by' => auth()->id(),
                        ])->id;
                    }
                }

                if (!empty($toKeepEU)) {
                    NewTicketOwner::where('is_primary', false)
                        ->where('new_ticket_id', $ticket->id)
                        ->whereNotIn('id', $toKeepEU)
                        ->delete();
                } else {
                    NewTicketOwner::where('is_primary', false)
                        ->where('new_ticket_id', $ticket->id)
                        ->delete();
                }

                NewTicketHistory::create([
                    'new_ticket_id' => $ticket->id,
                    'description' => 'Ticket closed',
                    'data' => [
                        'type' => 'reply',
                        'user_id' => auth()->id(),
                        'status' => NewTicket::STATUS_CLOSED,
                        'description' => $request->reply,
                        'owners_included' => $request->extra_users ?? []
                    ],
                    'attachments' => $attachments,
                    'created_by' => auth()->id(),
                ]);

                DB::commit();

            } else if ($ticket->status == NewTicket::STATUS_CLOSED) {

                DB::beginTransaction();

                $attachments = $this->storeTicketAttachments($request->file('attachments', []));

                $toKeepEU = [];
                if (!empty($request->extra_users)) {
                    foreach ($request->extra_users as $extraUserId) {
                        $toKeepEU[] = NewTicketOwner::updateOrCreate([
                            'new_ticket_id' => $ticket->id,
                            'owner_id' => $extraUserId,
                            'is_primary' => false
                        ], [
                            'assigned_by' => auth()->id(),
                        ])->id;
                    }
                }

                if (!empty($toKeepEU)) {
                    NewTicketOwner::where('is_primary', false)
                        ->where('new_ticket_id', $ticket->id)
                        ->whereNotIn('id', $toKeepEU)
                        ->delete();
                } else {
                    NewTicketOwner::where('is_primary', false)
                        ->where('new_ticket_id', $ticket->id)
                        ->delete();
                }

                NewTicketHistory::create([
                    'new_ticket_id' => $ticket->id,
                    'description' => 'Ticket status changed to closed',
                    'data' => [
                        'type' => 'reply',
                        'user_id' => auth()->id(),
                        'is_closed' => 1,
                        'status' => NewTicket::STATUS_CLOSED,
                        'description' => $request->reply,
                        'owners_included' => $request->extra_users ?? []
                    ],
                    'attachments' => $attachments,
                    'created_by' => auth()->id(),
                ]);

                DB::commit();

            } else {
                return response()->json([
                    'error' => 'Something went wrong.'
                ], 422);
            }

            $ticket->load(['department', 'particular', 'issue', 'creator', 'store', 'owners.user', 'histories.author']);

            return response()->json([
                'success' => 'Ticket closed successfully.',
                'data' => $this->formatTicketResponse($ticket)
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Ticket not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to reopen ticket: ' . $e->getMessage()], 500);
        }
    }

    public function reopenTicket(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reply' => ['required', 'string'],
            'attachments.*' => ['nullable'],
            'extra_users' => ['nullable', 'array'],
            'extra_users.*' => ['exists:users,id'],
        ]);

        if ($validator->fails()) {
            $errorString = implode(", ", $validator->messages()->all());
            return response()->json(['error' => $errorString], 422);
        }

        try {
            $ticket = NewTicket::findOrFail($id);

            if ($ticket->status !== NewTicket::STATUS_CLOSED) {
                return response()->json([
                    'error' => 'Only closed tickets can be reopened.'
                ], 422);
            }

            DB::beginTransaction();

            $attachments = $this->storeTicketAttachments($request->file('attachments', []));

            $ticket->update([
                'status' => NewTicket::STATUS_PENDING,
                'is_reopened' => true,
            ]);

            $toKeepEU = [];
            if (!empty($request->extra_users)) {
                foreach ($request->extra_users as $extraUserId) {
                    $toKeepEU[] = NewTicketOwner::updateOrCreate([
                        'new_ticket_id' => $ticket->id,
                        'owner_id' => $extraUserId,
                        'is_primary' => false
                    ], [
                        'assigned_by' => auth()->id(),
                    ])->id;
                }
            }

            if (!empty($toKeepEU)) {
                NewTicketOwner::where('is_primary', false)
                    ->where('new_ticket_id', $ticket->id)
                    ->whereNotIn('id', $toKeepEU)
                    ->delete();
            } else {
                NewTicketOwner::where('is_primary', false)
                    ->where('new_ticket_id', $ticket->id)
                    ->delete();
            }

            NewTicketHistory::create([
                'new_ticket_id' => $ticket->id,
                'description' => 'Ticket re-opened',
                'data' => [
                    'type' => 'reply',
                    'user_id' => auth()->id(),
                    'status' => NewTicket::STATUS_PENDING,
                    'reopened' => true,
                    'description' => $request->reply,
                    'owners_included' => $request->extra_users ?? []
                ],
                'attachments' => $attachments,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            $ticket->load(['department', 'particular', 'issue', 'creator', 'store', 'owners.user', 'histories.author']);

            return response()->json([
                'success' => 'Ticket reopened successfully.',
                'data' => $this->formatTicketResponse($ticket)
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Ticket not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to reopen ticket: ' . $e->getMessage()], 500);
        }
    }

    public function replyTicket(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'reply' => ['required', 'string'],
            'attachments.*' => ['nullable'],
            'extra_users' => ['nullable', 'array'],
            'extra_users.*' => ['exists:users,id'],
        ]);

        if ($validator->fails()) {
            $errorString = implode(", ", $validator->messages()->all());
            return response()->json(['error' => $errorString], 422);
        }

        try {
            $ticket = NewTicket::findOrFail($id);

            DB::beginTransaction();

            $attachments = $this->storeTicketAttachments($request->file('attachments', []));

            $toKeepEU = [];
            if (!empty($request->extra_users)) {
                foreach ($request->extra_users as $extraUserId) {
                    $toKeepEU[] = NewTicketOwner::updateOrCreate([
                        'new_ticket_id' => $ticket->id,
                        'owner_id' => $extraUserId,
                        'is_primary' => false
                    ], [
                        'assigned_by' => auth()->id(),
                    ])->id;
                }
            }

            if (!empty($toKeepEU)) {
                NewTicketOwner::where('is_primary', false)
                    ->where('new_ticket_id', $ticket->id)
                    ->whereNotIn('id', $toKeepEU)
                    ->delete();
            } else {
                NewTicketOwner::where('is_primary', false)
                    ->where('new_ticket_id', $ticket->id)
                    ->delete();
            }

            NewTicketHistory::create([
                'new_ticket_id' => $ticket->id,
                'description' => 'Comment added',
                'data' => [
                    'type' => 'reply',
                    'user_id' => auth()->id(),
                    'status' => $ticket->status,
                    'description' => $request->reply,
                    'owners_included' => $request->extra_users ?? []
                ],
                'attachments' => $attachments,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            $ticket->load(['department', 'particular', 'issue', 'creator', 'store', 'owners.user', 'histories.author']);

            return response()->json([
                'success' => 'Reply added to successfully.',
                'data' => $this->formatTicketResponse($ticket)
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Ticket not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to reopen ticket: ' . $e->getMessage()], 500);
        }
    }

    protected function storeTicketAttachments(array $files): array
    {
        if (empty($files)) {
            return [];
        }

        $paths = [];

        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $paths[] = $file->store('new-ticket-attachments', 'public');
            }
        }

        return $paths;
    }

    protected static function formatTicketResponse(NewTicket $ticket): array
    {
        try {
            $this_user_can_accept = TicketRoleUser::where('ticket_role_id', 1)->where('user_id', auth()->user()->id)->exists()
            || auth()->user()->isAdmin() ? 1 : 0;
            $this_user_can_edit = TicketRoleUser::where('ticket_role_id', 1)->where('user_id', auth()->user()->id)->exists()
            || auth()->user()->isAdmin() || NewTicketOwner::where('new_ticket_id', $ticket->id)->where('owner_id', auth()->user()->id)->exists() ? 1 : 0;
            $this_user_can_reopen = $ticket->created_by == auth()->user()->id ? 1 : 0;

            $attachments = [];
            if (!empty($ticket->attachments)) {
                foreach ($ticket->attachments as $attachment) {
                    $attachments[] = asset('storage/' . $attachment);
                }
            }

            $theEsc = \App\Models\TicketEscalation::where('department_id', $ticket->department_id)
            ->where('particular_id', $ticket->particular_id)
            ->where('issue_id', $ticket->issue_id)
            ->first();

            $escalation_level_1_tat = isset($theEsc->id) && is_numeric($theEsc->level1_hours) && $theEsc->level1_hours > 0 ? $theEsc->level1_hours : 0;
            $escalation_level_2_tat = isset($theEsc->id) && is_numeric($theEsc->level2_hours) && $theEsc->level2_hours > 0 ? $theEsc->level2_hours : 0;


            $level1RemainingHours = self::remainingTatHM($ticket->in_progress_at, $escalation_level_1_tat);
            $level2RemainingHours = self::remainingTatHM($ticket->in_progress_at, $escalation_level_2_tat);

            return [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'department_id' => $ticket->department_id,
                'department' => $ticket->department ? $ticket->department->name : null,
                'particular_id' => $ticket->particular_id,
                'particular' => $ticket->particular ? $ticket->particular->name : null,
                'issue_id' => $ticket->issue_id,
                'task_id' => $ticket->task_id,
                'field_name' => $ticket->field_name,
                'issue' => $ticket->issue ? $ticket->issue->name : null,

                'this_user_can_accept' => $this_user_can_accept,
                'this_user_can_reopen' => $this_user_can_reopen,
                'this_user_can_edit' => $this_user_can_edit,

                'store_id' => $ticket->store_id,
                'store' => $ticket->store ? [
                    'id' => $ticket->store->id,
                    'code' => $ticket->store->code,
                    'name' => $ticket->store->name,
                ] : null,
                'subject' => $ticket->subject,
                'description' => $ticket->description,
                'priority' => $ticket->priority,
                'priority_label' => $ticket->priorityLabel,
                'status' => $ticket->status,
                'status_label' => $ticket->statusLabel,
                'is_reopened' => $ticket->is_reopened,
                'attachments' => $attachments,
                'created_by' => $ticket->creator ? [
                    'id' => $ticket->creator->id,
                    'name' => trim($ticket->creator->name . ' ' . $ticket->creator->middle_name . ' ' . $ticket->creator->last_name),
                ] : null,
                'owners' => $ticket->owners->map(function ($owner) {
                    return [
                        'id' => $owner->id,
                        'user_id' => $owner->owner_id,
                        'user' => $owner->user ? [
                            'id' => $owner->user->id,
                            'name' => trim($owner->user->name . ' ' . $owner->user->middle_name . ' ' . $owner->user->last_name),
                        ] : null,
                        'is_primary' => $owner->is_primary,
                    ];
                }),
                'histories' => $ticket->histories->map(function ($history) {
                    $type = $history->type ?? 'reply';
                    $historyAttachments = [];
                    
                    if (!empty($history->attachments)) {
                        foreach ($history->attachments as $attachment) {
                            $historyAttachments[] = asset('storage/' . $attachment);
                        }
                    }
                    
                    $authorName = optional($history->author)->name ?: 'System';
                    $authorFullName = $history->author ? trim($history->author->name . ' ' . $history->author->middle_name . ' ' . $history->author->last_name) : 'System';
                    
                    $action = 'replied';
                    if ($type === 'created') {
                        $action = 'created this ticket';
                    } elseif ($type === 'accepted') {
                        $action = 'accepted this ticket';
                    } elseif (isset($history->data['reopened']) && $history->data['reopened']) {
                        $action = 'reopened this ticket';
                    }
                    
                    return [
                        'id' => $history->id,
                        'type' => $type,
                        'description' => $history->description,
                        'action' => $action,
                        'author' => [
                            'id' => $history->author ? $history->author->id : null,
                            'name' => $authorFullName,
                        ],
                        'attachments' => $historyAttachments,
                        'data' => $history->data ?? [],
                        'created_at' => $history->created_at ? $history->created_at->format('Y-m-d H:i:s') : null,
                        'created_at_formatted' => $history->created_at ? $history->created_at->format('d M Y H:i') : null,
                        'time_display' => $history->created_at ? $history->created_at->format('H:i') : null,
                        'time_ago' => $history->created_at ? $history->created_at->diffForHumans() : null,
                    ];
                })->sortByDesc('created_at')->values(),
                'created_at' => $ticket->created_at ? $ticket->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $ticket->updated_at ? $ticket->updated_at->format('Y-m-d H:i:s') : null,
                'escalation_level_1_exists' => isset($theEsc->id) && is_numeric($theEsc->level1_hours) && $theEsc->level1_hours > 0 ? true : false,
                'escalation_level_2_exists' => isset($theEsc->id) && is_numeric($theEsc->level2_hours) && $theEsc->level2_hours > 0 ? true : false,

                'escalation_level_1_tat' => $escalation_level_1_tat,
                'escalation_level_2_tat' => $escalation_level_2_tat,

                'in_progress_at' => $ticket->in_progress_at,

                'escalation_level_1_deadline' => $level1RemainingHours,
                'escalation_level_2_deadline' => $level2RemainingHours,

                'users_to_show' => true
            ];

        } catch (\Exception $e) {
            return [];
        }
    }

    public static function remainingTatHM($startAt, $tatHours)
    {
        if (!$startAt || $tatHours <= 0) {
            return '00:00';
        }

        $start = Carbon::parse($startAt);
        $deadline = $start->copy()->addMinutes($tatHours * 60);
        $now = Carbon::now();

        if ($now->gte($deadline)) {
            return '00:00';
        }

        $totalMinutes = $now->diffInMinutes($deadline);

        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' .
            str_pad($minutes, 2, '0', STR_PAD_LEFT);
    }

    public function users(Request $request) {
        $fixRoles = [
            1, 6, 10, 11, 12, 13
        ];

        $data = User::select('id', 'name', 'middle_name', 'last_name', 'email', 'employee_id')
            ->when($request->filled('role_id'), function ($query) use ($request, $fixRoles) {
                $query->whereHas('roles', function ($roleQuery) use ($request, $fixRoles) {
                    $roleQuery->whereIn('id', $fixRoles);
                });
            })
            ->where('status', 1)
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => trim($user->name . ' ' . $user->middle_name . ' ' . $user->last_name),
                    'email' => $user->email,
                    'employee_id' => $user->employee_id,
                ];
            });

        return response()->json(['success' => $data]);
    }

    public function dataWebView(Request $request) {
        $id = $request->id;
        $task = ChecklistTask::find($id);
        return response()->json(['status' => true, 'data' => view('tasks.submission-web-view', compact('task', 'id'))->render()]);
    }

    public function taskStatus(Request $request) {
        return response()->json([
            'success' => [
                'PENDING', 'IN_PROGRESS', 'COMPLETED','PENDING_VERIFICATION','VERIFIED'
            ]
        ]);
    }

    public function taskProgres(Request $request) {
        $page = $request->page > -1 ? $request->page : 0;
        $perPage = $request->record_per_page > 0 ? $request->record_per_page : 5;
        $skip = $page * $perPage;

        $filterFrom = date('Y-m-d H:i:s', strtotime($request->from));
        $filterTo = date('Y-m-d H:i:s', strtotime($request->to));

        $tasks = ChecklistTask::with(['restasks', 'submissionentries', 'redos', 'parent.parent', 'parent.user' => function ($builder) {
            return $builder->withTrashed();
        }])
        ->when(auth()->check(), function ($inBldr) {
            $currentUserRole = isset(auth()->user()->roles[0]->id) ? auth()->user()->roles[0]->id : 0;

            if ($currentUserRole == 1) {
                $inBldr->whereNotNull('id');
            } else if ($currentUserRole == 10) {
                $opsStores = \App\Models\OperationManager::where('ops_id', auth()->user()->id)->pluck('dom_id')->toArray();

                $inBldr->whereHas('parent.actstore', function ($query) use ($opsStores) {
                    $query->where('dom_id', auth()->user()->id)
                    ->orWhereIn('dom_id', $opsStores);
                });
            } else if ($currentUserRole == 6) {
                $opsStores = \App\Models\OperationManager::where('ops_id', auth()->user()->id)->pluck('dom_id')->toArray();

                $inBldr->whereHas('parent.actstore', function ($query) use ($opsStores) {
                    $query->where('dom_id', auth()->user()->id)
                    ->orWhereIn('dom_id', $opsStores);
                });
            } else if ($currentUserRole == 11) {
                $inBldr->whereHas('parent.actstore', function ($query) {
                    $query->where('code', auth()->user()->employee_id);
                });
            } else {
                $inBldr->where(function ($innerBuilder) {
                    $innerBuilder->whereHas('parent.parent', function ($query) {
                        $query->where('checker_user_id', auth()->user()->id);
                    })
                    ->orWhereHas('parent', function ($query) {
                        $query->where('user_id', auth()->user()->id);
                    });
                });
            }
        })
        ->when(is_numeric(request('current_store_id')) && request('current_store_id') > 0, function ($builder) {
            $builder->whereHas('parent.actstore', function ($query) {
                $query->where('id', request('current_store_id'));
            });
        })
        ->when($request->showCancelled == 1, function ($builder) {
            return $builder->where('cancelled', 1);
        })
        ->when($request->showCancelled == 2, function ($builder) {
            return $builder->where('cancelled', 0);
        })
        ->when(in_array(request('status'), ['PENDING', 'IN_PROGRESS', 'PENDING_VERIFICATION', 'VERIFIED', 'COMPLETED']), function ($builder) {
            if (request('status') == 'PENDING') {
                $builder->where('status', 0);
            } else if (request('status') == 'IN_PROGRESS') {
                $builder->where('status', 1);
            } else if (request('status') == 'PENDING_VERIFICATION') {
                $builder->where('status', 2);
            } else if (request('status') == 'VERIFIED') {
                $builder->where('status', 3)
                ->whereHas('parent.parent', function ($query) {
                    $query->where('checker_user_id', '>', 0);
                });
            } else if (request('status') == 'COMPLETED') {
                $builder->where('status', 3)
                ->whereHas('parent.parent', function ($query) {
                    $query->whereNull('checker_user_id');
                });
            }
        })
        ->when(is_numeric($request->checklist) && $request->checklist > 0, function ($builder) {
            $builder->whereHas('parent.parent', function ($query) {
                $query->where('checklist_id', request('checklist'));
            });
        })

        ->scheduling();

        if (!empty($request->from) && !empty($request->to)) {
            $tasks = $tasks->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($filterFrom)))
            ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($filterTo)));
        } else if (!empty($request->from) && empty($request->to)) {
            $tasks = $tasks->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($filterFrom)));
        } else if (empty($request->from) && !empty($request->to)) {
            $tasks = $tasks->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($filterTo)));
        } else {
            $tasks = $tasks->where(function ($where) {
                $where->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), date('Y-m-d'))
                ->orWhere(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), date('Y-m-d'));
            });
        }

        $taskCount = $tasks->clone()->count();

        $tasks = $tasks
        ->orderBy('date', 'ASC')
        ->skip($skip)
        ->take($perPage)
        ->get()
        ->map(function ($el) {
            if ($el->status == 0) {
                $statusLabel = 'PENDING';
            } else if ($el->status == 1) {
                $statusLabel = 'IN-PROGRESS';
            } else if ($el->status == 2) {

                if (request('task_type') == 1 && request('filter_status') == 'PENDING_VERIFICATION') {
                    $statusLabel = 'PENDING-VERIFICATION';
                } else {
                    if (isset($el->parent->parent->checker_user_id)) {
                        if ($el->redos()->where('status', 1)->count() == 0 && $el->redos()->where('status', 0)->count() > 0) {
                            $statusLabel = 'REASSIGNED';
                        } else if ($el->redos()->where('status', 0)->count() == 0 && $el->redos()->where('status', 1)->count() == 0) {
                            $statusLabel = 'PENDING-VERIFICATION';
                        } else {
                            $statusLabel = 'VERIFYING';
                        }
                    } else {
                        $statusLabel = 'COMPLETED';
                    }
                }

            } else {
                if (isset($el->parent->parent->checker_user_id)) {
                    $statusLabel = 'VERIFIED';
                } else {
                    $statusLabel = 'COMPLETED';
                }
            }

            return [
                'id' => $el->id,
                'code' => $el->code,
                'date' => date('d M Y h:i A', strtotime($el->date)),
                'checklist_id' => $el->parent->parent->checklist_id,
                'checklist_name' => $el->parent->parent->checklist->name ?? '',
                'store_id' => isset($el->parent->actstore->id) ? $el->parent->actstore->id : null,
                'store_name' => isset($el->parent->actstore->name) ? $el->parent->actstore->name : '',
                'store_code' => isset($el->parent->actstore->code) ? $el->parent->actstore->code : '',
                'status' => $statusLabel
            ];
        });
        
        $tasks = $tasks->toArray();

        return response()->json(['success' => $tasks, 'total_records' => $taskCount, 'page' => intval($page), 'record_per_page' => $perPage], 200); 
    }

    public function checklistList(Request $request) {
        return response()->json([
            'success' => DynamicForm::select('id', 'name')->where('type', 0)->get()
        ]);
    }

    public function documentTypes() {
        return response()->json(['success' => \App\Models\Document::get()]);
    }

    public function documents(Request $request) {
        $documents = \App\Models\DocumentUpload::with(['document', 'store', 'users', 'addusers'])
        ->where('status', true)
        ->when($request->has('perpetual'), function ($builder) {
            $builder->where('perpetual', request('perpetual'));
        })
        ->when($request->has('location'), function ($builder) {
            $builder->where('location_id', request('location'));
        })
        ->when($request->has('document_type'), function ($builder) {
            $builder->where('document_id', request('document_type'));
        })
        ->when($request->has('expiry_from') && $request->has('expiry_to'), function ($builder) {
            $builder->where('perpetual', false)
            ->whereDate('expiry_date', '>=', date('Y-m-d', strtotime(request('expiry_from'))))
            ->whereDate('expiry_date', '<=', date('Y-m-d', strtotime(request('expiry_to'))));
        })
        ->when($request->has('issue_from') && $request->has('issue_to'), function ($builder) {
            $builder->whereDate('issue_date', '>=', date('Y-m-d', strtotime(request('issue_from'))))
            ->whereDate('issue_date', '<=', date('Y-m-d', strtotime(request('issue_to'))));
        })
        ->when(!auth()->user()->isAdmin(), function ($builder) {
            $builder->where(function ($innerBuilder) {
                $getCurrentUserRoles = auth()->user()->roles[0]->id ?? 0;

                if ($getCurrentUserRoles == Helper::$roles['store-phone']) {

                    $innerBuilder->where('enable_store_access', true)
                    ->whereHas('store', function ($innerInnerBuilder) {
                        $innerInnerBuilder->where('dom_id', auth()->user()->id);
                    });

                } else if ($getCurrentUserRoles == Helper::$roles['operations-manager']) {

                    $innerBuilder->where('enable_operation_manager_access', true)
                    ->where(function ($innerInnerBuilder) {
                        $underDom = \App\Models\OperationManager::where('ops_id', auth()->user()->id)
                        ->pluck('dom_id')->toArray();

                        array_push($underDom, auth()->user()->id);

                        $innerInnerBuilder->whereHas('store', function ($innerInnerBuilder) use ($underDom) {
                            $innerInnerBuilder->whereIn('dom_id', $underDom);
                        });
                    });

                } else if ($getCurrentUserRoles == Helper::$roles['divisional-operations-manager']) {

                    $innerBuilder->where('enable_dom_access', true)
                    ->where(function ($innerInnerBuilder) {
                        $underDom = \App\Models\OperationManager::where('ops_id', auth()->user()->id)
                        ->pluck('dom_id')->toArray();

                        array_push($underDom, auth()->user()->id);

                        $innerInnerBuilder->whereHas('store', function ($innerInnerBuilder) use ($underDom) {
                            $innerInnerBuilder->whereIn('dom_id', $underDom);
                        });
                    });

                } else {
                    $innerBuilder->whereHas('store', function ($innerInnerBuilder) {
                        $innerInnerBuilder->where('dom_id', auth()->user()->id);
                    });
                }
            })
            ->orWhereHas('addusers', function ($innerBuilder) {
                $innerBuilder->where('user_id', auth()->user()->id);
            });
        })
        ->get()
        ->map(function ($el) {
            return [
                'id' => $el->id,
                'file_name' => $el->attachment_path,
                'document_type' => $el->document,
                'store' => $el->store,
                'notification_users' => $el->users,
                'assigned_users' => $el->addusers,
                'perpetual' => boolval($el->perpetual),
                'expiry_date' => $el->expiry_date,
                'remark' => $el->remark,
                'issue_date' => $el->issue_date,
                'created_at' => $el->created_at
            ];
        });

        return response()->json(['success' => $documents]);
    }
}