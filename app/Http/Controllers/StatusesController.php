<?php

namespace App\Http\Controllers;

use App\Helpers\EditorLocale;
use App\Helpers\LaravelVersion;
use App\Models\Agent;
use App\Models\Status;
use App\Models\TicketSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class StatusesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        // seconds expected for L5.8<=, minutes before that
        /** all needed vars start */
        $u = Agent::find(auth()->user()->id);
        $setting = new TicketSetting();
        $tools = new ToolsController();
        $master = TicketSetting::grab('master_template');
        $email = TicketSetting::grab('email.template');
        $editor_enabled = TicketSetting::grab('editor_enabled');
        $codemirror_enabled = TicketSetting::grab('editor_html_highlighter');
        $codemirror_theme = TicketSetting::grab('codemirror_theme');
        $editor_locale = EditorLocale::getEditorLocale();
        $editor_options = file_get_contents(base_path().'/resources/views/ticket/json/summernote_init.json');
        $include_font_awesome = TicketSetting::grab('include_font_awesome');
        /** all needed vars end */

        $time = LaravelVersion::min('5.8') ? 60*60 : 60;
        $statuses = Status::all();

        return view('ticket.admin.status.index', compact('u', 'setting', 'tools', 'master', 'email', 'editor_enabled', 'codemirror_enabled', 'codemirror_theme', 'editor_locale', 'editor_options', 'include_font_awesome', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        /** all needed vars start */
        $u = Agent::find(auth()->user()->id);
        $setting = new TicketSetting();
        $tools = new ToolsController();
        $master = TicketSetting::grab('master_template');
        $email = TicketSetting::grab('email.template');
        $editor_enabled = TicketSetting::grab('editor_enabled');
        $codemirror_enabled = TicketSetting::grab('editor_html_highlighter');
        $codemirror_theme = TicketSetting::grab('codemirror_theme');
        $editor_locale = EditorLocale::getEditorLocale();
        $editor_options = file_get_contents(base_path().'/resources/views/ticket/json/summernote_init.json');
        $include_font_awesome = TicketSetting::grab('include_font_awesome');
        /** all needed vars end */
        return view('ticket.admin.status.create', compact('u', 'setting', 'tools', 'master', 'email', 'editor_enabled', 'codemirror_enabled', 'codemirror_theme', 'editor_locale', 'editor_options', 'include_font_awesome'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'      => 'required',
            'color'     => 'required',
        ]);

        $status = new Status();
        $status->create(['name' => $request->name, 'color' => $request->color]);

        Session::flash('status', "The status {$request->name} has been created!");

        Cache::forget('ticketit::statuses');

        return redirect()->action('\App\Http\Controllers\StatusesController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        return "All status related tickets here";
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $status = Status::findOrFail($id);
        /** all needed vars start */
        $u = Agent::find(auth()->user()->id);
        $setting = new TicketSetting();
        $tools = new ToolsController();
        $master = TicketSetting::grab('master_template');
        $email = TicketSetting::grab('email.template');
        $editor_enabled = TicketSetting::grab('editor_enabled');
        $codemirror_enabled = TicketSetting::grab('editor_html_highlighter');
        $codemirror_theme = TicketSetting::grab('codemirror_theme');
        $editor_locale = EditorLocale::getEditorLocale();
        $editor_options = file_get_contents(base_path().'/resources/views/ticket/json/summernote_init.json');
        $include_font_awesome = TicketSetting::grab('include_font_awesome');
        /** all needed vars end */
        return view('ticket.admin.status.edit', compact('status', 'u', 'setting', 'tools', 'master', 'email', 'editor_enabled', 'codemirror_enabled', 'codemirror_theme', 'editor_locale', 'editor_options', 'include_font_awesome'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name'      => 'required',
            'color'     => 'required',
        ]);

        $status = Status::findOrFail($id);
        $status->update(['name' => $request->name, 'color' => $request->color]);

        Session::flash('status', "The status {$request->name} has been modified!");

        Cache::forget('ticketit::statuses');

        return redirect()->action('\App\Http\Controllers\StatusesController@index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $status = Status::findOrFail($id);
        $name = $status->name;
        $status->delete();

        Session::flash('status', "The status {$name} has been deleted!");

        Cache::forget('ticketit::statuses');

        return redirect()->action('\App\Http\Controllers\StatusesController@index');
    }
}
