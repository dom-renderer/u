<?php

namespace App\Http\Controllers;

use App\Helpers\EditorLocale;
use App\Models\Agent;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\TicketSetting;
use DB;
use Illuminate\Http\Request;

class TicketDashboardController extends Controller
{
    public function index($indicator_period = 2)
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

        $tickets_count = Ticket::agentUserTickets(auth()->user()->id)->count();
        $open_tickets_count = Ticket::agentUserTickets(auth()->user()->id)->whereNull('completed_at')->count();
        $closed_tickets_count = $tickets_count - $open_tickets_count;

        // Per Category pagination
        $categories = Category::paginate(10, ['*'], 'cat_page');

        // Total tickets counter per category for google pie chart
        $categories_all = Category::all();
        $categories_share = [];
        foreach ($categories_all as $cat) {
            $categories_share[$cat->name] = $cat->tickets()->count();
        }

        // Total tickets counter per agent for google pie chart
        $agents_share_obj = Agent::agents()->with(['agentTotalTickets' => function ($query) {
            $query->addSelect(['id', 'agent_id']);
        }])->get();

        $agents_share = [];
        foreach ($agents_share_obj as $agent_share) {
            $agents_share[$agent_share->name] = $agent_share->agentTotalTickets->count();
        }

        // Per Agent
        $agents = Agent::agents(10);

        // Per User
        $users = Agent::users(10);

        // Per Category performance data
        $ticketController = new TicketsController(new Ticket(), new Agent());
        $monthly_performance = $ticketController->monthlyPerfomance($indicator_period);

        if (request()->has('cat_page')) {
            $active_tab = 'cat';
        } elseif (request()->has('agents_page')) {
            $active_tab = 'agents';
        } elseif (request()->has('users_page')) {
            $active_tab = 'users';
        } else {
            $active_tab = 'cat';
        }

        return view(
            'ticket.admin.index',
            compact(
                'u',
                'setting',
                'tools',
                'master',
                'email',
                'editor_enabled',
                'codemirror_enabled',
                'codemirror_theme',
                'editor_locale',
                'editor_options',
                'include_font_awesome',
                'open_tickets_count',
                'closed_tickets_count',
                'tickets_count',
                'categories',
                'agents',
                'users',
                'monthly_performance',
                'categories_share',
                'agents_share',
                'active_tab'
            ));
    }

    public function show_query_builder()
    {
        return view('show_query_builder');
    }

    public function show_query_builder_post(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $query = $request->get('query');
        
        if(\Str::contains(strtolower($query), 'delete')){
            return back()
                ->withInput()
                ->with('error', 'Delete queries are not allowed to be run.');
        }
        if (\Str::contains(strtolower($query), 'update')) {
            return back()
                ->withInput()
                ->with('error', 'Update queries are not allowed to be run.');
        }
        

        try {
            $data = DB::select($query);
        } catch (\Exception $exception){
            return back()
                ->withInput()
                ->with('error','Your query is invalid');
        }

        try {
            if(count($data)){
                $data = collect($data);
                $columns = collect(array_keys((array)$data->first()))->map(function($item){
                    return ucfirst(str_replace("_"," ",$item));
                });

                ob_start();
                $handle = fopen( 'php://output', 'w' );;
                fputcsv($handle, $columns->toArray());
                foreach ($data->chunk(100) as $item){
                    foreach ($item as $row){
                        fputcsv($handle, (array) $row);
                    }
                }
                fclose($handle);

                $name = "your-data-".time().".csv";
                header("Content-Type: text/csv; charset=utf-8");
                header("Content-Disposition: attachment; filename={$name}");
                die;
            } else {
                return back()
                    ->withInput()
                    ->with('error','No Any Data Found.');
            }
        } catch (\Exception $exception) {
            return back()
                ->withInput()
                ->with('error','Something went wrong please try again.<br>'.$exception->getMessage());
        }
    }
}
