<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Particular;
use App\Models\Department;

class ParticularController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return datatables()
            ->eloquent(Particular::query())
            ->addColumn('department', function ($row) {
                $dep = Department::withTrashed()->find($row->department_id);
                return $dep->name ?? '-';
            })
            ->addColumn('status', function ($row) {
                return $row->status ? 'Active' : 'InActive';
            })
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('particulars.show')) {
                    $action .= '<a href="'.route("particulars.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('particulars.edit')) {
                    $action .= '<a href="'.route('particulars.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if (auth()->user()->can('particulars.destroy')) {
                    $action .= '<form method="POST" action="'.route("particulars.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->rawColumns(['action'])
            ->toJson();
        }

        $page_title = 'Particulars';
        $page_description = 'Manage particulars here';
        return view('particulars.index', compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Particular Add';
        return view('particulars.create', compact('page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                    \Illuminate\Validation\Rule::unique('particulars')->where(function ($query) use ($request) {
                        return $query->where('department_id', $request->department);
                    }),
            ],
            'department' => 'required|exists:departments,id',
            'status' => 'nullable|in:0,1'
        ]);

        Particular::create([
            'name' => $request->name,
            'department_id' => $request->department,
            'status' => $request->status ? 1 : 0
        ]);

        return redirect()->route('particulars.index')->with('success','Particular created successfully');
    }

    public function show($id)
    {
        $page_title = 'Particular Show';
        $particular = Particular::find(decrypt($id));
        return view('particulars.show', compact('particular', 'page_title'));
    }

    public function edit($id)
    {
        $page_title = 'Particular Edit';
        $particular = Particular::find(decrypt($id));
        return view('particulars.edit', compact('particular', 'page_title', 'id'));
    }

    public function update(Request $request, $id)
    {
        $cId = decrypt($id);

        $request->validate([
            'name' => [
                'required',
                    \Illuminate\Validation\Rule::unique('particulars')->where(function ($query) use ($request, $cId) {
                        return $query->where('id', '!=', $cId)->where('department_id', $request->department);
                    }),
            ],
            'department' => 'required|exists:departments,id',
            'status' => 'nullable|in:0,1'
        ]);

        $particular = Particular::find($cId);
        $particular->update([
            'name' => $request->name,
            'department_id' => $request->department,
            'status' => $request->status ? 1 : 0
        ]);

        return redirect()->route('particulars.index')->with('success','Particular updated successfully');
    }

    public function destroy($id)
    {
        $particular = Particular::find(decrypt($id));
        $particular->delete();
        return redirect()->route('particulars.index')->with('success','Particular deleted successfully');
    }

    public function select2List(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
        $getAll = $request->getall;
        
        $query = Particular::query();
    
        if (!empty($queryString)) {
            $query->where('name', 'LIKE', "%{$queryString}%");
        }
    
        if ($request->has('department_id') && !empty($request->department_id)) {
            $query->where('department_id', $request->department_id);

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
