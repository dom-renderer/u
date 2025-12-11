<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ModelType;
use App\Models\Store;

class ModelTypeController extends Controller
{
    public function index(Request $request)
    {   
        if ($request->ajax()) {

            return datatables()
            ->eloquent(ModelType::query())
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('model-types.show')) {
                    $action .= '<a href="'.route("model-types.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('model-types.edit')) {
                    $action .= '<a href="'.route('model-types.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if (auth()->user()->can('model-types.destroy')) {
                    $action .= '<form method="POST" action="'.route("model-types.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->rawColumns(['action'])
            ->toJson();
        }

        $page_title = 'Model Types';
        $page_description = 'Manage model types here';
        return view('model-types.index',compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Model Type Add';

        return view('model-types.create', compact( 'page_title'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:model_types,name'
        ]);
    
        ModelType::create([
            'name' => $request->name,
            'description' => $request->description
        ]);
    
        return redirect()->route('model-types.index')->with('success','Model Type created successfully');
    }

    public function show($id)
    {
        $page_title = 'Model Type Show';
        $storetype = ModelType::find(decrypt($id));
    
        return view('model-types.show', compact('storetype', 'page_title'));
    }

    public function edit($id)
    {
        $page_title = 'Model Type Edit';
        $storetype = ModelType::find(decrypt($id));
    
        return view('model-types.edit', compact('storetype', 'page_title', 'id'));
    }
    
    public function update(Request $request, $id)
    {
        $cId = decrypt($id);

        $request->validate([
            'name' => "required|unique:model_types,name,{$cId}"
        ]);

        $storetype = ModelType::find($cId);
        $storetype->update($request->only(['name', 'description']));
    
        return redirect()->route('model-types.index')->with('success','Model Type updated successfully');
    }

    public function destroy($id)
    {
        $id = decrypt($id);

        if (Store::where('model_type', $id)->exists()) {
            return redirect()->route('model-types.index')->with('success','There are some stores exists with this store type.');
        }

        $storetype = ModelType::find($id);
        $storetype->delete();
        
        return redirect()->route('model-types.index')->with('success','Store Type deleted successfully');
    }

    public function select2List(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
    
        $query = ModelType::query();
    
        if (!empty($queryString)) {
            $query->where('name', 'LIKE', "%{$queryString}%");
        }
    
        $data = $query->paginate($limit, ['*'], 'page', $page);
    
        return response()->json([
            'items' => $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->name
                ];
            }),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }
}

