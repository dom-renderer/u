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
            <h1 class="login-title mb-15">Change Password</h1>
            @include('layouts.partials.messages')

            <form name="changepassword_form" method="post" action="{{ route('users.change.password.update') }}">
              @csrf
              <div class="form-group">
                <div class="form-row">
                  <div class="col-12">
                    <input type="password" class="form-control" id="current-password" placeholder="Current Password" name="current-password" required="required">
                  </div>
                </div>
              </div>
              <div class="form-group">
                <div class="form-row">
                  <div class="col-12">
                    <input type="password" class="form-control" id="new-password" placeholder="New Password" name="new-password" required="required">
                  </div>
                </div>
                <span class="text-danger text-left {{$errors->has('password') ? '' : 'd-none'}}" id="password-error">{{ $errors->first('password') }}</span>
              </div>
              <div class="form-group">
                <div class="form-row">
                  <div class="col-12">
                    <input type="password" class="form-control" id="new-password-confirmation" placeholder="Confirm New Password" name="new-password-confirmation" required="required">
                  </div>
                </div>
                <span class="text-danger text-left {{$errors->has('new-password-confirmation-error') ? '' : 'd-none'}}" id="new-password-confirmation-error">{{ $errors->first('new-password-confirmation-error') }}</span>
              </div>
              <button type="submit" class="btn btn-primary btn-fursa-form-submit"><img src="{!! url('assets/images/login-user-icon.svg') !!}" /> Change Password</button>
              <button type="submit" class="btn btn-primary btn-fursa-form-submit mt-3"><img src="{!! url('assets/images/next-step-icon.png') !!}" /> <a href="{{ route('logout.perform') }}" class="nav-link">Logout</a></button>

            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  window.onload = function() {

    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#new-password');
    const passwordError = document.querySelector('#password-error');
    const confirmPassword = document.querySelector('#new-password-confirmation');
    const confirmPasswordError = document.querySelector('#new-password-confirmation-error');

    document.querySelector("form[name='changepassword_form']").addEventListener('submit', function(e) {
      e.preventDefault();
      var regex = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})");
      let validEle = 0;

      if (regex.test(password.value) == false) {
        if (passwordError.classList.contains('d-none')) {
          passwordError.classList.remove('d-none');
        }
        passwordError.innerHTML = 'password must be a minimum of 8 characters including number, Upper, Lower And one special character';
        passwordError.focus();
      } else {
        passwordError.innerHTML = '';
        validEle++;
      }

      if (confirmPassword.value != password.value) {
        if (confirmPasswordError.classList.contains('d-none')) {
          confirmPasswordError.classList.remove('d-none');
        }
        confirmPasswordError.innerHTML = 'confirm password does not match.';
        confirmPasswordError.focus();
      } else {
        confirmPasswordError.innerHTML = '';
        validEle++;
      }

      if (validEle == 2) {
        document.changepassword_form.submit();
        return true;
      }

      return false;
    })
  };
</script>
@endsection