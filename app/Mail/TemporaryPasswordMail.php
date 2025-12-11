<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

class TemporaryPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = APP_NAME . " Temporary Password Merchant Account";
        $fursaa_notify_title = APP_NAME . " Temporary Password Merchant Account";
        $fursaa_notify_body = "<p style='line-height: 1.5em;'>Dear ".$this->user->name.",</p><p style='line-height: 1.5em;'>Below you can find your temporary password.</p>";
        $tempPasswordText = $this->user->temp_password;
        $actionUrl = route('login.show');
        $actionText = 'Go to My Account';
        return $this->subject($subject)
        ->view('emails.temporary-password', [
            'fursaa_notify_title' => $fursaa_notify_title, 
            'fursaa_notify_body' => $fursaa_notify_body,
            'actionUrl' => $actionUrl,
            'actionText' => $actionText,
            'tempPasswordText' => $tempPasswordText,
            'displayableActionUrl' => str_replace(['mailto:', 'tel:'], '', $actionUrl ?? ''),
        ])->with(['message' => $this]);
    }
}