<?php

namespace App\Http\Controllers;

use App\Models\ChecklistTask;
use Illuminate\Http\Request;
use App\Models\DynamicForm;
use App\Helpers\Helper;
use App\Models\Store;

class DoMDashboardController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            $data = [
                'bar_chart_label' => [],
                'bar_chart_label_bar' => [],
                'bar_chart_label_bar_color' => [],
                'bar_chart_data' => [],
                'bar_chart_store_ids' => []
            ];
            
            $finalArray = $tempData = [];

            $pointsForm = DynamicForm::select('id')->where('is_point_checklist', 1)->pluck('id')->toArray();

            ChecklistTask::scheduling()
            ->when(!auth()->user()->isAdmin(), function ($builder) {
                $builder->whereHas('parent.actstore', function ($innerBuilder) {
                    return $innerBuilder->where('dom_id', auth()->user()->id);
                });
            }, function ($outerBuilder) {
                $outerBuilder->when(request('dom') != 'all', function ($builder) {
                    $builder->whereHas('parent', function ($innerBuilder) {
                        return $innerBuilder->where('user_id', request('dom'));
                    });
                })->when(request('loc') != 'all', function ($builder) {
                    $builder->whereHas('parent.actstore', function ($innerBuilder) {
                        return $innerBuilder->where('id', request('loc'));
                    });
                });
            })
            ->when($request->loc != 'all', function ($builder) {
                $builder->whereHas('parent', function ($innerBuilder) {
                    return $innerBuilder->where('store_id', request('loc'));
                });
            })
            ->when($request->ltype != 'all', function ($builder) {
                $builder->whereHas('parent.actstore', function ($innerBuilder) {
                    return $innerBuilder->where('store_type', request('ltype'));
                });
            })
            ->when($request->lmodel != 'all', function ($builder) {
                $builder->whereHas('parent.actstore', function ($innerBuilder) {
                    return $innerBuilder->where('model_type', request('lmodel'));
                });
            })
            ->when($request->state != 'all', function ($builder) {
                $builder->whereHas('parent.actstore.thecity', function ($innerBuilder) {
                    return $innerBuilder->where('city_state', request('state'));
                });
            })
            ->when($request->city != 'all', function ($builder) {
                $builder->whereHas('parent.actstore', function ($innerBuilder) {
                    return $innerBuilder->where('city', request('city'));
                });
            })
            ->when($request->clist != 'all', function ($builder) {
                $builder->whereHas('parent.parent', function ($innerBuilder) {
                    return $innerBuilder->where('checklist_id', request('clist'));
                });
            },function ($builder) use ($pointsForm) {
                $builder->whereHas('parent.parent', function ($innerBuilder) use ($pointsForm) {
                    return $innerBuilder->whereIn('checklist_id', $pointsForm);
                });
            })
            ->whereIn('status', [2, 3])
            ->with('parent.actstore')
            ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($request->start)))
            ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($request->end)))
            ->select('percentage', 'extra_info', 'checklist_scheduling_id', 'id')
            ->orderBy('date', 'DESC')->get()->map(function ($element) use (&$finalArray) {

                if (isset($finalArray[$element->parent->store_id])) {
                    $finalArray[$element->parent->store_id]['percentage'] += $element->percentage;
                    $finalArray[$element->parent->store_id]['count'] += 1;
                } else {
                    $finalArray[$element->parent->store_id] = [
                        'name' => ($element->parent->actstore->code ?? '') . ' ' . $element->parent->actstore->name ?? '',
                        'percentage' => $element->percentage,
                        'count' => 1
                    ];
                }

            });

            if (!empty($finalArray)) {
                foreach ($finalArray as $store => $line) {
                    array_push($data['bar_chart_label'], $line['name']);
                    array_push($tempData, number_format($line['percentage'] / $line['count'], 2));
                    array_push($data['bar_chart_store_ids'], $store);
                }
            }

            $data['bar_chart_data'] = [$tempData];

            return response()->json(['data' => $data]);
        }

        $page_title = 'DoM Dashboard';
        return view('dashboard.dom-dashboard', compact('page_title'));
    }

    public function index3(Request $request) {
            $store = Store::find($request->store);
            $barChartData = $barChartLabels = [];

            $pointsForm = DynamicForm::select('id')->where('is_point_checklist', 1)->pluck('id')->toArray();

            if ($store) {
                ChecklistTask::with('parent.parent.checklist')
                ->scheduling()
                ->when(!auth()->user()->isAdmin(), function ($builder) {
                    $builder->whereHas('parent.actstore', function ($innerBuilder) {
                        return $innerBuilder->where('dom_id', auth()->user()->id);
                    });
                })
                ->whereHas('parent.actstore', function ($innerBuilder) use ($store) {
                    return $innerBuilder->where('id', $store->id);
                })
                ->whereIn('status', [2, 3])
                ->when($request->clist != 'all', function ($builder) {
                    $builder->whereHas('parent.parent', function ($innerBuilder) {
                        $innerBuilder->where('checklist_id', request('clist'));
                    });
                }, function ($builder) use ($pointsForm) {
                    $builder->whereHas('parent.parent', function ($innerBuilder) use ($pointsForm) {
                        $innerBuilder->whereIn('checklist_id', $pointsForm);
                    });
                })
                ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($request->start)))
                ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($request->end)))
                ->select('id', 'checklist_scheduling_id', 'date', 'status', 'percentage', 'status')
                ->orderBy('date', 'DESC')
                ->get()
                ->map(function ($element) use (&$barChartData, &$barChartLabels) {
                    $barChartData[] = number_format($element->percentage, 2);
                    $barChartLabels[] = ($element->parent->parent->checklist->name ?? 'Checklist') . " - " . date('d F, Y', strtotime($element->date));
                });
            }

            return response()->json(['data' => $barChartData, 'labels' => $barChartLabels, 'store_name' => $store->name]);
    }

    public function detail(Request $request) {
        $Task = ChecklistTask::find($request->id);

        if ($Task) {
            $inlineTable = '<table class="table w-100 table-striped table-bordered">
            <thead>
               <tr> 
               <th> Questions </th>
               <th> Answer </th>
               </tr>
            </thead>
            <tbody>';

            $iterableData = collect($Task->data)->filter(function ($item) {
                return ((strtolower($item->value) === 'fail') || (isset($item->label_value) && strtolower($item['label_value']) === 'fail') || strtolower($item->value) === 'no') || (isset($item->label_value) && strtolower($item['label_value']) === 'no');
            })->pluck('label')->all();

            $iterableData2 = collect($Task->data)->filter(function ($item) {
                return ((strtolower($item->value) === 'fail') || (isset($item->label_value) && strtolower($item['label_value']) === 'fail') || strtolower($item->value) === 'no') || (isset($item->label_value) && strtolower($item['label_value']) === 'no');
            })->map(function ($builder) {
                return isset($builder->label_value) ? $builder->label_value : $builder->value;
            })
            ->values();

            foreach ($iterableData as $thisKey => $thisQuestion) {
                $inlineTable .= "<tr><td>
            {$thisQuestion}
                </td>
                <td>" . (isset($iterableData2[$thisKey]) ? $iterableData2[$thisKey] : '') . "</td>
                </tr>";
            }

            $inlineTable .= '
            </tbody>
            </table>';

            return response()->json(['status' => true, 'html' => $inlineTable]);
        }

        return response()->json(['status' => false]);
    }





    // New Dashboard


    public function index2(Request $request) {
        if ($request->ajax()) {
            $tableData = '';
            $flaggedArray = [];
            $totalFlagged = 0;
            $data = [
                'flagged_items' => 0,
                'bar_chart_label' => ['Location A'],
                'bar_chart_label_bar' => ['MAX SCORE'],
                'bar_chart_label_bar_color' => ['#8dc1e9'],
                'bar_chart_data' => [
                    [10]
                ],
                'flagged_items_table' => '<tr> <td colspan="5" align="center"> No items found </td> </tr>'
            ];

            $stores = Store::when($request->store != 'all', function ($builder) {
                $builder->where('id', request('store'));
            })->pluck('name', 'id')->toArray();

            $data['bar_chart_label'] = array_values($stores);

            $pendingBarChartData = [];

            if ($stores) {

                foreach ($stores as $thisStore => $val) {
                    $totalChecklists = ChecklistTask::with(['parent.parent.checklist', 'parent.actstore', 'parent.user'])
                    ->scheduling()
                    ->when($request->dom != 'all', function ($builder) {
                        $builder->whereHas('parent', function ($innerBuilder) {
                            return $innerBuilder->where('user_id', request('dom'));
                        });
                    })
                    ->when($request->sop != 'all', function ($builder) {
                        $builder->whereHas('parent.parent.checklist', function ($innerBuilder) {
                            return $innerBuilder->where('id', request('sop'));
                        });
                    })
                    ->when($request->sop == 'all', function ($builder) use ($thisStore) {
                        $builder->whereHas('parent', function ($innerBuilder) use ($thisStore) {
                            return $innerBuilder->where('store_id', $thisStore);
                        });
                    })
                    ->whereIn('status', [Helper::$status['in-verification'], Helper::$status['completed']])
                    ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($request->start)))
                    ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($request->end)));

                    $allFlaggedItemData = $totalChecklists->clone()->whereIn('status', [Helper::$status['in-verification'], Helper::$status['completed']])->get();

                    $count_truthy_temp = 0;
                    $count_falsy_temp = 0;

                    foreach ($totalChecklists->clone()->whereIn('status', [Helper::$status['in-verification'], Helper::$status['completed']])->orderBy('date', 'DESC')->get() as $count_pending_inspection_temp_row) {
                        $count_truthy_temp += count(Helper::getBooleanFields($count_pending_inspection_temp_row['data'])['truthy']);
                        $count_falsy_temp += count(Helper::getBooleanFields($count_pending_inspection_temp_row['data'])['falsy']);
                    }

                    $pendingBarChartData[] = ($count_truthy_temp + $count_falsy_temp > 0 ? number_format((($count_truthy_temp / ($count_truthy_temp + $count_falsy_temp)) * 100), 2) : 0);
                    // $pendingBarChartData[] = $count_truthy_temp;
    
                    $theStore = Store::find($thisStore);

                    foreach ($totalChecklists->clone()->whereIn('status', [Helper::$status['in-verification'], Helper::$status['completed']])->get() as $thisCList) {
                        $tempTotal = count(Helper::getBooleanFields($thisCList->data)['falsy']);

                        if ($tempTotal > 0) {
                            $flaggedArray[] = [
                                'location_name' => isset($thisCList->parent->actstore->name) ? $thisCList->parent->actstore->name : '-',
                                'inspected_by' => isset($thisCList->parent->user->name) ? $thisCList->parent->user->name : '-',
                                'checklist_name' => isset($thisCList->parent->parent->checklist->name) ? $thisCList->parent->parent->checklist->name : '-',
                                'total_no' =>  $tempTotal,
                                'button' => '<button type="submit" class="btn btn-sm btn-primary open-detail" data-bs-target="#viewData" data-bs-toggle="modal" data-id="' . $thisCList->id . '"> View </button>'
                            ];   
                            $totalFlagged += $tempTotal;
                        }
                    }
                }

                if (count($flaggedArray) > 0) {
                    foreach ($flaggedArray as $row) {
                        $tableData .= '<tr>
                        <td> ' . $row['location_name'] . ' </td>
                        <td> ' . $row['inspected_by'] . ' </td>
                        <td> ' . $row['checklist_name'] . ' </td>
                        <td> ' . $row['total_no'] . ' </td>
                        <td> ' . $row['button'] . ' </td>                        
                        </tr>';
                    }
                }

                $data['flagged_items'] = $totalFlagged;
                $data['flagged_items_table'] = $tableData;
                $data['bar_chart_data'] = [
                    $pendingBarChartData
                ];
            }

            return response()->json(['data' => $data]);
        }

        $page_title = 'DoM Dashboard';
        return view('dashboard.index', compact('page_title'));
    }

    public function detail2(Request $request) {
        $Task = ChecklistTask::find($request->id);

        if ($Task) {
            $inlineTable = '<table class="table w-100 table-striped table-bordered">
            <thead>
               <tr> 
               <th> Questions </th>
               <th> Answer </th>
               </tr>
            </thead>
            <tbody>';

            $iterableData = collect(Helper::getBooleanFields($Task->data)['falsy'])->pluck('label')->all();
            $iterableData2 = collect(Helper::getBooleanFields($Task->data)['falsy'])->pluck('value_label')->all();

            foreach ($iterableData as $thisKey => $thisQuestion) {
                $inlineTable .= "<tr><td>
            {$thisQuestion}
                </td>
                <td>" . (isset($iterableData2[$thisKey]) ? $iterableData2[$thisKey] : '') . "</td>
                </tr>";
            }

            $inlineTable .= '
            </tbody>
            </table>';

            return response()->json(['status' => true, 'html' => $inlineTable]);
        }

        return response()->json(['status' => false]);
    }    
}