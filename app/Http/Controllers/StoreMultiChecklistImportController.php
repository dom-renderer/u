<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ChecklistSchedulingController;
use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\ChecklistSchedulingExtra;
use App\Models\ChecklistScheduling;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ChecklistTask;
use Illuminate\Http\Request;
use App\Models\Designation;
use App\Models\DynamicForm;
use App\Helpers\Helper;
use App\Models\Store;
use App\Models\User;
use stdClass;

class StoreMultiChecklistImportController extends Controller
{

    private static $openingChecklist = [
        100
    ];

    private static $shallowCleaning = [
        101
    ];

    private static $every4Hours = [
        102
    ];

    private static $dailyCleaning = [
        103
    ];

    private static $deepCleaning = [
        104
    ];

    private static $htr = [
        105
    ];

    private static $shiftHandover = [
        106
    ];

    private static $closingChecklist = [
        107
    ];

    private static $makeDeepCleanStaticOnMonday = true;

    public function import(Request $request) {
        if ($request->method() == 'POST' && $request->ajax()) {
            
            $request->validate([
                'import' => 'required|file|mimes:xlsx,xls',
            ]);

            $file = $request->file('import');
            $type = $file->getClientOriginalExtension();

            $response = $leaveBlank = $skipped = [];
            $errorCount = $successCount = $skipCount = 0;

            if (!in_array($type, ['xlsx'])) {

                ChecklistSchedulingController::recordImport([
                    'checklist_id' => null,
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
                'allow reschedule',
                'checklists',
                'frequency',
                'interval',
                'specific week days',
                'specific week days time',
                'perpetual'
            ];

            $isFileValid = false;
            $data = $duplicateRecord = [];
            $frequencySlug = [
                'every_hour' => 0,
                'every_n_hour' => 1,
                'daily' => 2,
                'every_n_day' => 3,
                'weekly' => 4,
                'biweekly' => 5,
                'monthly' => 6,
                'bimonthly' => 7,
                'quaerterly' => 8,
                'semi_annually' => 9,
                'anually' => 10,
                'specific_week_days' => 11
            ];

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
                        strtolower($headerRow[1]) == $expectedHeaders[1] &&
                        strtolower($headerRow[2]) == $expectedHeaders[2] &&
                        strtolower($headerRow[3]) == $expectedHeaders[3] &&
                        strtolower($headerRow[4]) == $expectedHeaders[4] &&
                        strtolower($headerRow[5]) == $expectedHeaders[5] &&
                        strtolower($headerRow[6]) == $expectedHeaders[6] &&
                        strtolower($headerRow[7]) == $expectedHeaders[7] &&
                        strtolower($headerRow[8]) == $expectedHeaders[8] &&
                        strtolower($headerRow[9]) == $expectedHeaders[9] &&
                        strtolower($headerRow[10]) == $expectedHeaders[10] &&
                        strtolower($headerRow[11]) == $expectedHeaders[11] &&
                        strtolower($headerRow[12]) == $expectedHeaders[12] &&
                        strtolower($headerRow[13]) == $expectedHeaders[13] &&
                        strtolower($headerRow[14]) == $expectedHeaders[14]
                    ) {
                        $isFileValid = true;
                    }
                }

            } catch (\Exception $e) {
                ChecklistSchedulingController::recordImport([
                    'checklist_id' => null,
                    'file_name' => $file->getClientOriginalName(),
                    'success' => 0,
                    'error' => 0,
                    'status' => 2,
                    'response' => [
                        'Error reading xlsx file: ' . $e->getMessage()
                    ]
                ], $file);
                
                return response()->json(['status' => false, 'message' => 'Error reading xlsx file.', 'er' => $e->getMessage()]);
            }

            $data = array_splice($data, 1, count($data));

            if (empty($data)) {
                ChecklistSchedulingController::recordImport([
                    'checklist_id' => null,
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

            // Final Code
            DB::beginTransaction();

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
                            $response[$key] = 'Store with given code does not exists at A' . ($key + 1);
                            $errorCount++;
                        }

                    } else {
                        if (!in_array($row[0], $getAllStores)) {
                            $errorCount++;
                            $response[$key] = 'Store with given code does not exists at A' . ($key + 1);
                            continue;
                        } else {
                            $store = Store::where('code', $row[0])->first();
                        }
                    }

                    $maker = User::where('employee_id', $store->code)->whereNotNull('employee_id')->where('employee_id', '!=', '')->first();

                    if (!$maker) {
                        $errorCount++;
                        $response[$key] = 'A user with store-phone role does not exists associated to store at A' . ($key + 1);
                        continue;
                    }
                    
                    if (!empty($row[1])) {
                        $exploded = explode('_', $row[1]);
                        $checker = User::where('employee_id', $exploded[0])->whereNotNull('employee_id')->where('employee_id', '!=', '')->first();

                        if (!$checker) {
                            $errorCount++;
                            $response[$key] = 'Checker employee does not exists at B' . ($key + 1);
                            continue;
                        }
                    }

                    if (!isset($row[9])) {
                        $errorCount++;
                        $response[$key] = 'Checklists are not defined at J' . ($key + 1);
                        continue;
                    }

                    $allOfTheTemplates = trim($row[9]);
                    $allOfTheTemplates = explode(';', $allOfTheTemplates);
                    $allOfTheTemplates = array_filter($allOfTheTemplates);

                    if (empty($allOfTheTemplates)) {
                        $errorCount++;
                        $response[$key] = 'Checklists are not defined at J' . ($key + 1) . ', Make sure checklists names are semicolon separated.';
                        continue;
                    }

                    $allOfTheTemplates = DynamicForm::whereIn('name', $allOfTheTemplates)->where('type', 0)->get();

                    if ($allOfTheTemplates->isEmpty()) {
                        $errorCount++;
                        $response[$key] = 'Checklists are not defined at J' . ($key + 1) . ', Make sure checklists names are semicolon separated and namings same as system checklists.';
                        continue;
                    }

                    // Duplicate Check
                    $keysOfThis = collect($data)->filter(function ($item) use ($row) {
                        return $item === $row;
                    })->keys();

                    if ($keysOfThis->isNotEmpty()) {
                        foreach ($keysOfThis as $keysOfThisK) {
                            if ($key != $keysOfThisK) {
                                $duplicateRecord[] = $keysOfThisK;
                            }
                        }
                    }

                    if (is_array($duplicateRecord) && in_array($key, $duplicateRecord)) {
                        $skipCount++;
                        $skipped[$key] = 'Record is ignored due to duplication across file';
                        continue;
                    }
                    // Duplicate Check

                    foreach ($allOfTheTemplates as $template) {
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

                        $startDateRaw = $row[2];
                        $startDate = is_numeric($startDateRaw)
                            ? Date::excelToDateTimeObject($startDateRaw)->format('Y-m-d')
                            : Helper::parseFlexibleDate($startDateRaw);

                        $startDate = date('Y-m-d', strtotime($startDate));

                        $startTimeRaw = $row[3];
                        $startTime = is_numeric($startTimeRaw)
                            ? Date::excelToDateTimeObject($startTimeRaw)->format('H:i:s')
                            : date('H:i:s', strtotime($startTimeRaw));

                        $endDateRaw = $row[4];
                        $endDate = is_numeric($endDateRaw)
                            ? Date::excelToDateTimeObject($endDateRaw)->format('Y-m-d')
                            : Helper::parseFlexibleDate($endDateRaw);
                        $endDate = date('Y-m-d', strtotime($endDate));                            

                        $endTimeRaw = $row[5];
                        $endTime = is_numeric($endTimeRaw)
                            ? Date::excelToDateTimeObject($endTimeRaw)->format('H:i:s')
                            : date('H:i:s', strtotime($endTimeRaw));

                        $startTimeOfStore = isset($store->open_time) ? date('H:i:s', strtotime($store->open_time)) : $startTime;
                        $endTimeOfStore = isset($store->close_time) ? date('H:i:s', strtotime($store->close_time)) : $endTime;

                        $opsstartTimeOfStore = isset($store->ops_start_time) ? date('H:i:s', strtotime($store->ops_start_time)) : $startTimeOfStore;
                        $opsendTimeOfStore = isset($store->ops_end_time) ? date('H:i:s', strtotime($store->ops_end_time)) : $endTimeOfStore;

                        $startTimestamp = $startDate . ' ' . $startTime;
                        $endTimestamp = $endDate . ' ' . $endTime;

                        $startTimestampWithStore = $startDate . ' ' . $startTimeOfStore;
                        $endTimestampWithStore = $endDate . ' ' . $endTimeOfStore;

                        $startTimestampWithOpsStore = $startDate . ' ' . $opsstartTimeOfStore;
                        $endTimestampWithOpsStore = $endDate . ' ' . $opsendTimeOfStore;


                        $hRequiredRaw = is_numeric($row[6])
                            ? Date::excelToDateTimeObject($row[6])->format('H:i:s')
                            : '08:00';

                        $graceRaw = is_numeric($row[7])
                            ? Date::excelToDateTimeObject($row[7])->format('H:i:s')
                            : '08:00';

                        $weekDayTime = is_numeric($row[13])
                            ? Date::excelToDateTimeObject($row[13])->format('H:i:s')
                            : '00:00';

                        /**
                         * Scheduling
                         * **/

                        $successCount++;

                        $iterateNTimes = [$store->code];

                        if ($hasMultipleRecord) {
                            $iterateNTimes = $explodeStoreString;
                        }

                        $iterateNTimes = Store::whereIn('code', $iterateNTimes)->get();

                        foreach ($iterateNTimes as $iteratingStore) {
                            if (empty($makerBranchType)) {
                                $errorCount++;
                                $response[$key] = 'store-phone User has not valid role at A' . ($key + 1);
                                continue 2;
                            }

                            if (empty($makerBranch)) {
                                $createdDesignation = Designation::updateOrCreate([
                                    'user_id' => $maker->id,
                                    'type_id' => $iteratingStore->id,
                                    'type' => 1
                                ]);

                                if (!$createdDesignation) {
                                    $errorCount++;
                                    $response[$key] = 'store-phone user is not in any required branch or location at A' . ($key + 1);
                                    continue 2;
                                }
                            }

                            $weekDays = isset($row[12]) ? $row[12] : '';

                            if (!empty($weekDays)) {
                                $weekDays = explode(';', preg_replace('/\s+/', '', $weekDays));
                                $weekDays = implode(',', $weekDays);
                            }

                            $theFrequency = isset($frequencySlug[$row[10]]) ? $frequencySlug[$row[10]] : 12;
                            $theInterval = isset($row[11]) && is_numeric($row[11]) && $row[11] > 0 ? $row[11] : 0;
                            $perP = isset($row[14]) && strtolower($row[14]) == 'yes' ? 1 : 0;

                            if ($theFrequency == 12) {
                                $checklistScheduling = ChecklistScheduling::create([
                                    'checklist_id' => $template->id,
                                    'frequency_type' => $theFrequency,

                                    'checker_branch_type' => $checkerBranchType,
                                    'checker_branch_id' => $checkerBranch,
                                    'checker_user_id' => $checker->id,

                                    'hours_required' => $hRequiredRaw,
                                    'start_grace_time' => $graceRaw,
                                    'end_grace_time' => $graceRaw,
                                    'allow_rescheduling' => isset($row[8]) && strtolower($row[8]) == 'yes' ? 1 : 0,
                                    'is_import' => 1,

                                    'start_at' => date('H:i:s', strtotime($startTime)),
                                    'completed_by' => date('H:i:s', strtotime($endTime)),

                                    'interval' => $theInterval,
                                    'weekdays' => $weekDays,
                                    'weekday_time' => $weekDayTime,
                                    'perpetual' => $perP,
                                    'start' => $startTimestamp,
                                    'end' => $endTimestamp,
                                    'completion_data' => [],
                                    'import_type' => 1
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
                            } else {

                                $userForMatrix = $maker->id;

                                if (is_numeric($userForMatrix)) {
                                    $finalCreationArray = [[
                                        'user_id' => $userForMatrix,
                                        'role_id' => Helper::$roles['store-phone'],
                                        'locations' => [
                                            $iteratingStore->id
                                        ]
                                    ]];

                                    $stchkallTimestampts = [];
                                    $stchkallDays = null;
                                    $stchkweekdayTime = null;

                                    $stchktype = 0;
                                    $stchktypeSlug = 'hourly';
                                    $shouldSpecifcDaysHaveInterval = false;

                                    if ($theFrequency == 0) {
                                        $stchktype = 0;
                                        $stchktypeSlug = 'hourly';
                                    } else if ($theFrequency == 1) {
                                        $stchktype = 1;
                                        $stchktypeSlug = $theInterval . ' hour';
                                    } else if ($theFrequency == 2) {
                                        $stchktype = 2;
                                        $stchktypeSlug = 'daily';
                                    } else if ($theFrequency == 3) {
                                        $stchktype = 3;
                                        $stchktypeSlug = $theInterval . ' day';
                                    } else if ($theFrequency == 4) {
                                        $stchktype = 4;
                                        $stchktypeSlug = 'weekly';
                                    } else if ($theFrequency == 5) {
                                        $stchktype = 5;
                                        $stchktypeSlug = 'biweekly';
                                    } else if ($theFrequency == 6) {
                                        $stchktype = 6;
                                        $stchktypeSlug = 'monthly';
                                    } else if ($theFrequency == 7) {
                                        $stchktype = 7;
                                        $stchktypeSlug = 'bimonthly';
                                    } else if ($theFrequency == 8) {
                                        $stchktype = 8;
                                        $stchktypeSlug = 'quarterly';
                                    } else if ($theFrequency == 9) {
                                        $stchktype = 9;
                                        $stchktypeSlug = 'semiannual';
                                    } else if ($theFrequency == 10) {
                                        $stchktype = 10;
                                        $stchktypeSlug = 'annual';
                                    } else if ($theFrequency == 11) {
                                        $stchktype = 11;
                                        $stchktypeSlug = 'specific_days';
                                        $stchkallDays = explode(',', $weekDays);
                                        $stchkweekdayTime = $weekDayTime;
                                        $shouldSpecifcDaysHaveInterval = true;
                                    }

                                    if (!empty($finalCreationArray)) {

                                        $checklistScheduling = ChecklistScheduling::create([
                                            'checklist_id' => $template->id,

                                            'start_at' => date('H:i:s', strtotime($startTime)),
                                            'completed_by' => date('H:i:s', strtotime($endTime)),

                                            'hours_required' => $hRequiredRaw,
                                            'start_grace_time' => $graceRaw,
                                            'end_grace_time' => $graceRaw,

                                            'checker_branch_type' => $checkerBranchType,
                                            'checker_branch_id' => $checkerBranch,
                                            'checker_user_id' => $checker->id,

                                            'frequency_type' => $stchktype,
                                            'interval' => $theInterval,
                                            'weekdays' => $theFrequency == 'specific_days' ? $weekDays : null,
                                            'weekday_time' => $theFrequency == 'specific_days' ? $weekDayTime : null,
                                            'perpetual' => $perP,
                                            'start' => $startTimestamp,
                                            'end' => $endTimestamp,
                                        ]);

                                        $finalDateArr = [];

                                        if (in_array($template->id, self::$closingChecklist)) {
                                            if ($perP != 1) {
                                                foreach (self::generateDateRanges($startTimestampWithStore, $endTimestampWithStore) as $iterationRow) {
                                                    $finalDateArr[] = \Carbon\Carbon::parse($iterationRow['end'])->subHours(2);
                                                }
                                            } else {
                                                $endTimestampWithStoreTemp = strtotime('Y-m-d H:i:s', strtotime("{$startTimestampWithStore} +30 day"));
                                                foreach (self::generateDateRanges($startTimestampWithStore, $endTimestampWithStoreTemp) as $iterationRow) {
                                                    $finalDateArr[] = \Carbon\Carbon::parse($iterationRow['end'])->subHours(2);
                                                }
                                            }
                                        } else if (in_array($template->id, self::$openingChecklist)) {
                                            if ($perP != 1) {
                                                foreach (self::generateDateRanges($startTimestampWithStore, $endTimestampWithStore) as $iterationRow) {
                                                    $finalDateArr[] = \Carbon\Carbon::parse($iterationRow['start']);
                                                }
                                            } else {
                                                $endTimestampWithStoreTemp = strtotime('Y-m-d H:i:s', strtotime("{$startTimestampWithStore} +30 day"));
                                                foreach (self::generateDateRanges($startTimestampWithStore, $endTimestampWithStoreTemp) as $iterationRow) {
                                                    $finalDateArr[] = \Carbon\Carbon::parse($iterationRow['start']);
                                                }
                                            }
                                        } else if (in_array($template->id, self::$shallowCleaning)) {
                                            if ($perP != 1) {
                                                foreach (self::generateDateRanges($startTimestampWithStore, $endTimestampWithStore) as $iterationRow) {
                                                    $tempArrStore = \App\Helpers\Frequencyv2::generate($iterationRow['start'], $iterationRow['end'], '2 hour', $stchkallDays, $stchkweekdayTime);
                                                    array_shift($tempArrStore);
                                                    $finalDateArr[] = $tempArrStore;
                                                }
                                            } else {
                                                $endTimestampWithStoreTemp = strtotime('Y-m-d H:i:s', strtotime("{$startTimestampWithStore} +30 day"));
                                                foreach (self::generateDateRanges($startTimestampWithStore, $endTimestampWithStoreTemp) as $iterationRow) {
                                                    $tempArrStore = \App\Helpers\Frequencyv2::generate($iterationRow['start'], $iterationRow['end'], '2 hour', $stchkallDays, $stchkweekdayTime);
                                                    array_shift($tempArrStore);
                                                    $finalDateArr[] = $tempArrStore;
                                                }
                                            }
                                        } else if (in_array($template->id, self::$every4Hours)) {
                                            if ($perP != 1) {
                                                foreach (self::generateDateRanges($startTimestampWithStore, $endTimestampWithStore) as $iterationRow) {
                                                    $tempArrStore = \App\Helpers\Frequencyv2::generate($iterationRow['start'], $iterationRow['end'], '4 hour', $stchkallDays, $stchkweekdayTime);
                                                    array_shift($tempArrStore);
                                                    $finalDateArr[] = $tempArrStore;
                                                }
                                            } else {
                                                $endTimestampWithStoreTemp = strtotime('Y-m-d H:i:s', strtotime("{$startTimestampWithStore} +30 day"));
                                                foreach (self::generateDateRanges($startTimestampWithStore, $endTimestampWithStoreTemp) as $iterationRow) {
                                                    $tempArrStore = \App\Helpers\Frequencyv2::generate($iterationRow['start'], $iterationRow['end'], '4 hour', $stchkallDays, $stchkweekdayTime);
                                                    array_shift($tempArrStore);
                                                    $finalDateArr[] = $tempArrStore;
                                                }
                                            }
                                        } else if (in_array($template->id, self::$shiftHandover)) {
                                            if ($perP != 1) {
                                                foreach (self::generateDateRanges($startTimestampWithStore, $endTimestampWithStore) as $iterationRow) {
                                                    $finalDateArr[] = \Carbon\Carbon::parse($iterationRow['start'])->setTime(14, 0);
                                                }
                                            } else {
                                                $endTimestampWithStoreTemp = strtotime('Y-m-d H:i:s', strtotime("{$startTimestampWithStore} +30 day"));
                                                foreach (self::generateDateRanges($startTimestampWithStore, $endTimestampWithStoreTemp) as $iterationRow) {
                                                    $finalDateArr[] = \Carbon\Carbon::parse($iterationRow['start'])->setTime(14, 0);
                                                }
                                            }
                                        } else if (in_array($template->id, self::$deepCleaning)) {
                                            if ($perP != 1) {
                                                $finalDateArr[] = \App\Helpers\Frequencyv2::generate($startTimestampWithStore, $endTimestampWithStore, 'specific_days', self::$makeDeepCleanStaticOnMonday ? ['monday'] : $stchkallDays, date("H:i", strtotime("$startTimestampWithStore")));
                                            } else {
                                                $endTimestampWithStoreTemp = strtotime('Y-m-d H:i:s', strtotime("{$startTimestampWithStore} +30 day"));
                                                $finalDateArr[] = \App\Helpers\Frequencyv2::generate($startTimestampWithStore, $endTimestampWithStoreTemp, 'specific_days', self::$makeDeepCleanStaticOnMonday ? ['monday'] : $stchkallDays, date("H:i", strtotime("$startTimestampWithStore")));
                                            }
                                        } else if (in_array($template->id, self::$htr)) {
                                            if ($perP != 1) {
                                                foreach (self::generateDateRanges($startTimestampWithStore, $endTimestampWithStore) as $iterationRow) {
                                                    $finalDateArr[] = \Carbon\Carbon::parse($iterationRow['start']);
                                                }
                                            } else {
                                                $endTimestampWithStoreTemp = strtotime('Y-m-d H:i:s', strtotime("{$startTimestampWithStore} +30 day"));
                                                foreach (self::generateDateRanges($startTimestampWithStore, $endTimestampWithStoreTemp) as $iterationRow) {
                                                    $finalDateArr[] = \Carbon\Carbon::parse($iterationRow['start']);
                                                }
                                            }
                                        } else if (in_array($template->id, self::$dailyCleaning)) {
                                            if ($perP != 1) {
                                                foreach (self::generateDateRanges($startTimestampWithStore, $endTimestampWithStore) as $iterationRow) {
                                                    $finalDateArr[] = \Carbon\Carbon::parse($iterationRow['start']);
                                                }
                                            } else {
                                                $endTimestampWithStoreTemp = strtotime('Y-m-d H:i:s', strtotime("{$startTimestampWithStore} +30 day"));
                                                foreach (self::generateDateRanges($startTimestampWithStore, $endTimestampWithStoreTemp) as $iterationRow) {
                                                    $finalDateArr[] = \Carbon\Carbon::parse($iterationRow['start']);
                                                }
                                            }
                                        } else {
                                            if (in_array($stchktype, [0, 1, 2])) {
                                                if ($perP != 1) {
                                                    foreach (self::generateDateRanges($startTimestampWithStore, $endTimestampWithStore) as $iterationRow) {
                                                        $finalDateArr[] = \App\Helpers\Frequencyv2::generate($iterationRow['start'], $iterationRow['end'], $stchktypeSlug, $stchkallDays, $stchkweekdayTime);
                                                    }
                                                } else {
                                                    $endTimestampWithStoreTemp = strtotime('Y-m-d H:i:s', strtotime("{$startTimestampWithStore} +30 day"));
                                                    foreach (self::generateDateRanges($startTimestampWithStore, $endTimestampWithStoreTemp) as $iterationRow) {
                                                        $finalDateArr[] = \App\Helpers\Frequencyv2::generate($iterationRow['start'], $iterationRow['end'], $stchktypeSlug, $stchkallDays, $stchkweekdayTime);
                                                    }
                                                }
                                            } else {
                                                $weekSpecifcdayInterval = [];
                                                if ($shouldSpecifcDaysHaveInterval) {
                                                    $weekSpecifcdayInterval = [
                                                        'has_interval' => 1,
                                                        'interval' => $theInterval > 0 ? $theInterval : 2,
                                                        'store_start_time' => $startTimeOfStore,
                                                        'store_end_time' => $endTimeOfStore
                                                    ];
                                                }

                                                if ($perP != 1) {
                                                    $finalDateArr[] = \App\Helpers\Frequencyv2::generate($startTimestamp, $endTimestamp, $stchktypeSlug, $stchkallDays, $stchkweekdayTime, $weekSpecifcdayInterval);
                                                } else {
                                                    $endTimestampTemp = strtotime('Y-m-d H:i:s', strtotime("{$startTimestamp} +30 day"));
                                                    $finalDateArr[] = \App\Helpers\Frequencyv2::generate($startTimestamp, $endTimestampTemp, $stchktypeSlug, $stchkallDays, $stchkweekdayTime, $weekSpecifcdayInterval);
                                                }
                                            }
                                        }

                                        if (in_array($template->id, self::$deepCleaning)) {
                                            $tempLastDateArray = self::getLastDatesOfMonth($startTimestampWithStore, $endTimestampWithStore);

                                            if (is_iterable($tempLastDateArray)) {
                                                foreach ($tempLastDateArray as $lastDayOfMonth) {
                                                    $lastDayOfMonth = \Carbon\Carbon::parse($lastDayOfMonth)->endOfMonth();

                                                    if (!$lastDayOfMonth->isMonday()) {
                                                        $htrLastDayOfMonthStart = \Carbon\Carbon::parse($lastDayOfMonth)->setTimeFrom(\Carbon\Carbon::parse($startTimestampWithStore));
                                                        $htrLastDayOfMonthEnd = \Carbon\Carbon::parse($lastDayOfMonth)->setTimeFrom(\Carbon\Carbon::parse($endTimestampWithStore));

                                                        if ($htrLastDayOfMonthEnd->lessThan($htrLastDayOfMonthStart)) {
                                                            $htrLastDayOfMonthEnd->addDay();
                                                        }

                                                        foreach (self::generateDateRanges($htrLastDayOfMonthStart, $htrLastDayOfMonthEnd) as $iterationRow) {
                                                            $finalDateArr[] = \Carbon\Carbon::parse($iterationRow['start']);
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        $finalDateArr = Arr::flatten($finalDateArr);
                                        $finalDateArr = array_filter($finalDateArr);

                                        \App\Jobs\GenerateChecklistTasksExtra::dispatch($checklistScheduling, $finalDateArr, $finalCreationArray);
                                    }
                                }
                            }
                        }

                        /**
                         * Scheduling
                         * **/
                    }
                }

                ChecklistSchedulingController::recordImport([
                    'checklist_id' => null,
                    'file_name' => $file->getClientOriginalName(),
                    'success' => $successCount,
                    'error' => $errorCount,
                    'skip_count' => $skipCount,
                    'status' => $successCount == 0 ? 2 : (
                        $errorCount > 0 ? 3 : 1
                    ),
                    'response' => $response,
                    'leave_blank' => $leaveBlank,
                    'skip' => $skipped
                ], $file, true);
                
                DB::commit();
                return response()->json(['status' => true, 'message' => 'Import scheduled successfully.']);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('ERROR ON SCHEDULE IMPORT:' . $e->getMessage() . ' ON LINE ' . $e->getLine());
                return response()->json(['status' => false, 'message' => 'Something went wrong.', 'err' => $e->getMessage(), 'line' => $e->getLine()]);
            }            
            // Final Code

        }
    }

    public static function getLastDatesOfMonth($startTimestamp, $endTimestamp) {
        $start = \Carbon\Carbon::parse($startTimestamp);
        $end = \Carbon\Carbon::parse($endTimestamp)->endOfDay();

        $dates = [];

        while ($start <= $end) {
            $lastDayOfMonth = $start->copy()->endOfMonth()->startOfDay();
            $dates[] = $lastDayOfMonth->format('Y-m-d H:i:s');
            
            $start->addMonthNoOverflow();
        }

        return $dates;
    }

    public function generateDateRanges($startTimestampWithStore, $endTimestampWithStore)
    {
        $startDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $startTimestampWithStore);
        $endDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $endTimestampWithStore);

        $startTime = $startDate->format('H:i:s');
        $endTime = $endDate->format('H:i:s');

        if ($startDate->gt($endDate)) {
            return [];
        }

        $result = collect()
            ->merge(collect(range(0, $startDate->diffInDays($endDate)))
                ->map(fn($i) => [
                    'start' => $startDate->copy()->addDays($i)->format('Y-m-d') . " {$startTime}",
                    'end' => self::adjustEndDate($startDate->copy()->addDays($i), $endTime),
                ])
            )
            ->values()
            ->toArray();

        return $result;
    }

    public static function adjustEndDate($currentDate, $endTime)
    {
        $endDateTime = $currentDate->copy()->format('Y-m-d') . " {$endTime}";
        $endDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $endDateTime);

        if ($endDate->lt($currentDate)) {
            $endDate->addDay();
        }

        return $endDate->format('Y-m-d H:i:s');
    }

    public static function getMidpointOrStartTime($start = null, $end = null)
    {
        try {
            if (!$start) {
                return \Carbon\Carbon::today()->startOfDay()->toDateTimeString();
            }

            $start = \Carbon\Carbon::parse($start);
            $end = $end ? \Carbon\Carbon::parse($end) : null;

            if ($end && $start->greaterThan($end)) {
                return $start->toDateTimeString();
            }

            if ($end) {
                $diffInSeconds = $start->diffInSeconds($end);
                $midpoint = $start->copy()->addSeconds($diffInSeconds / 2);
                return $midpoint->toDateTimeString();
            }

            return $start->toDateTimeString();
        } catch (\Exception $e) {
            return $start ? $start->toDateTimeString() : \Carbon\Carbon::today()->startOfDay()->toDateTimeString();
        }
    }

}