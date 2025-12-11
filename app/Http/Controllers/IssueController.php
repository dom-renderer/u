<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Issue;
use App\Models\Department;
use App\Models\Particular;
use Illuminate\Validation\Rule;

class IssueController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return datatables()
            ->eloquent(Issue::query())
            ->addColumn('department', function ($row) {
                $dep = Department::withTrashed()->find($row->department_id);
                return $dep->name ?? '-';
            })
            ->addColumn('particular', function ($row) {
                $par = Particular::withTrashed()->find($row->particular_id);
                return $par->name ?? '-';
            })
            ->addColumn('status', function ($row) {
                return $row->status ? 'Active' : 'InActive';
            })
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('issues.show')) {
                    $action .= '<a href="'.route("issues.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('issues.edit')) {
                    $action .= '<a href="'.route('issues.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if (auth()->user()->can('issues.destroy')) {
                    $action .= '<form method="POST" action="'.route("issues.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->rawColumns(['action'])
            ->toJson();
        }

        $page_title = 'Issues';
        $page_description = 'Manage issues here';
        return view('issues.index', compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Issue Add';
        return view('issues.create', compact('page_title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                Rule::unique('issues')->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department)
                                 ->where('particular_id', $request->particular);
                }),
            ],
            'department' => 'required|exists:departments,id',
            'particular' => [
                'required',
                Rule::exists('particulars', 'id')->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department);
                })
            ],
            'status' => 'nullable|in:0,1'
        ]);

        Issue::create([
            'name' => $request->name,
            'department_id' => $request->department,
            'particular_id' => $request->particular,
            'status' => $request->status ? 1 : 0
        ]);

        return redirect()->route('issues.index')->with('success','Issue created successfully');
    }

    public function show($id)
    {
        $page_title = 'Issue Show';
        $issue = Issue::find(decrypt($id));
        return view('issues.show', compact('issue', 'page_title'));
    }

    public function edit($id)
    {
        $page_title = 'Issue Edit';
        $issue = Issue::find(decrypt($id));
        return view('issues.edit', compact('issue', 'page_title', 'id'));
    }

    public function update(Request $request, $id)
    {
        $cId = decrypt($id);

        $request->validate([
            'name' => [
                'required',
                Rule::unique('issues')->where(function ($query) use ($request, $cId) {
                    return $query->where('id', '!=', $cId)
                                 ->where('department_id', $request->department)
                                 ->where('particular_id', $request->particular);
                }),
            ],
            'department' => 'required|exists:departments,id',
            'particular' => [
                'required',
                Rule::exists('particulars', 'id')->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department);
                })
            ],
            'status' => 'nullable|in:0,1'
        ]);

        $issue = Issue::find($cId);
        $issue->update([
            'name' => $request->name,
            'department_id' => $request->department,
            'particular_id' => $request->particular,
            'status' => $request->status ? 1 : 0
        ]);

        return redirect()->route('issues.index')->with('success','Issue updated successfully');
    }

    public function destroy($id)
    {
        $issue = Issue::find(decrypt($id));
        $issue->delete();
        return redirect()->route('issues.index')->with('success','Issue deleted successfully');
    }

    public function select2List(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
        $getAll = $request->getall;
        
        $query = Issue::query();
    
        if (!empty($queryString)) {
            $query->where('name', 'LIKE', "%{$queryString}%");
        }
    
        if ($request->has('particular_id') && !empty($request->particular_id)) {
            $query->where('particular_id', $request->particular_id);

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
