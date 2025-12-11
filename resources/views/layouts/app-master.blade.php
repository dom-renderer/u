<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> {{ APP_NAME }} | {{ isset($page_title) ? $page_title : 'Module' }} </title>

    <link href="{!! url('assets/css/bootstrap.min.css') !!}" rel="stylesheet">
    <link href="{!! url('assets/css/my-style.css') !!}" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.css') }}">

    <!-- code added by binal start--->
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <!-- code added by binal end--->
    <style type="text/css">
      .numberCircle {
        font-family: "OpenSans-Semibold", Arial, "Helvetica Neue", Helvetica, sans-serif;
        display: inline-block;
        color: #fff;
        text-align: center;
        line-height: 0px;
        border-radius: 50%;
        font-size: 12px;
        font-weight: 700;
        min-width: 38px;
        min-height: 38px;
      }

      .numberCircle span {
        display: inline-block;
        padding-top: 50%;
        padding-bottom: 50%;
        margin-left: 1px;
        margin-right: 1px;
      }

      /* Some Back Ground Colors */
      .clrTotal {
        background: #51a529;
      }
      .clrLike {
        background: #60a949;
      }
      .clrDislike {
        background: #bd3728;
      }
      .clrUnknown {
        background: #58aeee;
      }
      .clrStatusPause {
        color: #bd3728;
      }
      .clrStatusPlay {
        color: #60a949;
      }

      .LoaderSec {
        position: fixed;
        background: #465b97c7;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
        z-index: 99999999999;
      }

      .LoaderSec .loader {
        width: 55px;
        height: 55px;
        border: 6px solid #fff;
        border-bottom-color: #065e2e;
        border-radius: 50%;
        display: inline-block;
        -webkit-animation: rotation 1s linear infinite;
        animation: rotation 1s linear infinite;
        position: fixed;
        z-index: 9999999999999;
        transform: translate(-50%, -50%);
        top: 50%;
        left: 50%;
      }

      @keyframes rotation {
        0% {
          transform: rotate(0deg);
        }

        100% {
          transform: rotate(360deg);
        }
      }
    </style>

    @stack('css')

</head>

  <body>
  <div class="wrapper">

    <div class="LoaderSec d-none">
      <span class="loader"></span>
    </div>

    @include('layouts.partials.header')

    @include('layouts.partials.sidebar')

    <div class="content-wrapper">
      <div class="container-fluid">
        @yield('content')
      </div>
    </div>  
  </div>

  <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/js/jquery-ui.js') }}"></script>

<script>
    $(document).ready(function(){
        $(".main-header ul.navbar-nav li.nav-item.bars a.nav-link").click(function(){
          $("aside.main-sidebar").toggleClass("close-nav");
          $(".content-wrapper").toggleClass("close-nav");
          $(".main-header").toggleClass("close-nav");
      });

      $("aside.main-sidebar .sidebar nav > ul > li.nav-item > a.nav-link").click(function(){
        $(this).toggleClass("dd-close");
      });

      var url = window.location.href;
      $('.nav-dropdown li a').find('.active').removeClass('active');
      $('.nav-dropdown li a').filter(function(){
                  return this.href == url;
      }).addClass('active').removeClass('text-white');
    });
</script>

@stack('js')

{{-- for ticket --}}
<script src="{{ url('assets/js/jquery-validate.min.js') }}"></script>
@yield('script')

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var sidebar = document.querySelector('.main-sidebar');
        if (sidebar) {
            var scrollPos = localStorage.getItem('sidebar_scroll_position');
            if (scrollPos) {
                sidebar.scrollTop = scrollPos;
            }

            sidebar.addEventListener('scroll', function() {
                localStorage.setItem('sidebar_scroll_position', sidebar.scrollTop);
            });
        }
    });
</script>

<script>

  function isset(...variables) {
    return variables.every(variable => {
        if (variable === undefined || variable === null) return false;

        if (Array.isArray(variable)) return variable.length > 0;

        if (typeof variable === 'object') return Object.keys(variable).length > 0;

        return true;
    });
}

function anyIsset(...variables) {
    return variables.some(variable => {
        if (variable === undefined || variable === null) return false;

        if (Array.isArray(variable)) return variable.length > 0;

        if (typeof variable === 'object') return Object.keys(variable).length > 0;

        return true;
    });
}

</script>

  </body>
</html>