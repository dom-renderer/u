<?php

namespace App\Http\Controllers;

use App\Models\NewWorkflowAssignmentItem;
use App\Models\NewWorkflowAssignment;
use App\Models\NewWorkflowTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\DynamicForm;
use App\Models\Department;
use App\Models\User;

class WorkflowAssignmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $checklistScheduling = NewWorkflowAssignment::latest();

            return datatables()
                ->eloquent($checklistScheduling)
                ->editColumn('status', function ($row) {
                    if ($row->status) {
                        return '<span class="badge bg-success"> Active </span>';
                    } else {
                        return '<span class="badge bg-danger"> InActive </span>';
                    }
                })
                ->editColumn('start_from', function ($row) {
                    return $row->start_from ? $row->start_from->format('Y-m-d H:i') : '-';
                })
                ->addColumn('action', function ($row) {
                    $action = '';

                    if (auth()->user()->can('workflow-assignments.show')) {
                        $action .= '<a href="' . route("workflow-assignments.show", encrypt($row->id)) . '" class="btn btn-warning btn-sm me-2"> Show </a>';
                        $action .= '<a href="' . route("workflow-assignments.tree", encrypt($row->id)) . '" class="btn btn-primary btn-sm me-2" title="View Tree"><i class="bi bi-diagram-3"></i></a>';
                    }

                    if (auth()->user()->can('workflow-assignments.edit')) {
                        $action .= '<a href="' . route('workflow-assignments.edit', encrypt($row->id)) . '" class="btn btn-info btn-sm me-2">Edit</a>';
                    }

                    if (auth()->user()->can('workflow-assignments.destroy')) {
                        $action .= '<form method="POST" action="' . route("workflow-assignments.destroy", encrypt($row->id)) . '" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="' . csrf_token() . '"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                    }

                    return $action;
                })
                ->addColumn('stepscnt', function ($row) {
                    return $row->children()->count();
                })
                ->rawColumns(['action', 'status'])
                ->toJson();
        }

        $page_title = 'Workflow Assignment';
        $page_description = 'Manage workflow assignment here';
        return view('workflow-assignments.index', compact('page_title', 'page_description'));
    }

    public function create()
    {
        $templates = NewWorkflowTemplate::where('status', 1)->orderBy('title')->get(['id', 'title']);
        return view('workflow-assignments.create', compact('templates'));
    }

    public function loadTemplate($id)
    {
        $template = NewWorkflowTemplate::with('children')->find($id);
        
        if (!$template) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        $sections = [];
        if ($template->sections && is_array($template->sections)) {
            foreach ($template->sections as $section) {
                $sectionId = $section['id'] ?? 'section_' . uniqid();
                $sections[$sectionId] = [
                    'id' => $sectionId,
                    'name' => $section['name'] ?? '',
                    'code' => $section['code'] ?? '',
                    'description' => $section['description'] ?? '',
                    'steps' => []
                ];
            }
        }

        // Load steps for each section
        foreach ($template->children as $step) {
            $sectionId = $step->section_id;
            if (!isset($sections[$sectionId])) {
                $sections[$sectionId] = [
                    'id' => $sectionId,
                    'name' => $step->section_name ?? '',
                    'code' => $step->section_code ?? '',
                    'description' => $step->section_description ?? '',
                    'steps' => []
                ];
            }

            $stepId = 'step_' . $step->id;
            $sections[$sectionId]['steps'][$stepId] = [
                'id' => $stepId,
                'record_id' => null, // New step, no record_id
                'globalNumber' => $step->step,
                'step_name' => $step->step_name ?? '',
                'department_id' => $step->department_id ?? '',
                'checklist_id' => $step->checklist_id ?? '',
                'checklist_description' => $step->checklist_description ?? '',
                'trigger' => $step->trigger ?? 0,
                'dependency' => $step->dependency ?? 'ALL_COMPLETED',
                'dependency_steps' => $step->dependency_steps ?? [],
                'is_entry_point' => $step->is_entry_point ?? false,
                'user_id' => $step->user_id ?? '',
                'maker_turn_around_time_day' => $step->maker_turn_around_time_day ?? '',
                'maker_turn_around_time_hour' => $step->maker_turn_around_time_hour ?? '',
                'maker_escalation_user_id' => $step->maker_escalation_user_id ?? '',
                'maker_escalation_after_day' => $step->maker_escalation_after_day ?? '',
                'maker_escalation_after_hour' => $step->maker_escalation_after_hour ?? '',
                'maker_escalation_email_notification' => $step->maker_escalation_email_notification ?? '',
                'maker_escalation_push_notification' => $step->maker_escalation_push_notification ?? '',
                'checker_id' => $step->checker_id ?? '',
                'checker_turn_around_time_day' => $step->checker_turn_around_time_day ?? '',
                'checker_turn_around_time_hour' => $step->checker_turn_around_time_hour ?? '',
                'checker_escalation_user_id' => $step->checker_escalation_user_id ?? '',
                'checker_escalation_after_day' => $step->checker_escalation_after_day ?? '',
                'checker_escalation_after_hour' => $step->checker_escalation_after_hour ?? '',
                'checker_escalation_email_notification' => $step->checker_escalation_email_notification ?? '',
                'checker_escalation_push_notification' => $step->checker_escalation_push_notification ?? '',
                'department' => $step->department ? ['id' => $step->department->id, 'name' => $step->department->name] : null,
                'checklist' => $step->checklist ? ['id' => $step->checklist->id, 'name' => $step->checklist->name] : null,
                'user' => $step->user ? ['id' => $step->user->id, 'name' => $step->user->name, 'last_name' => $step->user->last_name ?? ''] : null,
                'checker' => $step->checker ? ['id' => $step->checker->id, 'name' => $step->checker->name, 'last_name' => $step->checker->last_name ?? ''] : null,
            ];
        }

        return response()->json([
            'title' => $template->title,
            'description' => $template->description,
            'sections' => $sections
        ]);
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

            $assignment = NewWorkflowAssignment::create([
                'new_workflow_template_id' => $validated['new_workflow_template_id'] ?? null,
                'title' => $validated['title'],
                'status' => request('status') == 1 ? 1 : 0,
                'description' => $validated['description'] ?? null,
                'start_from' => $validated['start_from'] ?? null,
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

                            $newStep = NewWorkflowAssignmentItem::create([
                                'new_workflow_assignment_id' => $assignment->id,
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
                                'dependency_steps' => [],
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
                    NewWorkflowAssignmentItem::where('id', $stepId)->update([
                        'dependency_steps' => self::stringToInt($mappedIds)
                    ]);
                }
            }

            return redirect()->route('workflow-assignments.index')->withSuccess('Assignment created successfully');
        });
    }

    public function edit($id)
    {
        $assignment = NewWorkflowAssignment::find(decrypt($id));
        $assignment = $assignment->load('children');
        $templates = NewWorkflowTemplate::where('status', 1)->orderBy('title')->get(['id', 'title']);

        return view('workflow-assignments.edit', compact('assignment', 'templates'));
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateStore($request);

        $new_workflow_assignment = NewWorkflowAssignment::find($id);

        return DB::transaction(function () use ($validated, $new_workflow_assignment) {
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

            $new_workflow_assignment->update([
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
                                NewWorkflowAssignmentItem::where('id', $stepData['record_id'])->update([
                                    'new_workflow_assignment_id' => $new_workflow_assignment->id,
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
                                    'dependency_steps' => $stepData['dependency'] === 'SELECTED_COMPLETED' ? self::stringToInt($stepData['dependency_steps'] ?? []) : [],
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
                                $allSteps[] = NewWorkflowAssignmentItem::create([
                                    'new_workflow_assignment_id' => $new_workflow_assignment->id,
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
                                    'dependency_steps' => $stepData['dependency'] === 'SELECTED_COMPLETED' ? self::stringToInt($stepData['dependency_steps'] ?? []) : [],
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
                NewWorkflowAssignmentItem::where('new_workflow_assignment_id', $new_workflow_assignment->id)->whereNotIn('id', $allSteps)->delete();
            } else {
                NewWorkflowAssignmentItem::where('new_workflow_assignment_id', $new_workflow_assignment->id)->delete();
            }

            return redirect()->route('workflow-assignments.index')->withSuccess('Assignment updated successfully');
        });
    }

    public function show($id)
    {
        $assignment = NewWorkflowAssignment::find(decrypt($id));
        $assignment = $assignment->load('children');

        return view('workflow-assignments.show', compact('assignment'));
    }

    /**
     * Display the tree visualization for a workflow assignment
     */
    public function treeView($id)
    {
        $assignment = NewWorkflowAssignment::find(decrypt($id));
        $assignment = $assignment->load([
            'children' => function ($query) {
                $query->with(['user', 'checker', 'department', 'checklist']);
            }
        ]);

        return view('workflow-assignments.tree', compact('assignment'));
    }

    /**
     * Get tree data in JSON format for D3.js visualization
     */
    public function treeData($id)
    {
        $assignment = NewWorkflowAssignment::find(decrypt($id));
        $assignment = $assignment->load([
            'children' => function ($query) {
                $query->with(['user', 'checker', 'department', 'checklist']);
            }
        ]);

        $nodes = [];
        $links = [];
        $stepIdMap = []; // Map step numbers to node indices

        // Create nodes for each step
        foreach ($assignment->children as $index => $step) {
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
                'dependency_steps' => self::stringToInt($step->dependency_steps ?? []),
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
        foreach ($assignment->children as $step) {
            $dependencySteps = self::stringToInt($step->dependency_steps ?? []);

            if (!empty($dependencySteps) && is_array($dependencySteps)) {
                foreach ($dependencySteps as $parentStepId) {
                    // Find the parent step by ID
                    $parentStep = $assignment->children->where('id', $parentStepId)->first();
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
        foreach ($assignment->sections ?? [] as $section) {
            $sections[$section['id']] = [
                'id' => $section['id'],
                'name' => $section['name'],
                'code' => $section['code'],
                'description' => $section['description'] ?? ''
            ];
        }

        return response()->json([
            'template' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'description' => $assignment->description
            ],
            'nodes' => $nodes,
            'links' => $links,
            'sections' => array_values($sections)
        ]);
    }

    public function destroy($id)
    {
        $assignment = NewWorkflowAssignment::find(decrypt($id));
        $assignment->children()->delete();
        $assignment->delete();

        return redirect()->route('workflow-assignments.index')->withSuccess('Assignment deleted');
    }

    protected function validateStore($request): array
    {
        $sectionsArray = [];

        if (!empty($request['data']) && is_array($request['data'])) {
            // Preserve section IDs as keys
            foreach ($request['data'] as $sectionId => $sectionData) {
                $sectionsArray[$sectionId] = $sectionData;
            }
        }

        $finalArray = [
            'sections' => $sectionsArray,
            'title' => $request['title'],
            'description' => $request['description'],
            'status' => $request['status'],
            'new_workflow_template_id' => $request['new_workflow_template_id'] ?? null,
            'start_from' => $request['start_from'] ?? null,
        ];

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'new_workflow_template_id' => 'nullable|exists:new_workflow_templates,id',
            'start_from' => 'nullable|date',
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
            'sections.*.steps.*.record_id' => 'nullable|integer',

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
                            $validated['sections'][$sectionId]['steps'][$stepId]['dependency_steps'] = self::stringToInt(array_values(array_unique(array_filter($stepData['dependency_steps'] ?? []))));
                        } else {
                            $validated['sections'][$sectionId]['steps'][$stepId]['dependency_steps'] = [];
                        }
                    }
                }
            }
        }

        return $validated;
    }

    public static function stringToInt($array)
    {
        return array_map('intval', $array);
    }
}

