<?php

namespace App\Http\Controllers;

use App\Models\NewWorkflowTemplateItem;
use App\Models\NewWorkflowTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\DynamicForm;
use App\Models\Department;
use App\Models\User;

class WorkflowTemplateController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $checklistScheduling = NewWorkflowTemplate::latest();

            return datatables()
                ->eloquent($checklistScheduling)
                ->editColumn('status', function ($row) {
                    if ($row->status) {
                        return '<span class="badge bg-success"> Active </span>';
                    } else {
                        return '<span class="badge bg-danger"> InActive </span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $action = '';

                    if (auth()->user()->can('workflow-templates.show')) {
                        $action .= '<a href="' . route("workflow-templates.show", encrypt($row->id)) . '" class="btn btn-warning btn-sm me-2"> Show </a>';
                        $action .= '<a href="' . route("workflow-templates.tree", encrypt($row->id)) . '" class="btn btn-primary btn-sm me-2" title="View Tree"><i class="bi bi-diagram-3"></i></a>';
                    }

                    if (auth()->user()->can('workflow-templates.edit')) {
                        $action .= '<a href="' . route('workflow-templates.edit', encrypt($row->id)) . '" class="btn btn-info btn-sm me-2">Edit</a>';
                    }

                    if (auth()->user()->can('workflow-templates.destroy')) {
                        $action .= '<form method="POST" action="' . route("workflow-templates.destroy", encrypt($row->id)) . '" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="' . csrf_token() . '"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                    }

                    return $action;
                })
                ->addColumn('stepscnt', function ($row) {
                    return $row->children()->count();
                })
                ->rawColumns(['action', 'status'])
                ->toJson();
        }

        $page_title = 'Workflow Template';
        $page_description = 'Manage workflow template here';
        return view('workflow-templates.index', compact('page_title', 'page_description'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $users = User::orderBy('name')->get(['id', 'name']);
        $checklists = DynamicForm::orderBy('name')->get(['id', 'name']);
        return view('workflow-templates.create', compact('departments', 'users', 'checklists'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateStore($request);

        return DB::transaction(function () use ($validated) {
            $sectionsData = [];
            if (isset($validated['sections'])) {
                foreach ($validated['sections'] as $sectionId => $sectionData) {
                    $sectionsData[] = [
                        'id' => $sectionId,
                        'name' => $sectionData['name'],
                        'code' => $sectionData['code'],
                        'description' => $sectionData['description'] ?? null,
                        'order' => array_search($sectionId, array_keys($validated['sections'])) + 1
                    ];
                }
            }

            $template = NewWorkflowTemplate::create([
                'title' => $validated['title'],
                'status' => request('status') == 1 ? 1 : 0,
                'description' => $validated['description'] ?? null,
                'sections' => $sectionsData,
                'added_by' => auth()->user()->id,
            ]);

            $stepNumberToId = [];
            $stepsToUpdate = [];

            if (isset($validated['sections'])) {
                foreach ($validated['sections'] as $sectionId => $sectionData) {
                    if (isset($sectionData['steps']) && is_array($sectionData['steps'])) {
                        $sectionOrder = array_search($sectionId, array_keys($validated['sections'])) + 1;

                        foreach ($sectionData['steps'] as $stepId => $stepData) {
                            $stepOrder = array_search($stepId, array_keys($sectionData['steps'])) + 1;
                            $stepNumber = $stepData['step'] ?? $stepOrder;

                            $newStep = NewWorkflowTemplateItem::create([
                                'new_workflow_template_id' => $template->id,
                                'section_id' => $sectionId,
                                'section_name' => $sectionData['name'],
                                'section_code' => $sectionData['code'],
                                'section_description' => $sectionData['description'] ?? null,
                                'section_order' => $sectionOrder,
                                'step_order' => $stepOrder,
                                'step' => $stepNumber,
                                'step_name' => $stepData['step_name'] ?? null,
                                'department_id' => $stepData['department_id'] ?? null,
                                'checklist_id' => $stepData['checklist_id'] ?? null,
                                'checklist_description' => $stepData['checklist_description'] ?? null,
                                'user_id' => $stepData['user_id'] ?? null,
                                'turn_around_time' => $stepData['turn_around_time'] ?? null,
                                'trigger' => $stepData['trigger'] ?? 0,
                                'dependency' => $stepData['dependency'] ?? 'ALL_COMPLETED',
                                'dependency_steps' => json_encode([]),
                                'is_entry_point' => filter_var($stepData['is_entry_point'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,

                                'maker_escalation_user_id' => $stepData['maker_escalation_user_id'] ?? null,
                                'maker_turn_around_time_day' => $stepData['maker_turn_around_time_day'] ?? null,
                                'maker_turn_around_time_hour' => $stepData['maker_turn_around_time_hour'] ?? null,
                                'maker_escalation_after_day' => $stepData['maker_escalation_after_day'] ?? null,
                                'maker_escalation_after_hour' => $stepData['maker_escalation_after_hour'] ?? null,
                                'maker_escalation_email_notification' => $stepData['maker_escalation_email_notification'] ?? null,
                                'maker_escalation_push_notification' => $stepData['maker_escalation_push_notification'] ?? null,

                                'checker_id' => $stepData['checker_id'] ?? null,
                                'checker_turn_around_time_day' => $stepData['checker_turn_around_time_day'] ?? null,
                                'checker_turn_around_time_hour' => $stepData['checker_turn_around_time_hour'] ?? null,

                                'checker_escalation_user_id' => $stepData['checker_escalation_user_id'] ?? null,
                                'checker_escalation_after_day' => $stepData['checker_escalation_after_day'] ?? null,
                                'checker_escalation_after_hour' => $stepData['checker_escalation_after_hour'] ?? null,
                                'checker_escalation_email_notification' => $stepData['checker_escalation_email_notification'] ?? null,
                                'checker_escalation_push_notification' => $stepData['checker_escalation_push_notification'] ?? null,
                            ]);

                            $stepNumberToId[$stepNumber] = $newStep->id;

                            if (($stepData['dependency'] ?? 'ALL_COMPLETED') === 'SELECTED_COMPLETED' && !empty($stepData['dependency_steps'])) {
                                $stepsToUpdate[$newStep->id] = $stepData['dependency_steps'];
                            }
                        }
                    }
                }
            }

            foreach ($stepsToUpdate as $stepId => $dependencyStepNumbers) {
                $mappedIds = [];
                foreach ($dependencyStepNumbers as $stepNumber) {
                    if (isset($stepNumberToId[$stepNumber])) {
                        $mappedIds[] = $stepNumberToId[$stepNumber];
                    }
                }
                if (!empty($mappedIds)) {
                    NewWorkflowTemplateItem::where('id', $stepId)->update([
                        'dependency_steps' => json_encode($mappedIds)
                    ]);
                }
            }

            return redirect()->route('workflow-templates.index')->withSuccess('Template created successfully');
        });
    }

    public function edit($id)
    {
        $template = NewWorkflowTemplate::find(decrypt($id));
        $template = $template->load('children');

        return view('workflow-templates.edit', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateStore($request);

        $new_workflow_template = NewWorkflowTemplate::find($id);

        return DB::transaction(function () use ($validated, $new_workflow_template) {
            $sectionsData = [];
            if (isset($validated['sections'])) {
                foreach ($validated['sections'] as $sectionId => $sectionData) {
                    $sectionsData[] = [
                        'id' => $sectionId,
                        'name' => $sectionData['name'],
                        'code' => $sectionData['code'],
                        'description' => $sectionData['description'] ?? null,
                        'order' => array_search($sectionId, array_keys($validated['sections'])) + 1
                    ];
                }
            }

            $new_workflow_template->update([
                'title' => $validated['title'],
                'status' => request('status') == 1 ? 1 : 0,
                'description' => $validated['description'] ?? null,
                'sections' => $sectionsData,
            ]);

            $allSteps = [];

            if (isset($validated['sections'])) {
                foreach ($validated['sections'] as $sectionId => $sectionData) {
                    if (isset($sectionData['steps']) && is_array($sectionData['steps'])) {
                        $sectionOrder = array_search($sectionId, array_keys($validated['sections'])) + 1;

                        foreach ($sectionData['steps'] as $stepId => $stepData) {
                            $stepOrder = array_search($stepId, array_keys($sectionData['steps'])) + 1;

                            if (isset($stepData['record_id']) && is_numeric($stepData['record_id']) && $stepData['record_id'] > 0) {
                                NewWorkflowTemplateItem::where('id', $stepData['record_id'])->update([
                                    'new_workflow_template_id' => $new_workflow_template->id,
                                    'section_id' => $sectionId,
                                    'section_name' => $sectionData['name'],
                                    'section_code' => $sectionData['code'],
                                    'section_description' => $sectionData['description'] ?? null,
                                    'section_order' => $sectionOrder,
                                    'step_order' => $stepOrder,
                                    'step' => $stepData['step'] ?? $stepOrder,
                                    'step_name' => $stepData['step_name'] ?? null,
                                    'department_id' => $stepData['department_id'] ?? null,
                                    'checklist_id' => $stepData['checklist_id'] ?? null,
                                    'checklist_description' => $stepData['checklist_description'] ?? null,
                                    'user_id' => $stepData['user_id'] ?? null,
                                    'turn_around_time' => $stepData['turn_around_time'] ?? null,
                                    'trigger' => $stepData['trigger'] ?? 0,
                                    'dependency' => $stepData['dependency'] ?? 'ALL_COMPLETED',
                                    'dependency_steps' => $stepData['dependency'] === 'SELECTED_COMPLETED' ? json_encode($stepData['dependency_steps'] ?? []) : json_encode([]),
                                    'is_entry_point' => filter_var($stepData['is_entry_point'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,

                                    'maker_escalation_user_id' => $stepData['maker_escalation_user_id'] ?? null,
                                    'maker_turn_around_time_day' => $stepData['maker_turn_around_time_day'] ?? null,
                                    'maker_turn_around_time_hour' => $stepData['maker_turn_around_time_hour'] ?? null,
                                    'maker_escalation_after_day' => $stepData['maker_escalation_after_day'] ?? null,
                                    'maker_escalation_after_hour' => $stepData['maker_escalation_after_hour'] ?? null,
                                    'maker_escalation_email_notification' => $stepData['maker_escalation_email_notification'] ?? null,
                                    'maker_escalation_push_notification' => $stepData['maker_escalation_push_notification'] ?? null,

                                    'checker_id' => $stepData['checker_id'] ?? null,
                                    'checker_turn_around_time_day' => $stepData['checker_turn_around_time_day'] ?? null,
                                    'checker_turn_around_time_hour' => $stepData['checker_turn_around_time_hour'] ?? null,

                                    'checker_escalation_user_id' => $stepData['checker_escalation_user_id'] ?? null,
                                    'checker_escalation_after_day' => $stepData['checker_escalation_after_day'] ?? null,
                                    'checker_escalation_after_hour' => $stepData['checker_escalation_after_hour'] ?? null,
                                    'checker_escalation_email_notification' => $stepData['checker_escalation_email_notification'] ?? null,
                                    'checker_escalation_push_notification' => $stepData['checker_escalation_push_notification'] ?? null,
                                ]);

                                $allSteps[] = $stepData['record_id'];
                            } else {
                                $allSteps[] = NewWorkflowTemplateItem::create([
                                    'new_workflow_template_id' => $new_workflow_template->id,
                                    'section_id' => $sectionId,
                                    'section_name' => $sectionData['name'],
                                    'section_code' => $sectionData['code'],
                                    'section_description' => $sectionData['description'] ?? null,
                                    'section_order' => $sectionOrder,
                                    'step_order' => $stepOrder,
                                    'step' => $stepData['step'] ?? $stepOrder,
                                    'step_name' => $stepData['step_name'] ?? null,
                                    'department_id' => $stepData['department_id'] ?? null,
                                    'checklist_id' => $stepData['checklist_id'] ?? null,
                                    'checklist_description' => $stepData['checklist_description'] ?? null,
                                    'user_id' => $stepData['user_id'] ?? null,
                                    'turn_around_time' => $stepData['turn_around_time'] ?? null,
                                    'trigger' => $stepData['trigger'] ?? 0,
                                    'dependency' => $stepData['dependency'] ?? 'ALL_COMPLETED',
                                    'dependency_steps' => $stepData['dependency'] === 'SELECTED_COMPLETED' ? json_encode($stepData['dependency_steps'] ?? []) : json_encode([]),
                                    'is_entry_point' => filter_var($stepData['is_entry_point'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,

                                    'maker_escalation_user_id' => $stepData['maker_escalation_user_id'] ?? null,
                                    'maker_turn_around_time_day' => $stepData['maker_turn_around_time_day'] ?? null,
                                    'maker_turn_around_time_hour' => $stepData['maker_turn_around_time_hour'] ?? null,
                                    'maker_escalation_after_day' => $stepData['maker_escalation_after_day'] ?? null,
                                    'maker_escalation_after_hour' => $stepData['maker_escalation_after_hour'] ?? null,
                                    'maker_escalation_email_notification' => $stepData['maker_escalation_email_notification'] ?? null,
                                    'maker_escalation_push_notification' => $stepData['maker_escalation_push_notification'] ?? null,

                                    'checker_id' => $stepData['checker_id'] ?? null,
                                    'checker_turn_around_time_day' => $stepData['checker_turn_around_time_day'] ?? null,
                                    'checker_turn_around_time_hour' => $stepData['checker_turn_around_time_hour'] ?? null,

                                    'checker_escalation_user_id' => $stepData['checker_escalation_user_id'] ?? null,
                                    'checker_escalation_after_day' => $stepData['checker_escalation_after_day'] ?? null,
                                    'checker_escalation_after_hour' => $stepData['checker_escalation_after_hour'] ?? null,
                                    'checker_escalation_email_notification' => $stepData['checker_escalation_email_notification'] ?? null,
                                    'checker_escalation_push_notification' => $stepData['checker_escalation_push_notification'] ?? null,
                                ])->id;
                            }
                        }
                    }
                }
            }

            if (!empty($allSteps)) {
                NewWorkflowTemplateItem::where('new_workflow_template_id', $new_workflow_template->id)->whereNotIn('id', $allSteps)->delete();
            } else {
                NewWorkflowTemplateItem::where('new_workflow_template_id', $new_workflow_template->id)->delete();
            }

            return redirect()->route('workflow-templates.index')->withSuccess('Template updated successfully');
        });
    }

    public function show($id)
    {
        $template = NewWorkflowTemplate::find(decrypt($id));
        $template = $template->load('children');

        return view('workflow-templates.show', compact('template'));
    }

    /**
     * Display the tree visualization for a workflow template
     */
    public function treeView($id)
    {
        $template = NewWorkflowTemplate::find(decrypt($id));
        $template = $template->load([
            'children' => function ($query) {
                $query->with(['user', 'checker', 'department', 'checklist']);
            }
        ]);

        return view('workflow-templates.tree', compact('template'));
    }

    /**
     * Get tree data in JSON format for D3.js visualization
     */
    public function treeData($id)
    {
        $template = NewWorkflowTemplate::find(decrypt($id));
        $template = $template->load([
            'children' => function ($query) {
                $query->with(['user', 'checker', 'department', 'checklist']);
            }
        ]);

        $nodes = [];
        $links = [];
        $stepIdMap = []; // Map step numbers to node indices

        // Create nodes for each step
        foreach ($template->children as $index => $step) {
            $stepIdMap[$step->id] = $index;

            $nodes[] = [
                'id' => $step->id,
                'step' => $step->step,
                'step_name' => $step->step_name ?? 'Step ' . $step->step,
                'section_id' => $step->section_id,
                'section_name' => $step->section_name ?? 'Section',
                'section_code' => $step->section_code,
                'is_entry_point' => (bool) $step->is_entry_point,
                'dependency' => $step->dependency,
                'dependency_steps' => $step->dependency_steps ?? [],
                'department' => $step->department->name ?? null,
                'checklist' => $step->checklist->name ?? null,
                'maker' => $step->user ? ($step->user->name . ' ' . ($step->user->last_name ?? '')) : null,
                'checker' => $step->checker ? ($step->checker->name . ' ' . ($step->checker->last_name ?? '')) : null,
                'trigger' => $step->trigger == 1 ? 'Manual' : 'Auto',
                'maker_tat' => [
                    'days' => $step->maker_turn_around_time_day,
                    'hours' => $step->maker_turn_around_time_hour
                ],
                'checker_tat' => [
                    'days' => $step->checker_turn_around_time_day,
                    'hours' => $step->checker_turn_around_time_hour
                ]
            ];
        }

        // Create links based on dependencies
        foreach ($template->children as $step) {
            $dependencySteps = $step->dependency_steps ?? [];

            if (!empty($dependencySteps) && is_array($dependencySteps)) {
                foreach ($dependencySteps as $parentStepId) {
                    // Find the parent step by ID
                    $parentStep = $template->children->where('id', $parentStepId)->first();
                    if ($parentStep && isset($stepIdMap[$parentStep->id])) {
                        $links[] = [
                            'source' => $parentStep->id,
                            'target' => $step->id
                        ];
                    }
                }
            }
        }

        // Group by sections for rendering
        $sections = [];
        foreach ($template->sections ?? [] as $section) {
            $sections[$section['id']] = [
                'id' => $section['id'],
                'name' => $section['name'],
                'code' => $section['code'],
                'description' => $section['description'] ?? ''
            ];
        }

        return response()->json([
            'template' => [
                'id' => $template->id,
                'title' => $template->title,
                'description' => $template->description
            ],
            'nodes' => $nodes,
            'links' => $links,
            'sections' => array_values($sections)
        ]);
    }

    public function destroy($id)
    {
        $template = NewWorkflowTemplate::find(decrypt($id));
        $template->children()->delete();
        $template->delete();

        return redirect()->route('workflow-templates.index')->withSuccess('Template deleted');
    }

    protected function validateStore($request): array
    {
        $finalArray = [];

        if (!empty($request['data']) && is_array($request['data'])) {
            foreach ($request['data'] as $row) {
                $finalArray[] = $row;
            }
        }

        $finalArray = [
            'sections' => $finalArray,
            'title' => $request['title'],
            'description' => $request['description'],
            'status' => $request['status']
        ];

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sections' => 'required|array|min:1',
            'sections.*.name' => 'required|string|max:255',
            'sections.*.code' => 'required|string|max:50',
            'sections.*.description' => 'nullable|string|max:500',
            'sections.*.steps' => 'required|array|min:1',
            'sections.*.steps.*.step_name' => 'required|string|max:255',
            'sections.*.steps.*.department_id' => 'nullable|exists:departments,id',
            'sections.*.steps.*.checklist_id' => 'nullable|exists:dynamic_forms,id',
            'sections.*.steps.*.checklist_description' => 'nullable|string',
            'sections.*.steps.*.user_id' => 'nullable|exists:users,id',
            'sections.*.steps.*.trigger' => 'required|in:0,1',
            'sections.*.steps.*.dependency' => 'required|in:ALL_COMPLETED,ANY_COMPLETED,SELECTED_COMPLETED',
            'sections.*.steps.*.dependency_steps' => 'array',
            'sections.*.steps.*.is_entry_point' => 'nullable',

            'sections.*.steps.*.maker_escalation_user_id' => 'nullable|exists:users,id',
            'sections.*.steps.*.maker_turn_around_time_day' => 'nullable|integer|min:0',
            'sections.*.steps.*.maker_turn_around_time_hour' => 'nullable|integer|min:0|max:23',
            'sections.*.steps.*.maker_escalation_after_day' => 'nullable|integer|min:0',
            'sections.*.steps.*.maker_escalation_after_hour' => 'nullable|integer|min:0|max:23',
            'sections.*.steps.*.maker_escalation_email_notification' => 'nullable|exists:notification_templates,id',
            'sections.*.steps.*.maker_escalation_push_notification' => 'nullable|exists:notification_templates,id',

            'sections.*.steps.*.checker_id' => 'nullable|exists:users,id',
            'sections.*.steps.*.checker_turn_around_time_day' => 'nullable|integer|min:0',
            'sections.*.steps.*.checker_turn_around_time_hour' => 'nullable|integer|min:0|max:23',

            'sections.*.steps.*.checker_escalation_user_id' => 'nullable|exists:users,id',
            'sections.*.steps.*.checker_escalation_after_day' => 'nullable|integer|min:0',
            'sections.*.steps.*.checker_escalation_after_hour' => 'nullable|integer|min:0|max:23',
            'sections.*.steps.*.checker_escalation_email_notification' => 'nullable|exists:notification_templates,id',
            'sections.*.steps.*.checker_escalation_push_notification' => 'nullable|exists:notification_templates,id',
        ];

        $request = new Request($finalArray);
        $validated = $request->validate($rules);

        if (!empty($validated['sections'])) {
            foreach ($validated['sections'] as $sectionId => $sectionData) {
                if (isset($sectionData['steps']) && is_array($sectionData['steps'])) {
                    foreach ($sectionData['steps'] as $stepId => $stepData) {
                        $isEntryPoint = filter_var($stepData['is_entry_point'] ?? false, FILTER_VALIDATE_BOOLEAN);

                        if ($isEntryPoint) {
                            $validated['sections'][$sectionId]['steps'][$stepId]['dependency_steps'] = [];
                            $validated['sections'][$sectionId]['steps'][$stepId]['dependency'] = 'ALL_COMPLETED';
                        } elseif (($stepData['dependency'] ?? 'ALL_COMPLETED') === 'SELECTED_COMPLETED') {
                            $validated['sections'][$sectionId]['steps'][$stepId]['dependency_steps'] = array_values(array_unique(array_filter($stepData['dependency_steps'] ?? [])));
                        } else {
                            $validated['sections'][$sectionId]['steps'][$stepId]['dependency_steps'] = [];
                        }
                    }
                }
            }
        }

        return $validated;
    }

    public function templateLists(Request $request)
    {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;

        $query = NewWorkflowTemplate::where('status', 1);

        if (!empty($queryString)) {
            $query->where('title', 'LIKE', "%{$queryString}%");
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'items' => collect($data->items())->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->title
                ];
            }),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }
}
