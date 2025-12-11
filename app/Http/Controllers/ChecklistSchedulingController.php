<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Jobs\GenerateChecklistTasksExtra;
use App\Models\ChecklistSchedulingExtra;
use App\Jobs\GenerateChecklistTasks;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ChecklistScheduling;
use Spatie\Permission\Models\Role;
use App\Models\SchedulingImport;
use App\Models\ChecklistTask;
use Illuminate\Http\Request;
use App\Models\DynamicForm;
use App\Models\Designation;
use App\Helpers\Helper;
use App\Models\Store;
use App\Models\User;
use stdClass;

class ChecklistSchedulingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            if (!empty($request->user)) {
                $users = User::select('id', 'name')->whereIn('id', request('user'))->pluck('name', 'id')->toArray();
                session()->put(['scheduled_user' => $users]);
            } else {
                session()->forget('scheduled_user');
            }

            if (!empty($request->checklist)) {
                $checklists = DynamicForm::select('name', 'id')->whereIn('id', request('checklist'))->pluck('name', 'id')->toArray();
                session()->put(['scheduled_checklist' => $checklists]);
            } else {
                session()->forget('scheduled_checklist');
            }

            if (!empty($request->frequency)) {
                session()->put(['scheduled_frequency' => request('frequency')]);
            } else {
                session()->forget('scheduled_frequency');
            }

            $checklistScheduling = ChecklistScheduling::when(!empty($request->checklist), function ($builder) {
                return $builder->whereIn('checklist_id', explode(',', request('checklist')));
            })->when(is_array($request->frequency), function ($builder) {
                return $builder->whereIn('frequency_type', explode(',', request('frequency')));
            })
            ->when(!empty($request->locations), function ($builder) {
                $builder->whereHas('children', function ($innerBuilder) {
                    $innerBuilder->whereIn('store_id', explode(',', request('locations')));
                });
            })
            ->when(!empty($request->makers), function ($builder) {
                $builder->whereHas('children', function ($innerBuilder) {
                    $innerBuilder->whereIn('user_id', explode(',', request('makers')));
                });
            })
            ->when(!empty($request->checkers), function ($builder) {
                $builder->whereIn('checker_user_id', explode(',', request('checkers')));
            })
            ->when(!empty($request->id), function ($builder) {
                $template = request('id');

                try {
                    $template = decrypt($template);
                } catch (\Exception $e) {
                    $template = 0;
                }

                $builder->where('checklist_id', $template);
            })
            ->orderBy('id', 'DESC');

            return datatables()
            ->eloquent($checklistScheduling)
            ->addColumn('user_name', function ($row) {
                return $row->user->name ?? '';
            })
            ->addColumn('checklist_name', function ($row) {
                $html = '<p>' . ($row->checklist->name ?? '');

                if ($row->perpetual) {
                    $html .= "&nbsp;<span class='badge bg-warning'> Perpetual </span>";
                }

                $html .= '</p>';

                return $html;
            })
            ->addColumn('freq', function ($row) {
                return isset(Helper::$frequency[$row->frequency_type]) ? Helper::$frequency[$row->frequency_type] : '-';
            })
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('checklist-scheduling.edit') && ChecklistScheduling::where('id', $row->id)->whereHas('children', function ($innerBuilder) {
                    $innerBuilder->whereHas('tasks', function ($innerinnerBuilder) {
                        $innerinnerBuilder->whereIn('status', [0, 1]);
                    });
                })->count() > 0) {
                    $action .= '<a href="'.route("checklist-scheduling.edit", encrypt($row->id)).'" class="btn btn-primary btn-sm me-2"> Edit </a>';
                }

                if (auth()->user()->can('checklist-scheduling.show')) {
                    $action .= '<a href="'.route("checklist-scheduling.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('checklist-scheduling.destroy')) {
                    $action .= '<form method="POST" action="'.route("checklist-scheduling.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->addColumn('locs', function ($row) {
                $stores = [];

                foreach ($row->children()->groupBy('store_id')->get() as $item) {
                    $stores[] = $item->actstore->name . ' - ' . $item->actstore->code;
                }

                return implode(' <br> ', $stores);
            })
            ->addColumn('mks', function ($row) {
                $employees = [];

                foreach ($row->children()->groupBy('user_id')->get() as $item) {
                    $employees[] = $item->user->employee_id . ' - ' . $item->user->name . ' - ' . $item->user->middle_name . ' - ' . $item->user->last_name;
                }

                return implode(' <br> ', $employees);
            })
            ->addColumn('chk', function ($row) {
                return isset($row->checker->employee_id) ? ($row->checker->employee_id . ' - ' . $row->checker->name . ' - ' . $row->checker->middle_name . ' - ' . $row->checker->last_name) : '-';
            })
            ->rawColumns(['action', 'checklist_name', 'mks', 'locs'])
            ->toJson();
        }

        $id = $request->template;
        $page_title = 'Checklist Scheduling';
        $page_description = 'Manage Checklist scheduling here';
        return view('checklist-scheduling.index',compact('page_title', 'page_description', 'id'));
    }

    public function create()
    {
        $page_title = 'Create Checklist Scheduling';
        $page_description = 'Add Checklist scheduling here';
        $makerRoles = Role::whereIn('id', [Helper::$roles['store-phone'], Helper::$roles['divisional-operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['operations-manager'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier']])->get();
        $checkerRoles = Role::whereIn('id', [Helper::$roles['store-phone'], Helper::$roles['divisional-operations-manager'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier'], Helper::$roles['admin']])->get();

        return view('checklist-scheduling.create',compact('page_title', 'page_description', 'makerRoles', 'checkerRoles'));
    }

    public function store(Request $request) {
        $request->validate([
            'checklist' => 'required',
            'type' => 'required',
            'start_date' => 'required'
        ]);

        $checkerRole = $request->checker_role;

        if (in_array($checkerRole, [Helper::$roles['divisional-operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['operations-manager']])) {
            $checkerRole = 3;
        } else if (in_array($checkerRole, [Helper::$roles['store-phone'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier']])) {
            $checkerRole = 1;
        } else {
            $checkerRole = 2;
        }

        $checkerInfo = Helper::getFirstBranch($request->checker_employee, $checkerRole);
        $existingLocations = $request->loc;
        $finalCreationArray = [];

        /***
         *  Checklist Scheduling Extra
         * **/

        if ($request->assination_type == 1) {

            $iterableUsers = Role::with(['users'])
            ->whereIn('id', request('maker_role'))
            ->get()
            ->mapWithKeys(function ($role) {
                return [$role->id => $role->users->pluck('id')->toArray()];
            })->filter()
            ->toArray();

            foreach ($iterableUsers as $rowRole => $rowUsers) {
                foreach ($rowUsers as $rowUser) {
                    if (in_array($rowRole, [Helper::$roles['divisional-operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['operations-manager']])) {
                        $finalCreationArray[] = [
                            'user_id' => $rowUser,
                            'role_id' => $rowRole,
                            'locations' => !empty($existingLocations) ? $existingLocations : Store::select('id')->where('dom_id', $rowUser)->pluck('id')->toArray()
                        ];
                    } else if (in_array($rowRole, [Helper::$roles['store-phone'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier']])) {
                        $finalCreationArray[] = [
                            'user_id' => $rowUser,
                            'role_id' => $rowRole,
                            'locations' => !empty($existingLocations) ? $existingLocations : Designation::select('type_id')->where('type', 1)->where('user_id', $rowUser)->pluck('type_id')->toArray()
                        ];
                    }
                }
            }

            
        } else if ($request->assination_type == 2) {

            $iterableUsers = Role::whereHas('users', function ($query) {
                $query->whereIn('id', request('maker_employee'));
            })->with(['users' => function ($query) {
                $query->whereIn('id', request('maker_employee'));
            }])
            ->whereIn('id', request('maker_role'))
            ->get()
            ->mapWithKeys(function ($role) {
                return [$role->id => $role->users->pluck('id')->toArray()];
            })->filter()
            ->toArray();

            foreach ($iterableUsers as $rowRole => $rowUsers) {
                foreach ($rowUsers as $rowUser) {
                    if (in_array($rowRole, [Helper::$roles['divisional-operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['operations-manager']])) {
                        $finalCreationArray[] = [
                            'user_id' => $rowUser,
                            'role_id' => $rowRole,
                            'locations' => !empty($existingLocations) ? $existingLocations : Store::select('id')->where('dom_id', $rowUser)->pluck('id')->toArray()
                        ];
                    } else if (in_array($rowRole, [Helper::$roles['store-phone'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier']])) {
                        $finalCreationArray[] = [
                            'user_id' => $rowUser,
                            'role_id' => $rowRole,
                            'locations' => !empty($existingLocations) ? $existingLocations : Designation::select('type_id')->where('type', 1)->where('user_id', $rowUser)->pluck('type_id')->toArray()
                        ];
                    }
                }
            }

        } else if ($request->assination_type == 3) {

            $iterableUsers = Role::whereHas('users', function ($query) {
                $query->whereNotIn('id', request('maker_employee'));
            })->with(['users' => function ($query) {
                $query->whereNotIn('id', request('maker_employee'));
            }])
            ->whereIn('id', request('maker_role'))
            ->get()
            ->mapWithKeys(function ($role) {
                return [$role->id => $role->users->pluck('id')->toArray()];
            })->filter()
            ->toArray();

            foreach ($iterableUsers as $rowRole => $rowUsers) {
                foreach ($rowUsers as $rowUser) {
                    if (in_array($rowRole, [Helper::$roles['divisional-operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['operations-manager']])) {
                        $finalCreationArray[] = [
                            'user_id' => $rowUser,
                            'role_id' => $rowRole,
                            'locations' => !empty($existingLocations) ? $existingLocations : Store::select('id')->where('dom_id', $rowUser)->pluck('id')->toArray()
                        ];
                    } else if (in_array($rowRole, [Helper::$roles['store-phone'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier']])) {
                        $finalCreationArray[] = [
                            'user_id' => $rowUser,
                            'role_id' => $rowRole,
                            'locations' => !empty($existingLocations) ? $existingLocations : Designation::select('type_id')->where('type', 1)->where('user_id', $rowUser)->pluck('type_id')->toArray()
                        ];
                    }
                }
            }

        }

        /***
         *  Checklist Scheduling Extra
         * **/

        \DB::beginTransaction();

        try {

            if (empty($request->checklist) || !is_array($request->checklist)) {
                \DB::rollback();
                return redirect()->back()->with('error', 'Please select atleast a checklist to assign');
            }

            foreach ($request->checklist as $theEachChecklist) {
                if ($request->type == 'once') {


                    if (!empty($finalCreationArray)) {

                        $checklistScheduling = ChecklistScheduling::create([
                            'checklist_id' => $theEachChecklist,
        
                            'start_at' => $request->start_at,
                            'completed_by' => $request->completed_by,
        
                            'start_grace_time' => $request->grace_start,
                            'end_grace_time' => $request->grace_end,
                            'hours_required' => $request->time_required,
        
                            'do_not_allow_late_submission' => $request->do_not_allow_late_submission == 1 ? : 0,
        
                            'checker_branch_type' => $checkerInfo['branch_type'],
                            'checker_branch_id' => $checkerInfo['branch_id'],
                            'checker_user_id' => $checkerInfo['user_id'],
        
                            'frequency_type' => 12,
                            'interval' => $request->interval,
                            'weekdays' => $request->type == 'specific_days' ? implode(',', $request->specific_days) : null,
                            'weekday_time' => $request->type == 'specific_days' ? $request->specific_time : null,
                            'start' => !empty($request->start_date) ? date('Y-m-d H:i:s', strtotime($request->start_date)) : null,
                            'end' => !empty($request->end_date) ? date('Y-m-d H:i:s', strtotime($request->end_date)) : null
                        ]);

                        $hash = md5(json_encode($checklistScheduling->checklist->schema ?? []));
                        
                        foreach ($finalCreationArray as $finalCreationArrayRow) {
                            $mkrRole = $finalCreationArrayRow['role_id'];

                            if (in_array($mkrRole, [Helper::$roles['divisional-operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['operations-manager']])) {
                                $mkrRole = 3;
                            } else if (in_array($mkrRole, [Helper::$roles['store-phone'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier']])) {
                                $mkrRole = 1;
                            } else {
                                $mkrRole = 2;
                            }
                    
                            $makerInfo = Helper::getFirstBranch($finalCreationArrayRow['user_id'], $mkrRole);

                            if (!isset($makerInfo['branch_id']) || !isset($makerInfo['branch_type'])) {
                                $theMakerEl = User::find($makerInfo['user_id']);
                                \DB::rollback();
                                \Log::error('USER HAS NOT ASSIGNED TO ANY BRANCH OR THIS ROLE DOES NOT HAVE PERMIT TO PERFORM THIS ACTION. ' . $theMakerEl->id);
                                return redirect()->back()->with('error',  $theMakerEl->employee_id . ' ' . $theMakerEl->name . ' ' . $theMakerEl->middle_name . ' ' . $theMakerEl->last_name . ' is not assigned in any branch');
                            }

                            foreach ($finalCreationArrayRow['locations'] as $finaLocation) {
                                $checklistSchedulingExtra = ChecklistSchedulingExtra::create([
                                    'checklist_scheduling_id' => $checklistScheduling->id,
                                    'branch_id' => $makerInfo['branch_id'],
                                    'store_id' => $finaLocation,
                                    'user_id' => $makerInfo['user_id'],
                                    'branch_type' => $makerInfo['branch_type']
                                ]);

                                $timestampValue = !empty($request->start_date) ? date('Y-m-d H:i:s', strtotime($request->start_date)) : null;
        
                                ChecklistTask::create([
                                    'code' => Helper::generateTaskNumber($timestampValue, $makerInfo['user_id']),
                                    'checklist_scheduling_id' => $checklistSchedulingExtra->id,
                                    'form' => new StdClass(),
                                    'version_id' => Helper::getFormVersion($checklistScheduling->checklist_id, $hash),
                                    'date' => $timestampValue,
                                    'type' => 0
                                ]);
                            }
                        }
                    }

                } else {
                    $allTimestampts = [];
                    $allDays = null;
                    $weekdayTime = null;
        
                    $type = 0;
                    $typeSlug = 'hourly';

                    if ($request->type == 'every_hour') {
                        $type = 0;
                        $typeSlug = 'hourly';
                    } else if ($request->type == 'hourly') {
                        $type = 1;
                        $typeSlug = $request->interval . ' hour';
                    } else if ($request->type == 'every_day') {
                        $type = 2;
                        $typeSlug = 'daily';
                    } else if ($request->type == 'daily') {
                        $type = 3;
                        $typeSlug = $request->interval . ' day';
                    } else if ($request->type == 'weekly') {
                        $type = 4;
                        $typeSlug = 'weekly';
                    } else if ($request->type == 'biweekly') {
                        $type = 5;
                        $typeSlug = 'biweekly';
                    } else if ($request->type == 'monthly') {
                        $type = 6;
                        $typeSlug = 'monthly';
                    } else if ($request->type == 'bimonthly') {
                        $type = 7;
                        $typeSlug = 'bimonthly';
                    } else if ($request->type == 'quarterly') {
                        $type = 8;
                        $typeSlug = 'quarterly';
                    } else if ($request->type == 'semiannual') {
                        $type = 9;
                        $typeSlug = 'semiannual';
                    } else if ($request->type == 'annual') {
                        $type = 10;
                        $typeSlug = 'annual';
                    } else if ($request->type == 'specific_days') {
                        $type = 11;
                        $typeSlug = 'specific_days';
                        $allDays = $request->specific_days;
                        $weekdayTime = $request->specific_time;
                    }

                    if (!empty($finalCreationArray)) {

                        $checklistScheduling = ChecklistScheduling::create([
                            'checklist_id' => $theEachChecklist,
        
                            'start_at' => $request->start_at,
                            'completed_by' => $request->completed_by,
        
                            'start_grace_time' => $request->grace_start,
                            'end_grace_time' => $request->grace_end,
                            'hours_required' => $request->time_required,
        
                            'do_not_allow_late_submission' => $request->do_not_allow_late_submission == 1 ? : 0,
        
                            'checker_branch_type' => $checkerInfo['branch_type'],
                            'checker_branch_id' => $checkerInfo['branch_id'],
                            'checker_user_id' => $checkerInfo['user_id'],
        
                            'frequency_type' => $type,
                            'interval' => $request->interval,
                            'weekdays' => $request->type == 'specific_days' ? implode(',', $request->specific_days) : null,
                            'weekday_time' => $request->type == 'specific_days' ? $request->specific_time : null,
                            'perpetual' => $request->perpetual == 1 ? 1 : 0,
                            'start' => !empty($request->start_date) ? date('Y-m-d H:i:s', strtotime($request->start_date)) : null,
                            'end' => !empty($request->end_date) ? date('Y-m-d H:i:s', strtotime($request->end_date)) : null
                        ]);

                        if ($request->perpetual != 1) {
                            $allTimestampts = \App\Helpers\Frequency::generate($request->start_date, $request->end_date, $typeSlug, $allDays, $weekdayTime);
                        }
            
                        GenerateChecklistTasksExtra::dispatch($checklistScheduling, $allTimestampts, $finalCreationArray);
                    }
                }
            }

            \DB::commit();
            return redirect()->route('checklist-scheduling.index')->with('success', 'Checklist scheduling created successfully');
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error($e->getMessage() . ' on line ' . $e->getLine());
            return redirect()->back()->with('error', 'Failed to create Checklist scheduling');
        }
    }

    public function edit($id)
    {
        $checklistScheduling = ChecklistScheduling::find(decrypt($id));

        if ($checklistScheduling) {

            $page_title = 'Edit Checklist Scheduling';
            $page_description = 'Edit Checklist scheduling here';

            return view('checklist-scheduling.edit',compact('page_title', 'page_description', 'checklistScheduling', 'id'));
        }

        return redirect()->route('checklist-scheduling.index')->with('error', 'Checklist scheduling not found');
    }

    public function update(Request $request, $id)
    {
        $checklistScheduling = ChecklistScheduling::find(decrypt($id));

        if ($checklistScheduling) {
            $checklistScheduling->start_at = date('H:i:s', strtotime($request->start_at));
            $checklistScheduling->completed_by = date('H:i:s', strtotime($request->completed_by));
            $checklistScheduling->hours_required = date('H:i:s', strtotime($request->time_required));
            $checklistScheduling->start_grace_time = date('H:i:s', strtotime($request->grace_start));
            $checklistScheduling->end_grace_time = date('H:i:s', strtotime($request->grace_end));
            $checklistScheduling->save();

            return redirect()->route('checklist-scheduling.index')->with('success', 'Checklist Scheduling updated successfully');
        }

        return redirect()->route('checklist-scheduling.index')->with('error', 'Checklist scheduling not found');
    }

    public function show($id)
    {
        $checklistScheduling = ChecklistScheduling::find(decrypt($id));

        if ($checklistScheduling) {
            if (request('dttble') == 'true') {
                return datatables()
                ->eloquent(ChecklistTask::where('checklist_scheduling_id', $checklistScheduling->id)->scheduling()->orderBy('date', 'desc'))
                ->addColumn('date', function ($row) {
                    return date('d-m-Y H:i', strtotime($row->date));
                })
                ->addColumn('status', function ($row) {
                    return $row->status == 1 ? '<span class="badge bg-success">Done</span>' : '<span class="badge bg-warning">Pending</span>';
                })
                ->rawColumns(['status'])
                ->toJson();
            }

            $page_title = 'Show Checklist Scheduling';
            $page_description = 'Show Checklist scheduling here';
            return view('checklist-scheduling.show',compact('page_title', 'page_description', 'checklistScheduling', 'id'));
        }

        return redirect()->route('checklist-scheduling.index')->with('error', 'Checklist scheduling not found');
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $checklistScheduling = ChecklistScheduling::find($id);

        if ($checklistScheduling) {

            \DB::beginTransaction();

            try {
                $checklistScheduling->delete();            
                $extras = ChecklistSchedulingExtra::where('checklist_scheduling_id', $id)->get();
                foreach ($extras as $extra) {
                    ChecklistTask::where('checklist_scheduling_id', $extra->id)->whereNotIn('status', [2, 3])->scheduling()->delete();
                    $extra->delete();
                }

                \DB::commit();
                return redirect()->route('checklist-scheduling.index')->with('success', 'Checklist scheduling deleted successfully');
            } catch (\Exception $e) {
                \DB::rollBack();
                return redirect()->route('checklist-scheduling.index')->with('error', 'Checklist scheduling not found');
            }
        }

        return redirect()->route('checklist-scheduling.index')->with('error', 'Checklist scheduling not found');
    }

    public function importScheduling(Request $request, $id = null) {
        ini_set('memory_limit', '-1');

        $template = null;
        $response = $leaveBlank = [];
        $errorCount = $successCount = 0;

        if ($id) {
            $id = decrypt($id);
            $template = DynamicForm::find($id);
        }

        if ($request->method() == 'POST') {
            $request->validate([
                'checklist' => 'required',
                'import' => 'required|file',
            ]);

            $file = $request->file('import');
            $type = $file->getClientOriginalExtension();

            if (!in_array($type, ['xlsx'])) {

                self::recordImport([
                    'checklist_id' => isset($template->id) ? $template->id : $request->checklist,
                    'file_name' => $file->getClientOriginalName(),
                    'success' => 0,
                    'error' => 0,
                    'status' => 2,
                    'response' => [
                        'File is not supported. please upload xlsx.'
                    ]
                ], $file);

                return response()->json(['status' => false, 'message' => 'File is not supported. please upload xlsx.']);
            }

            $expectedHeaders = [
                'storeid',
                'dom',
                'checker',
                'start date',
                'start time',
                'end date',
                'end time',
                'hours required',
                'grace time',
                'allow reschedule'
            ];

            $isFileValid = false;
            $data = [];

            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
                $worksheet = $spreadsheet->getActiveSheet();
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                for ($row = 1; $row <= $highestRow; $row++) {
                    $rowData = [];
                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
                        $rowData[] = $cellValue;
                    }
                    
                    $tempFilter = array_filter($rowData, function($value) {
                        return !is_null($value) && $value !== '';
                    });
                    
                    if (!empty($tempFilter)) {
                        $data[] = $rowData;
                    }
                }

                if (!empty($data)) {
                    $headerRow = $data[0];
                    if (
                        strtolower($headerRow[0]) == $expectedHeaders[0] &&
                        (strtolower($headerRow[1]) == $expectedHeaders[1] || strtolower($headerRow[1]) == 'maker') &&
                        strtolower($headerRow[2]) == $expectedHeaders[2] &&
                        strtolower($headerRow[3]) == $expectedHeaders[3] &&
                        strtolower($headerRow[4]) == $expectedHeaders[4] &&
                        strtolower($headerRow[5]) == $expectedHeaders[5] &&
                        strtolower($headerRow[6]) == $expectedHeaders[6] &&
                        strtolower($headerRow[7]) == $expectedHeaders[7] &&
                        strtolower($headerRow[8]) == $expectedHeaders[8] &&
                        strtolower($headerRow[9]) == $expectedHeaders[9]
                    ) {
                        $isFileValid = true;
                    }
                }

            } catch (\Exception $e) {
                self::recordImport([
                    'checklist_id' => isset($template->id) ? $template->id : $request->checklist,
                    'file_name' => $file->getClientOriginalName(),
                    'success' => 0,
                    'error' => 0,
                    'status' => 2,
                    'response' => [
                        'Error reading xlsx file: ' . $e->getMessage()
                    ]
                ], $file);
                
                return response()->json(['status' => false, 'message' => 'Error reading xlsx file.']);
            }

            if (!$isFileValid) {
                self::recordImport([
                    'checklist_id' => isset($template->id) ? $template->id : $request->checklist,
                    'file_name' => $file->getClientOriginalName(),
                    'success' => 0,
                    'error' => 0,
                    'status' => 2,
                    'response' => [
                        'Uploaded file headers do not match the expected format.'
                    ]
                ], $file);
                return response()->json(['status' => false, 'message' => 'Uploaded file headers do not match the expected format.']);
            }

            // Remove header row from data
            $data = array_splice($data, 1, count($data));

            if (empty($data)) {
                self::recordImport([
                    'checklist_id' => isset($template->id) ? $template->id : $request->checklist,
                    'file_name' => $file->getClientOriginalName(),
                    'success' => 0,
                    'error' => 0,
                    'status' => 2,
                    'response' => [
                        'File has not data.'
                    ]
                ], $file);

                return response()->json(['status' => false, 'message' => 'File has not data']);
            }

            $getAllStores = Store::select('code')->whereNotNull('code')->where('code', '!=', '')->pluck('code')->toArray();
            $store = $maker = $checker = new stdClass;

            \DB::beginTransaction();

            try {

                foreach ($data as $key => $row) {
                    if (strtolower($row[0]) == 'leave' || strtolower($row[0]) == 'week off' || strtolower($row[0]) == 'wfh') {
                        $leaveBlank[$key] = $key;
                        continue;
                    }
                
                    $explodeStoreString = explode(' , ', $row[0]);
                    $hasMultipleRecord = false;

                    if (is_array($explodeStoreString) && count($explodeStoreString) > 1) {
                        $throwError = false;
                        $hasMultipleRecord = true;

                        foreach ($explodeStoreString as $explodeStoreStringRow) {
                            if (!in_array($explodeStoreStringRow, $getAllStores)) {
                                $throwError = true;
                            }         
                        }

                        if ($throwError) {
                            $response[$key] = 'Store with given code does not exists at A' . ($key);
                            $errorCount++;
                        }

                    } else {
                        if (!in_array($row[0], $getAllStores)) {
                            $errorCount++;
                            $response[$key] = 'Store with given code does not exists at A' . ($key);
                            continue;
                        } else {
                            $store = Store::where('code', $row[0])->first();
                        }
                    }
                    
                    if (!empty($row[1])) {
                        $exploded = explode('_', $row[1]);
                        $maker = User::where('employee_id', $exploded[0])->whereNotNull('employee_id')->where('employee_id', '!=', '')->first();

                        if (!$maker) {
                            $errorCount++;
                            $response[$key] = 'DOM does not exists at B' . ($key);
                            continue;
                        }
                    }
                    
                    if (!empty($row[2])) {
                        $exploded = explode('_', $row[2]);
                        $checker = User::where('employee_id', $exploded[0])->whereNotNull('employee_id')->where('employee_id', '!=', '')->first();

                        if (!$checker) {
                            $errorCount++;
                            $response[$key] = 'Checker employee does not exists at B' . ($key);
                            continue;
                        }
                    }
                    
                    // if ($checker->employee_id === $maker->employee_id) {
                    //         $errorCount++;
                    //         $response[$key] = 'Checker and DOM could not be same at B' . ($key + 1);
                    //         continue;
                    // }

                    $checkerBranch = $checkerBranchType = $makerBranch = $makerBranchType = null;
                    $checkerRoles = $checker->roles()->pluck('id')->toArray();
                    $makerRoles = $maker->roles()->pluck('id')->toArray();

                    if (in_array(Helper::$roles['divisional-operations-manager'], $checkerRoles) || in_array(Helper::$roles['head-of-department'], $checkerRoles) || in_array(Helper::$roles['operations-manager'], $checkerRoles)) {
                        $checkerBranch = Designation::where('user_id', $checker->id)->where('type', 3)->first()->type_id ?? null;
                        $checkerBranchType = 2;
                    } else if (in_array(Helper::$roles['store-phone'], $checkerRoles) || in_array(Helper::$roles['store-manager'], $checkerRoles) || in_array(Helper::$roles['store-employee'], $checkerRoles) || in_array(Helper::$roles['store-cashier'], $checkerRoles)) {
                        $checkerBranch = Designation::where('user_id', $checker->id)->where('type', 1)->first()->type_id ?? null;
                        $checkerBranchType = 1;
                    }

                    if (in_array(Helper::$roles['divisional-operations-manager'], $makerRoles) || in_array(Helper::$roles['head-of-department'], $makerRoles) || in_array(Helper::$roles['operations-manager'], $makerRoles)) {
                        $makerBranch = Designation::where('user_id', $maker->id)->where('type', 3)->first()->type_id ?? null;
                        $makerBranchType = 2;
                    } else if (in_array(Helper::$roles['store-phone'], $makerRoles) || in_array(Helper::$roles['store-manager'], $makerRoles) || in_array(Helper::$roles['store-employee'], $makerRoles) || in_array(Helper::$roles['store-cashier'], $makerRoles)) {
                        $makerBranch = Designation::where('user_id', $maker->id)->where('type', 1)->first()->type_id ?? null;
                        $makerBranchType = 1;
                    }

                        $startDateRaw = $row[3];
                        $startDate = is_numeric($startDateRaw)
                            ? Date::excelToDateTimeObject($startDateRaw)->format('Y-m-d')
                            : Helper::parseFlexibleDate($startDateRaw);

                        $startDate = date('Y-m-d', strtotime($startDate));

                        $startTimeRaw = $row[4];
                        $startTime = is_numeric($startTimeRaw)
                            ? Date::excelToDateTimeObject($startTimeRaw)->format('H:i:s')
                            : date('H:i:s', strtotime($startTimeRaw));

                        $endDateRaw = $row[5];
                        $endDate = is_numeric($endDateRaw)
                            ? Date::excelToDateTimeObject($endDateRaw)->format('Y-m-d')
                            : Helper::parseFlexibleDate($endDateRaw);
                        $endDate = date('Y-m-d', strtotime($endDate));                            

                        $endTimeRaw = $row[6];
                        $endTime = is_numeric($endTimeRaw)
                            ? Date::excelToDateTimeObject($endTimeRaw)->format('H:i:s')
                            : date('H:i:s', strtotime($endTimeRaw));

                        $startTimestamp = $startDate . ' ' . $startTime;
                        $endTimestamp = $endDate . ' ' . $endTime;


                        $hRequiredRaw = is_numeric($row[7])
                            ? Date::excelToDateTimeObject($row[7])->format('H:i:s')
                            : '08:00';

                        $graceRaw = is_numeric($row[8])
                            ? Date::excelToDateTimeObject($row[8])->format('H:i:s')
                            : '08:00';

                    /**
                     * Scheduling
                     * **/

                    $template = DynamicForm::find($request->checklist);
                    $successCount++;

                    $iterateNTimes = [$store->code];

                    if ($hasMultipleRecord) {
                        $iterateNTimes = $explodeStoreString;
                    }

                    $iterateNTimes = Store::whereIn('code', $iterateNTimes)->get();

                    foreach ($iterateNTimes as $iteratingStore) {
                        if (empty($makerBranchType)) {
                            $errorCount++;
                            $response[$key] = 'User has not valid role at B' . ($key);
                            continue 2;
                        }

                        if (empty($makerBranch)) {
                            $errorCount++;
                            $response[$key] = 'User is not in any required branch or location at B' . ($key);
                            continue 2;
                        }

                        $checklistScheduling = ChecklistScheduling::create([
                            'checklist_id' => $template->id,
                            'frequency_type' => 12,
        
                            'checker_branch_type' => $checkerBranchType,
                            'checker_branch_id' => $checkerBranch,
                            'checker_user_id' => $checker->id,
        
                            'hours_required' => $hRequiredRaw,
                            'start_grace_time' => $graceRaw,
                            'end_grace_time' => $graceRaw,
                            'allow_rescheduling' => isset($row[9]) && strtolower($row[9]) == 'yes' ? 1 : 0,
                            'is_import' => 1,

                            'start_at' => date('H:i:s', strtotime($startTime)),
                            'completed_by' => date('H:i:s', strtotime($endTime)),

                            'interval' => 0,
                            'weekdays' => null,
                            'weekday_time' => null,
                            'perpetual' => 0,
                            'start' => $startTimestamp,
                            'end' => $endTimestamp,
                            'completion_data' => []
                        ]);

                        $hash = md5(json_encode($checklistScheduling->checklist->schema ?? []));

                        $checklistSchedulingExtra = ChecklistSchedulingExtra::create([
                            'checklist_scheduling_id' => $checklistScheduling->id,
                            'branch_id' => $makerBranch,
                            'store_id' => $iteratingStore->id,
                            'user_id' => $maker->id,
                            'branch_type' => $makerBranchType
                        ]);

                        ChecklistTask::create([
                            'code' => Helper::generateTaskNumber($startTimestamp, $maker->id),
                            'checklist_scheduling_id' => $checklistSchedulingExtra->id,
                            'form' => new StdClass(),
                            'version_id' => Helper::getFormVersion($checklistScheduling->checklist_id, $hash),
                            'date' => $startTimestamp,
                            'type' => 0
                        ]);
                    }

                    /**
                     * Scheduling
                     * **/
                }

                self::recordImport([
                    'checklist_id' => isset($template->id) ? $template->id : $request->checklist,
                    'file_name' => $file->getClientOriginalName(),
                    'success' => $successCount,
                    'error' => $errorCount,
                    'status' => $successCount == 0 ? 2 : (
                        $errorCount > 0 ? 3 : 1
                    ),
                    'response' => $response,
                    'leave_blank' => $leaveBlank
                ], $file, true);
                
                \DB::commit();
                return response()->json(['status' => true, 'message' => 'Import scheduled successfully.']);

            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error('ERROR ON SCHEDULE IMPORT:' . $e->getMessage() . ' ON LINE ' . $e->getLine());
                return response()->json(['status' => false, 'message' => 'Something went wrong.']);
            }
        }

        $page_title = 'Import Checklist Scheduling';
        $page_description = 'import Checklist scheduling';

        return view('checklist-scheduling.import', compact('page_title', 'page_description', 'template', 'id'));
    }

    public static function recordImport($data, $file, $canRewrite = false) {
        try {
            $originalPath = storage_path('app/public/scheduling-imports/original');
            $modifiedPath = storage_path('app/public/scheduling-imports/modified');
            if (!file_exists($originalPath)) {
                mkdir($originalPath, 0777, true);
            }
            if (!file_exists($modifiedPath)) {
                mkdir($modifiedPath, 0777, true);
            }
            $modified = $original = null;
            if ($canRewrite) {
                $fileName = date('YmdHis') . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($originalPath, $fileName);
                $modified = $original = $fileName;
                /**
                 * Update XLSX
                 ****/
                $inputPath = "{$originalPath}/{$fileName}";
                $outputPath = "{$modifiedPath}/{$fileName}";
                
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
                $spreadsheet = $reader->load($inputPath);
                $worksheet = $spreadsheet->getActiveSheet();
                
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                
                $worksheet->setCellValueByColumnAndRow($highestColumnIndex + 1, 1, 'Status');
                $worksheet->setCellValueByColumnAndRow($highestColumnIndex + 2, 1, 'Message');
                
                $iteration = 0;

                for ($row = 2; $row <= $highestRow; $row++) {
                    if (isset($data['response'][$iteration])) {
                        $worksheet->setCellValueByColumnAndRow($highestColumnIndex + 1, $row, 'Error');
                        $worksheet->setCellValueByColumnAndRow($highestColumnIndex + 2, $row, $data['response'][$iteration]);
                    } else if (isset($data['leave_blank'][$iteration])) {
                        $worksheet->setCellValueByColumnAndRow($highestColumnIndex + 1, $row, '');
                        $worksheet->setCellValueByColumnAndRow($highestColumnIndex + 2, $row, '');
                    } else if (isset($data['skip'][$iteration])) {
                        $worksheet->setCellValueByColumnAndRow($highestColumnIndex + 1, $row, 'Skip');
                        $worksheet->setCellValueByColumnAndRow($highestColumnIndex + 2, $row, $data['skip'][$iteration]);
                    } else {
                        $worksheet->setCellValueByColumnAndRow($highestColumnIndex + 1, $row, 'Success');
                        $worksheet->setCellValueByColumnAndRow($highestColumnIndex + 2, $row, '');
                    }
                    $iteration++;
                }
                
                $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save($outputPath);
                
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
                /**
                 * Update XLSX
                 ****/
                
            } else {
                $fileName = date('YmdHis') . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($originalPath, $fileName);
                $modified = $original = $fileName;
            }
            SchedulingImport::create([
                'checklist_id' => $data['checklist_id'],
                'file_name' => $data['file_name'],
                'success' => $data['success'],
                'error' => $data['error'],
                'status' => $data['status'],
                'skip' => isset($data['skip_count']) ? $data['skip_count'] : 0,
                'original_file' => $original,
                'modified_file' => $modified,
                'uploaded_by' => auth()->check() ? auth()->user()->id : null,
                'response' => $data['response']
            ]);
        } catch (\Exception $e) {
            \Log::error('SCHEDULING IMPORT ERROR WHILE LOGGING : ' . $e->getMessage() . ' ON LINE : ' . $e->getLine());
        }
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        if (!$ids || !is_array($ids)) {
            return response()->json(['message' => 'Invalid request'], 400);
        }

        \DB::beginTransaction();

        try {
            foreach ($ids as $id) {
                ChecklistScheduling::where('id', $id)->delete();
                $extras = ChecklistSchedulingExtra::where('checklist_scheduling_id', $id)->get();
                foreach ($extras as $extra) {
                    ChecklistTask::where('checklist_scheduling_id', $extra->id)->whereNotIn('status', [2, 3])->scheduling()->delete();
                    $extra->delete();
                }
            }

            \DB::commit();
            return response()->json(['message' => 'Selected schedules have been deleted.']);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['message' => 'Invalid request'], 400);
        }
    }

}
