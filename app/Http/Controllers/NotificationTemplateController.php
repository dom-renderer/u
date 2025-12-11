<?php

namespace App\Http\Controllers;

use App\Models\TemplatePresetNotification;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {

            return datatables()
            ->eloquent(NotificationTemplate::query())
            ->editColumn('type', function ($row) {
                if ($row->type) {
                    return 'Push Notificaiton';
                } else {
                    return 'Email';
                }
            })
            ->editColumn('status', function ($row) {
                if ($row->status) {
                    return '<span class="badge bg-success"> Active </span>';
                } else {
                    return '<span class="badge bg-danger"> InActive </span>';
                }
            })
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('notification-templates.show')) {
                    $action .= '<a href="'.route("notification-templates.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('notification-templates.edit')) {
                    $action .= '<a href="'.route('notification-templates.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if (auth()->user()->can('notification-templates.destroy')) {
                    $action .= '<form method="POST" action="'.route("notification-templates.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->rawColumns(['action', 'status'])
            ->toJson();
        }

        $page_title = 'Notification Template';
        $page_description = 'Manage notification templates here';

        return view('notification-templates.index',compact('page_title', 'page_description'));
    }

    public function create()
    {
        $page_title = 'Template Add';

        return view('notification-templates.create', compact( 'page_title'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'title' => 'required',
            'description' => 'required',
            'status' => 'required'
        ]);

        NotificationTemplate::create([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'title' => $validated['title'],
            'content' => $validated['description'],
            'status' => $validated['status'],
            'completion_type' => $request->completion == 1 ? 1 : 0
        ]);

        return redirect()->route('notification-templates.index')->with('success', 'Template created successfully.');
    }

    public function edit(Request $request, $id)
    {
        $page_title = 'Template Edit';
        $notification = NotificationTemplate::find(decrypt($id));

        return view('notification-templates.edit', compact( 'page_title', 'notification', 'id'));
    }

    public function update(Request $request, $id) {
        $validated = $request->validate([
            'name' => 'required',
            'type' => 'required',
            'title' => 'required',
            'description' => 'required',
            'status' => 'required'
        ]);

        $notification = NotificationTemplate::find(decrypt($id));
        $notification->type = $validated['type'];
        $notification->name = $validated['name'];
        $notification->title = $validated['title'];
        $notification->content = $validated['description'];
        $notification->status = $validated['status'];
        $notification->completion_type = $request->completion == 1 ? 1 : 0;
        $notification->save();

        return redirect()->route('notification-templates.index')->with('success', 'Template upadted successfully.');
    }

    public function show(Request $request, $id)
    {
        $page_title = 'Template Show';
        $notification = NotificationTemplate::find(decrypt($id));

        return view('notification-templates.show', compact( 'page_title', 'notification', 'id'));
    }
    
    public function destroy($id)
    {
        $temp = NotificationTemplate::find(decrypt($id));
        TemplatePresetNotification::where('notification_template_id', $temp->id)->delete();
        $temp->delete();
        
        return redirect()->route('notification-templates.index')->with('success','Template deleted successfully');
    }

    public function select2List(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $completionType = $request->completion_type;
        $type = $request->type;
        $limit = 10;
    
        $query = NotificationTemplate::query();
    
        if (!empty($queryString)) {
            $query = $query->where(function ($innerBuilder) use ($queryString) {
                return $innerBuilder->where('name', 'LIKE', "%{$queryString}%")
                ->orWhere('title', 'LIKE', "%{$queryString}%");
            });
        }

        if ($completionType == 1) {
            $query = $query->where('completion_type', 1);
        } else if ($completionType === 0) {
            $query = $query->where('completion_type', 0);
        }

        if ($type == 1) {
            $query = $query->where('type', 1);
        } else if ($type === 0) {
            $query = $query->where('type', 0);
        }
    
        $data = $query->paginate($limit, ['*'], 'page', $page);
    
        return response()->json([
            'items' => $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => (request('withType') == 1 ? ucwords(NotificationTemplate::typeOf($item->type) . ' - ') : '') . $item->name . ' - ' . $item->title
                ];
            }),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }
}
