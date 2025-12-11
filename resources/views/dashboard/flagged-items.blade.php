@extends('layouts.app-master')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
    <style>
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
@endpush

@section('content')
    <div class="row">

        <div class="">
            <!-- Flagged Items Table -->
            <div class="section">
                <h2>Flagged Items
                    <button class="btn btn-success btn-sm float-end export-flagged-items"> Export </button>
                </h2>
                
                <div class="row mb-3">
                    <div class="col-md-4 col-lg-4 col-xl-2">
                        <label for="filterStart" class="form-label">Start Date</label>
                        <input type="text" id="filterStart" class="form-control" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('d-m-Y') }}">
                    </div>
                    
                    <div class="col-md-4 col-lg-4 col-xl-2">
                        <label for="filterEnd" class="form-label">End Date</label>
                        <input type="text" id="filterEnd" class="form-control" value="{{ date('d-m-Y') }}">
                    </div>

                    <div class="col-md-4 col-lg-4 col-xl-2">
                        <label for="filterDom" class="form-label">DoM</label>
                        <select id="filterDom" class="form-select">
                            @if(auth()->user()->isAdmin())
                                <option value="all" selected> All </option>
                            @else
                                <option value="{{ auth()->user()->id }}"> {{ auth()->user()->employee_id }} - {{ auth()->user()->name }} {{ auth()->user()->middle_name }} {{ auth()->user()->last_name }} </option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-4 col-lg-4 col-xl-2">
                        <label for="filterState" class="form-label">State</label>
                        <select id="filterState">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-4 col-lg-4 col-xl-2">
                        <label for="filterCity" class="form-label">City</label>
                        <select id="filterCity">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-4 col-lg-4 col-xl-2">
                        <label for="filterStore" class="form-label">Location</label>
                        <select id="filterStore">
                            <option value="all" selected> All </option>
                        </select>
                    </div>
                </div>
                <div class="table -responsive">
                    <table class="table table-striped" id="table2">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Checklist</th>
                                <th>DoM</th>
                                <th>Location</th>
                                <th>City</th>
                                <th>State</th>
                                <th>Initial Status</th>
                                <th>Latest Status</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody id="table2body">
                        </tbody>
                    </table>
                </div>
            </div>





            <div class="section">
                <h2>Tickets
                    <button class="btn btn-success btn-sm float-end export-tickets"> Export </button>
                </h2>

                <div class="row mb-3">
                    <div class="col-md-3 col-lg-6">
                        <label for="tktStart" class="form-label">Start Date</label>
                        <input type="text" id="tktStart" class="form-control" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('d-m-Y') }}">
                    </div>
                    <div class="col-md-3 col-lg-6">
                        <label for="tktEnd" class="form-label">End Date</label>
                        <input type="text" id="tktEnd" class="form-control" value="{{ date('d-m-Y') }}">
                    </div>
                    <div class="col-md-3 col-lg-6">
                        <label for="tktDoM" class="form-label">Raised By</label>
                        <select id="tktDoM" class="form-select">
                            @if(auth()->user()->isAdmin())
                                <option value="all" selected> All </option>
                            @else
                                <option value="{{ auth()->user()->id }}"> {{ auth()->user()->employee_id }} - {{ auth()->user()->name }} {{ auth()->user()->middle_name }} {{ auth()->user()->last_name }} </option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-3 col-lg-6">
                        <label for="tktState" class="form-label">State</label>
                        <select id="tktState">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-3 col-lg-6">
                        <label for="tktCity" class="form-label">City</label>
                        <select id="tktCity">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-3 col-lg-6">
                        <label for="tktLocation" class="form-label">Location</label>
                        <select id="tktLocation">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-3 col-lg-6">
                        <label for="tktDeptartment" class="form-label">Department</label>
                        <select id="tktDeptartment">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-3 col-lg-6">
                        <label for="tktStatus" class="form-label">Status</label>
                        <select id="tktStatus">
                            <option value="all" selected> All </option>
                            <option value="pending">Pending</option>
                            <option value="accepted">Accepted</option>
                            <option value="in_progress">In Progress</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item"  role="presentation">
                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Pending</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Accepted</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="onhold-tab" data-bs-toggle="tab" data-bs-target="#onhold" type="button" role="tab" aria-controls="onhold" aria-selected="false">In Progress</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Closed</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="stale-tab" data-bs-toggle="tab" data-bs-target="#stale" type="button" role="tab" aria-controls="contact" aria-selected="false">Stale</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                        <div class="table-responisve">
                            <table class="table table-striped table-responsive"  style="width:100%;" id="ticket-table-a">
                                <thead>
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Title</th>
                                        <th>Location</th>
                                        <th>City</th>
                                        <th>Department</th>
                                        <th>Particular</th>
                                        <th>Issue</th>
                                        <th>Raised By</th>
                                        <th>Date Opened</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        <div class="table-responisve">
                            <table class="table table-striped table-responsive"  style="width:100%;" id="ticket-table-b">
                                <thead>
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Title</th>
                                        <th>Location</th>
                                        <th>City</th>
                                        <th>Department</th>
                                        <th>Particular</th>
                                        <th>Issue</th>
                                        <th>Raised By</th>
                                        <th>Date Opened</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>      
                        </div>                  
                    </div>
                    <div class="tab-pane fade" id="onhold" role="tabpanel" aria-labelledby="onhold-tab">
                        <div class="table-responisve">
                            <table class="table table-striped table-responsive"  style="width:100%;" id="ticket-table-onhold">
                                <thead>
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Title</th>
                                        <th>Location</th>
                                        <th>City</th>
                                        <th>Department</th>
                                        <th>Particular</th>
                                        <th>Issue</th>
                                        <th>Raised By</th>
                                        <th>Date Opened</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>                      
                    </div>
                    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                        <div class="table-responisve">
                            <table class="table table-striped table-responsive"  style="width:100%;" id="ticket-table-c">
                                <thead>
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Title</th>
                                        <th>Location</th>
                                        <th>City</th>
                                        <th>Department</th>
                                        <th>Particular</th>
                                        <th>Issue</th>
                                        <th>Raised By</th>
                                        <th>Date Opened</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>                        
                    </div>
                    <div class="tab-pane fade" id="stale" role="tabpanel" aria-labelledby="contact-tab">
                        <div class="table-responisve">
                            <table class="table table-striped table-responsive"  style="width:100%;" id="ticket-table-d">
                                <thead>
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Title</th>
                                        <th>Location</th>
                                        <th>City</th>
                                        <th>Department</th>
                                        <th>Particular</th>
                                        <th>Issue</th>
                                        <th>Raised By</th>
                                        <th>Date Opened</th>
                                        <th>Day Opened</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="modal fade" id="viewData" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel"> Detailed </h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="detail-Body">

        </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(document).ready(function() {

            $('.export-flagged-items').on('click', function () {
                
                $.ajax({
                    url: "{{ route('export-flagged-items-export') }}",
                    type: 'GET',
                    xhrFields:{
                        responseType: 'blob'
                    },
                    data: {
                        startd: function() {
                            return $('#filterStart').val();
                        },
                        endd: function() {
                            return $('#filterEnd').val();
                        },
                        dom: function() {
                            return $('#filterDom').val();
                        },
                        store: function() {
                            return $('#filterStore').val();
                        },
                        state: function() {
                            return $('#filterState').val();
                        },
                        city: function() {
                            return $('#filterCity').val();
                        }
                    },
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function (response) {
                        var url = window.URL || window.webkitURL;
                            var objectUrl = url.createObjectURL(response);
                            var a = $("<a />", {
                                href: objectUrl,
                                download: "{{ date('d-m-Y-His') }}-flagged-items.pdf"
                            }).appendTo("body")
                            a[0].click()
                            a.remove()
                    },
                    complete: function (response) {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });           
                
            });
            
            $('.export-tickets').on('click', function () {
                
                $.ajax({
                    url: "{{ route('export-tickets') }}",
                    type: 'GET',
                    xhrFields:{
                        responseType: 'blob'
                    },
                    data: {
                        startd: function() {
                            return $('#tktStart').val();
                        },
                        endd: function() {
                            return $('#tktEnd').val();
                        },
                        dom: function() {
                            return $('#tktDoM').val();
                        },
                        store: function() {
                            return $('#tktLocation').val();
                        },
                        state: function() {
                            return $('#tktState').val();
                        },
                        city: function() {
                            return $('#tktCity').val();
                        },
                        dept: function() {
                            return $('#tktDeptartment').val();
                        },
                        status: function() {
                            return $('#tktStatus').val();
                        }
                    },
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function (response) {
                        var url = window.URL || window.webkitURL;
                            var objectUrl = url.createObjectURL(response);
                            var a = $("<a />", {
                                href: objectUrl,
                                download: "{{ date('d-m-Y-His') }}-flagged-items.pdf"
                            }).appendTo("body")
                            a[0].click()
                            a.remove()
                    },
                    complete: function (response) {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });  

            });

            let usersTable = new DataTable('#table2', {
                pageLength: 10,
                "aLengthMenu": [[10, 50, 100, 250], [10, 50, 100, 250]],
                ajax: {
                    url: "{{ route('flagged-items-dashboard') }}",
                    data: function ( d ) {
                        return $.extend( {}, d, {
                            startd : $('#filterStart').val(),
                            endd : $('#filterEnd').val(),
                            dom: $('#filterDom').val(),
                            store: $('#filterStore').val(),
                            state: $('#filterState').val(),
                            city: $('#filterCity').val()
                        });
                    }
                },
                processing: false,
                ordering: false,
                serverSide: true,
                columns: [
                    { data: 'item_name' },
                    { data: 'clistname' },
                    { data: 'dom_name' },
                    { data: 'location_name' },
                    { data: 'city_name' },
                    { data: 'state_name' },
                    { data: 'initial_status_name' },
                    { data: 'latest_status_name' },
                    { data: 'last_updated' }
                ],
                initComplete: function(settings) {

                }
            });

            $('#filterStart').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                    usersTable.ajax.reload();
                }
            });

            $('#filterEnd').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                    usersTable.ajax.reload();
                }
            });

            $('#filterState').select2({
                placeholder: 'Select State',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('state-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                usersTable.ajax.reload();
                $('#filterCity').val(null).trigger('change');
            });

            $('#filterCity').select2({
                placeholder: 'Select City',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('city-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            state: function() {
                                return $('#filterState').val();
                            },
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                usersTable.ajax.reload();
            });

            $('#filterDom').select2({
                placeholder: 'Select DOM',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('users-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            ignoreDesignation: 1,
                            roles: "{{ implode(',', [Helper::$roles['store-phone'],Helper::$roles['store-manager'],Helper::$roles['store-employee'], Helper::$roles['store-cashier'], Helper::$roles['divisional-operations-manager'], Helper::$roles['operations-manager'], Helper::$roles['head-of-department']  ]) }}",
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                usersTable.ajax.reload();
            });

            $('#filterSop').select2({
                placeholder: 'Select Checklist',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            }).on('change', function() {
                usersTable.ajax.reload();
            });

            $('#filterStore').select2({
                placeholder: 'Select location',
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('stores-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                usersTable.ajax.reload();
            });











            // Ticket Tabs

            let ticket1 = new DataTable('#ticket-table-a', {
                pageLength: 10,
                "aLengthMenu": [[10, 50, 100, 250], [10, 50, 100, 250]],
                ajax: {
                    url: "{{ route('get-ticket-listing') }}",
                    data: function ( d ) {
                        return $.extend( {}, d, {
                            mainstatus : 'pending',
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
                    { data: 'location_name' },
                    { data: 'city_name' },
                    { data: 'department_name' },
                    { data: 'priority_name' },
                    { data: 'issue_name' },
                    { data: 'dom_name' },
                    { data: 'date_opened' },
                    { data: 'status_name' }
                ],
                initComplete: function(settings) {

                }
            });
            let ticket2 = new DataTable('#ticket-table-b', {
                pageLength: 10,
                "aLengthMenu": [[10, 50, 100, 250], [10, 50, 100, 250]],
                ajax: {
                    url: "{{ route('get-ticket-listing') }}",
                    data: function ( d ) {
                        return $.extend( {}, d, {
                            mainstatus : 'accepted',                            
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
                    { data: 'location_name' },
                    { data: 'city_name' },
                    { data: 'department_name' },
                    { data: 'priority_name' },
                    { data: 'issue_name' },
                    { data: 'dom_name' },
                    { data: 'date_opened' },                    
                    { data: 'status_name' }
                ],
                initComplete: function(settings) {

                }
            });

            let ticketonhold = new DataTable('#ticket-table-onhold', {
                pageLength: 10,
                "aLengthMenu": [[10, 50, 100, 250], [10, 50, 100, 250]],
                ajax: {
                    url: "{{ route('get-ticket-listing') }}",
                    data: function ( d ) {
                        return $.extend( {}, d, {
                            mainstatus : 'in_progress',                            
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
                    { data: 'location_name' },
                    { data: 'city_name' },
                    { data: 'department_name' },
                    { data: 'priority_name' },
                    { data: 'issue_name' },
                    { data: 'dom_name' },
                    { data: 'date_opened' },                    
                    { data: 'status_name' }
                ],
                initComplete: function(settings) {

                }
            });

            let ticket3 = new DataTable('#ticket-table-c', {
                pageLength: 10,
                "aLengthMenu": [[10, 50, 100, 250], [10, 50, 100, 250]],
                ajax: {
                    url: "{{ route('get-ticket-listing') }}",
                    data: function ( d ) {
                        return $.extend( {}, d, {
                            mainstatus : 'closed',                            
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
                    { data: 'location_name' },
                    { data: 'city_name' },
                    { data: 'department_name' },
                    { data: 'priority_name' },
                    { data: 'issue_name' },
                    { data: 'dom_name' },
                    { data: 'date_opened' },                    
                    { data: 'status_name' }
                ],
                initComplete: function(settings) {

                }
            });

            let ticket4 = new DataTable('#ticket-table-d', {
                pageLength: 10,
                "aLengthMenu": [[10, 50, 100, 250], [10, 50, 100, 250]],
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
                    { data: 'location_name' },
                    { data: 'city_name' },
                    { data: 'department_name' },
                    { data: 'priority_name' },
                    { data: 'issue_name' },
                    { data: 'dom_name' },
                    { data: 'date_opened' },                    
                    { data: 'opened' },                    
                    { data: 'status_name' }
                ],
                initComplete: function(settings) {

                }
            });

            $('#tktStart').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                    ticket1.ajax.reload();
                    ticket2.ajax.reload();
                    ticket3.ajax.reload();
                    ticket4.ajax.reload();
                    ticketonhold.ajax.reload();
                }
            });

            $('#tktEnd').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                    ticket1.ajax.reload();
                    ticket2.ajax.reload();
                    ticket3.ajax.reload();
                    ticket4.ajax.reload();
                    ticketonhold.ajax.reload();
                }
            });

            $('#tktDoM').select2({
                placeholder: 'Select DOM',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('users-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            ignoreDesignation: 1,
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                    ticket1.ajax.reload();
                    ticket2.ajax.reload();
                    ticket3.ajax.reload();
                    ticket4.ajax.reload();
                    ticketonhold.ajax.reload();
            });

            $('#tktState').select2({
                placeholder: 'Select State',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('state-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                    ticket1.ajax.reload();
                    ticket2.ajax.reload();
                    ticket3.ajax.reload();
                    ticket4.ajax.reload();
                    ticketonhold.ajax.reload();
                $('#filterCity').val(null).trigger('change');
            });

            $('#tktCity').select2({
                placeholder: 'Select City',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('city-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            state: function() {
                                return $('#filterState').val();
                            },
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                    ticket1.ajax.reload();
                    ticket2.ajax.reload();
                    ticket3.ajax.reload();
                    ticket4.ajax.reload();
                    ticketonhold.ajax.reload();
            });

            $('#tktLocation').select2({
                placeholder: 'Select location',
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('stores-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                    ticket1.ajax.reload();
                    ticket2.ajax.reload();
                    ticket3.ajax.reload();
                    ticket4.ajax.reload();
                    ticketonhold.ajax.reload();
            });

            $('#tktDeptartment').select2({
                placeholder: 'Select Department',
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('departments-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                    ticket1.ajax.reload();
                    ticket2.ajax.reload();
                    ticket3.ajax.reload();
                    ticket4.ajax.reload();
                    ticketonhold.ajax.reload();
            });

            $('#tktStatus').select2({
                placeholder: 'Select location',
                width: '100%',
                theme: 'classic'
            }).on('change', function() {
                    ticket1.ajax.reload();
                    ticket2.ajax.reload();
                    ticket3.ajax.reload();
                    ticket4.ajax.reload();
                    ticketonhold.ajax.reload();
            });
            // Ticket Tabs

        });
    </script>
@endpush