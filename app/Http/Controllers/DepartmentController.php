<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\DepartmentUser;
use App\Models\TicketitEscalation;
use App\Models\TicketitEscalationUser;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {   
        if ($request->ajax()) {

            return datatables()
            ->eloquent(Department::query())
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('departments.show')) {
                    $action .= '<a href="'.route("departments.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('departments.edit')) {
                    $action .= '<a href="'.route('departments.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if (auth()->user()->can('departments.destroy')) {
                    $action .= '<form method="POST" action="'.route("departments.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->rawColumns(['action'])
            ->toJson();
        }

        $page_title = 'Department';
        $page_description = 'Manage departments here';
        return view('departments.index',compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Department Add';

        return view('departments.create', compact( 'page_title'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:departments,name'
        ]);
    
        $id = Department::create([
            'name' => $request->name,
            'description' => $request->description
        ])->id;

        if (!empty($request->users)) {
            foreach ($request->users as $user) {
                DepartmentUser::create([
                    'department_id' => $id,
                    'user_id' => $user
                ]);
            }
        }

        return redirect()->route('departments.index')->with('success','Department created successfully');
    }

    public function show($id)
    {
        $page_title = 'Departments Show';
        $department = Department::find(decrypt($id));
    
        return view('departments.show', compact('department', 'page_title'));
    }

    public function edit($id)
    {
        $page_title = 'Department Edit';
        $department = Department::find(decrypt($id));
        $escalations = TicketitEscalation::where('department_id', $department->id)
            ->with(['escalationUsers.user', 'escalationUsers.template'])
            ->get();
    
        return view('departments.edit', compact('department', 'escalations', 'page_title', 'id'));
    }
    
    public function update(Request $request, $id)
    {
        $cId = decrypt($id);

        $request->validate([
            'name' => "required|unique:departments,name,{$cId}"
        ]);

        $department = Department::find($cId);
        $department->update($request->only(['name', 'description']));

        $shouldKeep = [];

        if (!empty($request->users)) {
            foreach ($request->users as $user) {
                $shouldKeep[] = DepartmentUser::updateOrCreate([
                    'department_id' => $cId,
                    'user_id' => $user
                ])->id;
            }
        }

        if (!empty($shouldKeep)) {
            DepartmentUser::whereNotIn('id', $shouldKeep)->where('department_id', $cId)->delete();
        } else {
            DepartmentUser::where('department_id', $cId)->delete();
        }

        return redirect()->route('departments.index')->with('success','Department updated successfully');
    }

    public function destroy($id)
    {
        $department = Department::find(decrypt($id));
        $department->delete();
        
        return redirect()->route('departments.index')->with('success','Department deleted successfully');
    }

    public function select2List(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
        $getAll = $request->getall;
        
        $query = Department::query();
    
        if (!empty($queryString)) {
            $query->where('name', 'LIKE', "%{$queryString}%");
        }
    
        $data = $query->paginate($limit, ['*'], 'page', $page);
        $response = $data->items();
        $response = collect($response)->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => $item->name
            ];
        });
    
        if ($getAll && $page == 1) {
            $response->push(['id' => 'all', 'text' => 'All']);
        }

        return response()->json([
            'items' => $response->reverse()->values(),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }
}
