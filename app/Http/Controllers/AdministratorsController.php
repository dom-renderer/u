<?php

namespace App\Http\Controllers;

use App\Helpers\EditorLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Agent;
use App\Models\TicketSetting;

class AdministratorsController extends Controller
{
    public function index()
    {
        $administrators = Agent::admins();
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
        return view('ticket.admin.administrator.index', compact('administrators', 'u', 'setting', 'tools', 'master', 'email', 'editor_enabled', 'codemirror_enabled', 'codemirror_theme', 'editor_locale', 'editor_options', 'include_font_awesome'));
    }

    public function create()
    {
        $users = Agent::paginate(TicketSetting::grab('paginate_items'));
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
        return view('ticket.admin.administrator.create', compact('users', 'u', 'setting', 'tools', 'master', 'email', 'editor_enabled', 'codemirror_enabled', 'codemirror_theme', 'editor_locale', 'editor_options', 'include_font_awesome'));
    }

    public function store(Request $request)
    {
        $administrators_list = $this->addAdministrators($request->input('administrators'));
        $administrators_names = implode(',', $administrators_list);

        Session::flash('status', "{$administrators_names} are added to administrators");

        return redirect()->action('\App\Http\Controllers\AdministratorsController@index');
    }

    public function update($id, Request $request)
    {
        $this->syncAdministratorCategories($id, $request);

        Session::flash('status', "Joined categories successfully");

        return redirect()->action('\App\Http\Controllers\AdministratorsController@index');
    }

    public function destroy($id)
    {
        $administrator = $this->removeAdministrator($id);

        Session::flash('status', "Removed {$administrator->name} from the administrators team");

        return redirect()->action('\App\Http\Controllers\AdministratorsController@index');
    }

    /**
     * Assign users as administrators.
     *
     * @param $user_ids
     *
     * @return array
     */
    public function addAdministrators($user_ids)
    {
        $users = Agent::find($user_ids);
        foreach ($users as $user) {
            $user->ticketit_admin = true;
            $user->save();
            $users_list[] = $user->name;
        }

        return $users_list;
    }

    /**
     * Remove user from the administrators.
     *
     * @param $id
     *
     * @return mixed
     */
    public function removeAdministrator($id)
    {
        $administrator = Agent::find($id);
        $administrator->ticketit_admin = false;
        $administrator->save();

        // Remove him from tickets categories as well
        if (version_compare(app()->version(), '5.2.0', '>=')) {
            $administrator_cats = $administrator->categories->pluck('id')->toArray();
        } else { // if Laravel 5.1
            $administrator_cats = $administrator->categories->lists('id')->toArray();
        }

        $administrator->categories()->detach($administrator_cats);

        return $administrator;
    }

    /**
     * Sync Administrator categories with the selected categories got from update form.
     *
     * @param $id
     * @param Request $request
     */
    public function syncAdministratorCategories($id, Request $request)
    {
        $form_cats = ($request->input('administrator_cats') == null) ? [] : $request->input('administrator_cats');
        $administrator = Agent::find($id);
        $administrator->categories()->sync($form_cats);
    }
}
