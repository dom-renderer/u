<?php

namespace App\Http\Controllers;

use App\Helpers\EditorLocale;
use App\Helpers\Helper;
use App\Helpers\LaravelVersion;
use App\Models\Agent;
use App\Models\Category;
use App\Models\ChecklistTask;
use App\Models\Comment;
use App\Models\Department;
use App\Models\Priority;
use App\Models\RedoAction;
use App\Models\Status;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketHistory;
use App\Models\TicketSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Google\Service\Directory\Role;
use Google\Service\ServiceControl\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class TicketsController extends Controller
{
    protected $tickets;
    protected $agent;

    public function __construct(Ticket $tickets, Agent $agent)
    {
        $this->middleware('App\Http\Middleware\ResAccessMiddleware', ['only' => []]);
        $this->middleware('App\Http\Middleware\IsAgentMiddleware', ['only' => ['edit', 'update']]);
        $this->middleware('App\Http\Middleware\IsAdminMiddleware', ['only' => ['destroy']]);

        $this->tickets = $tickets;
        $this->agent = $agent;
    }

    public function data($complete = false)
    {
        if (LaravelVersion::min('5.4')) {
            $datatables = app(\Yajra\DataTables\DataTables::class);
        } else {
            $datatables = app(\Yajra\Datatables\Datatables::class);
        }

        $user = Agent::find(auth()->user()->id);
        
            if ($complete) {

                if (isset(auth()->user()->roles[0]->id) && (in_array(auth()->user()->roles[0]->id, [Helper::$roles['admin'], 13]))) {
                    $collection = Ticket::complete();
                } else {
                    $collection = Ticket::complete()->agentUserTickets($user->id);
                }
            } else {

                if (isset(auth()->user()->roles[0]->id) && (in_array(auth()->user()->roles[0]->id, [Helper::$roles['admin'], 13]))) {
                    $collection = Ticket::active();
                } else {
                    $collection = Ticket::active()->agentUserTickets($user->id);
                }
            }
        
        $collection
            ->join('users', 'users.id', '=', 'ticketit.user_id')
            ->join('departments', 'departments.id', '=', 'ticketit.department_id')
            ->join('ticketit_statuses', 'ticketit_statuses.id', '=', 'ticketit.status_id')
            ->leftJoin('ticketit_priorities', 'ticketit_priorities.id', '=', 'ticketit.priority_id')
            ->leftJoin('checklist_tasks', 'checklist_tasks.id', '=', 'ticketit.task_id')
            ->leftJoin('ticketit_escalation_executions', function($join) {
                $join->on('ticketit_escalation_executions.ticket_id', '=', 'ticketit.id')
                     ->whereRaw('ticketit_escalation_executions.id = (
                         SELECT MAX(tee2.id) 
                         FROM ticketit_escalation_executions tee2 
                         WHERE tee2.ticket_id = ticketit.id
                     )');
            })
            ->leftJoin('ticketit_escalations', 'ticketit_escalations.id', '=', 'ticketit_escalation_executions.ticketit_escalation_id')
            ->select([
                'ticketit.id',
                'ticketit.ticket_number',
                'ticketit.subject AS subject',
                'ticketit_statuses.name AS status',
                'ticketit_statuses.color AS color_status',
                'ticketit_priorities.name AS p_name',
                'departments.name AS d_name',
                'ticketit_priorities.color AS p_color',
                'ticketit.id AS agent',
                'ticketit.updated_at AS updated_at',
                'checklist_tasks.code AS code',                
                'users.name AS owner',
                'ticketit.agent_id',
                'ticketit_escalations.escalation_level',
                'ticketit_escalation_executions.created_at AS escalation_executed_at',
            ]);

        if (!empty(request('task'))) {
            $collection = $collection->where('task_id', request('task'));
        }

        if (!empty(request('status'))) {
            $collection = $collection->where('status_id', request('status'));
        }
        
        if (!empty(request('priority'))) {
            $collection = $collection->where('priority_id', request('priority'));
        }
        
        if (!empty(request('department'))) {
            $collection = $collection->where('department_id', request('department'));
        }
        
        if (!empty(request('createdby'))) {
            $collection = $collection->where('user_id', request('createdby'));
        }

        $collection = $datatables->of($collection);

        $this->renderTicketTable($collection,$complete);

        $collection->editColumn('p_name', function($row){
            return "<span class='badge text-white' style='background-color: {$row->p_color}'>{$row->p_name}</span>";
        });
        $collection->editColumn('updated_at', '{!! \Carbon\Carbon::parse($updated_at)->diffForHumans() !!}');

        $collection->addColumn('escalation_status', function($row) {
            if ($row->escalation_level && $row->escalation_executed_at) {
                $escalatedAt = \Carbon\Carbon::parse($row->escalation_executed_at);
                return '<span class="badge bg-warning text-dark" title="Escalated on ' . $escalatedAt->format('Y-m-d H:i:s') . '">
                    <i class="bi bi-exclamation-triangle-fill"></i> L' . $row->escalation_level . ' Escalated
                </span>';
            }
            return '';
        });

        // method rawColumns was introduced in laravel-datatables 7, which is only compatible with >L5.4
        // in previous laravel-datatables versions escaping columns wasn't defaut
        $collection->rawColumns(['ticket_number', 'subject', 'status','p_name', 'priority', 'category', 'agent','manageticket', 'escalation_status']);

        return $collection->addIndexColumn()->make(true);
    }

    public function renderTicketTable($collection,$complete=null)
    {
        $collection->editColumn('ticket_number', function ($ticket) {
            return (string) link_to_route(
                TicketSetting::grab('main_route').'.show',
                $ticket->ticket_number,
                $ticket->ticket_number
            );
        });
        $collection->editColumn('manageticket', function ($ticket) use($complete) {
            $slug = isset(auth()->user()->roles[0]->id) ? auth()->user()->roles[0]->id : 0;
            $ticketcommentcount = Comment::where(['ticket_id' => $ticket->id])->count();
            if($ticketcommentcount > 0 && !$complete) {
                $button_name = '<i class="bi bi-pen"></i>';
            } else if($slug == Helper::$roles['admin'] || $slug == 13){
                $button_name = '<i class="bi bi-eye-fill"></i>';
            } else {
                if($complete) {
                    $button_name = '<i class="bi bi-eye-fill"></i>';
                } else {
                    $button_name = '<i class="bi bi-pen"></i>';
                }

            }
            return '<a href="'.route('tickets.show',$ticket->ticket_number).'" class="btn btn-success">'.$button_name.'</a>';

        }); //$collection->editColumn('manageticket', '<a href="#" class="btn btn-success">Manage Ticket</a>');

        $collection->editColumn('subject', function ($ticket) {
            return (string) $ticket->subject;
        });

        $collection->editColumn('status', function ($ticket) {
            $color = $ticket->color_status;
            $status = e($ticket->status);

            return "<div style='color: $color'>$status</div>";
        });

        return $collection;
    }

    /**
     * Display a listing of active tickets related to user.
     *
     * @return Response
     */
    public function index()
    {
        Artisan::call("optimize:clear");
        Artisan::call("cache:clear");
        Artisan::call("config:clear");
        $complete = false;

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

        return view('ticket.index', compact('complete', 'u', 'setting', 'tools', 'master', 'email', 'editor_enabled', 'codemirror_enabled', 'codemirror_theme', 'editor_locale', 'editor_options', 'include_font_awesome'));
    }

    /**
     * Display a listing of completed tickets related to user.
     *
     * @return Response
     */
    public function indexComplete()
    {
        Artisan::call("optimize:clear");
        Artisan::call("cache:clear");
        Artisan::call("config:clear");
        $complete = true;

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

        return view('ticket.index', compact('complete', 'u', 'setting', 'tools', 'master', 'email', 'editor_enabled', 'codemirror_enabled', 'codemirror_theme', 'editor_locale', 'editor_options', 'include_font_awesome'));
    }

    /**
     * Returns priorities, categories and statuses lists in this order
     * Decouple it with list().
     *
     * @return array
     */
    protected function PCS()
    {
        // seconds expected for L5.8<=, minutes before that
        $time = LaravelVersion::min('5.8') ? 60*60 : 60;

        $departments = Department::all();

        $priorities = Priority::all();

        $categories = Category::all();

        $statuses = Status::all();

        if (LaravelVersion::min('5.3.0')) {
            return [$priorities->pluck('name', 'id'), $categories->pluck('name', 'id'), $statuses->pluck('name', 'id'), $departments->pluck('name', 'id')];
        } else {
            return [$priorities->lists('name', 'id'), $categories->lists('name', 'id'), $statuses->lists('name', 'id'), $departments->lists('name', 'id')];
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        Artisan::call("optimize:clear");
        Artisan::call("cache:clear");
        Artisan::call("config:clear");
        list($priorities, $categories, $departments) = $this->PCS();

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
        $all_agents = Agent::Agents()->pluck('name', 'id')->toArray();
        $priorities = Priority::all();
        $departments = Department::all();
        /** all needed vars end */

        return view('ticket.tickets.create', compact('u', 'setting', 'tools', 'master', 'email', 'editor_enabled', 'codemirror_enabled', 'codemirror_theme', 'editor_locale', 'editor_options', 'include_font_awesome', 'priorities', 'categories', 'all_agents', 'departments'));
    }

    /**
     * Store a newly created ticket and auto assign an agent for it.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'subject'     => 'required|min:3',
            'content'     => 'required|min:6',
            // 'priority_id' => 'required|exists:mysql2.ticketit_priorities,id',
            // 'category_id' => 'required|exists:mysql2.ticketit_categories,id',
            'agent_id' => 'array',
            'agent_id.*' => 'required|exists:users,id',
        ]);

        $first_admin = Agent::admins()->first();
        $deptUser = \App\Models\DepartmentUser::with(['user'])->where('department_id', $request->department_id)->get();

        if ($deptUser->isEmpty()) {
            session()->flash('status', "Department has no users!");
            return redirect()->back();
        }

        if(empty($first_admin)) {
            return redirect()->back()->with('warning', "Any admin is not exist.")->withInput();
        }
        $ticket = new Ticket();

        $tick_it = Ticket::latest()->first();
        if(empty($tick_it)) {
            $ticket->ticket_number = "TW-1001";
        } else {
            if(empty($tick_it->ticket_number)) {
                $ticket->ticket_number = "TW-1001";
            } else {
                $tix = explode('-', $tick_it->ticket_number);
                $number = $tix[1];
                $main_number = (int) substr($number, 0, -3);
                $plus_number = (int) substr($number, -3);
                $catch_number = sprintf('%03d', $plus_number + 1);
                if($plus_number >= 999) {
                    $catch_number = "001";
                    $main_number = $main_number + 1;
                }
                $str = "TW-".$main_number.$catch_number;
                $ticket->ticket_number = $str;
            }
        }

        $ticket->subject = $request->subject;
        $ticket->department_id = $request->department_id;
        $ticket->setPurifiedContent($request->get('content'));

         $ticket->priority_id = $request->priority_id;
        // $ticket->category_id = $request->category_id;

        $ticket->status_id = TicketSetting::grab('default_status_id');
        $ticket->user_id = auth()->user()->id;
        // $ticket->autoSelectAgent();
        $ticket->save();

        $allSavedFiles = [];

        if ($request->hasFile('attachments')) {
            if (!file_exists(storage_path('app/public/ticket-uploads'))) {
                mkdir(storage_path('app/public/ticket-uploads'), 0777, true);
            }

            foreach ($request->file('attachments') as $file) {
                $fileName = 'TU-' . date('YmdHis') . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(storage_path('app/public/ticket-uploads'), $fileName);

                if (is_file(storage_path("app/public/ticket-uploads/{$fileName}"))) {
                    $allSavedFiles[] = TicketAttachment::create([
                        'ticket_id' => $ticket->id,
                        'file' => $fileName
                    ])->id;
                }
            }
        }

        TicketHistory::updateOrCreate([
            "ticket_id" => $ticket->id,
            "description" => "Ticket has been created by ".auth()->user()->name,
            "user_id" => auth()->user()->id
        ], [
            "ticket_id" => $ticket->id,
            "description" => "Ticket has been created by ".auth()->user()->name,
            "user_id" => auth()->user()->id,
            "created_at" => \Carbon\Carbon::now(),
            "updated_at" => \Carbon\Carbon::now()
        ]);

        foreach ($deptUser as $agentId) {
            \App\Models\TicketMember::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agentId->user_id
            ]);

            TicketHistory::updateOrCreate([
                "ticket_id" => $ticket->id,
                "description" => "Ticket has been assigned to ".$agentId->user->name ?? '',
                "user_id" => auth()->user()->id
            ], [
                "ticket_id" => $ticket->id,
                "description" => "Ticket has been assigned to ".$agentId->user->name ?? '',
                "user_id" => auth()->user()->id,
                "created_at" => \Carbon\Carbon::now(),
                "updated_at" => \Carbon\Carbon::now()
            ]);
        }

        if (!empty($allSavedFiles)) {
            TicketHistory::create([
                "ticket_id" => $ticket->id,
                "description" => "Attachments has been added with ticket generation",
                "type" => 1,
                "model" => TicketAttachment::class,
                "estimate_time" => date('Y-m-d H:i:s', strtotime('+10 days')),
                "model_id" => $allSavedFiles[0],
                "user_id" => auth()->user()->id,
                "created_at" => \Carbon\Carbon::now(),
                "updated_at" => \Carbon\Carbon::now()
            ]);
        }

        // Mail send for all user
        Helper::ticket_mail_send($ticket->id,'Add');

        session()->flash('status', "The ticket has been created!");
        return redirect()->route(TicketSetting::grab('main_route') . '.index');
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
        /** all needed vars start */
        Artisan::call("optimize:clear");
        Artisan::call("cache:clear");
        Artisan::call("config:clear");
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

        $tic_it = Ticket::where("ticket_number", $id)->first();
        if(empty($tic_it)) {
            return redirect()->back()->with('warning', "This ticket is not exist.")->withInput();
        }
        $ids_inn = [$tic_it->user_id, $tic_it->agent_id];
        if(!in_array(auth()->user()->id, $ids_inn) && !isset(auth()->user()->roles[0]->id) && !in_array(auth()->user()->roles[0]->id, [Helper::$roles['admin'], 13])) {
            abort(403);
        }

        if (empty($tic_it)) {
            return redirect()->route(TicketSetting::grab('main_route').'.index')->with('warning', "Ticket is not found.");
        }
        $id = $tic_it->id;
        $ticket = Ticket::where('id',$id)->first();

        list($priority_lists, $category_lists, $status_lists) = $this->PCS();

        $close_perm = $this->permToClose($id);
        $reopen_perm = $this->permToReopen($id);

        // $cat_agents = Category::find($ticket->category_id)->agents()->agentsLists();
        // if (is_array($cat_agents)) {
        //     $agent_lists = ['auto' => 'Auto Select'] + $cat_agents;
        // } else {
            $agent_lists = ['auto' => 'Auto Select'];
        // }
        $histories = TicketHistory::where("ticket_id", $id)->orderBy('id', 'DESC')->get()->toArray();
        $comments = $ticket->comments()->orderBy('id','desc')->paginate(TicketSetting::grab('paginate_items'));
        return view('ticket.tickets.show', compact('u', 'setting', 'tools', 'master', 'email', 'editor_enabled', 'codemirror_enabled', 'codemirror_theme', 'editor_locale', 'editor_options', 'include_font_awesome', 'ticket', 'status_lists', 'priority_lists', 'category_lists', 'agent_lists', 'comments', 'close_perm', 'reopen_perm', 'histories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            // 'subject'     => 'required|min:3',
            // 'content'     => 'required|min:6',
            // 'priority_id' => 'required|exists:mysql2.ticketit_priorities,id',
            // 'category_id' => 'required|exists:mysql2.ticketit_categories,id',
            'status_id'   => 'required|exists:ticketit_statuses,id'
        ]);

        $line = "Ticket has been updated by ".auth()->user()->name;

        $first_admin = Agent::admins()->first();
        if(empty($first_admin)) {
            return redirect()->back()->with('warning', "Any admin is not exist.")->withInput();
        }

        $ticket = $this->tickets->findOrFail($id);

        // $ticket->subject = $request->subject;

        // $ticket->setPurifiedContent($request->get('content'));
        if($request->status_id && is_numeric($request->status_id) && $request->status_id > 0){
            if ($request->status_id != $ticket->status_id) {
                $line = "The status has been changed to " . (Status::where('id', $request->status_id)->first()->name ?? '-') . " by ".auth()->user()->name;
            }

            $ticket->status_id = $request->status_id;
        }
        // $ticket->category_id = $request->category_id;
        if($request->priority_id && is_numeric($request->priority_id) && $request->priority_id > 0){
            if ($request->priority_id != $ticket->priority_id) {
                $line = "The priority has been changed to " . (Priority::where('id', $request->priority_id)->first()->name ?? '-') . " by ".auth()->user()->name;
            }

            $ticket->priority_id = $request->priority_id;
        }

        $ticket->save();

        TicketHistory::create([
            "ticket_id" => $ticket->id,
            "description" => $line,
            "user_id" => auth()->user()->id,
            "created_at" => \Carbon\Carbon::now(),
            "updated_at" => \Carbon\Carbon::now()
        ]);

        session()->flash('status', "The ticket has been modified!");
        return redirect()->route(TicketSetting::grab('main_route').'.show', $ticket->ticket_number);
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
        $ticket = $this->tickets->findOrFail($id);
        $subject = $ticket->subject;
        $ticket->delete();

        session()->flash('status', "The ticket {$subject} has been deleted!");

        return redirect()->route(TicketSetting::grab('main_route').'.index');
    }

    /**
     * Mark ticket as complete.
     *
     * @param int $id
     *
     * @return Response
     */
    public function complete($id)
    {
        // if ($this->permToClose($id) == 'yes') {
            $ticket = $this->tickets->findOrFail($id);
            $ticket->completed_at = Carbon::now();
            $subject = $ticket->subject;
            $ticket->save();

            TicketHistory::create([
                "ticket_id" => $ticket->id,
                "description" => "Ticket has been moved to complete by ".auth()->user()->name,
                "user_id" => auth()->user()->id,
                "created_at" => \Carbon\Carbon::now(),
                "updated_at" => \Carbon\Carbon::now()
            ]);

            // Mail send for all user
            Helper::ticket_mail_send($id,'Complete');

            session()->flash('status', "The ticket {$subject} has been completed!");
            return redirect()->route(TicketSetting::grab('main_route').'.index');
        // }

        return redirect()->route(TicketSetting::grab('main_route').'.index')
            ->with('warning', "You are not permitted to do this action!");
    }

    /**
     * Reopen ticket from complete status.
     *
     * @param int $id
     *
     * @return Response
     */
    public function reopen($id)
    {
        if ($this->permToReopen($id) == 'yes') {

            Helper::ticket_mail_send($id,'Reopened');

            $ticket = $this->tickets->findOrFail($id);
            $ticket->completed_at = null;
            $ticket->status_id = 1;
            $subject = $ticket->subject;
            $ticket->save();

            TicketHistory::create([
                "ticket_id" => $ticket->id,
                "description" => "Ticket has been reopened by ".auth()->user()->name,
                "user_id" => auth()->user()->id,
                "created_at" => \Carbon\Carbon::now(),
                "updated_at" => \Carbon\Carbon::now()
            ]);

            session()->flash('status', "The ticket {$subject} has been reopened!");

            return redirect()->route(TicketSetting::grab('main_route').'.index');
        }

        return redirect()->route(TicketSetting::grab('main_route').'.index')
            ->with('warning', "You are not permitted to do this action!");
    }

    public function agentSelectList($category_id, $ticket_id)
    {
        $cat_agents = Category::find($category_id)->agents()->agentsLists();
        if (is_array($cat_agents)) {
            $agents = ['auto' => 'Auto Select'] + $cat_agents;
        } else {
            $agents = ['auto' => 'Auto Select'];
        }

        $selected_Agent = $this->tickets->find($ticket_id)->agent->id;
        $select = '<select class="form-control" id="agent_id" name="agent_id">';
        foreach ($agents as $id => $name) {
            $selected = ($id == $selected_Agent) ? 'selected' : '';
            $select .= '<option value="'.$id.'" '.$selected.'>'.$name.'</option>';
        }
        $select .= '</select>';

        return $select;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function permToClose($id)
    {
        $close_ticket_perm = TicketSetting::grab('close_ticket_perm');

        if ($this->agent->isAdmin() && $close_ticket_perm['admin'] == 'yes') {
            return 'yes';
        }
        if ($this->agent->isAgent() && $close_ticket_perm['agent'] == 'yes') {
            return 'yes';
        }
        if ($this->agent->isTicketOwner($id) && $close_ticket_perm['owner'] == 'yes') {
            return 'yes';
        }

        return 'no';
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function permToReopen($id)
    {
        $ticket = $this->tickets->findOrFail($id);
        $reopen_ticket_perm = TicketSetting::grab('reopen_ticket_perm');
        if ($this->agent->isAdmin() && $reopen_ticket_perm['admin'] == 'yes') {
            return 'yes';
        } elseif ($this->agent->isAgent() && $reopen_ticket_perm['agent'] == 'yes') {
            return 'yes';
        } elseif ($this->agent->isTicketOwner($id) && $reopen_ticket_perm['owner'] == 'yes') {
            return 'yes';
        }

        return 'no';
    }

    /**
     * Calculate average closing period of days per category for number of months.
     *
     * @param int $period
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function monthlyPerfomance($period = 2)
    {
        $categories = Category::all();
        foreach ($categories as $cat) {
            $records['categories'][] = $cat->name;
        }

        for ($m = $period; $m >= 0; $m--) {
            $from = Carbon::now();
            $from->day = 1;
            $from->subMonth($m);
            $to = Carbon::now();
            $to->day = 1;
            $to->subMonth($m);
            $to->endOfMonth();
            $records['interval'][$from->format('F Y')] = [];
            foreach ($categories as $cat) {
                $records['interval'][$from->format('F Y')][] = round($this->intervalPerformance($from, $to, $cat->id), 1);
            }
        }

        return $records;
    }

    /**
     * Calculate the date length it took to solve a ticket.
     *
     * @param Ticket $ticket
     *
     * @return int|false
     */
    public function ticketPerformance($ticket)
    {
        if ($ticket->completed_at == null) {
            return false;
        }

        $created = new Carbon($ticket->created_at);
        $completed = new Carbon($ticket->completed_at);
        $length = $created->diff($completed)->days;

        return $length;
    }

    /**
     * Calculate the average date length it took to solve tickets within date period.
     *
     * @param $from
     * @param $to
     *
     * @return int
     */
    public function intervalPerformance($from, $to, $cat_id = false)
    {
        if ($cat_id) {
            $tickets = Ticket::agentUserTickets(auth()->user()->id)->where('category_id', $cat_id)->whereBetween('completed_at', [$from, $to])->get();
        } else {
            $tickets = Ticket::agentUserTickets(auth()->user()->id)->whereBetween('completed_at', [$from, $to])->get();
        }

        if (empty($tickets->first())) {
            return false;
        }

        $performance_count = 0;
        $counter = 0;
        foreach ($tickets as $ticket) {
            $performance_count += $this->ticketPerformance($ticket);
            $counter++;
        }
        $performance_average = $performance_count / $counter;

        return $performance_average;
    }

    public static function makeTicketAdmin($flg = 0)
    {
        User::whereHas('roles', function($q){ $q->where('name', 'Super Admin'); })->update(["ticketit_admin" => 1]);
        if ($flg != 1) {
            return redirect()->route(TicketSetting::grab('main_route').'.index')
            ->with('status', "Admin has been set successfully.");
        }
    }

    public static function makeTicketAgent($flg = 0)
    {
        $users = User::whereHas('roles', function($q){ $q->where('name', '!=', 'Super Admin'); })->get();
        foreach($users as $user) {
            if($user->hasPermissionTo("Ticket System") === true) {
                User::find($user->id)->update(["ticketit_agent" => 1]);
            } else {
                User::find($user->id)->update(["ticketit_agent" => 0]);
            }
        }
        if ($flg != 1) {
            return redirect()->route(TicketSetting::grab('main_route').'.index')
            ->with('status', "Admin has been set successfully.");
        }
    }

    public function get_admin_agent()
    {
        if(request()->get_agent_admin == 'agent') {
            return response()->json(["status" => true, "data" => Agent::Agents()->pluck('name', 'id')->toArray()]);
        } else {
            return response()->json(["status" => true, "data" => Agent::Admins()->pluck('name', 'id')->toArray()]);
        }
    }

    public function add_estimatetime(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        $finalDate = date('Y-m-d', strtotime($request->estimate_time));
        $ticket = $this->tickets->findOrFail($request->ticket_id);
        if($ticket->estimate_time == null){
            $check = "Estimate date added";
        } else {
            $check = "Estimate date changed";
        }
        $ticket->estimate_time = $finalDate;
        $ticket->save();

        TicketHistory::updateOrCreate([
            "ticket_id" => $ticket->id,
            "description" => "Estimation date has been added on " . $finalDate,
            "user_id" => auth()->user()->id
        ], [
            "ticket_id" => $ticket->id,
            "description" => "Estimation date has been added on " . $finalDate,
            "user_id" => auth()->user()->id,
            "created_at" => \Carbon\Carbon::now(),
            "updated_at" => \Carbon\Carbon::now()
        ]);

        // $emails = [$ticket->user->email];
        // $html = '<p>'.auth()->user()->name.' '.$check.' estimate date '.date('d-m-Y', strtotime($request->estimate_time)).' in ticket '.$ticket->ticket_number.' </p>';
        // Mail::send([], [], function ($message) use ($emails, $html, $ticket, $check) {
        //     $message->to($emails)
        //         ->subject('Estimate date '.$check.' - '.$ticket->ticket_number)
        //         ->setBody($html, 'text/html');
        // });

        // send mail for added and chnage estimate date
        Helper::ticket_mail_send($request->ticket_id,$check);

        return \Response::json(200);
    }

    public function getListing(Request $request) {
        $mainStatus = $request->mainstatus;

        $tickets = \App\Models\NewTicket::with(['particular', 'issue', 'task' => function ($builder) {
            $builder->withTrashed();
        }, 'task.parent' => function ($builder) {
            $builder->withTrashed();
        },'task.parent.parent' => function ($builder) {
            $builder->withTrashed();
        }, 'task.parent.actstore' => function ($builder) {
            $builder->withTrashed();
        }, 'task.parent.user' => function ($builder) {
            $builder->withTrashed();
        }])
        ->whereNotNull('task_id')
        ->when(in_array($mainStatus, ['pending', 'accepted', 'in_progress', 'closed']), function ($builder) use ($mainStatus) {
            $builder->where('status', $mainStatus);
        }, function ($builder) {
            $builder->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime('-2 days')))
            ->where('status', '!=', 'completed');
        })

        ->when($request->dom != 'all', function ($builder) {
            $builder->whereHas('task.parent.parent', function ($innerBuilder) {
                return $innerBuilder->where('checker_user_id', request('dom'));
            });
        })
        ->when($request->store != 'all', function ($builder) {
            $builder->whereHas('task.parent', function ($innerBuilder) {
                return $innerBuilder->where('store_id', request('store'));
            });
        })
        ->when($request->city != 'all' && !empty($request->city), function ($builder) {
            $builder->whereHas('task.parent.actstore.thecity', function ($innerBuilder) {
                return $innerBuilder->where('city_id', request('city'));
            });
        })
        ->when($request->dept != 'all' && !empty($request->dept), function ($builder) {
            $builder->whereHas('department', function ($innerBuilder) {
                return $innerBuilder->where('id', request('dept'));
            });
        })
        ->when($request->state != 'all' && !empty($request->state), function ($builder) {
            $builder->whereHas('task.parent.actstore.thecity', function ($innerBuilder) {
                return $innerBuilder->where('city_state', request('state'));
            });
        })
        ->when(in_array($mainStatus, ['pending', 'accepted', 'in_progress', 'closed']), function ($builder) {
            $builder->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime(request('startd'))))
            ->where(\DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime(request('endd'))));
        })
        ->latest();

        return datatables()
        ->eloquent($tickets)
        ->editColumn('ticket_number', function ($row) {
            return '<a href="'.route('ticket-management.show',encrypt($row->id)).'">'.$row->ticket_number.'</a>';
        })
        ->addColumn('department_name', function ($row) {
            return $row->department->name ?? '';
        })
        ->addColumn('priority_name', function ($row) {
            return $row->particular->name ?? '';
        })
        ->addColumn('issue_name', function ($row) {
            return $row->issue->name ?? '';
        })
        ->addColumn('location_name', function ($row) {
            return  isset($row->task->parent->actstore->id) ? ($row->task->parent->actstore->code . ' - ' . $row->task->parent->actstore->name) : '';
        })
        ->addColumn('city_name', function ($row) {
            return $row->task->parent->actstore->thecity->city_name ?? '';
        })
        ->addColumn('dom_name', function ($row) {
            return isset($row->creator->id) ? ($row->creator->employee_id . ' - ' . $row->creator->name . ' ' . $row->creator->middle_name . ' ' . $row->creator->last_name) : '-';
        })
        ->addColumn('status_name', function ($row) {
            return $row->status;
        })
        ->addColumn('opened', function ($row) {
            return Carbon::parse($row->created_at)->diffInDays(now());
        })
        ->addColumn('date_opened', function ($row) {
            return Carbon::parse($row->created_at)->format('d-m-Y H:i');
        })
        ->rawColumns(['ticket_number'])
        ->toJson();

    }
}
