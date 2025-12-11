<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordRequest;
use App\Http\Requests\PasswordResetRequest;
use App\Mail\PasswordChangedMail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(PasswordRequest $request)
    {
        $request->getCredentials();

        $status = Password::sendResetLink(
            $request->only('email')
        );
        return $status === Password::RESET_LINK_SENT
                    ? back()->with('error', 'A link has been sent to your email address')
                    : back()->with('error', 'Something went wrong!');
    }

    public static function showResetForm($token) {
        return view('auth.reset-password', ['token' => $token]);
    }

    public static function reset(PasswordResetRequest $request) {
        $request->getCredentials();
        
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => $password
                ]);
     
                if($user->save()) {
                    event(new PasswordReset($user));
                    if (filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                        Mail::to($user->email)->send(new PasswordChangedMail($user));
                    }
                }
     
            }
        );
     
        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login.show')->withSuccess(['status' => __($status)])
                    : back()->withErrors(['email' => [__($status)]]);
    }
}
