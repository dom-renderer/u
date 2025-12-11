<?php

namespace App\Http\Controllers;

use App\Models\ChecklistScheduling;
use App\Models\RescheduledTask;
use App\Models\SubmissionTime;
use App\Models\ChecklistTask;
use Illuminate\Http\Request;
use App\Models\DynamicForm;
use Illuminate\Support\Arr;
use App\Models\RedoAction;
use App\Helpers\Helper;
use App\Models\Store;
use App\Models\User;

class ScheduledTaskController extends Controller
{
    public function index(Request $request)
    {
        ini_set('memory_limit', '-1');

        if ($request->ajax()) {

            if (!empty($request->scheduled_task_loc)) {
                $users = User::select('id', 'name')->whereIn('id', request('locs'))->pluck('name', 'id')->toArray();
                session()->put(['scheduled_task_loc' => $users]);
            } else {
                session()->forget('scheduled_task_loc');
            }

            if (!empty($request->user)) {
                $users = User::select('id', 'name')->whereIn('id', request('user'))->pluck('name', 'id')->toArray();
                session()->put(['scheduled_task_user' => $users]);
            } else {
                session()->forget('scheduled_task_user');
            }

            if (!empty($request->checker)) {
                $users = User::select('id', 'name')->whereIn('id', request('checker'))->pluck('name', 'id')->toArray();
                session()->put(['scheduled_task_user_checker' => $users]);
            } else {
                session()->forget('scheduled_task_user_checker');
            }

            if (!empty($request->checklist)) {
                $checklists = DynamicForm::select('name', 'id')->whereIn('id', request('checklist'))->pluck('name', 'id')->toArray();
                session()->put(['scheduled_task_checklist' => $checklists]);
            } else {
                session()->forget('scheduled_task_checklist');
            }

            if (!empty($request->frequency)) {
                session()->put(['scheduled_task_frequency' => request('frequency')]);
            } else {
                session()->forget('scheduled_task_frequency');
            }

            if (!empty($request->from)) {
                session()->put(['scheduled_task_from' => request('from')]);
            } else {
                session()->forget('scheduled_task_from');
            }

            if (!empty($request->to)) {
                session()->put(['scheduled_task_to' => request('to')]);
            } else {
                session()->forget('scheduled_task_to');
            }

            if (!empty($request->status)) {
                session()->put(['scheduled_task_status' => request('status')]);
            } else {
                session()->forget('scheduled_task_status');
            }

            if (!empty($request->showCancelled) && in_array($request->showCancelled, [1, 2])) {
                session()->put(['cancellation_status' => request('showCancelled')]);
            } else {
                session()->forget('cancellation_status');
            }

            $allStoreName = Store::selectRaw("id, CONCAT(COALESCE(code, ''), ' - ', COALESCE(name, '')) as name")->pluck('name', 'id')->toArray();
            $allCTemplateName = DynamicForm::selectRaw("id, name")->pluck('name', 'id')->toArray();
            $allEmployees = User::whereHas('roles', function ($builder) {
                $builder->whereIn('id', [Helper::$roles['store-phone'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], 
                Helper::$roles['store-cashier'], Helper::$roles['divisional-operations-manager'], Helper::$roles['admin'], Helper::$roles['operations-manager']
            ]);
            })
            ->selectRaw("id, CONCAT(COALESCE(employee_id, ''), ' - ', COALESCE(name, ''), ' ', COALESCE(middle_name, ''), ' ', COALESCE(last_name, '')) as name")
            ->pluck('name', 'id')->toArray();

            $currentUser = auth()->user()->id;
            $thisUserRoles = auth()->user()->roles()->pluck('id')->toArray();

            $checklistScheduling = ChecklistTask::query()
            ->whereHas('parent', function($query) {
                $query->whereHas('parent', function($query) {
                    $query->whereNull('deleted_at');
                })->whereNull('deleted_at');
            })
            ->when(!in_array(Helper::$roles['admin'], $thisUserRoles), function ($builder) use ($currentUser) {
                $builder->where(function ($innerBuilder) use ($currentUser) {
                    $innerBuilder->orWhereHas('parent', function ($innerBuilder2) use ($currentUser) {
                        $innerBuilder2->where('user_id', $currentUser);
                    });
                });
            })
            ->when($request->showCancelled == 1 || $request->showCancelled == 2, function ($builder) {
                if (request('showCancelled') == 1) {
                    $builder->where('cancelled', 1);
                } else {
                    $builder->where('cancelled', 0);
                }
            })
            ->when(!empty($request->locs), function ($builder) {
                return $builder->whereHas('parent', function ($innerBuilder) {
                    $innerBuilder->whereIn('store_id', request('locs'));
                });
            })
            ->when(!empty($request->user), function ($builder) {
                return $builder->whereHas('parent', function ($innerBuilder) {
                    $innerBuilder->whereIn('user_id', request('user'));
                });
            })
            ->when(!empty($request->checker), function ($builder) {
                return $builder->whereHas('parent.parent', function ($innerBuilder) {
                    $innerBuilder->whereIn('checker_user_id', request('checker'));
                });
            })
            ->when(!empty($request->checklist), function ($builder) {
                return $builder->whereHas('parent.parent', function ($innerBuilder) {
                    return $innerBuilder->whereIn('checklist_id', request('checklist'));
                });
            })
            ->when(is_array($request->frequency), function ($builder) {
                return $builder->whereHas('parent.parent', function ($innerBuilder) {
                    return $innerBuilder->whereIn('frequency_type', request('frequency'));
                });
            })
            ->when(!empty($request->from), function ($builder) {
                return $builder->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime(request('from'))));
            })
            ->when(!empty($request->to), function ($builder) {
                return $builder->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime(request('to'))));
            })
            ->when($request->status === '0' || in_array($request->status, range(1, 6)), function ($builder) {
                session()->put(['scheduled_task_status' => request('status')]);

                if (request('status') === '0' || request('status') === '1') {
                    return $builder->where('status', request('status'));
                } else if (request('status') === '5') {
                    return $builder->where('status', 3)
                    ->whereHas('parent.parent', function ($innerBuilder) {
                        $innerBuilder->whereNotNull('checker_user_id');
                    });
                } else if (request('status') === '6') {
                    return $builder->where('status', 3)
                    ->whereHas('parent.parent', function ($innerBuilder) {
                        $innerBuilder->whereNull('checker_user_id');
                    });
                } else {
                    if (request('status') === '2') {
                        return $builder->where('status', 2)
                        ->whereDoesntHave('redos', function ($innerBuilder) {
                            $innerBuilder->whereIn('status', [0, 1]);
                        });
                    } else if (request('status') === '3') {
                        return $builder->where('status', 2)
                        ->whereDoesntHave('redos', function ($innerBuilder) {
                            $innerBuilder->where('status', 1);
                        })
                        ->whereHas('redos', function ($innerBuilder) {
                            $innerBuilder->where('status', 0);
                        });
                    } else {
                        return $builder->where('status', 2)
                        ->whereHas('redos', function ($innerBuilder) {
                            $innerBuilder->where('status', 1);
                        });
                    }
                }
            })
            ->scheduling()
            ->orderBy('id', 'DESC');

            return datatables()
            ->eloquent($checklistScheduling)
            ->addColumn('checklist_name', function ($row) use ($allCTemplateName) {
                return isset($row->parent->parent->checklist_id) && isset($allCTemplateName[$row->parent->parent->checklist_id]) ? $allCTemplateName[$row->parent->parent->checklist_id] : '';
            })
            ->editColumn('date', function ($row) {
                return date('d-m-Y H:i', strtotime($row->date));
            })
            ->editColumn('status', function ($row) use ($thisUserRoles, $currentUser) {
                if ($row->cancelled) {
                    return '<span class="badge bg-danger">Cancelled</span>';
                }

                if ((in_array(Helper::$roles['admin'], $thisUserRoles) || $row->checker_user_id == $currentUser)) {
                    if (in_array($row->status, [2, 3])) {

                        $html = '';

                        if ($row->status == 0) {
                            $html .= '<span class="badge bg-warning">Pending</span>';
                        } else if ($row->status == 1) {
                            $html .= '<span class="badge bg-info">In-Progress</span>';
                        } else if ($row->status == 2) {
                            if (isset($row->parent->parent->checker_user_id)) {
                                if ($row->redos()->count() == 0) {
                                    $html .= '<span class="badge bg-secondary">Pending Verification</span>';
                                } else if ($row->redos()->where('status', 1)->count() == 0) {
                                    $html .= '<span class="badge bg-secondary">Reassigned</span>';
                                } else {
                                    $html .= '<span class="badge bg-secondary">Verifying</span>';
                                }
                            } else {
                                $html .= '<span class="badge bg-success">Completed</span>';
                            }
                        } else {
                            if (isset($row->parent->parent->checker_user_id)) {
                                $html .= '<span class="badge bg-success">Verified</span>';
                            } else {
                                $html .= '<span class="badge bg-success">Completed</span>';
                            }
                        }

                        if ($row->status != 3) {
                            $html .= "<br><br><select class='me-2 change-status' data-id='".$row->id."' data-last-selected='".$row->status."'>
                            <option value='2' ".($row->status == 2 ? 'selected' : '').">Pending Verification</option>
                            <option value='3' ".($row->status == 3 ? 'selected' : '').">Verified</option>
                            </select>";
                        }

                        return $html;

                    } else {
                        if ($row->status == 0) {
                            return '<span class="badge bg-warning">Pending</span>';
                        } else if ($row->status == 1) {
                            return '<span class="badge bg-info">In-Progress</span>';
                        } else if ($row->status == 2) {
                            if (isset($row->parent->parent->checker_user_id)) {
                                if ($row->redos()->count() == 0) {
                                    return '<span class="badge bg-secondary">Pending Verification</span>';
                                } else if ($row->redos()->where('status', 1)->count() == 0) {
                                    return '<span class="badge bg-secondary">Reassigned</span>';
                                } else {
                                    return '<span class="badge bg-secondary">Verifying</span>';
                                }
                            } else {
                                return '<span class="badge bg-success">Completed</span>';
                            }
                        } else {
                            if (isset($row->parent->parent->checker_user_id)) {
                                return '<span class="badge bg-success">Verified</span>';
                            } else {
                                return '<span class="badge bg-success">Completed</span>';
                            }
                        }
                    }
                    
                } else {
                    if ($row->status == 0) {
                        return '<span class="badge bg-warning">Pending</span>';
                    } else if ($row->status == 1) {
                        return '<span class="badge bg-info">In-Progress</span>';
                    } else if ($row->status == 2) {
                        if (isset($row->parent->parent->checker_user_id)) {
                            if ($row->redos()->count() == 0) {
                                return '<span class="badge bg-secondary">Pending Verification</span>';
                            } else if ($row->redos()->where('status', 1)->count() == 0) {
                                return '<span class="badge bg-secondary">Reassigned</span>';
                            } else {
                                return '<span class="badge bg-secondary">Verifying</span>';
                            }
                        } else {
                            return '<span class="badge bg-success">Completed</span>';
                        }
                    } else {
                        if (isset($row->parent->parent->checker_user_id)) {
                            return '<span class="badge bg-success">Verified</span>';
                        } else {
                            return '<span class="badge bg-success">Completed</span>';
                        }
                    }
                }
            })
            ->addColumn('action', function ($row) use ($thisUserRoles, $currentUser) {
                $dropdownItems = '';
            
                if (in_array($row->status, [1, 2, 3]) && !empty($row->data)) {
            
                    if (auth()->user()->can('scheduled-tasks.show')) {
                        $dropdownItems .= '<li><a class="dropdown-item" href="'.route('checklists-submission-comparison', encrypt($row->id)).'">Compare</a></li>';

                        $dropdownItems .= '<li><a class="dropdown-item" href="'.route('checklists-submission-view-for-maker', encrypt($row->id)).'">Data</a></li>';
                        
                        if (isset($row->parent->parent->checker_user_id) && $row->parent->parent->checker_user_id == auth()->user()->id) {
                            $dropdownItems .= '<li><a class="dropdown-item" href="'.route('checklists-submission-view-for-checker', encrypt($row->id)).'">Check</a></li>';
                        }
                    }
            
                    if (auth()->user()->can('task-export-excel')) {
                        $dropdownItems .= '<li><a class="dropdown-item" href="'.route("task-export-excel", $row->id).'">Export Excel</a></li>';
                    }
            
                    if (auth()->user()->can('task-export-pdf')) {
                        $dropdownItems .= '<li><a class="dropdown-item" href="'.route("task-export-pdf", $row->id).'">Export PDF</a></li>';
                    }
            
                    if (auth()->user()->can('task-log')) {
                        $dropdownItems .= '<li><a class="dropdown-item" href="'.route("task-log", encrypt($row->id)).'">Logs</a></li>';
                    }

                    $dropdownItems .= '<li><a class="dropdown-item text-primary" href="' . route("task-export-pdf", $row->id) . '?regenerate=1' . '"> Regenerate PDF </a></li>';
                }
            
                if (auth()->user()->can('reschedule-task')) {
                    if ((isset($row->parent->user_id) && $currentUser == $row->parent->user_id) || in_array(Helper::$roles['admin'], $thisUserRoles)) {
                        if (!(isset($row->restasks[0]) && $row->restasks[0]->status === 0 && in_array($row->status, [0, 1]))) {
                            $dropdownItems .= '<li><a class="dropdown-item reschedule-task" href="#" data-href="'.route("reschedule-task", encrypt($row->id)).'">Reschedule</a></li>';
                        }
                    }
                }

                if (auth()->user()->can('cancel-task') && $row->cancelled == 0) {
                    $dropdownItems .= '<li><a class="dropdown-item text-danger cancel-task" href="#" data-href="'.route("cancel-task", encrypt($row->id)).'"> Cancel </a></li>';
                } else {
                    $dropdownItems .= '<li><a class="dropdown-item cancellation-note" href="#" data-note="'. $row->cancellation_reason .'"> Cancellation Note </a></li>';
                }          

                if (auth()->user()->can('scheduled-tasks.edit') && $row->status == 0) {
                    $dropdownItems .= '<li><a class="dropdown-item edit-task-date" href="#" data-currdate="' . date('d-m-Y H:i', strtotime($row->date)) . '" data-href="'.route("scheduled-tasks.update", encrypt($row->id)).'"> Edit Date </a></li>';
                }

                if (auth()->user()->can('scheduled-tasks-change-date-time') && in_array($row->status, [0, 1])) {
                    $dropdownItems .= '<li><a class="dropdown-item alter-task-start-end-time" href="#" data-current-start="' . date('H:i', strtotime($row->parent->parent->start_at)) . '" data-current-end="' . date('H:i', strtotime($row->parent->parent->completed_by)) . '"  data-href="'.route("scheduled-tasks-change-date-time", encrypt($row->id)).'"> Edit Start & End Time </a></li>';
                }
            
                if (auth()->user()->can('scheduled-tasks.destroy')) {
                    $dropdownItems .= '<li>
                        <form method="POST" action="'.route("scheduled-tasks.destroy", encrypt($row->id)).'">
                            '.csrf_field().'
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="dropdown-item text-danger deleteGroup">Delete</button>
                        </form>
                    </li>';
                }
            
                if ($dropdownItems) {
                    $action = '
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Actions
                        </button>
                        <ul class="dropdown-menu">
                            '.$dropdownItems.'
                        </ul>
                    </div>';
                } else {
                    $action = '-';
                }
            
                return $action;
            })            
            ->editColumn('code', function ($row) use ($currentUser) {
                $html = $row->code;

                if (isset($row->parent->parent) && $row->parent->parent->checker_user_id == $currentUser) {
                    $html .= " <br/> <span class='badge bg-warning'> To Check </span>";
                }

                if (isset($row->restasks[0]) && $row->restasks[0]->status === 0) {
                    $html .= " <br/> <span class='badge bg-primary'> Rescheduling Requested </span>";
                }

                return $html;
            })
            ->addColumn('store_name', function ($row) use ($allStoreName) {
                return isset($row->parent->store_id) && isset($allStoreName[$row->parent->store_id]) ? $allStoreName[$row->parent->store_id] : '';
            })
            ->addColumn('user_name', function ($row) use ($allEmployees) {
                return isset($row->parent->user_id) && isset($allEmployees[$row->parent->user_id]) ? $allEmployees[$row->parent->user_id] : '';
            })
            ->addColumn('checker_user_name', function ($row) use ($allEmployees) {
                return isset($row->parent->parent->checker_user_id) && isset($allEmployees[$row->parent->parent->checker_user_id]) ? $allEmployees[$row->parent->parent->checker_user_id] : '';
            })
            ->rawColumns(['action', 'status', 'code'])
            ->toJson();
        }

        $page_title = 'Scheduled Tasks';
        $page_description = 'Manage scheduled tasks here';
        return view('tasks.index',compact('page_title', 'page_description'));
    }

    public function changeStartEndTime(Request $request, $id) {
        $id = decrypt($id);

        $task = ChecklistTask::find($id)->parent->parent->id ?? null;

        if ($task) {
            ChecklistScheduling::where('id', $task)->update(['start_at' => date('H:i:s', strtotime($request->startD)), 'completed_by' => date('H:i:s', strtotime($request->endD))]);

            return response()->json(['status' => true]);
        } else {
            return response()->json(['status' => false]);
        }
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $task = ChecklistTask::where('id', $id)->scheduling();

        if ($task) {
            $task->delete();
            return redirect()->route('scheduled-tasks.index')->with('success', 'Task deleted successfully');
        }

        return redirect()->route('scheduled-tasks.index')->with('error', 'Task not found');
    }

    public function submission(Request $request, $id)
    {
        $task = ChecklistTask::where('id', decrypt($id))->scheduling();

        if ($task) {
            if ($request->method() == 'POST') {
                if (empty($task->data)) {
                    $task->data = json_decode($request->data, true);
                    $task->status = Helper::$status['completed'];
                    $task->save();

                    return redirect()->route('submission-response', ['submission_response' => 'success']);
                } else {
                    return redirect()->route('submission-response', ['submission_response' => 'failed', 'already_submitted' => 1]);
                }
            }
    
            return view('tasks.submission', compact('task', 'id'));
        }

        return redirect()->route('submission-response', ['submission_response' => 'failed']);
    }

    public function submissionView(Request $request, $id) {
        $decId = decrypt($id);
        $task = ChecklistTask::find($decId);

        if ($task) {
            if (isset($task->parent->parent->checker_user_id) && $task->parent->parent->checker_user_id == auth()->user()->id) {
                $redoActionData = RedoAction::where('task_id', $decId)->get()->keyBy('field_id')->toArray();
                return view('tasks.submission-checker', compact('task', 'id', 'redoActionData'));
            }

            return view('tasks.submission-view', compact('task', 'id'));
        }

        return redirect()->route('submission-response', ['submission_response' => 'failed']);
    }

    public function sideBySideComparison(Request $request, $id) {
        $decId = decrypt($id);
        $task = ChecklistTask::find($decId);

        if ($task) {
            return view('tasks.submission-compare', compact('task', 'id'));
        }

        return redirect()->route('submission-response', ['submission_response' => 'failed']);
    }

    public function submissionViewForMaker(Request $request, $id) {
        $decId = decrypt($id);
        $task = ChecklistTask::find($decId);

        if ($task) {
            return view('tasks.submission-view', compact('task', 'id'));
        }

        return redirect()->route('submission-response', ['submission_response' => 'failed']);
    }

    public function submissionViewForChecker(Request $request, $id) {
        $decId = decrypt($id);
        $task = ChecklistTask::find($decId);

        if ($task) {
            $redoActionData = RedoAction::where('task_id', $decId)->get()->keyBy('field_id')->toArray();
            return view('tasks.submission-checker', compact('task', 'id', 'redoActionData'));
        }

        return redirect()->route('submission-response', ['submission_response' => 'failed']);
    }

    public function truthyFalsyFields(Request $request) {
        
        $task = ChecklistTask::find($request->task_id);
        $flaggedItems = Helper::getBooleanFields($task->data)[in_array($request->type, ['truthy', 'falsy']) ? $request->type : 'falsy'];

        $groupedData = [];
        foreach ($flaggedItems as $item) {
            $groupedData[$item['className']][] = (object)$item;
        }

        $formToPass = Helper::getVersionForm($task->version_id);
        $isPointChecklist = Helper::isPointChecklist($formToPass);

        return response()->json(['status' => true, 'html' => view('tasks.truthy-falsy', compact('flaggedItems', 'task', 'groupedData', 'isPointChecklist'))->render()]);
    }

    public function verifyEachFields(Request $request, $id) {
        //RedoAction
        $id = decrypt($id);
        $task = ChecklistTask::find($id);

        if (empty($request->justify_field)) {
            return redirect()->route('scheduled-tasks.index')->with('success', 'Updated successfully');
        }

        \DB::beginTransaction();

        try {
            $json = $task->data;

            foreach ($request->justify_field as $index => $value) {
                if ($value == 'approve') {
                    foreach ($json as &$item) {
                        if (isset($item->className) && $item->className === $index) {
                            $item->approved = 'yes';
                        }
                    }
                } else if ($value == 'decline') {
                    foreach ($json as &$item) {
                        if (isset($item->className) && $item->className === $index) {
                            $item->approved = 'no';
                        }
                    }

                    $redoActionExists = RedoAction::where('task_id', $id)
                    ->where('field_id', $index);

                    $tempArr = isset($request->action[$index]) ? (array)json_decode($request->action[$index]) : [];

                    if ($redoActionExists->exists()) {
                        $redoActionExists->update([
                            'title' => isset($tempArr['title']) ? $tempArr['title'] : '',
                            'remarks' => isset($tempArr['remark']) ? $tempArr['remark'] : '',
                            'status' => 0,
                            'start_at' => isset($tempArr['start']) ? date('Y-m-d H:i:s', strtotime($tempArr['start'])) : '',
                            'completed_by' => isset($tempArr['end']) ? date('Y-m-d H:i:s', strtotime($tempArr['end'])) : '',
                            'do_not_allow_late_submission' => isset($tempArr['lsub']) ? $tempArr['lsub'] : 0
                        ]);
                    } else {
                        RedoAction::create([
                            'task_id' => $id,
                            'field_id' => $index,
                            'title' => isset($tempArr['title']) ? $tempArr['title'] : '',
                            'remarks' => isset($tempArr['remark']) ? $tempArr['remark'] : '',
                            'start_at' => isset($tempArr['start']) ? date('Y-m-d H:i:s', strtotime($tempArr['start'])) : '',
                            'completed_by' => isset($tempArr['end']) ? date('Y-m-d H:i:s', strtotime($tempArr['end'])) : '',
                            'do_not_allow_late_submission' => isset($tempArr['lsub']) ? $tempArr['lsub'] : 0
                        ]);
                    }
                }
            }

            $task->data = $json;
            $task->save();

            //generating from notification center
            // \App\Jobs\GenerateOptimizedTaskPdf::dispatch($task->id);

            \DB::commit();
            return redirect()->back()->with('success', 'Updated successfully');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('CHECKER VERIFICATION: ' . $e->getMessage() . ' ON LINE ' . $e->getLine());
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    public function changeStatus(Request $request) {
        $order = ChecklistTask::find($request->id);
        $order->status = $request->status;
        $order->save();

        //generating from notification center
        // \App\Jobs\GenerateOptimizedTaskPdf::dispatch($order->id);

        return response()->json(['status' => true, 'message' => 'Status updated successfully']);
    }

    public function reassignmentList(Request $request) {
        if ($request->ajax()) {

            $allEmployees = User::selectRaw("id, CONCAT(COALESCE(employee_id, ''), ' - ', COALESCE(name, ''), ' ', COALESCE(middle_name, ''), ' ', COALESCE(last_name, '')) as name")
            ->pluck('name', 'id')->toArray();

            $checklistScheduling = ChecklistTask::when(!in_array(Helper::$roles['admin'], auth()->user()->roles()->pluck('id')->toArray()), function ($builder) {
                $builder->where(function ($innerBuilder) {
                    $innerBuilder->orWhereHas('parent', function ($innerBuilder2) {
                        $innerBuilder2->where('user_id', auth()->user()->id);
                    });
                });
            })
            ->whereHas('redos', function ($builder) {
                $builder->whereIn('status', [0, 1]);
            })
            ->when(!empty($request->maker), function ($builder) {
                $builder->whereHas('parent', function ($innerBuilder) {
                    $innerBuilder->whereIn('user_id', request('maker'));
                });
            })
            ->when(!empty($request->checker), function ($builder) {
                $builder->whereHas('parent.parent', function ($innerBuilder) {
                    $innerBuilder->whereIn('checker_user_id', request('checker'));
                });
            })
            ->when(!empty($request->checklist), function ($builder) {
                $builder->whereHas('parent.parent', function ($innerBuilder) {
                    $innerBuilder->whereIn('checklist_id', request('checklist'));
                });
            })
            ->when(!empty($request->loc), function ($builder) {
                $builder->whereHas('parent', function ($innerBuilder) {
                    $innerBuilder->whereIn('store_id', request('loc'));
                });
            })
            ->when(!empty($request->date), function ($builder) {
                $builder->where('id', request('date'));
            })
            ->when($request->status == 'pending' || $request->status == 'completed', function ($builder) {
                if (request('status') == 'pending') {
                    $builder->whereHas('redos', function ($innerBuilder) {
                        $innerBuilder->where('status', 0);
                    });
                } else {
                    $builder->whereDoesntHave('redos', function ($innerBuilder) {
                        $innerBuilder->where('status', 0);
                    })
                    ->whereHas('redos', function ($innerBuilder) {
                        $innerBuilder->where('status', 1);
                    });
                }
            })
            ->orderBy('id', 'DESC');

            return datatables()
            ->eloquent($checklistScheduling)

            ->addColumn('user_name', function ($row) use ($allEmployees) {
                return isset($row->parent->user_id) && isset($allEmployees[$row->parent->user_id]) ? $allEmployees[$row->parent->user_id] : '';
            })
            ->addColumn('checker_user_name', function ($row) use ($allEmployees) {
                return isset($row->parent->parent->checker_user_id) && isset($allEmployees[$row->parent->parent->checker_user_id]) ? $allEmployees[$row->parent->parent->checker_user_id] : '';
            })
            ->addColumn('checklist_name', function ($row) {
                return $row->parent->parent->checklist->name ?? '-';
            })
            ->addColumn('location_name', function ($row) {
                return ($row->parent->actstore->code ?? '') . ' - ' . ($row->parent->actstore->name ?? '');
            })
            ->addColumn('total_reassingments', function ($row) {
                return RedoAction::where('task_id', $row->id)->count();
            })
            ->addColumn('status', function ($row) {
                if ($row->redos()->where('status', 0)->count() > 0) {
                    return '<span class="badge bg-warning"> Pending </span>';
                } else {
                    return '<span class="badge bg-success"> Completed </span>';
                }
            })
            ->addColumn('action', function ($row) {
                $action = '';
                
                if (auth()->user()->can('reassignments.show')) {
                    $action .= '<a href="'.route('reassignments.show', encrypt($row->id)).'" class="btn btn-info btn-sm me-2"> Data </a>';
                }

                return $action;
            })
            ->rawColumns(['action', 'status'])
            ->toJson();
        }

        $page_title = 'Re-Do';
        $page_description = 'Manage re-dos here';
        return view('reassignments.index',compact('page_title', 'page_description'));
    }

    public function reassignmentView(Request $request, $id) {
        $task = RedoAction::with(['task'])->where('task_id', decrypt($id))->first();
        $allData = RedoAction::where('task_id', decrypt($id))->get()->keyBy('field_id')->toArray();
        $allClass = array_keys($allData);

        return view('reassignments.show', compact('task', 'allClass', 'allData'));
    }

    public function compare(Request $request) {
        $data = $labels = $dataPoints = $dataIds = [];
        $date = \Carbon\Carbon::now();

        for ($i = 5; $i >= 0; $i--) {

            $tasks = ChecklistTask::whereHas('parent.parent', function ($builder) {
                $builder->where('checklist_id', request('id'));
            })
            ->whereHas('parent', function ($builder) {
                $builder->where('store_id', request('store_id'));
            })
            ->where('date', '>=', $date->startOfMonth()->format('Y-m-d') . ' 00:00:00')
            ->where('date', '<=', $date->endOfMonth()->format('Y-m-d') . ' 23:59:59')
            ->whereIn('status', [2, 3])
            ->orderBy('date', 'DESC')
            ->get();

            foreach ($tasks as $task) {
            
                $varients = Helper::categorizePoints($task->data ?? []);
            
                $total = count(Helper::selectPointsQuestions($task->data));
                $toBeCounted = $total - count($varients['na']);

                $failed = abs(count(array_column($varients['negative'], 'value')));
                $achieved = $toBeCounted - abs($failed);
            
                if ($failed <= 0) {
                    $achieved = array_sum(array_column($varients['positive'], 'value'));
                }
            
                if ($toBeCounted > 0) {
                    $percentage = ($achieved / $toBeCounted) * 100;
                } else {
                    $percentage = 0;
                }

                $labels[] = date('d F', strtotime($task->date));
                $data[] = floatval(number_format($percentage, 2));
                $dataIds[] = $task->id;
                $dataPoints[] = $task->id;
            }

            $date = $date->subDays($date->daysInMonth);
        }

        $data = array_reverse($data);
        $labels = array_reverse($labels);
        $dataPoints = array_reverse($dataPoints);
        $dataIds = array_reverse($dataIds);

        return response()->json([
            'status' => true,
            'data' => $data,
            'label' => $labels,
            'datapoints' => $dataPoints,
            'ids' => $dataIds
        ]);
    }

    public function fetchTaskDataToCompare(Request $request) {
        $task = ChecklistTask::find($request->current);
        $tasks = empty($request->tasks) ? [] : $request->tasks;
        array_unshift($tasks, $request->current);

        if (count($tasks) > 3) {
            $tasks = array_slice($tasks, 0, 3);
        }

        return response()->json(['status' => true, 'html' => view('tasks.compare', compact('tasks', 'task'))->render()]);
    }

    public function exportComparison(Request $request) {
        $tasks = empty($request->ids) ? [] : $request->ids;

        if (count($tasks) > 3) {
            $tasks = array_slice($tasks, 0, 3);
        }

        $tasks = ChecklistTask::whereIn('id', $tasks)->latest()->get();
        
        $finalArray = [];
        $styleData = [];

        $finalArray[] = [
            'CHECKLIST',
            $tasks[0]->parent->parent->checklist->name ?? '',
        ];
        $styleData[] = ['type' => 'header_row', 'row' => count($finalArray)];

        $finalArray[] = [
            'STORE',
            $tasks[0]->parent->actstore->name ?? ''
        ];
        $styleData[] = ['type' => 'header_row', 'row' => count($finalArray)];

        $finalArray[] = [''];

        $sectionHeader = ['SECTION'];
        foreach ($tasks as $task) {
            $sectionHeader[] = date('d-m-Y', strtotime($task->date ?? ''));
        }
        $finalArray[] = $sectionHeader;
        $styleData[] = ['type' => 'section_header', 'row' => count($finalArray)];

        $percentageRows = [];
        
        $formToPass = Helper::getVersionForm($tasks[0]->version_id);

        foreach ($formToPass as $page => $form) {
            $hasHeader = collect($form)->where('type', 'header')->count();

            if ($hasHeader > 0) {
                $sectionRow = [collect($form)->where('type', 'header')->get(0)->label ?? '',];
                $percentageValues = [];

                foreach ($tasks as $task) {
                    $sectionArray = collect($task->data)->where('page', $page + 1);
                    $thisVarients = Helper::categorizePoints($sectionArray ?? []);
                    $thisTotal = count(Helper::selectPointsQuestions($sectionArray));
                    $thisToBeCounted = $thisTotal - count($thisVarients['na']);

                    $thisFailed = abs(count(array_column($thisVarients['negative'], 'value')));
                    $thisAchieved = $thisToBeCounted - abs($thisFailed);

                    if ($thisFailed <= 0) {
                        $thisAchieved = array_sum(array_column($thisVarients['positive'], 'value'));
                    }
                    
                    if ($thisToBeCounted > 0) {
                        $thisPer = number_format(($thisAchieved / $thisToBeCounted) * 100, 2);
                    } else {
                        $thisPer = 0;
                    }

                    $sectionRow[] = "{$thisPer}%";
                    $percentageValues[] = floatval($thisPer);
                }

                $finalArray[] = $sectionRow;
                $percentageRows[] = [
                    'row' => count($finalArray),
                    'values' => $percentageValues,
                    'start_col' => 2
                ];
            }
        }

        $styleData[] = ['type' => 'percentage_rows', 'data' => $percentageRows];

        $finalArray[] = [''];

        $sectionHeader = ['Date'];
        foreach ($tasks as $task) {
            $sectionHeader[] = date('d-m-Y', strtotime($task->date ?? ''));
        }
        $finalArray[] = $sectionHeader;

        $sectionHeader = ['Inspection Item'];
        foreach ($tasks as $task) {
            $sectionHeader[] = [];
        }
        $finalArray[] = $sectionHeader;

        $groupedData = [];
        foreach ($tasks[0]->data as $item) {
            $groupedData[$item->className][] = $item;
        }

        $siteUrl = url('storage/workflow-task-uploads') . '/';
        $totalQuestions = ['Total Questions'];
        $passedQuestions = ['Passed'];
        $failedQuestions = ['Failed'];
        $NAQuestions = ['N/A'];
        $Percentages = ['Percentage'];
        $Results = ['Result'];

        foreach ($groupedData as $object) {
            $line = [$object[0]->label];

            foreach ($tasks as $task) {
                $finalLine = [];
                foreach ($task->data as $submissionItem) {
                    if ($submissionItem->className == $object[0]->className) {
                        if ($submissionItem->isFile) {
                            if (is_array($submissionItem->value)) {
                                $finalLine[] = array_map(function ($v) use ($siteUrl) {
                                    return $siteUrl . ltrim($v, '/');
                                }, $submissionItem->value);
                            } else if (is_string($submissionItem->value)) {
                                $finalLine[] = $siteUrl . ltrim($submissionItem->value, '/');
                            }
                        } else {
                            $finalLine[] = (array)(property_exists($submissionItem, 'value_label') ? $submissionItem->value_label : $submissionItem->value);
                        }
                    }
                }

                $line = array_merge($line, [implode(',', Arr::flatten($finalLine))]);
            }

            $finalArray[] = $line;
        }

        foreach ($tasks as $task) {
            $thatVarients = Helper::categorizePoints($task->data ?? []);
            $thatTotal = count(Helper::selectPointsQuestions($task->data));
            $thatToBeCounted = $thatTotal - count($thatVarients['na']);

            $thatFailed = abs(count(array_column($thatVarients['negative'], 'value')));
            $thatAchieved = $thatToBeCounted - abs($thatFailed);

            if ($thatFailed <= 0) {
                $thatAchieved = array_sum(array_column($thatVarients['positive'], 'value'));
            }
            
            if ($thatToBeCounted > 0) {
                $thatPer = number_format(($thatAchieved / $thatToBeCounted) * 100, 2);
            } else {
                $thatPer = 0;
            }

            $totalQuestions[] = $thatTotal;
            $passedQuestions[] = $thatAchieved;
            $failedQuestions[] = count($thatVarients['negative']);
            $NAQuestions[] = count($thatVarients['na']);
            $Percentages[] = number_format($thatPer, 2);
            $Results[] = $thatPer > 80 ? 'Pass' : 'Fail';
        }

        $finalArray[] = $totalQuestions;
        $finalArray[] = $passedQuestions;
        $finalArray[] = $failedQuestions;
        $finalArray[] = $NAQuestions;
        $finalArray[] = $Percentages;
        $finalArray[] = $Results;

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\StyledTaskExport($finalArray, $styleData), 
            'task-comparison-report.xlsx'
        );
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        ChecklistTask::whereIn('id', $ids)->delete();
        SubmissionTime::whereIn('task_id', $ids)->delete();

        return response()->json(['status' => true]);
    }

    public function reschedule(Request $request, $encryptedId)
    {
        $id = decrypt($encryptedId);

        $last = RescheduledTask::where('task_id', $id)->latest()->first();

        if ($last && $last->status === 0) {
            return response()->json(['status' => false, 'message' => 'Rescheduling approval is already in pending.']);
        }

        $resDate = date('Y-m-d H:i:s', strtotime($request->date));
        $mainTask = ChecklistTask::find($id);

        RescheduledTask::create([
            'task_id' => $id,
            'remarks' => $request->remark,
            'date' => $resDate,
            'task_date' => $mainTask->date ?? null
        ]);

        $task = ChecklistTask::find($id);
        \App\Jobs\NotificationRescheduleRequest::dispatch($task, $resDate);

        return response()->json(['status' => true]);
    }

    public function cancel(Request $request, $encryptedId)
    {
        $id = decrypt($encryptedId);

        $task = ChecklistTask::find($id);
        $task->cancelled = 1;
        $task->cancellation_reason = $request->remark;
        $task->save();

        return response()->json(['status' => true]);
    }

    public function update(Request $request, $encryptedId)
    {
        $id = decrypt($encryptedId);

        ChecklistTask::where('id', $id)->update([
            'date' => date('Y-m-d H:i:s', strtotime($request->date))
        ]);

        return response()->json(['status' => true]);
    }

    public function select2List(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
        $getAll = $request->getall;
    
        $query = ChecklistTask::query()
        ->scheduling();
    
        if (!empty($queryString)) {
            $query->where('code', 'LIKE', "%{$queryString}%");
        }
    
        $data = $query->paginate($limit, ['*'], 'page', $page);
        $response = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => $item->code
            ];
        });

        return response()->json([
            'items' => $response->reverse()->values(),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }

    public function checklistDatesList(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
        $getAll = $request->getall;
    
        $query = ChecklistTask::when(!empty($request->checklist_id), function ($builder) {
            $builder->whereHas('parent.parent', function ($innerBuilder) {
                $innerBuilder->where('checklist_id', request('checklist_id'));
            });
        }, function ($builder) {
            $builder->where('id', 0);
        })
        ->scheduling();
    
        if (!empty($queryString)) {
            $query->where(\DB::raw("DATE_FORMAT(date, '%d-%m-%Y')"), 'LIKE', "%{$queryString}%");
        }
    
        $data = $query->paginate($limit, ['*'], 'page', $page);
        $response = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => date('d-m-Y', strtotime($item->date))
            ];
        });

        return response()->json([
            'items' => $response->reverse()->values(),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }

    public function taskVersioning(Request $request) {
        if ($request->method() == 'POST') {

        } else {

            if ($request->ajax()) {
                $tasks = ChecklistTask::scheduling()
                ->where('status', 0)
                ->where(function ($builder) {
                    $builder->whereNull('version_id')
                    ->orWhere('version_id', '');
                });

                return datatables()
                ->eloquent($tasks)
                ->addColumn('action', function ($row) {
                    $action = '';
                    $action .= '<a href="'.route('reassignments.show', encrypt($row->id)).'" class="btn btn-info btn-sm me-2"> Data </a>';
                    return $action;
                })
                ->rawColumns(['action'])
                ->toJson();
            } else {
                return view('tasks.version');
            }
        }
    }
}
