<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            return datatables()
            ->eloquent(StoreCategory::query())
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('store-categories.show')) {
                    $action .= '<a href="'.route("store-categories.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('store-categories.edit')) {
                    $action .= '<a href="'.route('store-categories.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if (auth()->user()->can('store-categories.destroy')) {
                    $action .= '<form method="POST" action="'.route("store-categories.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->rawColumns(['action'])
            ->toJson();
        }

        $page_title = 'Location Categories';
        $page_description = 'Manage location categories here';
        return view('store-categories.index',compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Location Category Add';

        return view('store-categories.create', compact( 'page_title'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                Rule::unique( 'store_categories', 'name' )->whereNull( 'deleted_at' ),
            ],
        ]);
    
        StoreCategory::create([
            'name' => $request->name,
        ]);
    
        return redirect()->route('store-categories.index')->with('success','Location Category created successfully');
    }

    public function show($id)
    {
        $page_title = 'Location Category Show';
        $storecategory = StoreCategory::find(decrypt($id));
    
        return view('store-categories.show', compact('storecategory', 'page_title'));
    }

    public function edit($id)
    {
        $page_title = 'Location Category Edit';
        $storecategory = StoreCategory::find(decrypt($id));
    
        return view('store-categories.edit', compact('storecategory', 'page_title', 'id'));
    }
    
    public function update(Request $request, $id)
    {
        $cId = decrypt($id);

        $request->validate([
            'name' => [
                'required',
                Rule::unique('store_categories', 'name')->whereNull('deleted_at')->ignore($cId),
            ],
        ]);

        $storecategory = StoreCategory::find($cId);
        $storecategory->update( $request->only( [ 'name' ] ) );
    
        return redirect()->route('store-categories.index')->with('success','Location Category updated successfully');
    }

    public function destroy($id)
    {
        $id = decrypt($id);

        if ( Store::where( 'store_category', $id )->exists() ) {
            return redirect()->route( 'store-categories.index' )->with( 'error', 'There are some stores exists with this location category.' );
        }

        $storecategory = StoreCategory::find($id);
        $storecategory->delete();
        
        return redirect()->route('store-categories.index')->with('success','Location Category deleted successfully');
    }

}

