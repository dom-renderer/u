<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user, $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = APP_NAME . " Password Reset Request";
        $fursaa_notify_title = APP_NAME . " Password Reset Request";
        $fursaa_notify_body = "You have requested to reset your password.  Please click below link to rest your password :";
        $actionUrl = route('password.reset', ['token' => $this->token, 'email' => $this->user->email]);
        $actionText = 'Reset';
        return $this->subject($subject)
        ->view('emails.reset-password', [
            'fursaa_notify_title' => $fursaa_notify_title, 
            'fursaa_notify_body' => $fursaa_notify_body,
            'actionUrl' => $actionUrl,
            'actionText' => $actionText,
            'displayableActionUrl' => str_replace(['mailto:', 'tel:'], '', $actionUrl ?? ''),
        ])->with(['message' => $this]);
    }
}