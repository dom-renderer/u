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
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>

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


      @keyframes rotation {
        0% {
          transform: rotate(0deg);
        }

        100% {
          transform: rotate(360deg);
        }
      }

              .section {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
            background-color: #f8f9fa;
            box-shadow: 0 2px 4px #0000001a;
        }

        .section h2 {
            color: #065e2e;
        }

        .select2-container--classic .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }

        .select2-container--classic .select2-selection--single {
            height: 40px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__clear {
            height: 37px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__rendered {
            line-height: 39px !important;
        }

        ul.nav-tabs button.nav-link.active {
            color: #065e2e!important;
            border-bottom: 1px solid #065e2e!important;
        }
    </style>

    @stack('css')

</head>

  <body>
  <div class="wrapper">
            <div class="section">

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item col-3" role="presentation">
                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Pending</button>
                    </li>
                    <li class="nav-item col-3" role="presentation">
                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">In-Progress</button>
                    </li>
                    <li class="nav-item col-3" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Completed</button>
                    </li>
                    <li class="nav-item col-3" role="presentation">
                        <button class="nav-link" id="stale-tab" data-bs-toggle="tab" data-bs-target="#stale" type="button" role="tab" aria-controls="contact" aria-selected="false">Stale</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                        <table class="table table-striped table-responsive"  style="width:100%;" id="ticket-table-a">
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Title</th>
                                    <th>Department</th>
                                    <th>Priority</th>
                                    <th>Location</th>
                                    <th>City</th>
                                    <th>DoM</th>
                                    <th>Date Opened</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="table2body">
                        </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        <table class="table table-striped table-responsive"  style="width:100%;" id="ticket-table-b">
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Title</th>
                                    <th>Department</th>
                                    <th>Priority</th>
                                    <th>Location</th>
                                    <th>City</th>
                                    <th>DoM</th>
                                    <th>Date Opened</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="table2body">
                        </tbody>
                        </table>                        
                    </div>
                    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                        <table class="table table-striped table-responsive"  style="width:100%;" id="ticket-table-c">
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Title</th>
                                    <th>Department</th>
                                    <th>Priority</th>
                                    <th>Location</th>
                                    <th>City</th>
                                    <th>DoM</th>
                                    <th>Date Opened</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="table2body">
                        </tbody>
                        </table>                        
                    </div>
                    <div class="tab-pane fade" id="stale" role="tabpanel" aria-labelledby="contact-tab">
                        <table class="table table-striped table-responsive"  style="width:100%;" id="ticket-table-d">
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Title</th>
                                    <th>Department</th>
                                    <th>Priority</th>
                                    <th>Location</th>
                                    <th>City</th>
                                    <th>DoM</th>
                                    <th>Date Opened</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="table2body">
                        </tbody>
                        </table>                        
                    </div>
                </div>
            </div>
  </div>

<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery-ui.js') }}"></script>
<script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>

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
  $(document).on('select2:open', () => {
    document.querySelector('.select2-search__field').focus();
  });

  $(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
    $(this).closest(".select2-container").siblings('select:enabled').select2('open');
  })

  $("select").on('select2:closing', function (e) {
    $(e.target).data("select2").$selection.one('focus focusin', function (e) {
      e.stopPropagation();
    });
  });

$(document).ready(function () {
    // Ticket Tabs

            let ticket1 = new DataTable('#ticket-table-a', {
                "aLengthMenu": [[50, 100, 250], [50, 100, 250]],
                ajax: {
                    url: "{{ route('get-ticket-listing') }}",
                    data: function ( d ) {
                        return $.extend( {}, d, {
                            mainstatus : 1,
                            startd : $('#tktStart').val(),
                            endd : $('#tktEnd').val(),
                            dom: $('#tktDoM').val(),
                            store: $('#tktLocation').val(),
                            state: $('#tktState').val(),
                            city: $('#tktCity').val(),
                            dept: $('#tktDeptartment').val(),
                            status: $('#tktStatus').val()
                        });
                    }
                },
                processing: false,
                ordering: false,
                serverSide: true,
                columns: [
                    { data: 'ticket_number' },
                    { data: 'subject' },
                    { data: 'department_name' },
                    { data: 'priority_name' },
                    { data: 'location_name' },
                    { data: 'city_name' },
                    { data: 'dom_name' },
                    { data: 'opened' },
                    { data: 'status_name' }
                ],
                initComplete: function(settings) {

                }
            });
            let ticket2 = new DataTable('#ticket-table-b', {
                "aLengthMenu": [[50, 100, 250], [50, 100, 250]],
                ajax: {
                    url: "{{ route('get-ticket-listing') }}",
                    data: function ( d ) {
                        return $.extend( {}, d, {
                            mainstatus : 2,                            
                            startd : $('#tktStart').val(),
                            endd : $('#tktEnd').val(),
                            dom: $('#tktDoM').val(),
                            store: $('#tktLocation').val(),
                            state: $('#tktState').val(),
                            city: $('#tktCity').val(),
                            dept: $('#tktDeptartment').val(),
                            status: $('#tktStatus').val()
                        });
                    }
                },
                processing: false,
                ordering: false,
                serverSide: true,
                columns: [
                    { data: 'ticket_number' },
                    { data: 'subject' },
                    { data: 'department_name' },
                    { data: 'priority_name' },
                    { data: 'location_name' },
                    { data: 'city_name' },
                    { data: 'dom_name' },
                    { data: 'opened' },                    
                    { data: 'status_name' }
                ],
                initComplete: function(settings) {

                }
            });
            let ticket3 = new DataTable('#ticket-table-c', {
                "aLengthMenu": [[50, 100, 250], [50, 100, 250]],
                ajax: {
                    url: "{{ route('get-ticket-listing') }}",
                    data: function ( d ) {
                        return $.extend( {}, d, {
                            mainstatus : 3,                            
                            startd : $('#tktStart').val(),
                            endd : $('#tktEnd').val(),
                            dom: $('#tktDoM').val(),
                            store: $('#tktLocation').val(),
                            state: $('#tktState').val(),
                            city: $('#tktCity').val(),
                            dept: $('#tktDeptartment').val(),
                            status: $('#tktStatus').val()
                        });
                    }
                },
                processing: false,
                ordering: false,
                serverSide: true,
                columns: [
                    { data: 'ticket_number' },
                    { data: 'subject' },
                    { data: 'department_name' },
                    { data: 'priority_name' },
                    { data: 'location_name' },
                    { data: 'city_name' },
                    { data: 'dom_name' },
                    { data: 'opened' },                    
                    { data: 'status_name' }
                ],
                initComplete: function(settings) {

                }
            });
            let ticket4 = new DataTable('#ticket-table-d', {
                "aLengthMenu": [[50, 100, 250], [50, 100, 250]],
                ajax: {
                    url: "{{ route('get-ticket-listing') }}",
                    data: function ( d ) {
                        return $.extend( {}, d, {
                            startd : $('#tktStart').val(),
                            endd : $('#tktEnd').val(),
                            dom: $('#tktDoM').val(),
                            store: $('#tktLocation').val(),
                            state: $('#tktState').val(),
                            city: $('#tktCity').val(),
                            dept: $('#tktDeptartment').val(),
                            status: $('#tktStatus').val()
                        });
                    }
                },
                processing: false,
                ordering: false,
                serverSide: true,
                columns: [
                    { data: 'ticket_number' },
                    { data: 'subject' },
                    { data: 'department_name' },
                    { data: 'priority_name' },
                    { data: 'location_name' },
                    { data: 'city_name' },
                    { data: 'dom_name' },
                    { data: 'opened' },                    
                    { data: 'status_name' }
                ],
                initComplete: function(settings) {

                }
            });

            // Ticket Tabs
});

</script>

  </body>
</html>