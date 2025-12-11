<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Encryption\DecryptException;
use App\Models\NewTicketEscalationExecution;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Models\NewTicketHistory;
use App\Models\TicketEscalation;
use Illuminate\Validation\Rule;
use App\Models\NewTicketOwner;
use Illuminate\Http\Request;
use App\Models\UserIssue;
use App\Models\NewTicket;
use App\Models\User;
use App\Exports\TicketsExport;
use Maatwebsite\Excel\Facades\Excel;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ticket-management.index')->only(['index']);
        $this->middleware('permission:ticket-management.create')->only(['create', 'store']);
        $this->middleware('permission:ticket-management.show')->only(['show']);
        $this->middleware('permission:ticket-management.edit')->only(['edit', 'update']);
        $this->middleware('permission:ticket-management.accept')->only(['accept']);
        $this->middleware('permission:ticket-escalations.index')->only(['escalations']);
        $this->middleware('permission:ticket-escalations.create')->only(['createEscalation', 'storeEscalation']);
        $this->middleware('permission:ticket-escalations.edit')->only(['editEscalation', 'updateEscalation']);
        $this->middleware('permission:ticket-escalations.destroy')->only(['destroyEscalation']);
        $this->middleware('permission:ticket-escalations.view')->only(['showEscalation']);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $tab = $request->get('tab', 'pending');

            $cloneTickets = NewTicket::query()
                ->with(['department', 'particular', 'issue', 'creator', 'store'])
                ->when($request->filled('department_id'), function ($builder) {
                    $builder->where('department_id', request('department_id'));
                })
                ->when($request->filled('particular_id'), function ($builder) {
                    $builder->where('particular_id', request('particular_id'));
                })
                ->when($request->filled('issue_id'), function ($builder) {
                    $builder->where('issue_id', request('issue_id'));
                })
                ->when($request->filled('location'), function ($builder) {
                    $builder->where('store_id', request('location'));
                })
                ->when($request->filled('assigned'), function ($builder) {
                    $builder->whereHas('owners', function ($innerBuilder) {
                        $innerBuilder->where('owner_id', request('assigned'));
                    });
                })
                ->when($request->filled('created_from'), function ($builder) {
                    $builder->whereDate('created_at', '>=', date('Y-m-d', strtotime(request('created_from'))));
                })
                ->when($request->filled('created_to'), function ($builder) {
                    $builder->whereDate('created_at', '<=', date('Y-m-d', strtotime(request('created_to'))));
                })
                ->when($request->filled('created_by'), function ($builder) {
                    $builder->where('created_by', request('created_by'));
                });

            $query = NewTicket::query()
                ->with(['department', 'particular', 'issue', 'creator', 'store'])
                ->withTabStatus($tab)
                ->when($request->filled('department_id'), function ($builder) {
                    $builder->where('department_id', request('department_id'));
                })
                ->when($request->filled('particular_id'), function ($builder) {
                    $builder->where('particular_id', request('particular_id'));
                })
                ->when($request->filled('issue_id'), function ($builder) {
                    $builder->where('issue_id', request('issue_id'));
                })
                ->when($request->filled('location'), function ($builder) {
                    $builder->where('store_id', request('location'));
                })
                ->when($request->filled('assigned'), function ($builder) {
                    $builder->whereHas('owners', function ($innerBuilder) {
                        $innerBuilder->where('owner_id', request('assigned'));
                    });
                })
                ->when($request->filled('created_from'), function ($builder) {
                    $builder->whereDate('created_at', '>=', date('Y-m-d', strtotime(request('created_from'))));
                })
                ->when($request->filled('created_to'), function ($builder) {
                    $builder->whereDate('created_at', '<=', date('Y-m-d', strtotime(request('created_to'))));
                })
                ->when($request->filled('created_by'), function ($builder) {
                    $builder->where('created_by', request('created_by'));
                });

            return datatables()
                ->eloquent($query)
                ->addColumn('department', function (NewTicket $ticket) {
                    return optional($ticket->department)->name ?: '-';
                })
                ->addColumn('particular', function (NewTicket $ticket) {
                    return optional($ticket->particular)->name ?: '-';
                })
                ->addColumn('issue', function (NewTicket $ticket) {
                    return optional($ticket->issue)->name ?: '-';
                })
                ->addColumn('operator', function (NewTicket $ticket) {
                    return ($ticket->store->code ?? '') . ' - ' . ($ticket->store->name ?? '');
                })
                ->addColumn('priority', function (NewTicket $ticket) {
                    return $ticket->priorityLabel;
                })
                ->addColumn('status', function (NewTicket $ticket) {
                    return $ticket->statusLabel;
                })
                ->addColumn('created_by', function (NewTicket $ticket) {
                    if ($ticket->creator) {
                        return trim($ticket->creator->name . ' ' . $ticket->creator->middle_name . ' ' . $ticket->creator->last_name);
                    }

                    return '-';
                })
                ->addColumn('created_at', function (NewTicket $ticket) {
                    return $ticket->created_at ? $ticket->created_at->format('d M Y H:i') : '-';
                })
                ->addColumn('action', function (NewTicket $ticket) {
                    $dropdownItems = '';
                    $encryptedId = encrypt($ticket->id);

                if ($ticket->status === NewTicket::STATUS_PENDING && Gate::allows('ticket-management.accept')) {
                    $dropdownItems .= '<li><form method="POST" action="' . route('ticket-management.accept', $encryptedId) . '" class="acceptTicketForm">
                        ' . csrf_field() . '
                        <button type="submit" class="dropdown-item text-success">Accept & In-Progress</button>
                    </form></li>';

                    $dropdownItems .= '<li><a class="dropdown-item text-info" data-tid="' . $encryptedId . '" href="' . route('ticket-management.assign-users', $encryptedId) . '">Accept & Assign</a></li>';
                }

                if (in_array($ticket->status, [NewTicket::STATUS_IN_PROGRESS, NewTicket::STATUS_ACCEPTED]) && Gate::allows('ticket-management.edit')) {
                    $dropdownItems .= '<li><a class="dropdown-item" href="' . route('ticket-management.edit', $encryptedId) . '">Edit</a></li>';
                }

                if (Gate::allows('ticket-management.show')) {
                    $dropdownItems .= '<li><a class="dropdown-item" href="' . route('ticket-management.show', $encryptedId) . '">View</a></li>';
                }

                if (in_array($ticket->status, [NewTicket::STATUS_CLOSED]) && Gate::allows('ticket-management.reopen')) {
                    $dropdownItems .= '<li><a class="dropdown-item" href="' . route('ticket-management.reopen', $encryptedId) . '?ro=1">Re-Open</a></li>';
                }
            
                if ($dropdownItems) {
                    $action = '
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Actions
                        </button>
                        <ul class="dropdown-menu">
                            '.$dropdownItems.'
                        </ul>
                    </div>';
                } else {
                    $action = '-';
                }
            
                return $action;
                })
                ->rawColumns(['action'])
                ->with([
                    'pending_count' => $cloneTickets->clone()->withTabStatus('pending')->count(),
                    'accepted_count' => $cloneTickets->clone()->withTabStatus('accepted')->count(),
                    'in_progress_count' => $cloneTickets->clone()->withTabStatus('in_progress')->count(),
                    'closed_count' => $cloneTickets->clone()->withTabStatus('closed')->count(),
                ])
                ->toJson();
        }

        $page_title = 'Tickets';
        return view('tickets.index', compact('page_title'));
    }

    public function create()
    {
        $page_title = 'Create Ticket';
        $priorities = $this->priorities();

        return view('tickets.create', compact('page_title', 'priorities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'particular_id' => [
                'required',
                Rule::exists('particulars', 'id')->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department_id);
                }),
            ],
            'issue_id' => [
                'required',
                Rule::exists('issues', 'id')->where(function ($query) use ($request) {
                    return $query->where('particular_id', $request->particular_id);
                }),
            ],
            'shop_operator_id' => ['required', 'exists:stores,id'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', Rule::in(array_keys($this->priorities()))],
            'attachments.*' => ['nullable', 'image', 'max:3072'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $attachments = $this->storeAttachments($request->file('attachments', []));

            $ticket = NewTicket::create([
                'department_id' => $validated['department_id'],
                'particular_id' => $validated['particular_id'],
                'issue_id' => $validated['issue_id'],
                'store_id' => $validated['shop_operator_id'],
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'attachments' => $attachments,
                'priority' => $validated['priority'],
                'created_by' => auth()->id(),
            ]);

            foreach (UserIssue::where('issue_id', $validated['issue_id'])->get() as $issue) {
                NewTicketOwner::create([
                    'new_ticket_id' => $ticket->id,
                    'owner_id' => $issue->user_id,
                    'assigned_by' => auth()->id(),
                    'is_primary' => true,
                ]);
            }

            if (!empty($request->extra_users) && is_array($request->extra_users)) {
                foreach ($request->extra_users as $eU) {
                    NewTicketOwner::create([
                        'new_ticket_id' => $ticket->id,
                        'owner_id' => $eU,
                        'is_primary' => false,
                        'assigned_by' => auth()->id(),
                    ]);
                }
            }

            NewTicketHistory::create([
                'new_ticket_id' => $ticket->id,
                'description' => 'Ticket created',
                'data' => [
                    'type' => 'created',
                    'user_id' => auth()->id(),
                    'priority' => $validated['priority'],
                    'description' => $validated['description'],
                    'owners_included' => $request->extra_users ?? []
                ],
                'attachments' => $attachments,
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()->route('ticket-management.index')->with('success', 'Ticket created successfully.');
    }

    public function show(string $id)
    {
        $ticket = $this->resolveTicket($id);
        $ticket->load(['department', 'particular', 'issue', 'store', 'creator']);
        $histories = $ticket->histories()->with('author')->orderBy('created_at')->get();

        $page_title = 'Ticket Details';

        return view('tickets.show', compact('ticket', 'histories', 'page_title'));
    }

    public function edit(string $id)
    {
        $ticket = $this->resolveTicket($id);
        $ticket->load(['department', 'particular', 'issue', 'store', 'creator']);
        $histories = $ticket->histories()->with('author')->orderBy('created_at')->get();
        $newTicketOwners = NewTicketOwner::with('user')->where('new_ticket_id', $ticket->id)->where('is_primary', false)->get();

        abort_if(request('ro') != 1 && !in_array($ticket->status, [NewTicket::STATUS_IN_PROGRESS, NewTicket::STATUS_ACCEPTED]), 404);

        if (request()->has('ro') && request('ro') == 1) {
            $isReopen = true;
            $page_title = 'Reopen Ticket';
        } else {
            $isReopen = false;
            $page_title = 'Update Ticket';
        }

        return view('tickets.edit', compact('ticket', 'histories', 'page_title', 'newTicketOwners', 'isReopen'));
    }

    public function update(Request $request, string $id)
    {
        $ticket = $this->resolveTicket($id);
        $oldStatus = $ticket->status;

        abort_if($request->is_reopen != 1 && !in_array($ticket->status, [NewTicket::STATUS_IN_PROGRESS, NewTicket::STATUS_ACCEPTED]), 404);

        $validated = $request->validate([
            'reply' => ['required', 'string'],
            'status' => ['required', Rule::in([NewTicket::STATUS_IN_PROGRESS, NewTicket::STATUS_ACCEPTED, NewTicket::STATUS_CLOSED, NewTicket::STATUS_PENDING])],
            'attachments.*' => ['nullable', 'image', 'max:3072'],
        ]);

        DB::transaction(function () use ($ticket, $request, $validated, $oldStatus) {
            $attachments = $this->storeAttachments($request->file('attachments', []));

            $ticket->update([
                'status' => $validated['status'],
            ]);

            $toKeepEU = [];

            if (!empty($request->extra_users)) {
                foreach ($request->extra_users as $eU) {
                    $toKeepEU[] = NewTicketOwner::updateOrCreate([
                        'new_ticket_id' => $ticket->id,
                        'owner_id' => $eU,
                        'is_primary' => false
                    ], [
                        'assigned_by' => auth()->id(),
                    ])->id;
                }
            }

            if (!empty($toKeepEU)) {
                NewTicketOwner::where('is_primary', false)->where('new_ticket_id', $ticket->id)->whereNotIn('id', $toKeepEU)->delete();
            } else {
                NewTicketOwner::where('is_primary', false)->where('new_ticket_id', $ticket->id)->delete();
            }

            if ($request->has('is_reopen') && $request->is_reopen == 1) {
                if ($validated['status'] == NewTicket::STATUS_ACCEPTED || $validated['status'] == NewTicket::STATUS_IN_PROGRESS) {
                    NewTicketHistory::create([
                        'new_ticket_id' => $ticket->id,
                        'description' => 'Ticket re-opened and set to ' . $validated['status'],
                        'data' => [
                            'type' => 'reply',
                            'user_id' => auth()->id(),
                            'status' => $validated['status'],
                            'reopened' => true,
                            'description' => $validated['reply'],
                            'owners_included' => $request->extra_users ?? []
                        ],
                        'attachments' => $attachments,
                        'created_by' => auth()->id(),
                    ]);
                } else if ($validated['status'] == NewTicket::STATUS_CLOSED) {
                    NewTicketHistory::create([
                        'new_ticket_id' => $ticket->id,
                        'description' => 'Comment added to closed ticket',
                        'data' => [
                            'type' => 'reply',
                            'user_id' => auth()->id(),
                            'status' => $validated['status'],
                            'description' => $validated['reply'],
                            'owners_included' => $request->extra_users ?? []
                        ],
                        'attachments' => $attachments,
                        'created_by' => auth()->id(),
                    ]);
                } else {
                    NewTicketHistory::create([
                        'new_ticket_id' => $ticket->id,
                        'description' => 'Ticket re-opened',
                        'data' => [
                            'type' => 'reply',
                            'user_id' => auth()->id(),
                            'status' => $validated['status'],
                            'reopened' => true,
                            'description' => $validated['reply'],
                            'owners_included' => $request->extra_users ?? []
                        ],
                        'attachments' => $attachments,
                        'created_by' => auth()->id(),
                    ]);                    
                }
            } else {
                if ($validated['status'] == NewTicket::STATUS_IN_PROGRESS && $validated['status'] != $oldStatus) {
                    NewTicketHistory::create([
                        'new_ticket_id' => $ticket->id,
                        'description' => 'Ticket status changed to in-progress',
                        'data' => [
                            'type' => 'reply',
                            'user_id' => auth()->id(),
                            'status' => $validated['status'],
                            'description' => $validated['reply'],
                            'owners_included' => $request->extra_users ?? []
                        ],
                        'attachments' => $attachments,
                        'created_by' => auth()->id(),
                    ]);
                } else if ($validated['status'] == NewTicket::STATUS_CLOSED && $validated['status'] != $oldStatus) {
                    NewTicketHistory::create([
                        'new_ticket_id' => $ticket->id,
                        'description' => 'Ticket status changed to closed',
                        'data' => [
                            'type' => 'closed',
                            'user_id' => auth()->id(),
                            'status' => $validated['status'],
                            'description' => $validated['reply'],
                            'owners_included' => $request->extra_users ?? []
                        ],
                        'attachments' => $attachments,
                        'created_by' => auth()->id(),
                    ]);
                } else if ($validated['status'] == NewTicket::STATUS_IN_PROGRESS) {
                    NewTicketHistory::create([
                        'new_ticket_id' => $ticket->id,
                        'description' => $validated['reply'],
                        'data' => [
                            'type' => 'reply',
                            'user_id' => auth()->id(),
                            'status' => $validated['status'],
                            'description' => $validated['reply'],
                            'owners_included' => $request->extra_users ?? []
                        ],
                        'attachments' => $attachments,
                        'created_by' => auth()->id(),
                    ]);
                } else {
                    NewTicketHistory::create([
                        'new_ticket_id' => $ticket->id,
                        'description' => NewTicket::STATUS_CLOSED == $validated['status'] ? 'Ticket closed' : $validated['reply'],
                        'data' => [
                            'type' => 'reply',
                            'user_id' => auth()->id(),
                            'status' => $validated['status'],
                            'description' => $validated['reply'],
                            'owners_included' => $request->extra_users ?? []
                        ],
                        'attachments' => $attachments,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            if ($validated['status'] == NewTicket::STATUS_IN_PROGRESS && $validated['status'] != $oldStatus) {
                NewTicketEscalationExecution::where('ticket_id', $ticket->id)->delete();

                $ticket->update([
                    'in_progress_at' => now(),
                ]);
            }
        });

        return redirect()->route('ticket-management.index')->with('success', 'Ticket updated successfully.');
    }

    public function accept(string $id)
    {
        $ticket = $this->resolveTicket($id);

        abort_if($ticket->status !== NewTicket::STATUS_PENDING, 404);

        DB::transaction(function () use ($ticket) {
            $ticket->update([
                'status' => NewTicket::STATUS_IN_PROGRESS,
            ]);

            NewTicketHistory::create([
                'new_ticket_id' => $ticket->id,
                'description' => 'Ticket status changed to in-progress.',
                'data' => [
                    'type' => 'reply',
                    'user_id' => auth()->id(),
                    'status' => NewTicket::STATUS_IN_PROGRESS,
                    'description' => '',
                    'owners_included' => $request->extra_users ?? []
                ],
                'attachments' => [],
                'created_by' => auth()->id()
            ]);
        });

        return redirect()->route('ticket-management.index')->with('success', 'Ticket accepted successfully.');
    }

    public function assignUsers(string $encryptedTicket, Request $request)
    {
        $ticket = $this->resolveTicket($encryptedTicket);

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'users' => ['nullable', 'array'],
                'users.*' => ['integer', 'exists:users,id'],
            ]);

            DB::transaction(function () use ($ticket, $validated) {
                $selected = $validated['users'] ?? [];
                $toKeepIds = [];

                foreach ($selected as $userId) {
                    $record = NewTicketOwner::withTrashed()->updateOrCreate([
                        'new_ticket_id' => $ticket->id,
                        'owner_id' => $userId,
                        'is_primary' => false,
                    ], [
                        'assigned_by' => auth()->id(),
                    ]);

                    if (method_exists($record, 'trashed') && $record->trashed()) {
                        $record->restore();
                    }

                    $toKeepIds[] = $record->id;
                }

                $ticket->update([
                    'status' => 'accepted'
                ]);

                if (!empty($toKeepIds)) {
                    NewTicketOwner::where('new_ticket_id', $ticket->id)
                        ->where('is_primary', false)
                        ->whereNotIn('id', $toKeepIds)
                        ->delete();
                } else {
                    NewTicketOwner::where('new_ticket_id', $ticket->id)
                        ->where('is_primary', false)
                        ->delete();
                }
            });

            return response()->json(['status' => 'success']);
        }

        $owners = NewTicketOwner::with('user')
            ->where('new_ticket_id', $ticket->id)
            ->where('is_primary', false)
            ->get();

        $users = $owners->filter(function ($o) {
            return isset($o->user->id);
        })->map(function ($o) {
            $u = $o->user;
            $display = trim(($u->employee_id ? ($u->employee_id . ' - ') : '') . $u->name . ' ' . $u->middle_name . ' ' . $u->last_name);
            return [
                'id' => $u->id,
                'text' => $display ?: ('User #' . $u->id),
            ];
        })->values();

        return response()->json([
            'ticket_id' => $ticket->id,
            'encrypted_ticket' => $encryptedTicket,
            'users' => $users,
        ]);
    }

    public function escalations(Request $request)
    {
        if ($request->ajax()) {
            $query = TicketEscalation::query()
                ->with(['department', 'particular', 'issue'])
                ->when($request->filled('department_id'), function ($builder) {
                    $builder->where('department_id', request('department_id'));
                })
                ->when($request->filled('particular_id'), function ($builder) {
                    $builder->where('particular_id', request('particular_id'));
                })
                ->when($request->filled('issue_id'), function ($builder) {
                    $builder->where('issue_id', request('issue_id'));
                });

            return datatables()
                ->eloquent($query)
                ->addColumn('department', function (TicketEscalation $e) {
                    return optional($e->department)->name ?: '-';
                })
                ->addColumn('particular', function (TicketEscalation $e) {
                    return optional($e->particular)->name ?: '-';
                })
                ->addColumn('issue', function (TicketEscalation $e) {
                    return optional($e->issue)->name ?: '-';
                })
                ->addColumn('level1', function (TicketEscalation $e) {
                    return $e->level1_hours . ' h';
                })
                ->addColumn('level2', function (TicketEscalation $e) {
                    return $e->level2_hours . ' h';
                })
                ->addColumn('created_at', function (TicketEscalation $e) {
                    return $e->created_at ? $e->created_at->format('d M Y H:i') : '-';
                })
                ->addColumn('action', function (TicketEscalation $e) {
                    $actions = '';
                    if (Gate::allows('ticket-escalations.view')) {
                        $actions .= '<a href="' . route('ticket-escalations.view', $e->id) . '" class="btn btn-primary btn-sm">View</a>';
                    }
                    if (Gate::allows('ticket-escalations.edit')) {
                        $actions .= '<a href="' . route('ticket-escalations.edit', $e->id) . '" class="btn btn-info btn-sm ms-1">Edit</a>';
                    }
                    if (Gate::allows('ticket-escalations.destroy')) {
                        $actions .= '<form method="POST" action="' . route('ticket-escalations.destroy', $e->id) . '" class="d-inline-block ms-1 escalationDeleteForm">'
                            . csrf_field() . method_field('DELETE') .
                            '<button type="submit" class="btn btn-danger btn-sm">Delete</button></form>';
                    }
                    return $actions ?: '-';
                })
                ->rawColumns(['action'])
                ->toJson();
        }

        $page_title = 'Ticket Escalations';
        return view('tickets.escalation-list', compact('page_title'));
    }

    public function createEscalation()
    {
        $page_title = 'Create Ticket Escalation';
        return view('tickets.create-escalation', compact('page_title'));
    }

    public function storeEscalation(Request $request)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'particular_id' => [
                'required',
                Rule::exists('particulars', 'id')->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department_id);
                }),
            ],
            'issue_id' => [
                'required',
                Rule::exists('issues', 'id')->where(function ($query) use ($request) {
                    return $query->where('particular_id', $request->particular_id);
                }),
            ],
            'level1_hours' => ['required', 'integer', 'min:1'],
            'level1_users' => ['required', 'array', 'min:1'],
            'level1_notifications' => ['required', 'array', 'min:1'],
            'level2_hours' => ['required', 'integer', 'min:1'],
            'level2_users' => ['required', 'array', 'min:1'],
            'level2_notifications' => ['required', 'array', 'min:1'],

            'pending_level1_hours' => ['required', 'integer', 'min:1'],
            'pending_level1_users' => ['required', 'array', 'min:1'],
            'pending_level1_notifications' => ['required', 'array', 'min:1'],
            'pending_level2_hours' => ['required', 'integer', 'min:1'],
            'pending_level2_users' => ['required', 'array', 'min:1'],
            'pending_level2_notifications' => ['required', 'array', 'min:1'],
        ]);

        TicketEscalation::updateOrCreate([
            'department_id' => $validated['department_id'],
            'particular_id' => $validated['particular_id'],
            'issue_id' => $validated['issue_id'],
        ], [
            'level1_hours' => $validated['level1_hours'],
            'level1_users' => $validated['level1_users'],
            'level1_notifications' => $validated['level1_notifications'],
            'level2_hours' => $validated['level2_hours'],
            'level2_users' => $validated['level2_users'],
            'level2_notifications' => $validated['level2_notifications'],

            'pending_level1_hours' => $validated['pending_level1_hours'] ?? null,
            'pending_level1_users' => $validated['pending_level1_users'] ?? [],
            'pending_level1_notifications' => $validated['pending_level1_notifications'] ?? [],
            'pending_level2_hours' => $validated['pending_level2_hours'] ?? null,
            'pending_level2_users' => $validated['pending_level2_users'] ?? [],
            'pending_level2_notifications' => $validated['pending_level2_notifications'] ?? [],
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('ticket-escalations.index')->with('success', 'Escalation saved successfully.');
    }

    public function showEscalation(Request $request, $id) {
        $page_title = 'View Ticket Escalation';
        $escalation = TicketEscalation::with(['department', 'particular', 'issue'])->findOrFail($id);
        $level1Users = User::whereIn('id', $escalation->level1_users ?? [])->get(['id','name']);
        $level2Users = User::whereIn('id', $escalation->level2_users ?? [])->get(['id','name']);
        $level1Templates = NotificationTemplate::whereIn('id', $escalation->level1_notifications ?? [])->get(['id','title']);
        $level2Templates = NotificationTemplate::whereIn('id', $escalation->level2_notifications ?? [])->get(['id','title']);


        $pendingLevel1Users = User::whereIn('id', $escalation->pending_level1_users ?? [])->get(['id','name']);
        $pendingLevel2Users = User::whereIn('id', $escalation->pending_level2_users ?? [])->get(['id','name']);
        $pendingLevel1Templates = NotificationTemplate::whereIn('id', $escalation->pending_level1_notifications ?? [])->get(['id','title']);
        $pendingLevel2Templates = NotificationTemplate::whereIn('id', $escalation->pending_level2_notifications ?? [])->get(['id','title']);

        return view('tickets.view-escalation', compact('page_title', 'escalation', 'level1Users', 'level2Users', 'level1Templates', 'level2Templates', 'pendingLevel1Users', 'pendingLevel2Users', 'pendingLevel1Templates', 'pendingLevel2Templates'));
    }

    public function editEscalation($id)
    {
        $page_title = 'Edit Ticket Escalation';
        $escalation = TicketEscalation::with(['department', 'particular', 'issue'])->findOrFail($id);
        $level1Users = User::whereIn('id', $escalation->level1_users ?? [])->get(['id','name']);
        $level2Users = User::whereIn('id', $escalation->level2_users ?? [])->get(['id','name']);
        $level1Templates = NotificationTemplate::whereIn('id', $escalation->level1_notifications ?? [])->get(['id','title']);
        $level2Templates = NotificationTemplate::whereIn('id', $escalation->level2_notifications ?? [])->get(['id','title']);


        $pendingLevel1Users = User::whereIn('id', $escalation->pending_level1_users ?? [])->get(['id','name']);
        $pendingLevel2Users = User::whereIn('id', $escalation->pending_level2_users ?? [])->get(['id','name']);
        $pendingLevel1Templates = NotificationTemplate::whereIn('id', $escalation->pending_level1_notifications ?? [])->get(['id','title']);
        $pendingLevel2Templates = NotificationTemplate::whereIn('id', $escalation->pending_level2_notifications ?? [])->get(['id','title']);

        return view('tickets.edit-escalation', compact('page_title', 'escalation', 'level1Users', 'level2Users', 'level1Templates', 'level2Templates', 'pendingLevel1Users', 'pendingLevel2Users', 'pendingLevel1Templates', 'pendingLevel2Templates'));
    }

    public function updateEscalation(Request $request, $id)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'particular_id' => [
                'required',
                Rule::exists('particulars', 'id')->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department_id);
                }),
            ],
            'issue_id' => [
                'required',
                Rule::exists('issues', 'id')->where(function ($query) use ($request) {
                    return $query->where('particular_id', $request->particular_id);
                }),
            ],
            'level1_hours' => ['required', 'integer', 'min:1'],
            'level1_users' => ['required', 'array', 'min:1'],
            'level1_notifications' => ['required', 'array', 'min:1'],
            'level2_hours' => ['required', 'integer', 'min:1'],
            'level2_users' => ['required', 'array', 'min:1'],
            'level2_notifications' => ['required', 'array', 'min:1'],
            
            'pending_level1_hours' => ['nullable', 'integer', 'min:1'],
            'pending_level1_users' => ['nullable', 'array'],
            'pending_level1_notifications' => ['nullable', 'array'],
            'pending_level2_hours' => ['nullable', 'integer', 'min:1'],
            'pending_level2_users' => ['nullable', 'array'],
            'pending_level2_notifications' => ['nullable', 'array'],
        ]);

        $escalation = TicketEscalation::findOrFail($id);
        $escalation->update([
            'department_id' => $validated['department_id'],
            'particular_id' => $validated['particular_id'],
            'issue_id' => $validated['issue_id'],
            'level1_hours' => $validated['level1_hours'],
            'level1_users' => $validated['level1_users'],
            'level1_notifications' => $validated['level1_notifications'],
            'level2_hours' => $validated['level2_hours'],
            'level2_users' => $validated['level2_users'],

            'level2_notifications' => $validated['level2_notifications'],
            'pending_level1_hours' => $validated['pending_level1_hours'] ?? null,
            'pending_level1_users' => $validated['pending_level1_users'] ?? [],
            'pending_level1_notifications' => $validated['pending_level1_notifications'] ?? [],
            'pending_level2_hours' => $validated['pending_level2_hours'] ?? null,
            'pending_level2_users' => $validated['pending_level2_users'] ?? [],
            'pending_level2_notifications' => $validated['pending_level2_notifications'] ?? [],
        ]);

        return redirect()->route('ticket-escalations.index')->with('success', 'Escalation updated successfully.');
    }

    public function destroyEscalation($id)
    {
        $escalation = TicketEscalation::findOrFail($id);
        $escalation->delete();
        return redirect()->route('ticket-escalations.index')->with('success', 'Escalation deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = NewTicket::query()
            ->with(['department', 'particular', 'issue', 'creator', 'store', 'primaryOwners.user', 'histories'])
            ->when($request->filled('department_id'), function ($builder) {
                $builder->where('department_id', request('department_id'));
            })
            ->when($request->filled('particular_id'), function ($builder) {
                $builder->where('particular_id', request('particular_id'));
            })
            ->when($request->filled('issue_id'), function ($builder) {
                $builder->where('issue_id', request('issue_id'));
            })
            ->when($request->filled('location'), function ($builder) {
                $builder->where('store_id', request('location'));
            })
            ->when($request->filled('assigned'), function ($builder) {
                $builder->whereHas('owners', function ($innerBuilder) {
                    $innerBuilder->where('owner_id', request('assigned'));
                });
            })
            ->when($request->filled('created_from'), function ($builder) {
                $builder->whereDate('created_at', '>=', date('Y-m-d', strtotime(request('created_from'))));
            })
            ->when($request->filled('created_to'), function ($builder) {
                $builder->whereDate('created_at', '<=', date('Y-m-d', strtotime(request('created_to'))));
            })
            ->when($request->filled('created_by'), function ($builder) {
                $builder->where('created_by', request('created_by'));
            });

        $tickets = $query->get();

        $fileName = 'tickets_export_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new TicketsExport($tickets), $fileName);
    }

    protected function resolveTicket(string $encryptedId): NewTicket
    {
        try {
            $id = decrypt($encryptedId);
        } catch (DecryptException $exception) {
            abort(404);
        }

        return NewTicket::findOrFail($id);
    }

    protected function storeAttachments(array $files): array
    {
        if (empty($files)) {
            return [];
        }

        $paths = [];

        foreach ($files as $file) {
            if ($file) {
                $paths[] = $file->store('new-ticket-attachments', 'public');
            }
        }

        return $paths;
    }

    protected function priorities(): array
    {
        return [
            NewTicket::PRIORITY_LOW => 'Low',
            NewTicket::PRIORITY_MEDIUM => 'Medium',
            NewTicket::PRIORITY_HIGH => 'High',
            NewTicket::PRIORITY_CRITICAL => 'Critical',
        ];
    }
}
