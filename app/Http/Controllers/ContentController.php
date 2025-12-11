<?php

namespace App\Http\Controllers;

use App\Models\ContentAnalytic;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\ContentAccessPermission;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use App\Models\ContentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ContentTag;
use App\Helpers\Helper;
use App\Models\Content;
use App\Models\Topic;
use App\Models\Tag;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
        $categories = Content::query()
        ->when(!empty($request->categories) && is_array($request->categories), function ($builder) {
            $builder->whereIn('topic_id', request('categories'));
        })
        ->when($request->status == 1, function ($builder) {
            $builder->where('status', 1);
        })
        ->when($request->status == 2, function ($builder) {
            $builder->where('status', 0);
        })
        ->when(!empty($request->from), function ($builder) {
            $builder->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime(request('from'))));
        })
        ->when(!empty($request->to), function ($builder) {
            $builder->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime(request('to'))));
        })
        ->orderBy('ordering');


        return dataTables()->eloquent($categories)
            ->editColumn('status', function ($row) {
                if ($row->status) {
                    return '<span class="bg-success badge"> Active </span>';
                } else {
                    return '<span class="bg-danger badge"> InActive </span>';
                }
            })
            ->editColumn('cat', function ($row) {
                return $row->topic->name ?? '';
            })
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('contents.show')) {
                    $action .= '<a href="'.route("contents.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('contents.edit')) {
                    $action .= '<form style="display:inline;" method="POST" action="'.route("contents.enable-disable", encrypt($row->id)).'" ><input type="hidden" name="_token" value="'.csrf_token().'">';
                    if ($row->status) {
                        $action .= '<button type="submit" class="btn btn-danger btn-sm me-2 status-changer" data-description="Disable Content" data-blable="Disable"> Disable </button>';
                    } else {
                        $action .= '<button type="submit" class="btn btn-success btn-sm me-2 status-changer" data-description="Enable Content" data-blable="Enable"> Enable </button>';
                    }
                    $action .= '</form>';
                }

                if (auth()->user()->can('contents.edit')) {
                    $action .= '<a href="'.route('contents.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if (auth()->user()->can('contents.destroy')) {
                    $action .= '<form method="POST" action="'.route("contents.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->addColumn('pub_date', function ($row) {
                return date('d-m-Y H:i', strtotime($row->created_at));
            })
            ->addColumn('exp_date', function ($row) {
                if (empty($row->expiry_date)) {
                    return '<span class="badge bg-danger"> No Expiry </span>';
                } else {
                    if (date('d-m-Y', strtotime($row->expiry_date)) < date('d-m-Y') && $row->status == 0) {
                        return '<span class="badge bg-danger"> Expired on ' . date('d-m-Y', strtotime($row->expiry_date)) . ' </span>';
                    }

                    return date('d-m-Y', strtotime($row->expiry_date));
                }
            })
            ->rawColumns(['action', 'status', 'exp_date'])
            ->make(true);
        }

        $page_title = 'Content';
        $page_description = 'Manage content here';
            
        return view('contents.index', compact('page_title', 'page_description'));
    }

    public function create()
    {
        $roles = Role::latest()->get();

        return view('contents.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'topic_id' => 'required|exists:topics,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'boolean',
            'attachments' => 'required|array|min:1',
            'attachments.*.type' => 'required|in:video,image,document',
            'attachments.*.description' => 'nullable|string',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
    
        $content = Content::create([
            'topic_id' => $request->topic_id,
            'title' => $request->title,
            'slug' => Helper::slug($request->title),
            'description' => urldecode($request->description),
            'expiry_date' => empty($request->expdate) ? null : date('Y-m-d', strtotime($request->expdate)),
            'status' => $request->status ?? 1,
            'added_by' => Auth::id(),
        ]);

        // Permisison Management

        ContentAccessPermission::create([
            'content_id' => $content->id,
            'permission_matrix' => [
                'type' => $request->assination_type,
                'roles' => request('roles'),
                'users' => request('employees')
            ]
        ]);

        // Permisison Management

        $allTags = [];

        if (is_array($request->tags) && !empty($request->tags)) {
            foreach ($request->tags as $tag) {
                $tempTag = Tag::where(\DB::raw('LOWER(title)'), strtolower($tag))->first();
                if (!$tempTag) {
                    $allTags[] = Tag::create(['title' => $tag])->id;
                } else {
                    $allTags[] = $tempTag->id;
                }
            }
        }

        if (!empty($allTags)) {
            foreach ($allTags as $thisTag) {
                ContentTag::create([
                    'tag_id' => $thisTag,
                    'content_id' => $content->id
                ]);
            }
        }
    
        if ($request->has('attachments')) {
            $order = 0;
            foreach ($request->attachments as $attachment) {
                $originalPath = $attachment['path'];
    
                $myTempPath = "temp_uploads/" . md5(auth()->user()->id);
                
                if (Str::startsWith($originalPath, "{$myTempPath}")) {
                    $newPath = 'content_attachments/' . basename($originalPath);
                    Storage::disk('public')->move($originalPath, $newPath);
                } else {
                    $newPath = $originalPath;
                }
    
                ContentAttachment::create([
                    'content_id' => $content->id,
                    'type' => $attachment['type'],
                    'path' => str_replace('content_attachments/', '', $newPath),
                    'description' => isset($attachment['description']) ? urldecode($attachment['description']) : null,
                    'order' => $order++,
                ]);
            }
            
            Storage::disk('public')->deleteDirectory($myTempPath);
        }
    
        return redirect()->route('contents.index')->with('success', 'Content created successfully');
    }
    

    public function show(Request $request, $id)
    {
        $content = Content::find(decrypt($id));
        $topics = Topic::where('status', 1)->get();
        $roles = Role::latest()->get();
        $selectedRoles = $content->permission->permission_matrix->roles ?? [];
        $selectedEmployees = $content->permission->permission_matrix->employees ?? [];
        $permissionMatrix = ['type' => $content->permission->permission_matrix->type ?? 0];

        return view('contents.show', compact('content', 'topics', 'roles', 'selectedRoles', 'selectedEmployees', 'permissionMatrix'));
    }

    public function edit(Request $request, $id)
    {
        $content = Content::find(decrypt($id));
        $topics = Topic::where('status', 1)->get();
        $roles = Role::latest()->get();
        $selectedRoles = $content->permission->permission_matrix->roles ?? [];
        $selectedEmployees = $content->permission->permission_matrix->employees ?? [];
        $permissionMatrix = ['type' => $content->permission->permission_matrix->type ?? 0];

        return view('contents.edit', compact('content', 'topics', 'roles', 'selectedRoles', 'selectedEmployees', 'permissionMatrix'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'topic_id' => 'required|exists:topics,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'boolean',
            'attachments' => 'nullable|array',
            'attachments.*.type' => 'required_with:attachments.*.path|in:video,image,document',
            'attachments.*.description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $content = Content::findOrFail($id);

        $content->update([
            'topic_id' => $request->topic_id,
            'title' => $request->title,
            'expiry_date' => empty($request->expdate) ? null : date('Y-m-d', strtotime($request->expdate)),
            'slug' => Helper::slug($request->title),
            'description' => urldecode($request->description),
            'status' => $request->status ?? 1,
        ]);

        // Permission Management

        ContentAccessPermission::updateOrCreate(
            ['content_id' => $content->id],
            ['permission_matrix' => [
                'type' => $request->assination_type,
                'roles' => $request->roles,
                'users' => $request->employees
            ]]
        );

        // Permission Management

        $allTags = [];
        if (is_array($request->tags) && !empty($request->tags)) {
            foreach ($request->tags as $tag) {
                $tempTag = Tag::where(\DB::raw('LOWER(title)'), strtolower($tag))->first();
                if (!$tempTag) {
                    $allTags[] = Tag::create(['title' => $tag])->id;
                } else {
                    $allTags[] = $tempTag->id;
                }
            }
        }
        
        ContentTag::where('content_id', $content->id)->delete();
        foreach ($allTags as $thisTag) {
            ContentTag::create([
                'tag_id' => $thisTag,
                'content_id' => $content->id
            ]);
        }

        if ($request->has('attachments')) {
            ContentAttachment::where('content_id', $content->id)->delete();
            $order = 0;
            $myTempPath = "temp_uploads/" . md5(auth()->user()->id);

            foreach ($request->attachments as $attachment) {
                $originalPath = $attachment['path'];

                if (Str::startsWith($originalPath, "{$myTempPath}/")) {
                    $newPath = 'content_attachments/' . basename($originalPath);
                    Storage::disk('public')->move($originalPath, $newPath);
                } else {
                    $newPath = $originalPath;
                }

                ContentAttachment::create([
                    'content_id' => $content->id,
                    'type' => $attachment['type'],
                    'path' => str_replace('content_attachments/', '', $newPath),
                    'description' => isset($attachment['description']) ? urldecode($attachment['description']) : null,
                    'order' => $order++,
                ]);
            }

            Storage::disk('public')->deleteDirectory($myTempPath);
        }

        return redirect()->route('contents.index')->with('success', 'Content updated successfully');
    }

    public function enableDisable($id) {
        $content = Content::find(decrypt($id));
        $content->status = $content->status == 1 ? 0 :  1;
        $content->save();

        return redirect()->back()->with('success', 'Content status changed successfully.');
    }
    
    public function destroy(Request $request, $id)
    {
        $content = Content::find(decrypt($id));

        foreach ($content->attachments as $attachment) {
            if ($attachment->path && Storage::disk('public')->exists("content_attachments/{$attachment->path}")) {
                Storage::disk('public')->delete($attachment->path);
            }
        }

        $content->delete();
        return redirect()->route('contents.index')->with('success', 'Content deleted successfully');
    }

    public function deleteAttachment($id)
    {
        $attachment = ContentAttachment::findOrFail($id);
        
        if ($attachment->path && Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }
        
        $attachment->delete();
        
        return response()->json(['success' => true]);
    }

    public function uploadAttachment(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:102400',
            'type' => 'required|in:video,image,document',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $myTempPath = "temp_uploads/" . md5(auth()->user()->id);
        $file = $request->file('file');
        $originalFilename = $file->getClientOriginalName();
        $filename = time() . '_' . $originalFilename;
    
        if ($request->has('dzchunkindex')) {
            $result = $this->handleChunkedUpload($request, $myTempPath, $filename);
            
            if (!is_string($result)) {
                return $result;
            }
            
            $path = $result;
        } else {
            $path = $file->storeAs($myTempPath, $filename, 'public');
            
            if ($request->type === 'video') {
                $path = $this->compressVideo($path, $myTempPath, $filename);
            }
        }

        return response()->json([
            'success' => true,
            'path' => $path,
            'original_name' => $originalFilename
        ]);
    }

    private function handleChunkedUpload($request, $myTempPath, $filename)
    {
        $file = $request->file('file');
        $uuid = $request->get('dzuuid');
        $chunkIndex = $request->get('dzchunkindex');
        $totalChunks = $request->get('dztotalchunkcount');
        
        $tempFolder = storage_path("app/public/tmp/{$uuid}");
        if (!is_dir($tempFolder)) {
            mkdir($tempFolder, 0777, true);
        }
        
        $file->move($tempFolder, $chunkIndex);
        
        $uploadedChunks = count(scandir($tempFolder)) - 2;
        if ($uploadedChunks == $totalChunks) {
            $uploadDir = storage_path("app/public/{$myTempPath}");
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $finalPath = $uploadDir . '/' . $filename;
            $output = fopen($finalPath, 'wb');
            
            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkPath = $tempFolder . '/' . $i;
                $chunkContent = file_get_contents($chunkPath);
                fwrite($output, $chunkContent);
            }
            fclose($output);
            
            array_map('unlink', glob($tempFolder . '/*'));
            rmdir($tempFolder);
            
            $path = "{$myTempPath}/{$filename}";
            
            if ($request->type === 'video') {
                $path = $this->compressVideo($path, $myTempPath, $filename);
            }
            
            return $path;
        }
        
        return response()->json(['chunkReceived' => $chunkIndex], 200);
    }

    private function compressVideo($path, $myTempPath, $filename)
    {
        $compressedFilename = 'compressed_' . $filename;
        $compressedPath = storage_path("app/public/{$myTempPath}/{$compressedFilename}");
        
        $inputPath = storage_path("app/public/{$path}");
        // $command = "ffmpeg -i \"{$inputPath}\" -c:v libx264 -crf 23 -preset medium -c:a aac -b:a 128k \"{$compressedPath}\"";
        // ffmpeg -i "input.mp4" -c:v libx264 -crf 28 -preset slow -c:a aac -b:a 96k "output.mp4"
        $command = "ffmpeg -i \"{$inputPath}\" -c:v libx264 -crf 40 -preset slow -c:a aac -b:a 96k \"{$compressedPath}\"";

        
        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);
        
        if ($returnVar === 0 && file_exists($compressedPath)) {
            return "{$myTempPath}/{$compressedFilename}";
        }
        
        return $path;
    }


    public function analytics(Request $request) 
    {
        if ($request->ajax()) {
        $analytics = ContentAnalytic::latest();

        if ($request->has('user') && !is_null($request->user)) {
            $analytics = $analytics->where('user_id', $request->user);
        }

        if ($request->has('content') && !is_null($request->content)) {
            $analytics = $analytics->whereHas('video', function ($builder) {
                $builder->where('content_id', request('content'));
            });
        }

        if ($request->has('status') && !is_null($request->status)) {
            if ($request->status == 'pending') {
                $analytics = $analytics->where('watching_time', 0);
            } else if ($request->status == 'partially') {
                $analytics = $analytics->where('watching_time', '>', 0)->where('watching_time', '<', \DB::raw('total_seconds'));
            } else if ($request->status == 'completed') {
                $analytics = $analytics->where('watching_time', '>=', \DB::raw('total_seconds'));
            }
        }

        return dataTables()->eloquent($analytics)
            ->editColumn('status', function ($row) {
                if ($row->watching_time == 0) {
                    return '<span class="bg-danger badge"> Not Watched </span>';
                } elseif ($row->watching_time >= $row->total_seconds) {
                    return '<span class="bg-primary badge"> Fully Watched </span>';
                } else {
                    return '<span class="bg-success badge"> Partially Watched </span>';
                }
            })
            ->addColumn('usr', function ($row) {
                return ($row->user->name ?? ' ') . ' ' . ($row->user->middle_name ?? ' ') . ' ' . ($row->user->last_name ?? ' ');
            })
            ->addColumn('percent', function ($row) {
                return $row->total_seconds > 0 ? (number_format(($row->watching_time / $row->total_seconds) * 100, 2) . '%') : '0.00%';
            })
            ->addColumn('cntnt', function ($row) {
                return $row->video->content->title ?? '';
            })
            ->addColumn('vid', function ($row) {
                $id = $row->video->path ?? '';

                return '<a target="_blank" href="' . asset('storage/content_attachments/' . $row->video->path ?? '' ) . '"> ' . $id . ' </a>';
            })
            ->rawColumns(['status', 'vid'])
            ->make(true);
        }

        $page_title = 'Content Analytics';
        $page_description = 'View content analytics here';
            
        return view('contents.analytics', compact('page_title', 'page_description'));
    }

    public function getAllContent(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $type = $request->type;
        $limit = 10;
    
        $query = Content::query();
    
        if (!empty($queryString)) {
            $query->where('title', 'LIKE', "%{$queryString}%");
        }
    
        $data = $query->paginate($limit, ['*'], 'page', $page);
    
        return response()->json([
            'items' => $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->title
                ];
            }),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }

    public function sort(Request $request) {
        $contents = $request->cat_ids;

        foreach ($contents as $order => $content) {
            Content::find($content)->update(['ordering' => $order]);
        }

        return response()->json(['status' => true]);
    }    
}
