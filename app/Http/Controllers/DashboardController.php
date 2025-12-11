<?php

namespace App\Http\Controllers;

use App\Models\ChecklistTask;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\DocumentUpload;
use App\Models\Store;
use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index() 
    {
        if (auth()->user()->can('dom-dashboard')) {
            return redirect()->route('dom-dashboard');
        } else {
            return view('dashboard');
        }
    }

    public function logout()
    {
        \Auth::logout();
        session()->flush();
        
        return redirect()->route('login.show');
    }

    public function filter(Request $request) {
        $tableData2 = $tableData = '';
        $totalFlagged = 0;
        $response = [
            'stores' => [],
            'store_data' => [],
            'd1' => '0',
            'd2' => '0',
            'd3' => '0',
            'd4' => '0',
            'compliance_rate' => '',
            'flagged_items' => ''
        ];

        $stores = Store::when($request->store != 'all', function ($builder) {
            $builder->where('id', request('store'));
        })->pluck('name', 'id')->toArray();

        if (empty($stores)) {
            $stores = Store::when($request->dom != 'all', function ($builder) {
                $builder->where('dom_id', request('dom'));
            })->pluck('name', 'id')->toArray();
        } else {
            $stores = Store::when($request->dom != 'all', function ($builder) {
                $builder->where('dom_id', request('dom'));
            })->whereIn('id', array_keys($stores))->pluck('name', 'id')->toArray();
        }

        if ($stores) {
            $complianceRate = [];

            foreach ($stores as $thisStore => $val) {
                $totalChecklists = ChecklistTask::whereHas('parent', function ($builder) use ($thisStore) {
                    $builder->where('branch_type', 1)->where('branch_id', $thisStore);
                })
                ->scheduling()
                ->whereHas('parent.actstore', function ($builder) {
                    if (request('state') != 'all') {
                        $builder->whereHas('thecity', function ($innerBuilder) {
                            $innerBuilder->where('city_state', request('state'));
                        });
                    }
    
                    if (request('city') != 'all') {
                        $builder->whereHas('thecity', function ($innerBuilder) {
                            $innerBuilder->where('city_id', request('city'));
                        });
                    }
                })
                ->whereBetween(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), [date('Y-m-d', strtotime($request->start)), date('Y-m-d', strtotime($request->end))]);
    
                $pendingChecklist = $totalChecklists->clone()->where('status', 0);
                $completedChecklist = $totalChecklists->clone()->where('status', 1);
                $allFlaggedItemData = $totalChecklists->clone()->where('status', 1)->get();

                if ($pendingChecklist->count() > 0) {
                    $thisTempRate = number_format(($completedChecklist->count() / $pendingChecklist->count()) * 100, 2);
                } else {
                    $thisTempRate = 0;
                }

                $complianceRate[] = $thisTempRate;

                $theStore = Store::find($thisStore);

                $tableData .= "<tr> 
                <td> $val </td>
                <td> " . (isset($theStore->thecity->city_state) ? $theStore->thecity->city_state : '') . " </td>
                <td> " . (isset($theStore->thecity->city_name) ? $theStore->thecity->city_name : '') . " </td>
                <td> " . (isset($theStore->dom->name) ? $theStore->dom->name : '') . " </td>
                <td> $thisTempRate </td>
                </tr>";

                $counter = 1;

                if (!empty($allFlaggedItemData)) {
                    foreach ($allFlaggedItemData as $allFlaggedItemDataVal) {
                        if ($counter == 5) {
                            break;
                        }

                        foreach (Helper::getKeyValueHavingValue($allFlaggedItemDataVal->data, 'no') as $thisKey => $thisVal) {
                            if ($counter == 5) {
                                break;
                            }
    
                            $itemName = explode('.', $thisKey);
                            $itemName = end($itemName);

                            $tableData2 .= "<tr>
                            <td> " . (\Str::headline($itemName)) . " </td>
                            <td> $val </td>
                            <td> " . (isset($theStore->thecity->city_state) ? $theStore->thecity->city_state : '') . " </td>
                            <td> " . (isset($theStore->thecity->city_name) ? $theStore->thecity->city_name : '') . " </td>
                            <td> " . (isset($allFlaggedItemDataVal->parent->parent->checklist->name) ? $allFlaggedItemDataVal->parent->parent->checklist->name : 'Checklist') . " </td>
                            </tr>";       
    
                            $counter++;
                        }
                    }

                    foreach ($allFlaggedItemData as $allFlaggedItemDataVal) {
                        foreach (Helper::getKeyValueHavingValue($allFlaggedItemDataVal->data, 'no') as $thisKey => $thisVal) {
                            $totalFlagged++;
                        }
                    }
                }
            }

            $totalChecklists = ChecklistTask::whereHas('parent', function ($builder) use ($stores) {
                $builder->where('branch_type', 1)->whereIn('branch_id', array_keys($stores));
            })
            ->scheduling()
            ->whereHas('parent.actstore', function ($builder) {
                if (request('state') != 'all') {
                    $builder->whereHas('thecity', function ($innerBuilder) {
                        $innerBuilder->where('city_state', request('state'));
                    });
                }

                if (request('city') != 'all') {
                    $builder->whereHas('thecity', function ($innerBuilder) {
                        $innerBuilder->where('city_id', request('city'));
                    });
                }
            })
            ->whereBetween(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), [date('Y-m-d', strtotime($request->start)), date('Y-m-d', strtotime($request->end))]);

            $pendingChecklist = $totalChecklists->clone()->where('status', 0);
            $completedChecklist = $totalChecklists->clone()->where('status', 1);

            $response = [
                'stores' => array_values($stores),
                'store_data' => $complianceRate,
                'd1' => $pendingChecklist->count() > 0 ? number_format(($completedChecklist->count() / $pendingChecklist->count()) * 100, 2) : 0,
                'd2' => $totalChecklists->count(),
                'd3' => $pendingChecklist->count(),
                'd4' => $totalFlagged,
                'compliance_rate' => $tableData,
                'flagged_items' => $tableData2
            ];
        }

        return response()->json(['data' => $response]);
    }

    public function exportExcel(Request $request, $id) {
        $task = ChecklistTask::find( $id);

        $json = $task->data ?? [];
        if (is_string($json)) {
            $data = json_decode($json, true);
        } else if (is_array($json)) {
            $data = $json;
        } else {
            $data = [];
        }
        
        $siteUrl = url('storage/workflow-task-uploads') . '/';

        foreach ($data as $item) {
            if (!empty($item->isFile)) {
                if (is_array($item->value)) {
                    $item->value = array_map(function ($v) use ($siteUrl) {
                        return $siteUrl . ltrim($v, '/');
                    }, $item->value);
                } elseif (is_string($item->value)) {
                    $item->value = $siteUrl . ltrim($item->value, '/');
                }
            }
        }

        $groupedData = [];
        foreach ($data as $item) {
            if (!isset($groupedData[$item->className])) {
                $groupedData[$item->className][] = $item->label;
            }

            $groupedData[$item->className][] = property_exists($item, 'value_label') ? (!empty($item->value_label) ? $item->value_label : $item->value) : $item->value;
        }

        $groupedData = array_values($groupedData);
        $fileName = $task->code . '-' . (date('m-d-Y', strtotime($task->date))) . ".xlsx";

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

        $groupedData[] = ["Total Questions", $total];
        $groupedData[] = ["Passed", $achieved];
        $groupedData[] = ["Failed", count($varients['negative'])];
        $groupedData[] = ["N/A", count($varients['na'])];
        $groupedData[] = ["Percentage", "{$percentage}%"];
        $groupedData[] = ["Final Result", $percentage > 80 ? "Pass" : "Fail"];

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\TaskExport($groupedData, $task), $fileName);
    }

    public function exportPdf(Request $request, $id) {
        if ($request->regenerate == 1) {
            return self::testPdf($request, $id);
        } else {
            if (!(file_exists(storage_path("app/public/task-pdf/task-{$id}.pdf")) && is_file(storage_path("app/public/task-pdf/task-{$id}.pdf")))) {
                abort(404, 'You have to request for task report generation');
                return false;
            }

            $path = storage_path("app/public/task-pdf/task-{$id}.pdf");

            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="task-' . $id . '.pdf"'
            ]);
        }
    }

    public function exportCompressedPdf(Request $request, $id) {
        $path = storage_path("app/public/task-pdf/task-compressed-{$id}.pdf");

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="task-' . $id . '.pdf"'
        ]);
    }

    public static function testPdf(Request $request, $id) {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        $task = ChecklistTask::with(['parent.parent.checklist', 'parent.actstore', 'clist'])->find($id);
        $path = storage_path('app/public/task-pdf');

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        // Generate PDF
            $json = $task->data ?? [];
            if (is_string($json)) {
                $data = json_decode($json, true);
            } else if (is_array($json)) {
                $data = $json;
            } else {
                $data = [];
            }
            
            $namesToBeIgnored = array_combine(Helper::$namesToBeIgnored, Helper::$namesToBeIgnored);
            $groupedData = [];

            if (isset($task->parent->parent->checklist_id) && in_array($task->parent->parent->checklist_id, [106, 107])) {
                foreach ($data as $item) {
                    if (!isset($namesToBeIgnored[$item->name])) {
                        if (!isset($groupedData[$item->className])) {
                            $groupedData[$item->className][] = $item->label;
                        }

                        $groupedData[$item->className][] = property_exists($item, 'value_label') ? (!empty($item->value_label) ? $item->value_label : $item->value) : $item->value;
                    }
                }
            } else {
                foreach ($data as $item) {
                    if (!isset($groupedData[$item->className])) {
                        $groupedData[$item->className][] = $item->label;
                    }

                    $groupedData[$item->className][] = property_exists($item, 'value_label') ? (!empty($item->value_label) ? $item->value_label : $item->value) : $item->value;
                }
            }

            $groupedData = array_values($groupedData);

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

            $finalResultData = [];

            $finalResultData['total_count'] = $total;
            $finalResultData['passed'] = $achieved;
            $finalResultData['failed'] = count($varients['negative']);
            $finalResultData['na'] = count($varients['na']);
            $finalResultData['percentage'] = "{$percentage}%";
            $finalResultData['final_result'] = $percentage > 80 ? "Pass" : "Fail";

            $formToPass = Helper::getVersionForm($task->version_id);
            if (!Helper::isPointChecklist($formToPass)) {
                $toBeCounted = collect($task->data)->flatten(1)->pluck('className')->filter()->unique()->count();
            }

        if ($request->type == 'html') {
            return view('tasks.pdf', ['data' => $groupedData, 'task' => $task, 'toBeCounted' => $toBeCounted, 'finalResultData' => $finalResultData]);
        } else {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('tasks.pdf', ['data' => $groupedData, 'task' => $task, 'toBeCounted' => $toBeCounted, 'finalResultData' => $finalResultData])
            ->setPaper('A4', 'landscape');

            return $pdf->stream("task-{$task->id}.pdf");
        }
    }

    public function flaggedItemsView(Request $request) {
        if ($request->ajax()) {

                $items = ChecklistTask::with(['parent.actstore.thecity', 'parent.parent.checklist', 'parent.parent.checker', 'parent.actstore', 'parent.user'])
                ->scheduling()
                ->when(!auth()->user()->isAdmin(), function ($builder) {
                    $builder->whereHas('parent.actstore', function ($innerBuilder) {
                        return $innerBuilder->where('dom_id', auth()->user()->id);
                    });
                }, function ($outerBuilder) {
                    $outerBuilder->when(request('dom') != 'all', function ($builder) {
                        $builder->whereHas('parent', function ($innerBuilder) {
                            return $innerBuilder->where('user_id', request('dom'));
                        });
                    })
                    ->when(request('store') != 'all', function ($builder) {
                        $builder->whereHas('parent.actstore', function ($innerBuilder) {
                            return $innerBuilder->where('store_id', request('store'));
                        });
                    });
                })
                ->when($request->city != 'all' && !empty($request->city), function ($builder) {
                    $builder->whereHas('parent.actstore.thecity', function ($innerBuilder) {
                        return $innerBuilder->where('city_id', request('city'));
                    });
                })
                ->when($request->state != 'all' && !empty($request->state), function ($builder) {
                    $builder->whereHas('parent.actstore.thecity', function ($innerBuilder) {
                        return $innerBuilder->where('city_state', request('state'));
                    });
                })
                ->whereIn('status', [Helper::$status['in-progress'], Helper::$status['in-verification'], Helper::$status['completed']])
                ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($request->startd)))
                ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($request->endd)))
                ->get()
                ->map(function ($item) {
                    $falsyValues = Helper::getBooleanFields($item->data)['falsy'];
                    $data = [];

                    if (count($falsyValues) > 0) {
                        foreach ($falsyValues as $falsyValue) {
                            $data[] = [
                                'item_name' => html_entity_decode($falsyValue['label']),
                                'clistname' => isset($item->parent->parent->checklist->id) ? $item->parent->parent->checklist->name : '',
                                'dom_name' => isset($item->parent->user->id) ? ($item->parent->user->employee_id . ' - ' . $item->parent->user->name . ' ' .  $item->parent->user->middle_name . ' '  . $item->parent->user->last_name) : '',
                                'location_name' => isset($item->parent->actstore->id) ? ($item->parent->actstore->code . ' - ' . $item->parent->actstore->name) : '',
                                'city_name' => $item->parent->actstore->thecity->city_name ?? '',
                                'state_name' => $item->parent->actstore->thecity->city_state ?? '',
                                'initial_status_name' => 'Pending',
                                'latest_status_name' => Helper::getLatestStatus($item->id, $falsyValue['className']),
                                'last_updated' => date('d-m-Y H:i', strtotime($item->updated_at)),
                            ];
                        }
                    }

                    return $data;
                });

            return datatables()
            ->of(collect($items)->collapse()->values()->toArray())
            ->toJson();
        }

        return view('dashboard.flagged-items');
    }

    public function pdfFItems(Request $request) {

        $items = ChecklistTask::with(['parent.actstore.thecity', 'parent.parent.checklist', 'parent.parent.checker', 'parent.actstore', 'parent.user'])
        ->scheduling()
        ->when(!auth()->user()->isAdmin(), function ($builder) {
            $builder->whereHas('parent.actstore', function ($innerBuilder) {
                return $innerBuilder->where('dom_id', auth()->user()->id);
            });
        }, function ($outerBuilder) {
            $outerBuilder->when(request('dom') != 'all', function ($builder) {
                $builder->whereHas('parent', function ($innerBuilder) {
                    return $innerBuilder->where('user_id', request('dom'));
                });
            })
            ->when(request('store') != 'all', function ($builder) {
                $builder->whereHas('parent.actstore', function ($innerBuilder) {
                    return $innerBuilder->where('store_id', request('store'));
                });
            });
        })
        ->when($request->city != 'all' && !empty($request->city), function ($builder) {
            $builder->whereHas('parent.actstore.thecity', function ($innerBuilder) {
                return $innerBuilder->where('city_id', request('city'));
            });
        })
        ->when($request->state != 'all' && !empty($request->state), function ($builder) {
            $builder->whereHas('parent.actstore.thecity', function ($innerBuilder) {
                return $innerBuilder->where('city_state', request('state'));
            });
        })
        ->whereIn('status', [Helper::$status['in-progress'], Helper::$status['in-verification'], Helper::$status['completed']])
        ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($request->startd)))
        ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($request->endd)))
        ->get()
        ->map(function ($item) {
            $falsyValues = Helper::getBooleanFields($item->data)['falsy'];
            $data = [];

            if (count($falsyValues) > 0) {
                foreach ($falsyValues as $falsyValue) {
                    $data[] = [
                        'item_name' => html_entity_decode($falsyValue['label']),
                        'clistname' => isset($item->parent->parent->checklist->id) ? $item->parent->parent->checklist->name : '',
                        'dom_name' => isset($item->parent->user->id) ? ($item->parent->user->employee_id . ' - ' . $item->parent->user->name . ' ' .  $item->parent->user->middle_name . ' '  . $item->parent->user->last_name) : '',
                        'location_name' => isset($item->parent->actstore->id) ? ($item->parent->actstore->code . ' - ' . $item->parent->actstore->name) : '',
                        'city_name' => $item->parent->actstore->thecity->city_name ?? '',
                        'state_name' => $item->parent->actstore->thecity->city_state ?? '',
                        'initial_status_name' => 'Pending',
                        'latest_status_name' => Helper::getLatestStatus($item->id, $falsyValue['className']),
                        'last_updated' => date('d-m-Y H:i', strtotime($item->updated_at)),
                    ];
                }
            }

            return $data;
        });

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.flagged-items-pdf', ['data' => $items]);

        return $pdf->stream('report.pdf');
    }

    public function pdfTickets(Request $request) {

        $pending = Ticket::with(['tsk' => function ($builder) {
            $builder->withTrashed();
        }, 'tsk.parent' => function ($builder) {
            $builder->withTrashed();
        },'tsk.parent.parent' => function ($builder) {
            $builder->withTrashed();
        }, 'tsk.parent.actstore' => function ($builder) {
            $builder->withTrashed();
        }, 'tsk.parent.user' => function ($builder) {
            $builder->withTrashed();
        }])
        ->whereNotNull('task_id')
        ->when(1, function ($builder) {
            $builder->where('status_id', 1)->where(function ($innerBuilder) {
                $innerBuilder->whereNull('completed_at');
            });
        })

        ->when(!auth()->user()->isAdmin(), function ($builder) {
            $builder->whereHas('tsk.parent.actstore', function ($innerBuilder) {
                return $innerBuilder->where('dom_id', auth()->user()->id);
            });
        }, function ($outerBuilder) {
            $outerBuilder->when(request('dom') != 'all', function ($builder) {
                $builder->whereHas('tsk.parent', function ($innerBuilder) {
                    return $innerBuilder->where('user_id', request('dom'));
                });
            })
            ->when(request('store') != 'all', function ($builder) {
                $builder->whereHas('tsk.parent.actstore', function ($innerBuilder) {
                    return $innerBuilder->where('store_id', request('store'));
                });
            });
        })
        ->when($request->city != 'all' && !empty($request->city), function ($builder) {
            $builder->whereHas('tsk.parent.actstore.thecity', function ($innerBuilder) {
                return $innerBuilder->where('city_id', request('city'));
            });
        })
        ->when($request->dept != 'all' && !empty($request->dept), function ($builder) {
            $builder->whereHas('department', function ($innerBuilder) {
                return $innerBuilder->where('id', request('dept'));
            });
        })
        ->when($request->state != 'all' && !empty($request->state), function ($builder) {
            $builder->whereHas('tsk.parent.actstore.thecity', function ($innerBuilder) {
                return $innerBuilder->where('city_state', request('state'));
            });
        })
        ->when(1, function ($builder) {
            $builder->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime(request('startd'))))
            ->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime(request('endd'))));
        })
        ->latest()
        ->get();

        $onhold = Ticket::with(['tsk' => function ($builder) {
            $builder->withTrashed();
        }, 'tsk.parent' => function ($builder) {
            $builder->withTrashed();
        },'tsk.parent.parent' => function ($builder) {
            $builder->withTrashed();
        }, 'tsk.parent.actstore' => function ($builder) {
            $builder->withTrashed();
        }, 'tsk.parent.user' => function ($builder) {
            $builder->withTrashed();
        }])
        ->whereNotNull('task_id')
        ->when(1, function ($builder)  {
            $builder->where('status_id', 3)->where(function ($innerBuilder) {
                $innerBuilder->whereNull('completed_at');
            });
        }, function ($builder) {
            $builder->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime('-2 days')))
            ->whereNull('completed_at');
        })

        ->when(!auth()->user()->isAdmin(), function ($builder) {
            $builder->whereHas('tsk.parent.actstore', function ($innerBuilder) {
                return $innerBuilder->where('dom_id', auth()->user()->id);
            });
        }, function ($outerBuilder) {
            $outerBuilder->when(request('dom') != 'all', function ($builder) {
                $builder->whereHas('tsk.parent', function ($innerBuilder) {
                    return $innerBuilder->where('user_id', request('dom'));
                });
            })
            ->when(request('store') != 'all', function ($builder) {
                $builder->whereHas('tsk.parent.actstore', function ($innerBuilder) {
                    return $innerBuilder->where('store_id', request('store'));
                });
            });
        })
        ->when($request->city != 'all' && !empty($request->city), function ($builder) {
            $builder->whereHas('tsk.parent.actstore.thecity', function ($innerBuilder) {
                return $innerBuilder->where('city_id', request('city'));
            });
        })
        ->when($request->dept != 'all' && !empty($request->dept), function ($builder) {
            $builder->whereHas('department', function ($innerBuilder) {
                return $innerBuilder->where('id', request('dept'));
            });
        })
        ->when($request->state != 'all' && !empty($request->state), function ($builder) {
            $builder->whereHas('tsk.parent.actstore.thecity', function ($innerBuilder) {
                return $innerBuilder->where('city_state', request('state'));
            });
        })
        ->when(1, function ($builder) {
            $builder->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime(request('startd'))))
            ->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime(request('endd'))));
        })
        ->latest()
        ->get();

        $inprogress = Ticket::with(['tsk' => function ($builder) {
            $builder->withTrashed();
        }, 'tsk.parent' => function ($builder) {
            $builder->withTrashed();
        },'tsk.parent.parent' => function ($builder) {
            $builder->withTrashed();
        }, 'tsk.parent.actstore' => function ($builder) {
            $builder->withTrashed();
        }, 'tsk.parent.user' => function ($builder) {
            $builder->withTrashed();
        }])
        ->whereNotNull('task_id')
        ->when(1, function ($builder)  {
            $builder->where('status_id', 2)->where(function ($innerBuilder) {
                $innerBuilder->whereNull('completed_at');
            });
        }, function ($builder) {
            $builder->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime('-2 days')))
            ->whereNull('completed_at');
        })

        ->when(!auth()->user()->isAdmin(), function ($builder) {
            $builder->whereHas('tsk.parent.actstore', function ($innerBuilder) {
                return $innerBuilder->where('dom_id', auth()->user()->id);
            });
        }, function ($outerBuilder) {
            $outerBuilder->when(request('dom') != 'all', function ($builder) {
                $builder->whereHas('tsk.parent', function ($innerBuilder) {
                    return $innerBuilder->where('user_id', request('dom'));
                });
            })
            ->when(request('store') != 'all', function ($builder) {
                $builder->whereHas('tsk.parent.actstore', function ($innerBuilder) {
                    return $innerBuilder->where('store_id', request('store'));
                });
            });
        })
        ->when($request->city != 'all' && !empty($request->city), function ($builder) {
            $builder->whereHas('tsk.parent.actstore.thecity', function ($innerBuilder) {
                return $innerBuilder->where('city_id', request('city'));
            });
        })
        ->when($request->dept != 'all' && !empty($request->dept), function ($builder) {
            $builder->whereHas('department', function ($innerBuilder) {
                return $innerBuilder->where('id', request('dept'));
            });
        })
        ->when($request->state != 'all' && !empty($request->state), function ($builder) {
            $builder->whereHas('tsk.parent.actstore.thecity', function ($innerBuilder) {
                return $innerBuilder->where('city_state', request('state'));
            });
        })
        ->when(1, function ($builder) {
            $builder->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime(request('startd'))))
            ->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime(request('endd'))));
        })
        ->latest()
        ->get();

        $completed = Ticket::with(['tsk' => function ($builder) {
            $builder->withTrashed();
        }, 'tsk.parent' => function ($builder) {
            $builder->withTrashed();
        },'tsk.parent.parent' => function ($builder) {
            $builder->withTrashed();
        }, 'tsk.parent.actstore' => function ($builder) {
            $builder->withTrashed();
        }, 'tsk.parent.user' => function ($builder) {
            $builder->withTrashed();
        }])
        ->whereNotNull('task_id')
        ->when(1, function ($builder) {
            $builder->whereNotNull('completed_at')->where('completed_at', '!=', '');
        }, function ($builder) {
            $builder->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime('-2 days')))
            ->whereNull('completed_at');
        })

        ->when(!auth()->user()->isAdmin(), function ($builder) {
            $builder->whereHas('tsk.parent.actstore', function ($innerBuilder) {
                return $innerBuilder->where('dom_id', auth()->user()->id);
            });
        }, function ($outerBuilder) {
            $outerBuilder->when(request('dom') != 'all', function ($builder) {
                $builder->whereHas('tsk.parent', function ($innerBuilder) {
                    return $innerBuilder->where('user_id', request('dom'));
                });
            })
            ->when(request('store') != 'all', function ($builder) {
                $builder->whereHas('tsk.parent.actstore', function ($innerBuilder) {
                    return $innerBuilder->where('store_id', request('store'));
                });
            });
        })
        ->when($request->city != 'all' && !empty($request->city), function ($builder) {
            $builder->whereHas('tsk.parent.actstore.thecity', function ($innerBuilder) {
                return $innerBuilder->where('city_id', request('city'));
            });
        })
        ->when($request->dept != 'all' && !empty($request->dept), function ($builder) {
            $builder->whereHas('department', function ($innerBuilder) {
                return $innerBuilder->where('id', request('dept'));
            });
        })
        ->when($request->state != 'all' && !empty($request->state), function ($builder) {
            $builder->whereHas('tsk.parent.actstore.thecity', function ($innerBuilder) {
                return $innerBuilder->where('city_state', request('state'));
            });
        })
        ->when(1, function ($builder) {
            $builder->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime(request('startd'))))
            ->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime(request('endd'))));
        })
        ->latest()
        ->get();

        $stale = Ticket::with(['tsk' => function ($builder) {
            $builder->withTrashed();
        }, 'tsk.parent' => function ($builder) {
            $builder->withTrashed();
        },'tsk.parent.parent' => function ($builder) {
            $builder->withTrashed();
        }, 'tsk.parent.actstore' => function ($builder) {
            $builder->withTrashed();
        }, 'tsk.parent.user' => function ($builder) {
            $builder->withTrashed();
        }])
        ->whereNotNull('task_id')
        ->when(1, function ($builder) {
            $builder->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime('-2 days')))
            ->whereNull('completed_at');
        })

        ->when(!auth()->user()->isAdmin(), function ($builder) {
            $builder->whereHas('tsk.parent.actstore', function ($innerBuilder) {
                return $innerBuilder->where('dom_id', auth()->user()->id);
            });
        }, function ($outerBuilder) {
            $outerBuilder->when(request('dom') != 'all', function ($builder) {
                $builder->whereHas('tsk.parent', function ($innerBuilder) {
                    return $innerBuilder->where('user_id', request('dom'));
                });
            })
            ->when(request('store') != 'all', function ($builder) {
                $builder->whereHas('tsk.parent.actstore', function ($innerBuilder) {
                    return $innerBuilder->where('store_id', request('store'));
                });
            });
        })
        ->when($request->city != 'all' && !empty($request->city), function ($builder) {
            $builder->whereHas('tsk.parent.actstore.thecity', function ($innerBuilder) {
                return $innerBuilder->where('city_id', request('city'));
            });
        })
        ->when($request->dept != 'all' && !empty($request->dept), function ($builder) {
            $builder->whereHas('department', function ($innerBuilder) {
                return $innerBuilder->where('id', request('dept'));
            });
        })
        ->when($request->state != 'all' && !empty($request->state), function ($builder) {
            $builder->whereHas('tsk.parent.actstore.thecity', function ($innerBuilder) {
                return $innerBuilder->where('city_state', request('state'));
            });
        })
        ->latest()
        ->get();


        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.ticket-report-pdf', ['pending' => $pending, 'inprogress' => $inprogress, 'completed' => $completed, 'stale' => $stale, 'onhold' => $onhold]);

        return $pdf->stream('report.pdf');
    }

    public function documentDashboard( Request $request ) {

        if ( $request->ajax() ) {
            $today = \Carbon\Carbon::today();
            $nearDate = $today->copy()->addDays(30);

            $query = DocumentUpload::with(['document', 'storeCategory', 'store'])
                ->when($request->section == 'near_expiration', function ($q) use ($today, $nearDate) {
                    $q->whereBetween('expiry_date', [$today, $nearDate])
                    ->where(function ($inner) use ($today) {
                        $inner->whereNull( 'remind_me_later_at' )
                                ->orWhere( 'remind_me_later_at', '<=', $today );
                    });
                })
                ->when($request->section == 'expired', function ($q) use ($today) {
                    $q->where('expiry_date', '<', $today)
                    ->where(function ($inner) use ($today) {
                        $inner->whereNull( 'remind_me_later_at' )
                                ->orWhere( 'remind_me_later_at', '<=', $today );
                    });
                });

            return datatables()->eloquent($query)
                ->addColumn('document_name', function ($row) {
                    return !empty($row->document) ? $row->document->name : '-';
                })
                ->addColumn('location_category', function ($row) {
                    return !empty($row->storeCategory) ? $row->storeCategory->name : '-';
                })
                ->addColumn('location', function ($row) {
                    return !empty($row->store) ? $row->store->name : '-';
                })
                ->addColumn('expiry_date', function ($row) {
                    return !empty($row->expiry_date) ? \Carbon\Carbon::parse($row->expiry_date)->format('d-m-Y') : '-';
                })
                ->addColumn('issue_date', function ($row) {
                    return !empty($row->issue_date) ? \Carbon\Carbon::parse($row->issue_date)->format('d-m-Y') : '-';
                })
                ->addColumn('attachment', function ($row) {
                    if ( !empty($row->attachment_path) ) {
                        return '<a href="' . $row->attachment_path . '" target="_blank" class="btn btn-sm btn-secondary">View</a>';
                    }
                    return '-';
                })
                ->addColumn('action', function($row) {
                    $action = '<button class="btn btn-sm btn-secondary zp_remindLaterBtn m-1" data-url="' . route( 'document-dashboard-remindLater', encrypt( $row->id ) ) . '">Remind Me Later</button>';
                    if ( auth()->user()->can( 'document-upload.destroy' ) ) {
                        $action .= '<form method="POST" action="'.route("document-upload.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup m-1">Delete</button></form>';
                    }
                    return $action;
                })
                ->addIndexColumn()
                ->rawColumns( [ 'action', 'attachment' ] )
                ->toJson();
        }
        return view( 'dashboard.document-dashboard' );
    }

    public function documentRemindLater( $id ) {
        $doc = DocumentUpload::findOrFail( decrypt( $id ) );
        $doc->remind_me_later_at = \Carbon\Carbon::tomorrow();
        $doc->save();

        return response()->json( [ 'status' => true, 'message' => 'We will remind you again tomorrow.' ] );
    }
}