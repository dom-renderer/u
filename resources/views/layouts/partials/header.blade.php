<div class="main-header">
      <div class="container-fluid">
        <div class="row">
          <div class="col">
            <!-- Navbar -->
            <nav class="navbar navbar-expand">
              <!-- Left navbar links -->
              <ul class="navbar-nav">
                <li class="nav-item bars">
                  <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <span class="line"></span>
                    <span class="line"></span>
                    <span class="line"></span>
                  </a>
                </li>
                <li class="nav-item header-title"><a href="#" class="nav-link"> {{ APP_NAME }} </a></li>
              </ul>
            </nav>
            <!-- /.navbar -->
          </div>
          <div class="col">
            <div class="premium-merchant flex-div">
              <img src="{{ asset('assets/logo.webp') }}" alt="{{ APP_NAME }}" style="height: 60px;width: 60px;border-radius: 50%;border: 3px solid #065e2e;">
            </div>
          </div>
          <div class="col">
            <div class="user-account flex-div">
              @auth
                <p>{{auth()->user()->name}}<br> <span id="user_clock_header">{{ now()->isoFormat('LLLL') }}</span></p>
              @endauth
              <img src="{!! url('assets/images/user-noti.svg') !!}" alt="user-notification" class="">
            </div>
          </div>
        </div>
      </div>
    </div>
  <script src="{{ asset('assets/js/swal.min.js') }}"></script>
<script type="text/javascript">
  var hasSessionSuccess = "{{Session::has('success')}}";
  if(hasSessionSuccess) {
    Swal.fire({
      position: 'top-end',
      icon: 'success',
      title: "{{session('success')}}",
      showConfirmButton: false,
      timer: 1500
    })
  }
  var hasSessionError = "{{Session::has('error')}}";
  if(hasSessionError) {
    Swal.fire({
      position: 'top-end',
      icon: 'error',
      title: "{{session('error')}}",
      showConfirmButton: false,
      timer: 1500
    })
  }
  var hasSessionWarning = "{{Session::has('warning')}}";
  if(hasSessionWarning) {
    Swal.fire({
      position: 'top-end',
      icon: 'warning',
      title: "{{session('warning')}}",
      showConfirmButton: false,
      timer: 1500
    })
  }
  function showTime() {
    var date = new Date()
    //Wednesday, May 24, 2023 11:42 AM
    const options = {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
      timeZone: "{{date_default_timezone_get()}}"
    };

    return date.toLocaleString(undefined, options).replace('at', '');
  }
  window.setInterval(function() {
    document.getElementById("user_clock_header").innerHTML = showTime();
  }, 1000);
</script>
