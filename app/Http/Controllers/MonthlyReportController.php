<?php

namespace App\Http\Controllers;

use App\Models\MonthlyReportExport;
use App\Models\ChecklistTask;
use Illuminate\Http\Request;
use App\Models\DynamicForm;
use App\Helpers\Helper;
use App\Models\City;
use App\Models\User;

class MonthlyReportController extends Controller
{
   public function index(Request $request) {
      if ($request->ajax()) {

      }
      
      $page_title = 'Monthly reports - DoM Checklist';
      $reports =  MonthlyReportExport::latest()->get();
      $checklists =  DynamicForm::select('id', 'name')->orderBy('ordering')->pluck('name', 'id')->toArray();
      $users =  User::selectRaw("id, CONCAT(name, ' ', middle_name, ' ', last_name) as name")->pluck('name', 'id')->toArray();
      $cities =  City::selectRaw("city_id as id, city_name as name")->pluck('name', 'id')->toArray();

      return view('dashboard.monthly-report', compact('page_title', 'reports', 'checklists', 'users', 'cities'));
   }

    public function export(Request $request) {

        $checklistsToInclude = DynamicForm::where('type', 0)->when($request->sop != 'all', function ($innerBuilder) {
            $innerBuilder->where('id', request('sop'));
        })
        ->orderBy('ordering')
        ->get();

        $theFinalArray = [];

        foreach ($checklistsToInclude as $checklistsToIncludeRow) {

            $templates = ChecklistTask::with(['parent.parent.checklist', 'parent.actstore', 'parent.user'])
            ->scheduling()
            ->whereHas('parent.parent.checklist', function ($innerBuilder) use ($checklistsToIncludeRow) {
                return $innerBuilder->where('id', $checklistsToIncludeRow->id);
            })
            ->when($request->dom != 'all', function ($builder) {
                $builder->whereHas('parent', function ($innerBuilder) {
                    return $innerBuilder->where('user_id', request('dom'));
                });
            })
            ->when($request->state != 'all' && !empty($request->state), function ($builder) {
                $builder->whereHas('parent.actstore.thecity', function ($innerBuilder) {
                    return $innerBuilder->where('city_state', request('state'));
                });
            })
            ->when($request->city != 'all' && !empty($request->city), function ($builder) {
                $builder->whereHas('parent.actstore', function ($innerBuilder) {
                    return $innerBuilder->where('city', request('city'));
                });
            })        
            ->whereIn('status', [Helper::$status['in-progress'], Helper::$status['in-verification'], Helper::$status['completed']])
            ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($request->start)))
            ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($request->end)))
            ->orderBy('date', 'ASC')
            ->get()
            ->groupBy(function ($task) {
                return optional(optional(optional($task->parent)->parent)->checklist)->id;
            });

            $mainArray = [];

            foreach ($templates as $templateId => $template) {

                $headers = ['DATE', 'TASK CODE', 'STORE NAME', 'STORE CODE', 'DOM', 'START TIME', 'END TIME', 'OPS TIME', 'STATUS'];
                $firstTask = $template->first();
                $formToPass = Helper::getVersionForm($firstTask->version_id);
                $isPointCList = Helper::isPointChecklist($formToPass);
                $totalSections = $justClassNames = [];

                if ($isPointCList) {
                    $headers = array_merge($headers, ["TOTAL QUESTIONS", "PASSED", "FAILED", "N/A", "PERCENTAGE", "RESULT"]);
                }

                if (!empty($firstTask)) {
                    $groupedHeaderData = [];
                    foreach ($formToPass as $keyOfPage => $page) {
                        if ($keyOfPage == 0) {
                            continue;
                        }

                        $nameOfHeader = collect($page)->where('type', 'header')->get(0)->label ?? '';

                        $totalSections[(':explodable:') . ($nameOfHeader == '' ? ('Section ' . chr($keyOfPage + 64)) : $nameOfHeader)] = $page;

                        foreach ($page as $field) {
                            if (property_exists($field, 'className')) {
                                if (!isset($groupedHeaderData[$field->className])) {
                                    $groupedHeaderData[$field->className][] = $field->label;
                                    $justClassNames[$field->className][] = $field;
                                } else {
                                    $groupedHeaderData[$field->className][] = $field->label;
                                    $justClassNames[$field->className][] = $field;                                
                                }
                            }
                        }
                    }

                    if (!empty($groupedHeaderData)) {
                        $duplicateSectionResultHeaders = [];
                        foreach ($totalSections as $totalSectionsRowKey => $totalSectionsRow) {
                            $explodedKey = explode(':explodable:', $totalSectionsRowKey);
                            $explodedKey = isset($explodedKey[1]) ? $explodedKey[1] : '';
                            // $duplicateSectionResultHeaders = array_merge($duplicateSectionResultHeaders, ["{$explodedKey} TOTAL QUESTIONS", "{$explodedKey} PASSED", "{$explodedKey} FAILED", "{$explodedKey} N/A", "{$explodedKey} PERCENTAGE", "{$explodedKey} RESULT"]);
                        }

                        $headers = array_merge($headers, \Arr::flatten($groupedHeaderData));
                        
                        if (!empty($duplicateSectionResultHeaders)) {
                            $headers = array_merge($headers, $duplicateSectionResultHeaders);
                        }

                        $mainArray[] = $headers;
                    }
                }

                $mainArray[] = ["", "", ""];

                foreach ($template as $task) {

                    $json = $task->data ?? [];
                    if (is_string($json)) {
                        $data = json_decode($json, true);
                    } else if (is_array($json)) {
                        $data = $json;
                    } else {
                        $data = [];
                    }
                    
                    $siteUrl = url('storage/workflow-task-uploads') . '/';

                    foreach ($data as &$item) {
                        if (!empty($item->isFile)) {
                            if (is_array($item->value)) {
                                $item->value = array_map(function ($v) use ($siteUrl) {
                                    return $siteUrl . ltrim($v, '/');
                                }, $item->value);

                                $item->value = implode(' ; ', $item->value);
                            } elseif (is_string($item->value)) {
                                $item->value = $siteUrl . ltrim($item->value, '/');
                            }
                        } else {
                            if (is_array($item->value)) {
                                $item->value = empty(trim(implode(', ', $item->value))) ? 'N/A' : implode(', ', $item->value);
                            } else {
                                $item->value = empty(trim($item->value)) ? 'N/A' : $item->value;
                            }
                        }
                    }

                    $date1 = \Carbon\Carbon::parse($task->started_at);
                    $date2 = \Carbon\Carbon::parse($task->completion_date);
                    $diff = $date1->diff($date2);

                    // Calculations
                        $varients = Helper::categorizePoints($task->data ?? []);

                        $total = count(Helper::selectPointsQuestions($task->data));
                        $toBeCounted = $total - count($varients['na']);

                        $failed = abs(count(array_column($varients['negative'], 'value')));
                        $achieved = $toBeCounted - abs($failed);
                        
                        if ($failed <= 0) {
                            $achieved = array_sum(array_column($varients['positive'], 'value'));
                        }
                        
                        if ($toBeCounted > 0) {
                            $percentage = number_format(($achieved / $toBeCounted) * 100, 2);
                        } else {
                            $percentage = 0;
                        }
                    // Calculations

                    $groupedData = [
                        date('d-m-Y', strtotime($task->date)), 
                        $task->code, 
                        $task->parent->actstore->name ?? '', 
                        $task->parent->actstore->code ?? '', 
                        ($task->parent->user->name ?? '') . ' ' . $task->parent->user->middle_name ?? '' . ' ' . $task->parent->user->last_name ?? '', 
                        date('d-m-Y H:i', strtotime($task->started_at)), 
                        !empty($task->completion_date) ? date('d-m-Y H:i', strtotime($task->completion_date)) : '', 
                        !empty($task->completion_date) ? "{$diff->d} days, {$diff->h} hours, {$diff->i} minutes" : '',
                        $task->status == 1 ? 'IN-PROGRESS' : ($task->status == 2 ? 'COMPLETED' : 'COMPLETED'),
                    ];

                    $resData = [
                        $total,
                        $achieved,
                        count($varients['negative']),
                        count($varients['na']),
                        "{$percentage}%",
                        $percentage > 80 ? "Pass" : "Fail"
                    ];

                    if ($isPointCList) {
                        $groupedData = array_merge($groupedData, $resData);
                    }

                    foreach ($justClassNames as $justRow) {
                        foreach ($justRow as $rowJust) {
                            $founded = false;
                            if (property_exists($rowJust, 'name')) {
                                foreach ($data as $item) {
                                    if ($item->name == $rowJust->name) {
                                        $founded = true;
                                        $groupedData[] = property_exists($item, 'value_label') ? (!empty($item->value_label) ? $item->value_label : $item->value) : $item->value;
                                        continue;
                                    }
                                }
                            }

                            if ($founded === false) {
                                $groupedData[] = "";
                            }
                        }
                    }

                    if (!empty($groupedHeaderData)) {
                        $groupedData = \Arr::flatten($groupedData);
                    }

                    $percentagesToAdd = [];

                    // foreach (collect($data)->groupBy('page')->values()->toArray() as $keyOfData => $totalSectionsRow) {
                    //     if ($keyOfData == 0) {
                    //         continue;
                    //     }

                    //     // Section wise Calculation
                    //         $varients = Helper::categorizePoints($totalSectionsRow ?? []);

                    //         $total = count(Helper::selectPointsQuestions($totalSectionsRow));
                    //         $toBeCounted = $total - count($varients['na']);

                    //         $failed = abs(count(array_column($varients['negative'], 'value')));
                    //         $achieved = $toBeCounted - abs($failed);
                            
                    //         if ($failed <= 0) {
                    //             $achieved = array_sum(array_column($varients['positive'], 'value'));
                    //         }
                            
                    //         if ($toBeCounted > 0) {
                    //             $percentage = number_format(($achieved / $toBeCounted) * 100, 2);
                    //         } else {
                    //             $percentage = 0;
                    //         }

                    //         $percentagesToAdd = array_merge($percentagesToAdd, [
                    //             $total,
                    //             $achieved,
                    //             count($varients['negative']),
                    //             count($varients['na']),
                    //             "{$percentage}%",
                    //             $percentage > 80 ? "Pass" : "Fail"
                    //         ]);                        
                    //     // Section wise Calculation
                    // }

                    
                    if (!empty($percentagesToAdd)) {
                        $groupedData = array_merge($groupedData, $percentagesToAdd);
                    }

                    $mainArray[] = $groupedData;
                }

                $mainArray[] = ["", "", ""];
            }

            $theFinalArray[$checklistsToIncludeRow->name] = $mainArray;
        }

        $directory = 'monthly-report';
        $filename = time() . '-monthly-report.xlsx';
        $path = $directory . '/' . $filename;

        if (!\Illuminate\Support\Facades\Storage::exists($directory)) {
            \Illuminate\Support\Facades\Storage::makeDirectory($directory);
        }

        \Maatwebsite\Excel\Facades\Excel::store(new \App\Exports\AllTaskExport($theFinalArray), $path, 'public');

        if (file_exists(storage_path('app/public/' . $path)) && is_file(storage_path('app/public/' . $path))) {
            MonthlyReportExport::create([
                'user_id' => auth()->user()->id,
                'file' => $filename,
                'start_date' => date('Y-m-d', strtotime($request->start)),
                'end_date' => date('Y-m-d', strtotime($request->end)),
                'checklist' => $request->sop,
                'dom' => $request->dom,
                'state' => $request->state,
                'city' => $request->city
            ]);
        }

        return response()->download(storage_path('app/public/' . $path));
    }
}
