<?php

namespace App\Http\Controllers;

use App\Models\TemplatePresetNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ChecklistTask;
use Illuminate\Http\Request;
use App\Models\DynamicForm;
use App\Models\FormVersion;
use Illuminate\Support\Str;

class ChecklistController extends Controller
{

    private static $totalPresetNotificationTypes = 8;

    public function index(Request $request)
    {   
        if ($request->ajax()) {

            return datatables()
            ->eloquent(DynamicForm::inspection()->orderBy('ordering'))
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('checklists.show')) {
                    $action .= '<a href="'.route("checklists.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('import.scheduling')) {
                    $action .= '<a href="'.route('import.scheduling', encrypt($row->id)).'" class="btn btn-success btn-sm me-2"> Import Schedule </a>';
                }

                if (auth()->user()->can('checklists.show')) {
                    $action .= '<a href="'.route('checklists.render', encrypt($row->id)).'" class="btn btn-danger btn-sm me-2">Render</a>';
                }

                if (auth()->user()->can('checklists.edit')) {
                    $action .= '<a href="'.route('checklists.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if (auth()->user()->can('checklist-scheduling.index')) {
                    $action .= '<a href="'.route('checklist-scheduling.index', ['template' => encrypt($row->id)]).'" class="btn btn-secondary btn-sm me-2"> Scheduling List </a>';
                }

                $action .= '<a href="'. asset('assets/schedule-import.xlsx') .'" class="btn btn-success btn-sm me-2"> Download Sample Scheduling File </a>';

                $action .= '<a href="'.route('duplicate-checklist', encrypt($row->id)).'" class="btn btn-success btn-sm me-2">Duplicate</a>';

                if (auth()->user()->can('checklists.destroy')) {
                    $action .= '<form method="POST" action="'.route("checklists.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->setRowId(function($row) {
                return 'row_' . $row->id;
            })
            ->rawColumns(['action'])
            ->toJson();
        }

        $page_title = 'Checklists Template';
        $page_description = 'Manage templates here';
        return view('checklists.index',compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Add Template';

        return view('checklists.create', compact( 'page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'form_schema' => 'required'
        ]);

        DB::beginTransaction();

        try {
            $isPointChecklist = boolval($request->is_point_checklist);

            $schema = collect($request->form_schema)->transform(function ($element) use ($isPointChecklist) {
                $object = json_decode($element);
                foreach ($object as &$value) {
                    if (property_exists($value, 'label')) {
                        $value->label = trim(str_replace(["\n", "\r"], "", strip_tags($value->label)));
                    }

                    if ($isPointChecklist && property_exists($value, 'name') && property_exists($value, 'type') && (Str::contains($value->type,'radio-group')) && !Str::contains($value->name,'point-radio-group') && !Str::contains($value->name,'points-radio-group')) {
                        $value->name = 'point-' . $value->name;
                    }

                    if (property_exists($value, 'className') && (empty(trim($value->className)) || $value->className == 'form-control')) {
                        $value->className = time() . uniqid() . '-' . rand(0, 9);
                    }
                }

                return $object;
            })->values()->toArray();

            $form = new DynamicForm();
            $form->name = $request->name;
            $form->allow_double_rescheduling = boolval($request->amtosd);
            $form->schema = $schema;
            $form->is_point_checklist = $isPointChecklist;
            $form->is_geofencing_enabled = $request->is_geofencing_enabled;
            $form->is_store_checklist = boolval($request->is_store_checklist);
            $form->remove_media_frequency = in_array($request->remove_media, ['never', 'every_n_day']) ? $request->remove_media : 'never';
            $form->remove_media_frequency_after_n_day = is_numeric($request->every_n_day_input) && $request->remove_media == 'every_n_day' && $request->every_n_day_input > 0 ? $request->every_n_day_input : 365;
            $form->save();

            for ($i = 1; $i <= self::$totalPresetNotificationTypes; $i++) {
                if ($request->has("not_{$i}") && !empty($request->post("not_{$i}")) && is_array($request->post("not_{$i}"))) {
                foreach ($request->input("not_{$i}") as $notification) {
                    TemplatePresetNotification::create([
                            'checklist_id' => $form->id,
                            'notification_template_id' => $notification,
                            'type' => $i
                        ]);
                    }
                }
            }

            $hash = md5(json_encode($schema));

            FormVersion::updateOrCreate([
                'checklist_id' => $form->id,
                'hash' => $hash
            ],[
                'checklist_id' => $form->id,
                'added_by' => auth()->user()->id,
                'form' => $schema,
                'hash' => $hash
            ]);

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Template created successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CHECKLIST TEMPLATE CREATION ERROR : ' . $e->getMessage() . ' ' . auth()->user()->id);
            return response()->json(['status' => false, 'message' => 'Something went wrong.']);
        }
    }

    public function edit(Request $request, $id)
    {
        $form = DynamicForm::find(decrypt($id));
        $page_title = 'Template Edit';

        return view('checklists.edit', compact('form', 'page_title', 'id'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'form_schema' => 'required'
        ]);

        DB::beginTransaction();

        try {
            $isPointChecklist = boolval($request->is_point_checklist);

            $schema = collect($request->form_schema)->transform(function ($element) use ($isPointChecklist) {
                $object = json_decode($element);

                foreach ($object as &$value) {
                    if (property_exists($value, 'label')) {
                        $value->label = trim(str_replace(["\n", "\r"], "", strip_tags($value->label)));
                    }

                    if ($isPointChecklist) {
                        if (property_exists($value, 'name') && property_exists($value, 'type') && (Str::contains($value->type,'radio-group')) && !Str::contains($value->name,'point-radio-group') && !Str::contains($value->name,'points-radio-group')) {
                            $value->name = 'point-' . $value->name;
                        }

                        if (property_exists($value, 'name') && (Str::contains($value->name,'point-point-radio-group') || Str::contains($value->name,'points-points-radio-group'))) {
                            $valueName = explode('radio-group', $value->name);
                            $value->name = 'point-radio-group' . end($valueName);
                        }
                    } else {
                        if (property_exists($value, 'name') && property_exists($value, 'type') && (Str::contains($value->type,'radio-group')) && (Str::contains($value->name,'point-radio-group') || Str::contains($value->name,'points-radio-group'))) {
                            $value->name = str_replace(['point-', 'points-'], '', $value->name);
                        }
                    }

                    if (property_exists($value, 'className') && (empty(trim($value->className)) || $value->className == 'form-control')) {
                        $value->className = time() . uniqid() . '-' . rand(0, 9);
                    }                
                }

                return $object;
            })->values()->toArray();

            $form = DynamicForm::find(decrypt($id));
            $form->name = $request->name;
            $form->allow_double_rescheduling = boolval($request->amtosd);
            $form->is_point_checklist = $isPointChecklist;
            $form->is_geofencing_enabled = $request->is_geofencing_enabled;
            $form->is_store_checklist = boolval($request->is_store_checklist);
            $form->remove_media_frequency = in_array($request->remove_media, ['never', 'every_n_day']) ? $request->remove_media : 'never';
            $form->remove_media_frequency_after_n_day = is_numeric($request->every_n_day_input) && $request->remove_media == 'every_n_day' && $request->every_n_day_input > 0 ? $request->every_n_day_input : 365;
            $form->schema = $schema;

            for ($i = 1; $i <= self::$totalPresetNotificationTypes; $i++) {
                if ($request->has("not_{$i}") && !empty($request->post("not_{$i}")) && is_array($request->post("not_{$i}"))) {
                $psttk = [];

                foreach ($request->input("not_{$i}") as $notification) {
                    $psttk[] = TemplatePresetNotification::updateOrCreate([
                                'checklist_id' => $form->id,
                                'notification_template_id' => $notification,
                                'type' => $i
                        ])->id;
                    }
                }

                if (!empty($psttk)) {
                    TemplatePresetNotification::where('checklist_id', $form->id)->whereNotIn('id', $psttk)->where('type', $i)->delete();
                } else {
                    TemplatePresetNotification::where('checklist_id', $form->id)->where('type', $i)->delete();
                }
            }

            $hash = md5(json_encode($schema));

            $version = FormVersion::updateOrCreate([
                'checklist_id' => $form->id,
                'hash' => $hash
            ],[
                'checklist_id' => $form->id,
                'added_by' => auth()->user()->id,
                'form' => $schema,
                'hash' => $hash
            ]);

            ChecklistTask::whereHas('parent.parent', function ($builder) use ($form) {
                $builder->where('checklist_id', $form->id ?? null);
            })
            ->whereIn('status', [0])
            ->update(['form' => '{}', 'version_id' => $version->id]);

            $form->save();

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Template updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CHECKLIST TEMPLATE UPDATION ERROR : ' . $e->getMessage() . ' ' . auth()->user()->id);
            return response()->json(['status' => false, 'message' => 'Something went wrong.']);
        }
    }

    public function show(Request $request, $id)
    {
        $form = DynamicForm::find(decrypt($id));
        $page_title = 'Template Show';

        return view('checklists.show', compact('form', 'page_title', 'id'));
    }

    public function destroy(Request $request, $id)
    {
        $form = DynamicForm::find(decrypt($id));
        $form->delete();

        return redirect()->route('checklists.index')->with('success', 'Template deleted successfully');
    }

    public function renderForViewOnly(Request $request, $id)
    {
        $form = DynamicForm::find(decrypt($id));
        $page_title = 'Template Render';

        return view('checklists.view-only-render', compact('form', 'page_title', 'id'));
    }

    public function duplicate(Request $request, $id) {
        if ($request->method() == 'GET') {
            $form = DynamicForm::find(decrypt($id));
            $page_title = 'Duplicate Template';

            return view('checklists.duplicate', compact('form', 'page_title', 'id'));
        } else if ($request->method() == 'PUT') {
            $request->validate([
                'name' => 'required',
                'form_schema' => 'required'
            ]);
    
            try {
                $isPointChecklist = boolval($request->is_point_checklist);

                $schema = collect($request->form_schema)->transform(function ($element) use ($isPointChecklist) {
                    $object = json_decode($element);

                    foreach ($object as &$value) {
                        if (property_exists($value, 'label')) {
                            $value->label = trim(str_replace(["\n", "\r"], "", strip_tags($value->label)));
                        }

                        if ($isPointChecklist) {
                            if (property_exists($value, 'name') && property_exists($value, 'type') && (Str::contains($value->type,'radio-group')) && !Str::contains($value->name,'point-radio-group') && !Str::contains($value->name,'points-radio-group')) {
                                $value->name = 'point-' . $value->name;
                            }

                            if (property_exists($value, 'name') && (Str::contains($value->name,'point-point-radio-group') || Str::contains($value->name,'points-points-radio-group'))) {
                                $valueName = explode('radio-group', $value->name);
                                $value->name = 'point-radio-group' . end($valueName);
                            }
                        } else {
                            if (property_exists($value, 'name') && property_exists($value, 'type') && (Str::contains($value->type,'radio-group')) && (Str::contains($value->name,'point-radio-group') || Str::contains($value->name,'points-radio-group'))) {
                                $value->name = str_replace(['point-', 'points-'], '', $value->name);
                            }
                        }

                        if (property_exists($value, 'className') && (empty(trim($value->className)) || $value->className == 'form-control')) {
                            $value->className = time() . uniqid() . '-' . rand(0, 9);
                        }                
                    }

                    return $object;
                })->values()->toArray();

                $form = new DynamicForm();
                $form->name = $request->name;
                $form->allow_double_rescheduling = boolval($request->amtosd);
                $form->is_point_checklist = $isPointChecklist;
                $form->is_geofencing_enabled = $request->is_geofencing_enabled;
                $form->is_store_checklist = boolval($request->is_store_checklist);
                $form->remove_media_frequency = in_array($request->remove_media, ['never', 'every_n_day']) ? $request->remove_media : 'never';
                $form->remove_media_frequency_after_n_day = is_numeric($request->every_n_day_input) && $request->remove_media == 'every_n_day' && $request->every_n_day_input > 0 ? $request->every_n_day_input : 365;
                $form->schema = $schema;
                $form->save();

                for ($i = 1; $i <= self::$totalPresetNotificationTypes; $i++) {
                    if ($request->has("not_{$i}") && !empty($request->post("not_{$i}")) && is_array($request->post("not_{$i}"))) {
                    $psttk = [];

                    foreach ($request->input("not_{$i}") as $notification) {
                        $psttk[] = TemplatePresetNotification::updateOrCreate([
                                    'checklist_id' => $form->id,
                                    'notification_template_id' => $notification,
                                    'type' => $i
                            ])->id;
                        }
                    }

                    if (!empty($psttk)) {
                        TemplatePresetNotification::where('checklist_id', $form->id)->whereNotIn('id', $psttk)->where('type', $i)->delete();
                    } else {
                        TemplatePresetNotification::where('checklist_id', $form->id)->where('type', $i)->delete();
                    }
                }

                $hash  = md5(json_encode($schema));

                $version = FormVersion::updateOrCreate([
                    'checklist_id' => $form->id,
                    'hash' => $hash
                ],[
                    'checklist_id' => $form->id,
                    'added_by' => auth()->user()->id,
                    'form' => $schema,
                    'hash' => $hash
                ]);

                DB::commit();
                return response()->json(['status' => true, 'message' => 'Template duplicated successfully.']);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('CHECKLIST TEMPLATE DUPLICATION ERROR : ' . $e->getMessage() . ' ' . auth()->user()->id);
                return response()->json(['status' => false, 'message' => 'Something went wrong.']);
            }

        } else {
            return redirect()->route('checklists.index');
        }
    }

    public function select2List(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $type = $request->type;
        $limit = 10;
        $getAll = $request->getall;
    
        $query = DynamicForm::orderBy('ordering');
    
        if (!empty($queryString)) {
            $query->where('name', 'LIKE', "%{$queryString}%");
        }

        if ($type == 1) {
            $query->inspection();
        } else if ($type == 2) {
            $query->workflow();
        }

        if ($request->has('onlyPoint') && $request->onlyPoint == 1) {
            $query->where('is_point_checklist', 1);
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);
        $response = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => $item->name
            ];
        });        
    
        if ($getAll && $page == 1) {
            $response->prepend(['id' => 'all', 'text' => 'All']);
        }

        return response()->json([
            'items' => $response,
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }

    public function reorder(Request $request)
    {
        $order = $request->input('order');

        foreach ($order as $index => $id) {
            DynamicForm::where('id', $id)->update(['ordering' => $index]);
        }

        return response()->json(['status' => 'success']);
    }

}
