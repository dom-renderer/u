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
			width: 100px!important;
			height: 100px!important;
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
							<h1 class="login-title">LOGIN</h1>
							
							@include('layouts.partials.messages')

							<form method="post" action="{{ route('login.perform') }}">

								<input type="hidden" name="_token" value="{{ csrf_token() }}" />
								<div class="form-group">
									<div class="form-row">
										<div class="col-12"><input type="text" class="form-control" id="" placeholder="Enter Your Username or Phone Number" name="username" value="{{ old('username') }}" required="required"></div>
										@if ($errors->has('username'))
										<span class="text-danger text-left">{{ $errors->first('username') }}</span>
										@endif

									</div>
								</div>
								<div class="form-group">
									<div class="form-row">
									<div class="col-12 input-group input-group-lg">
											<input type="password" class="form-control" id="password" placeholder="Enter Your Password" name="password" value="{{ old('password') }}" required="required">
											<span class="input-group-text border-input-radius"><i class="bi-eye-fill cursor-pointer" id="togglePassword"></i></span>
										</div>
									</div>
									@if (session()->has('error'))
									<span class="text-danger text-left">{{ session()->get('error') }}</span>
									@endif
								</div>
								<div class="form-group form-check">
									<div class="form-row">
										<div class="col-12">
											<input type="checkbox" class="form-check-input" id="lbl-for-check">
											<label class="form-check-label" for="lbl-for-check">Keep me logged in</label>
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="form-row">
										<div class="col-12">
											<a href="{{route('password.request')}}" class="">Forgot password?</a>
										</div>
									</div>
								</div>
								<button type="submit" class="btn btn-primary btn-fursa-form-submit"><img src="{!! url('assets/images/login-user-icon.svg') !!}" /> Login</button>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
	const togglePassword = document.querySelector('#togglePassword');
	const password = document.querySelector('#password');

	togglePassword.addEventListener('click', function (e) {
		// toggle the type attribute
		const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
		password.setAttribute('type', type);
		// toggle the eye slash icon
		this.classList.toggle('bi-eye-slash-fill');
	});
	</script>
	@endsection
