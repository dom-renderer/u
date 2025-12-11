<?php

namespace App\Console\Commands;

use App\Models\NewTicketEscalationExecution;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\TicketEscalation;
use Illuminate\Console\Command;
use App\Mail\EscalationMail;
use App\Models\NewTicket;
use App\Models\User;
use Carbon\Carbon;

class NewTicketEscalation extends Command
{
    protected $signature = 'execute:ticket-escalation';

    protected $description = 'Execute ticket escalation checks and send notifications accordingly.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Starting NEW ticket escalation execution...');

        try {
            $tickets = NewTicket::query()
                ->whereIn('status', [NewTicket::STATUS_IN_PROGRESS])
                ->with(['department', 'particular', 'issue'])
                ->get();

            $this->info("Found {$tickets->count()} new tickets to check for escalations");

            $executed = 0;
            foreach ($tickets as $ticket) {
                $executed += $this->processTicket($ticket);
            }

            $this->info("Successfully executed {$executed} escalations for NEW tickets");
            Log::info("NewTicketEscalation executed: processed {$tickets->count()} tickets, executed {$executed} escalations");

            return 0;
        } catch (\Exception $e) {
            $this->error('Error executing NEW ticket escalations: ' . $e->getMessage());
            Log::error('NewTicketEscalation failed: ' . $e->getMessage());
            return 1;
        }
    }

    private function processTicket(NewTicket $ticket)
    {
        if (!$ticket->department_id || !$ticket->particular_id || !$ticket->issue_id) {
            return 0;
        }

        $config = TicketEscalation::where('department_id', $ticket->department_id)
            ->where('particular_id', $ticket->particular_id)
            ->where('issue_id', $ticket->issue_id)
            ->first();

        if (!$config) {
            return 0;
        }

        $now = Carbon::now();
        $createdAt = Carbon::parse($ticket->created_at);
        $executed = 0;

        if (is_numeric($config->level1_hours) && $config->level1_hours >= 0) {
            $fireAt = $createdAt->copy()->addHours((int) $config->level1_hours);
            if ($now->greaterThanOrEqualTo($fireAt)) {
                $executed += $this->executeLevel($ticket, $config, 1, $config->level1_users ?? [], $config->level1_notifications ?? []);
            }
        }

        if (is_numeric($config->level2_hours) && $config->level2_hours >= 0) {
            $fireAt = $createdAt->copy()->addHours((int) $config->level2_hours + (int) $config->level1_hours);
            if ($now->greaterThanOrEqualTo($fireAt)) {
                $executed += $this->executeLevel($ticket, $config, 2, $config->level2_users ?? [], $config->level2_notifications ?? []);
            }
        }

        return $executed;
    }

    private function executeLevel(NewTicket $ticket, TicketEscalation $config, int $level, array $userIds, array $templateIds)
    {
        $already = NewTicketEscalationExecution::where('ticket_id', $ticket->id)
            ->where('escalation_id', $config->id)
            ->where('escalation_level', $level)
            ->exists();

        if ($already) {
            return 0;
        }

        try {
            $users = User::whereIn('id', $userIds)->get();
            $templates = NotificationTemplate::whereIn('id', $templateIds)->get();

            foreach ($users as $user) {
                foreach ($templates as $template) {
                    $this->sendTemplateNotification($ticket, $config, $level, $template, $user);
                }
            }

            NewTicketEscalationExecution::create([
                'ticket_id' => $ticket->id,
                'escalation_id' => $config->id,
                'escalation_level' => $level,
            ]);

            $this->line("Recorded escalation L{$level} for ticket #{$ticket->ticket_number}");
            return 1;
        } catch (\Exception $e) {
            Log::error("Failed to execute NEW escalation L{$level} for ticket {$ticket->id}, config {$config->id}: " . $e->getMessage());
            $this->error("Failed to execute escalation L{$level} for ticket {$ticket->ticket_number}: " . $e->getMessage());
            return 0;
        }
    }

    private function sendTemplateNotification(NewTicket $ticket, TicketEscalation $config, int $level, NotificationTemplate $template, User $user)
    {
        try {
            $content = $template->content;
            $content = str_replace('{$ticket_number}', $ticket->ticket_number, $content);
            $content = str_replace('{$ticket_subject}', (string) $ticket->subject, $content);
            $content = str_replace('{$ticket_priority}', ucfirst((string) $ticket->priority), $content);

            $content = str_replace('{$ticket_particular}', ucfirst((string) $ticket->particular->name ?? 'N/A'), $content);
            $content = str_replace('{$ticket_issue}', ucfirst((string) $ticket->issue->name ?? 'N/A'), $content);

            $content = str_replace('{$ticket_department}', optional($ticket->department)->name ?? 'N/A', $content);
            $content = str_replace('{$ticket_created_at}', Carbon::parse($ticket->created_at)->format('Y-m-d H:i:s'), $content);
            $content = str_replace('{$escalation_level}', (string) $level, $content);
            $content = str_replace('{$username}',  ($user->name ?? ' ') . ' ' . ($user->middle_name ?? ' ') . ' ' . ($user->last_name ?? ' '), $content);

            $subject = str_replace('{$ticket_number}', $ticket->ticket_number, (string) $template->title);
            $subject = str_replace('{$escalation_level}', (string) $level, $subject);

            if ((int) $template->type === 1) {
                $deviceTokens = [];
                if (!empty($user->id)) {
                    $deviceTokens = \App\Models\DeviceToken::where('user_id', $user->id)->pluck('token')->toArray();
                }
                if (!empty($deviceTokens)) {
                    \App\Helpers\Helper::sendPushNotification($deviceTokens, [
                        'title' => $subject,
                        'description' => $content,
                    ]);
                }
            } else {
                if (!empty($user->email)) {
                    Mail::to($user->email)->send(new EscalationMail($subject, $content));
                }
            }

            $this->line("Sent NEW escalation L{$level} using template: {$template->name} to user: {$user->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send NEW escalation L{$level} using template {$template->id} for ticket {$ticket->id}: " . $e->getMessage());
        }
    }
}
