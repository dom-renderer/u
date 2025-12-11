@extends('layouts.auth-master')

@section('content')
<style type="text/css">
    .cursor-pointer {
        cursor: pointer;
    }
    .border-input-radius {
        border-top-right-radius: 20px !important;
        border-bottom-right-radius: 20px !important;
    }

    .logo-css {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 3px solid #fff40f;
        padding: 5px;
    }
</style>
<div class="login-wrapper">
    <div class="container-fluid" style="height:100%;">
        <div class="row" style="height:100%;">
            <div class="col-12">
                <div class="logn-box" style="height:100%;">
                    <div class="logo"><a href="#" class="brand-link"><img src="{!! url('assets/logo.webp') !!}" alt="{{ APP_NAME }} Logo" class="img-logo logo-css"></a></div>
                    <div class="login-form fursa-form">
                        <h1 class="login-title mb-15">RESET PASSWORD</h1>
                        @include('layouts.partials.messages')

                        <form method="post" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}" />
                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col-12">
                                        <input type="email" class="form-control" id="" placeholder="Enter Your Email" name="email" value="{{ old('email', request('email')) }}" required="required">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col-12">
                                        <input type="password" class="form-control" id="password" placeholder="Enter Your Password" name="password" value="{{ old('password') }}" required="required">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="form-row">
                                    <div class="col-12">
                                        <input type="password" class="form-control" id="password_confirmation" placeholder="Enter Your Confirm Password" name="password_confirmation" value="{{ old('password_confirmation') }}" required="required">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-fursa-form-submit"><img src="{!! url('assets/images/login-user-icon.svg') !!}" /> Reset Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection