<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ChecklistSchedulingController;
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

class MultiChecklistImportController extends Controller
{
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
                'checklists'
            ];

            $isFileValid = false;
            $data = $duplicateRecord = [];

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
                        strtolower($headerRow[9]) == $expectedHeaders[9] &&
                        strtolower($headerRow[10]) == $expectedHeaders[10]
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
                    
                    if (!empty($row[1])) {
                        $exploded = explode('_', $row[1]);
                        $maker = User::where('employee_id', $exploded[0])->whereNotNull('employee_id')->where('employee_id', '!=', '')->first();

                        if (!$maker) {
                            $errorCount++;
                            $response[$key] = 'DOM does not exists at B' . ($key + 1);
                            continue;
                        }
                    }
                    
                    if (!empty($row[2])) {
                        $exploded = explode('_', $row[2]);
                        $checker = User::where('employee_id', $exploded[0])->whereNotNull('employee_id')->where('employee_id', '!=', '')->first();

                        if (!$checker) {
                            $errorCount++;
                            $response[$key] = 'Checker employee does not exists at B' . ($key + 1);
                            continue;
                        }
                    }

                    if (!isset($row[10])) {
                        $errorCount++;
                        $response[$key] = 'Checklists are not defined at K' . ($key + 1);
                        continue;
                    }

                    $allOfTheTemplates = trim($row[10]);
                    $allOfTheTemplates = explode(';', $allOfTheTemplates);
                    $allOfTheTemplates = array_filter($allOfTheTemplates);

                    if (empty($allOfTheTemplates)) {
                        $errorCount++;
                        $response[$key] = 'Checklists are not defined at K' . ($key + 1) . ', Make sure checklists names are semicolon separated.';
                        continue;
                    }

                    $allOfTheTemplates = DynamicForm::whereIn('name', $allOfTheTemplates)->where('type', 0)->get();

                    if ($allOfTheTemplates->isEmpty()) {
                        $errorCount++;
                        $response[$key] = 'Checklists are not defined at K' . ($key + 1) . ', Make sure checklists names are semicolon separated and namings same as system checklists.';
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

                        $successCount++;

                        $iterateNTimes = [$store->code];

                        if ($hasMultipleRecord) {
                            $iterateNTimes = $explodeStoreString;
                        }

                        $iterateNTimes = Store::whereIn('code', $iterateNTimes)->get();

                        foreach ($iterateNTimes as $iteratingStore) {
                            if (empty($makerBranchType)) {
                                $errorCount++;
                                $response[$key] = 'User has not valid role at B' . ($key + 1);
                                continue 2;
                            }

                            if (empty($makerBranch)) {
                                $errorCount++;
                                $response[$key] = 'User is not in any required branch or location at B' . ($key + 1);
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

        } else {
            return view('checklists.multi-import');
        }
    }
}
