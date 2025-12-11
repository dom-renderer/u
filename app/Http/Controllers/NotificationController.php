<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {

            $data = SystemNotification::latest();

            return datatables()
            ->eloquent($data)
            ->addColumn('userd', function ($row) {
                return isset($row->user->id) ? ($row->user->name . ' ' . $row->user->middle_name . ' ' . $row->user->last_name) : '';
            })
            ->editColumn('title', function ($row) {
                return '<a target="_blank" href="' . $row->link . '"> ' . $row->title . ' </a>';
            })
            ->addColumn('dttime', function ($row) {
                return date('d-m-Y H:i', strtotime($row->created_at));
            })
            ->rawColumns(['title'])
            ->addIndexColumn()
            ->toJson();
        }

        $page_title = 'Notifications';
        $page_description = 'See all notifications here';
        return view('notifications',compact('page_title', 'page_description'));
    }
}
