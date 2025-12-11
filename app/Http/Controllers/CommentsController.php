<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\TicketHistory;
use Illuminate\Support\Facades\Auth;

class CommentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('App\Http\Middleware\IsAdminMiddleware', ['only' => ['edit', 'update', 'destroy']]);
        // $this->middleware('App\Http\Middleware\ResAccessMiddleware', ['only' => 'store']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
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
            'ticket_id'   => 'required|exists:ticketit,id',
            'content'     => 'required|min:6',
        ]);

        $comment = new Comment();
        $comment->setPurifiedContent($request->get('content'));
        $comment->html = $request->get('content');

        $comment->ticket_id = $request->get('ticket_id');
        $comment->user_id = Auth::user()->id;
        $comment->save();

        $ticket = Ticket::find($comment->ticket_id);
        $ticket->updated_at = $comment->created_at;
        $ticket->save();

        TicketHistory::create([
            "ticket_id" => $ticket->id,
            "description" => "New comment posted by ".auth()->user()->name,
            "user_id" => auth()->user()->id,
            "type" => 1,
            'model' => Comment::class,
            'model_id' => $comment->id,
            "created_at" => \Carbon\Carbon::now(),
            "updated_at" => \Carbon\Carbon::now()
        ]);

        // Mail send for all user
        Helper::ticket_mail_send($ticket->id,'Reply');

        return back()->with('status', "Comment has been added successfully");
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
        //
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
        //
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
        //
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
        //
    }
}
