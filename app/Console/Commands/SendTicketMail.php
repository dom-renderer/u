<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketWatcherMail;
use Carbon\Carbon;

class SendTicketMail extends Command
{
    protected $signature = 'tickets:send-mail';
    protected $description = 'Send daily ticket mail to watchers at configured time';

    public function handle()
    {
        $setting = Setting::first();

        if (!$setting) {
            $this->warn('No setting found.');
            return;
        }

        if (!$setting->should_send_ticket_mail) {
            $this->info('Mail sending is disabled.');
            return;
        }

        if (!$setting->send_mail_at) {
            $this->warn('Send mail time not set.');
            return;
        }

        $current = Carbon::now()->format('H:i');
        $configured = Carbon::createFromFormat('H:i:s', $setting->send_mail_at)->format('H:i');

        if ($current !== $configured) {
            $this->info("Current time ($current) does not match scheduled time ($configured).");
            return;
        }

        if (empty($setting->ticket_watchers)) {
            $this->warn('No ticket watchers found.');
            return;
        }

        $users = User::whereIn('id', $setting->ticket_watchers)->get();

        foreach ($users as $user) {
            Mail::to($user->email)->send(new TicketWatcherMail($user));
            $this->info("Mail sent to: {$user->email}");
        }

        $this->info('Ticket mails sent successfully.');
    }
}