<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            return datatables()
            ->eloquent(Document::query())
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('documents.show')) {
                    $action .= '<a href="'.route("documents.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('documents.edit')) {
                    $action .= '<a href="'.route('documents.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if (auth()->user()->can('documents.destroy')) {
                    $action .= '<form method="POST" action="'.route("documents.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->rawColumns(['action'])
            ->toJson();
        }

        $page_title = 'Document Types';
        $page_description = 'Manage documents types here';
        return view('documents.index',compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Document Type Add';

        return view('documents.create', compact( 'page_title'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                Rule::unique( 'documents', 'name' )->whereNull( 'deleted_at' ),
            ],
            'slug' => [
                'required',
                Rule::unique( 'documents', 'slug' )->whereNull( 'deleted_at' ),
            ],
        ]);
    
        Document::create([
            'name' => $request->name,
            'slug' => $request->slug
        ]);
    
        return redirect()->route('documents.index')->with('success','Document created successfully');
    }

    public function show($id)
    {
        $page_title = 'Document Type Show';
        $document = Document::find(decrypt($id));
    
        return view('documents.show', compact('document', 'page_title'));
    }

    public function edit($id)
    {
        $page_title = 'Document Type Edit';
        $document = Document::find(decrypt($id));
    
        return view('documents.edit', compact('document', 'page_title', 'id'));
    }
    
    public function update(Request $request, $id)
    {
        $cId = decrypt($id);

        $request->validate([
            'name' => [
                'required',
                Rule::unique( 'documents', 'name' )->whereNull('deleted_at')->ignore($cId),
            ],
            'slug' => [
                'required',
                Rule::unique( 'documents', 'slug' )->whereNull('deleted_at')->ignore($cId),
            ],
        ]);

        $document = Document::find($cId);
        $document->update( $request->only( [ 'name', 'slug' ] ) );
    
        return redirect()->route('documents.index')->with('success','Document updated successfully');
    }

    public function destroy($id)
    {
        $id = decrypt($id);

        $document = Document::find($id);
        $document->delete();
        
        return redirect()->route('documents.index')->with('success','Document deleted successfully');
    }

    public function select2List(Request $request)
    {
        $searchQuery = $request->input('searchQuery');
        $page = $request->input('page', 1);
        $perPage = 10;

        $documents = Document::query()
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where('name', 'like', "%{$searchQuery}%");
            })
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'items' => $documents->getCollection()->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'text' => $doc->name,
                ];
            }),
            'pagination' => [
                'more' => $documents->hasMorePages(),
            ],
        ]);
    }

}

