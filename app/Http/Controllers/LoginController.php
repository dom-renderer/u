<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {        
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password]) || Auth::attempt(['phone_number' => $request->username, 'password' => $request->password])) {
            $user = Auth::user();
        } else {
            return redirect()->to('login')->with('error', 'These credentials do not match our records.');
        }

        if($user->status == 0) {
            session()->flush();
            return redirect()->to('login')->with('error', 'Your account is disabled. Please contact the administrator.');
        }

        Auth::login($user);

        return $this->authenticated($request, $user);
    }

    protected function authenticated(Request $request, $user) 
    {
        if (Auth::check()) {
            if (auth()->user()->can('flagged-items-dashboard')) {
                return redirect()->route(\App\Providers\RouteServiceProvider::DASHBOARD_ROUTE_NAME);
            } else {
                return redirect()->route('dashboard');
            }
        }
        return redirect()->intended();
    }
}