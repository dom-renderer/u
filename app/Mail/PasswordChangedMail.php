<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

class PasswordChangedMail extends Mailable
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
        $subject = APP_NAME . " Password Reset Successfully";
        $fursaa_notify_title = APP_NAME . " Password Reset Successfully";
        $fursaa_notify_body = "<p style='line-height: 1.5em;'>Dear ".$this->user->name.",</p><p style='line-height: 1.5em;'>This is to confirm that the password for your account has been successfully changed. Your account is now secured with the new password that you have set.</p><p style='line-height: 1.5em;'>If you did not change your password, please contact us immediately to report any unauthorized access to your account.</p>";
        $actionUrl = route('login.show');
        $actionText = 'Go to My Account';
        return $this->subject($subject)
        ->view('emails.password-changed', [
            'fursaa_notify_title' => $fursaa_notify_title, 
            'fursaa_notify_body' => $fursaa_notify_body,
            'actionUrl' => $actionUrl,
            'actionText' => $actionText,
            'displayableActionUrl' => str_replace(['mailto:', 'tel:'], '', $actionUrl ?? ''),
        ])->with(['message' => $this]);
    }
}