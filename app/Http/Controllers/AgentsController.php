<?php

namespace App\Http\Controllers;

use App\Helpers\EditorLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Agent;
use App\Models\TicketSetting;
use App\Helpers\LaravelVersion;

class AgentsController extends Controller
{
    public function index()
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
        $agents = Agent::agents()->get();

        return view('ticket.admin.agent.index', compact('agents', 'u', 'setting', 'tools', 'master', 'email', 'editor_enabled', 'codemirror_enabled', 'codemirror_theme', 'editor_locale', 'editor_options', 'include_font_awesome'));
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
        return view('ticket.admin.agent.create', compact('users', 'u', 'setting', 'tools', 'master', 'email', 'editor_enabled', 'codemirror_enabled', 'codemirror_theme', 'editor_locale', 'editor_options', 'include_font_awesome'));
    }

    public function store(Request $request)
    {
    	$rules = [
            'agents' => 'required|array|min:1',
        ];

        if(LaravelVersion::min('5.2')){
        	$rules['agents.*'] = 'integer|exists:users,id';
        }

    	$this->validate($request, $rules);

        $agents_list = $this->addAgents($request->input('agents'));
        $agents_names = implode(',', $agents_list);

        Session::flash('status', "Agents {$agents_names} are added to agents");

        return redirect()->action('\App\Http\Controllers\AgentsController@index');
    }

    public function update($id, Request $request)
    {
        $this->syncAgentCategories($id, $request);

        Session::flash('status', "Joined categories successfully");

        return redirect()->action('\App\Http\Controllers\AgentsController@index');
    }

    public function destroy($id)
    {
        $agent = $this->removeAgent($id);

        Session::flash('status', "Removed agent\s {$agent->name} from the agent team");

        return redirect()->action('\App\Http\Controllers\AgentsController@index');
    }

    /**
     * Assign users as agents.
     *
     * @param $user_ids
     *
     * @return array
     */
    public function addAgents($user_ids)
    {
        $users = Agent::find($user_ids);
        foreach ($users as $user) {
            $user->ticketit_agent = true;
            $user->save();
            $users_list[] = $user->name;
        }

        return $users_list;
    }

    /**
     * Remove user from the agents.
     *
     * @param $id
     *
     * @return mixed
     */
    public function removeAgent($id)
    {
        $agent = Agent::find($id);
        $agent->ticketit_agent = false;
        $agent->save();

        // Remove him from tickets categories as well
        if (version_compare(app()->version(), '5.2.0', '>=')) {
            $agent_cats = $agent->categories->pluck('id')->toArray();
        } else { // if Laravel 5.1
            $agent_cats = $agent->categories->lists('id')->toArray();
        }

        $agent->categories()->detach($agent_cats);

        return $agent;
    }

    /**
     * Sync Agent categories with the selected categories got from update form.
     *
     * @param $id
     * @param Request $request
     */
    public function syncAgentCategories($id, Request $request)
    {
        $form_cats = ($request->input('agent_cats') == null) ? [] : $request->input('agent_cats');
        $agent = Agent::find($id);
        $agent->categories()->sync($form_cats);
    }
}
