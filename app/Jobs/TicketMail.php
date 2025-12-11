<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TicketMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $ticket_data;
    public $users;
    public $ticket_type;
    public $ticket;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ticket_data, $users, $ticket_type, $ticket)
    {
        $this->ticket_data = $ticket_data;
        $this->users = $users;
        $this->ticket_type = $ticket_type;
        $this->ticket = $ticket;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ticket_data = $this->ticket_data;
        $users = $this->users;
        $ticket_type = $this->ticket_type;
        $ticket = $this->ticket;
        
        \Mail::send('emails.ticket_system', $ticket_data, function($message) use($users,$ticket_type,$ticket)
        {
            $message->to($users->email)->subject( ($ticket_type == 'Add' ? 'Ticket created' : ($ticket_type == 'Reply' ? 'Ticket reply' : ($ticket_type == 'Complete' ? 'Ticket completed' : ($ticket_type == 'Estimate date added' ? 'Estimate date added' : ($ticket_type == 'Estimate date changed' ? 'Estimate date changed' : ($ticket_type == 'Reopened' ? 'Ticket Reopened' : '' ) ) ) ))) .' - '.$ticket->ticket_number);
        });
    }
}
