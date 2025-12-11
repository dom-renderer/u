<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Models\StoreType;

class StoreTypeController extends Controller
{
    public function index(Request $request)
    {   
        if ($request->ajax()) {

            return datatables()
            ->eloquent(StoreType::query())
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('store-types.show')) {
                    $action .= '<a href="'.route("store-types.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('store-types.edit')) {
                    $action .= '<a href="'.route('store-types.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if (auth()->user()->can('store-types.destroy')) {
                    $action .= '<form method="POST" action="'.route("store-types.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->rawColumns(['action'])
            ->toJson();
        }

        $page_title = 'Location Types';
        $page_description = 'Manage location types here';
        return view('store-types.index',compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Location Type Add';

        return view('store-types.create', compact( 'page_title'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:store_types,name'
        ]);
    
        StoreType::create([
            'name' => $request->name,
            'description' => $request->description
        ]);
    
        return redirect()->route('store-types.index')->with('success','Store Type created successfully');
    }

    public function show($id)
    {
        $page_title = 'Location Type Show';
        $storetype = StoreType::find(decrypt($id));
    
        return view('store-types.show', compact('storetype', 'page_title'));
    }

    public function edit($id)
    {
        $page_title = 'Location Type Edit';
        $storetype = StoreType::find(decrypt($id));
    
        return view('store-types.edit', compact('storetype', 'page_title', 'id'));
    }
    
    public function update(Request $request, $id)
    {
        $cId = decrypt($id);

        $request->validate([
            'name' => "required|unique:store_types,name,{$cId}"
        ]);

        $storetype = StoreType::find($cId);
        $storetype->update($request->only(['name', 'description']));
    
        return redirect()->route('store-types.index')->with('success','Store Type updated successfully');
    }

    public function destroy($id)
    {
        $id = decrypt($id);

        if (Store::where('store_type', $id)->exists()) {
            return redirect()->route('store-types.index')->with('success','There are some stores exists with this store type.');
        }

        $storetype = StoreType::find($id);
        $storetype->delete();
        
        return redirect()->route('store-types.index')->with('success','Store Type deleted successfully');
    }

    public function select2List(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
    
        $query = StoreType::query();
    
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

