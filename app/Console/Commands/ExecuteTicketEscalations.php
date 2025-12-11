<?php

namespace App\Console\Commands;

use App\Models\TicketitEscalationExecution;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\TicketitEscalation;
use Illuminate\Console\Command;
use App\Mail\EscalationMail;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;

class ExecuteTicketEscalations extends Command
{
    public static $ticketVariables = [
        '{ticket_number}' => 'Ticket Number',
        '{ticket_subject}' => 'Ticket Subject',
        '{ticket_priority}' => 'Ticket Priority',
        '{ticket_department}' => 'Ticket Department',
        '{ticket_created_at}' => 'Ticket Created At',
        '{escalation_level}' => '(Ticket) Escalation Level',
        '{escalation_time}' => '(Ticket) Escalation Time',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:execute-escalations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute escalations for pending tickets (status_id = 1)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting ticket escalation execution...');
        
        try {
            $pendingTickets = Ticket::where('status_id', 1)
                ->whereNull('completed_at')
                ->with(['priority', 'department', 'user'])
                ->get();

            $this->info("Found {$pendingTickets->count()} pending tickets to check for escalations");

            $escalationsExecuted = 0;

            foreach ($pendingTickets as $ticket) {
                $escalationsExecuted += $this->processTicketEscalations($ticket);
            }

            $this->info("Successfully executed {$escalationsExecuted} escalations");
            
            Log::info("Ticket escalation command executed successfully. Processed {$pendingTickets->count()} tickets, executed {$escalationsExecuted} escalations.");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error executing escalations: " . $e->getMessage());
            Log::error("Ticket escalation command failed: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Process escalations for a single ticket
     *
     * @param Ticket $ticket
     * @return int Number of escalations executed
     */
    private function processTicketEscalations(Ticket $ticket)
    {
        $escalationsExecuted = 0;

        if (!$ticket->department_id || !$ticket->priority_id) {
            return 0;
        }

        $escalations = TicketitEscalation::where('department_id', $ticket->department_id)
            ->where('priority_id', $ticket->priority_id)
            ->with(['escalationUsers.user', 'escalationUsers.template'])
            ->orderBy('escalation_level')
            ->get();

        if ($escalations->isEmpty()) {
            return 0;
        }

        $ticketCreatedAt = Carbon::parse($ticket->created_at);
        $currentTime = Carbon::now();

        foreach ($escalations as $escalation) {
            $alreadyExecuted = TicketitEscalationExecution::where('ticketit_escalation_id', $escalation->id)
                ->where('ticket_id', $ticket->id)
                ->exists();

            if ($alreadyExecuted) {
                continue;
            }

            $escalationTime = explode(':', $escalation->escalation_fire_time);
            $days = (int) $escalationTime[0];
            $hours = (int) $escalationTime[1];
            $minutes = (int) $escalationTime[2];

            $escalationFireTime = $ticketCreatedAt->copy()
                ->addDays($days)
                ->addHours($hours)
                ->addMinutes($minutes);
            
            if ($currentTime->gte($escalationFireTime)) {
                $this->executeEscalation($ticket, $escalation);
                $escalationsExecuted++;

                $this->info("Executed escalation level {$escalation->escalation_level} for ticket #{$ticket->id}");
            }
        }

        return $escalationsExecuted;
    }

    /**
     * Execute a specific escalation
     *
     * @param Ticket $ticket
     * @param TicketitEscalation $escalation
     */
    private function executeEscalation(Ticket $ticket, TicketitEscalation $escalation)
    {
        try {
            TicketitEscalationExecution::create([
                'ticketit_escalation_id' => $escalation->id,
                'ticket_id' => $ticket->id
            ]);

            foreach ($escalation->escalationUsers as $escalationUser) {
                $this->sendTemplateNotification($ticket, $escalation, $escalationUser->template, $escalationUser->user);
            }

        } catch (\Exception $e) {
            Log::error("Failed to execute escalation for ticket {$ticket->id}, escalation {$escalation->id}: " . $e->getMessage());
            $this->error("Failed to execute escalation for ticket {$ticket->id}: " . $e->getMessage());
        }
    }

    /**
     * Send notification to a specific user
     *
     * @param Ticket $ticket
     * @param TicketitEscalation $escalation
     * @param \App\Models\User $user
     */
    private function sendUserNotification(Ticket $ticket, TicketitEscalation $escalation, NotificationTemplate $template, User $user)
    {
        try {
            $subject = "Ticket Escalation - Level {$escalation->escalation_level} - {$ticket->ticket_number}";
            
            $content = "Ticket #{$ticket->ticket_number} has been escalated to level {$escalation->escalation_level}.\n\n";
            $content .= "Subject: {$ticket->subject}\n";
            $content .= "Priority: " . ($ticket->priority->name ?? 'N/A') . "\n";
            $content .= "Department: " . ($ticket->department->name ?? 'N/A') . "\n";
            $content .= "Created: " . $ticket->created_at->format('Y-m-d H:i:s') . "\n";
            $content .= "Escalation Time: " . $escalation->escalation_fire_time . "\n\n";
            $content .= "Please take appropriate action on this ticket.";

            Mail::to($user->email)->send(new EscalationMail($subject, $content));
            
            $this->line("Sent escalation notification to user: {$user->email}");

        } catch (\Exception $e) {
            Log::error("Failed to send escalation notification to user {$user->id} for ticket {$ticket->id}: " . $e->getMessage());
        }
    }

    /**
     * Send notification using a template
     *
     * @param Ticket $ticket
     * @param TicketitEscalation $escalation
     * @param NotificationTemplate $template
     */
    private function sendTemplateNotification(Ticket $ticket, TicketitEscalation $escalation, NotificationTemplate $template, User $user)
    {
        try {
            $content = $template->content;
            $content = str_replace('{ticket_number}', $ticket->ticket_number, $content);
            $content = str_replace('{ticket_subject}', $ticket->subject, $content);
            $content = str_replace('{ticket_priority}', $ticket->priority->name ?? 'N/A', $content);
            $content = str_replace('{ticket_department}', $ticket->department->name ?? 'N/A', $content);
            $content = str_replace('{ticket_created_at}', $ticket->created_at->format('Y-m-d H:i:s'), $content);
            $content = str_replace('{escalation_level}', $escalation->escalation_level, $content);
            $content = str_replace('{escalation_time}', $escalation->escalation_fire_time, $content);

            $subject = str_replace('{ticket_number}', $ticket->ticket_number, $template->title);
            $subject = str_replace('{escalation_level}', $escalation->escalation_level, $subject);

            if ($template->type == 1) {
                $deviceTokens = [];
                if (isset($user->id) && $user->id > 0) {
                    $deviceTokens = \App\Models\DeviceToken::where('user_id', $user->id)->pluck('token')->toArray();
                }

                if (!empty($deviceTokens)) {
                    \App\Helpers\Helper::sendPushNotification($deviceTokens, [
                        'title' => $subject,
                        'description' => $content
                    ]);
                }
            } else {
                Mail::to($user->email)->send(new EscalationMail($subject, $content));
            }
            
            $this->line("Sent escalation notification using template: {$template->name}");

        } catch (\Exception $e) {
            Log::error("Failed to send escalation notification using template {$template->id} for ticket {$ticket->id}: " . $e->getMessage());
        }
    }
}
