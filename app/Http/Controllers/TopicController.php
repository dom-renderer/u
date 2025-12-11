<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\Topic;
use App\Models\Tag;

class TopicController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            $viewType = $request->get('view_type', 'table');
            
            if ($viewType === 'tree') {
                $categories = Topic::with(['parent', 'children'])
                    ->orderBy('ordering')
                    ->get();
                
                $hierarchicalData = $this->buildHierarchicalCollection($categories);
                
                return dataTables()->of($hierarchicalData)
                    ->addColumn('parentcat', function ($row) {
                        return $row['parent_name'] ?? '-';
                    })
                    ->editColumn('name', function ($row) {
                        return $this->formatTreeName($row);
                    })
                    ->editColumn('status', function ($row) {
                        if ($row['status']) {
                            return '<span class="bg-success badge"> Enable </span>';
                        } else {
                            return '<span class="bg-danger badge"> Disable </span>';
                        }
                    })
                    ->addColumn('action', function ($row) {
                        return $this->buildActionButtons($row);
                    })
                    ->addColumn('tree_data', function ($row) {
                        return [
                            'level' => $row['tree_level'],
                            'parent_id' => $row['parent_id'],
                            'has_children' => $row['has_children']
                        ];
                    })
                    ->rawColumns(['action', 'status', 'name'])
                    ->make(true);
            } else {
                $categories = Topic::with('parent')->orderBy('ordering');
                
                return dataTables()->eloquent($categories)
                    ->addColumn('parentcat', function ($row) {
                        return $row->parent->name ?? '-';
                    })
                    ->editColumn('status', function ($row) {
                        if ($row->status) {
                            return '<span class="bg-success badge"> Enable </span>';
                        } else {
                            return '<span class="bg-danger badge"> Disable </span>';
                        }
                    })
                    ->addColumn('action', function ($row) {
                        return $this->buildActionButtons($row);
                    })
                    ->rawColumns(['action', 'status'])
                    ->make(true);
            }
        }

        $page_title = 'Categories';
        $page_description = 'Manage categories here';
            
        return view('topics.index', compact('page_title', 'page_description'));
    }

    private function buildHierarchicalCollection($categories)
    {
        $hierarchical = collect();
        $this->buildHierarchy($categories, $hierarchical, null, 0);
        
        return $hierarchical->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'parent_id' => $category->parent_id,
                'parent_name' => $category->parent ? $category->parent->name : null,
                'status' => $category->status,
                'tree_level' => $category->tree_level,
                'has_children' => $category->children_count > 0,
                'ordering' => $category->ordering,
            ];
        })->toArray();
    }

    private function buildHierarchy($allCategories, &$result, $parentId = null, $level = 0)
    {
        $categories = $allCategories->where('parent_id', $parentId)->sortBy('ordering');
        
        foreach ($categories as $category) {
            $category->tree_level = $level;
            $category->children_count = $allCategories->where('parent_id', $category->id)->count();
            
            $result->push($category);
            
            $this->buildHierarchy($allCategories, $result, $category->id, $level + 1);
        }
    }

    private function formatTreeName($row)
    {
        $level = $row['tree_level'];
        $hasChildren = $row['has_children'];
        $categoryId = $row['id'];
        $categoryName = $row['name'];
        
        $indent = '<span class="tree-indent tree-indent-' . $level . '">';
        
        if ($hasChildren && $level === 0) {
            $indent .= '<i class="fas fa-chevron-down tree-toggle" data-category-id="' . $categoryId . '"></i>';
            $indent .= '<span class="category-name parent-category">';
            $indent .= '<i class="fas fa-folder"></i> ' . $categoryName;
            $indent .= '</span>';
        } elseif ($level > 0) {
            $indent .= '<span class="tree-line">';
            if ($hasChildren) {
                $indent .= '<i class="fas fa-chevron-down tree-toggle" data-category-id="' . $categoryId . '"></i>';
            } else {
                $indent .= '<i class="tree-toggle"></i>';
            }
            $indent .= '<span class="category-name child-category">';
            $indent .= '<i class="fas fa-file-alt"></i> ' . $categoryName;
            $indent .= '</span>';
            $indent .= '</span>';
        } else {
            $indent .= '<i class="tree-toggle"></i>';
            $indent .= '<span class="category-name parent-category">';
            $indent .= '<i class="fas fa-file-alt"></i> ' . $categoryName;
            $indent .= '</span>';
        }
        
        $indent .= '</span>';
        
        return $indent;
    }

    private function buildActionButtons($row)
    {
        $action = '';
        $rowId = is_array($row) ? $row['id'] : $row->id;
        $rowStatus = is_array($row) ? $row['status'] : $row->status;

        if (auth()->user()->can('topics.show')) {
            $action .= '<a href="'.route("topics.show", encrypt($rowId)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
        }

        if (auth()->user()->can('topics.edit')) {
            $action .= '<form style="display:inline;" method="POST" action="'.route("topics.enable-disable", encrypt($rowId)).'" ><input type="hidden" name="_token" value="'.csrf_token().'">';
            if ($rowStatus) {
                $action .= '<button type="submit" class="btn btn-danger btn-sm me-2 status-changer" data-description="Disable topic" data-blable="Disable"> Disable </button>';
            } else {
                $action .= '<button type="submit" class="btn btn-success btn-sm me-2 status-changer" data-description="Enable topic" data-blable="Enable"> Enable </button>';
            }
            $action .= '</form>';
        }

        if (auth()->user()->can('topics.edit')) {
            $action .= '<a href="'.route('topics.edit', encrypt($rowId)).'" class="btn btn-info btn-sm me-2">Edit</a>';
        }

        if (auth()->user()->can('topics.destroy')) {
            $action .= '<form method="POST" action="'.route("topics.destroy", encrypt($rowId)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
        }

        return $action;
    }

    public function create(Request $request) {
        $page_title = 'Category Add';
        $moduleLink = route('topics.index');
        return view('topics.create', compact('page_title','moduleLink'));
    }

    public function store(Request $request)
    {
        $user = new Topic();
        $user->name = $request->name;
        $user->status = $request->status;
        $user->slug = Helper::slug($request->name);
        
        if (!empty($request->parent)) {
            $user->parent_id = $request->parent;
        }

        $user->save();

        return redirect()->route('topics.index')->with('success', 'Topic added successfully.');
    }

    public function edit($id)
    {
        $page_title = 'Category Edit';
        $moduleLink = route('topics.index');
        $decryptedId = decrypt($id);
        $category = Topic::with(['parent'])->where('id', $decryptedId)->first();
        $totalSubCategories = Topic::where('parent_id', $decryptedId)->count();

        return view('topics.edit', compact('page_title', 'id', 'category','moduleLink', 'totalSubCategories'));
    }

    public function getSubCatCount(Request $request) {
        $count = 0;
        $topics = Topic::firstWhere('id', $request->id);

        if ($topics != null) {
            if (!empty($request->parent) && is_numeric($request->parent) && $request->parent != '0' && $request->parent != $topics->parent_id) {
                $count = Topic::where('parent_id', $topics->id)->count();
            }            
        }

        return response(['count' => $count]);
    }

    public function update(Request $request, $id)
    {
        $id = decrypt($id);
        $parent = trim($request->parent);

        \DB::beginTransaction();

        try {
            $user = Topic::find($id);

            if (!empty($parent) && is_numeric($parent) && $parent != '0' && $user->parent_id != $parent) {
                $user->parent_id = $parent;
            }
    
            $user->name = $request->name;
            $user->status = $request->status;
            $user->slug = Helper::slug($request->name);
            $user->save();

            \DB::commit();
            return redirect()->route('topics.index')->with('success', 'Topic updated successfully.');
        } catch (\Exception $e) {
            dd($e->getMessage());
            \DB::rollBack();
            return redirect()->route('topics.index')->with('error', 'Something went wrong!');
        }
    }

    public function show($id)
    {
        $page_title = 'Category Show';
        $moduleLink = route('topics.index');
        $category = Topic::with('children')->where('id', decrypt($id))->first();

        return view('topics.view', compact('page_title', 'category','moduleLink'));
    }

    public function destroy($id)
    {
        $category = Topic::find(decrypt($id));

        if ($category->delete()) {
            return redirect()->back()->with('success', 'Topic deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Something wnt wrong!');
        }
    }

    public function enableDisable($id) {
        $category = Topic::find(decrypt($id));
        $category->status = $category->status == 1 ? 0 :  1;
        $category->save();

        return redirect()->back()->with('success', 'Topic status changed successfully.');
    }

    public static function getChildren($category) {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'status' => $category->status,
            'children' => $category->children->map(function ($child) {
                return self::getChildren($child);
            })->values()->toArray()
        ];
    }

    public function categorySelect2(Request $request) 
    {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = env('SELECT2_PAGE_LENGTH', 5);
        $onlyActive = $request->onlyactive;
        $except = $request->except;
    
        $query = Topic::query();
    
        if (!empty($queryString)) {
            $query->where('name', 'LIKE', "%{$queryString}%");
        }

        if ($onlyActive == '1') {
            $query->where('status', 1);
        }

        if (!empty($except) && is_string($except)) {
            $except = explode(',', $except);
            if (count($except) > 0) {
                $query->whereNotIn('id', $except);
            }
        }
    
        $data = $query->paginate($limit, ['*'], 'page', $page);
    
        return response()->json([
            'items' => $data->map(function ($pro) {
                return [
                    'id' => $pro->id,
                    'text' => $pro->name
                ];
            }),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }

    public function getAllTags(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = env('SELECT2_PAGE_LENGTH', 5);
        $onlyActive = $request->onlyactive;
        $except = $request->except;
    
        $query = Tag::query();
    
        if (!empty($queryString)) {
            $query->where('title', 'LIKE', "%{$queryString}%");
        }

        if (!empty($except) && is_string($except)) {
            $except = explode(',', $except);
            if (count($except) > 0) {
                $query->whereNotIn('id', $except);
            }
        }
    
        $data = $query->paginate($limit, ['*'], 'page', $page);
    
        return response()->json([
            'items' => $data->map(function ($pro) {
                return [
                    'id' => $pro->id,
                    'text' => $pro->title
                ];
            }),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }

    public function sort(Request $request) {
        $categories = $request->cat_ids;

        foreach ($categories as $order => $category) {
            Topic::find($category)->update(['ordering' => $order]);
        }

        return response()->json(['status' => true]);
    }       
}
