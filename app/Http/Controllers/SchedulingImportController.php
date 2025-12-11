<?php

namespace App\Http\Controllers;

use App\Models\SchedulingImport;
use Illuminate\Http\Request;

class SchedulingImportController extends Controller
{

    public function index(Request $request) {
        if ($request->ajax()) {

            return datatables()
            ->eloquent(SchedulingImport::latest()->scheduling())
            ->addColumn('checklist_name', function ($row) {
                return $row->checklist->name ?? '-';
            })
            ->addColumn('total', function ($row) {
                return $row->success + $row->error;
            })
            ->addColumn('action', function ($row) {
                $action = '';

                if (is_file(storage_path("app/public/scheduling-imports/modified/{$row->modified_file}"))) {
                    $action .= '<a href="' . asset("storage/scheduling-imports/modified/{$row->modified_file}") . '" download class="btn btn-sm btn-primary"> Download </a>';
                }

                return $action;
            })
            ->addColumn('status', function ($row) {
                if ($row->status == 1) {
                    return '<span class="badge bg-success"> Success </span>';
                } else if ($row->status == 2) {
                    return '<span class="badge bg-danger"> Failed </span>';
                } else {
                    return '<span class="badge bg-warning"> Partial Success  </span>';
                }
            })
            ->editColumn('uploaded_by', function ($row) {
                return isset($row->uploader->id) ? ($row->uploader->employee_id . ' - ' . $row->uploader->name . ' - ' . $row->uploader->middle_name . ' - ' . $row->uploader->last_name) : '-';
            })
            ->editColumn('created_at', function ($row) {
                return date('d-m-Y H:i', strtotime($row->created_at));
            })
            ->rawColumns(['status', 'action'])
            ->toJson();
        }

        $page_title = 'Excel Import History';
        $page_description = 'View excel imports here';
        return view('checklist-scheduling.history',compact('page_title', 'page_description'));
    }

    public function bulkDelete(Request $request) {
        if (empty($request->ids)) {
            return response()->json(['status' => false, 'message' => 'Please select records to delete!']);
        }

        $filesToBeDeleted = [];

        \DB::beginTransaction();

        try {

            foreach ($request->ids as $id) {
                $line = SchedulingImport::find($id);

                if (!empty($line->original_file) && is_file(storage_path("app/public/scheduling-imports/{$line->original_file}"))) {
                    array_push($filesToBeDeleted, storage_path("app/public/scheduling-imports/{$line->original_file}"));
                }

                if (!empty($line->modified_file) && is_file(storage_path("app/public/scheduling-imports/{$line->modified_file}"))) {
                    array_push($filesToBeDeleted, storage_path("app/public/scheduling-imports/{$line->modified_file}"));
                }

                $line->delete();                
            }

            \DB::commit();

            if (!empty($filesToBeDeleted)) {
                foreach ($filesToBeDeleted as  $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }

            return response()->json(['status' => true, 'message' => 'Import record deleted successfully!']);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Something went wrong!']);
        }
    }
}
