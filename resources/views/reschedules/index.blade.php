@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/datatables/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/datatables/dataTables.bootstrap5.min.css') }}">
@endpush

@section('content')

    <div class="bg-light p-4 rounded">
        <h1>{{ $page_title }} </h1>
        <div class="lead">
            {{ $page_description }}
        </div>
        
        <div class="row">
            <div class="col-lg-3">
                <label class="col-form-label ffkfjfk" for="user-filter"> Employee (Maker) </label>
                <select id="user-filter" multiple>
                </select>
            </div>

            <div class="col-lg-3">
                <label class="col-form-label" for="user-filter-checker"> Employee (Checker) </label>
                <select id="user-filter-checker" multiple>
                </select>
            </div>

            <div class="col-lg-3">
                <label class="col-form-label" for="checklist-filter"> Checklist </label>
                <select id="checklist-filter" multiple>
                </select>
            </div>

            <div class="col-lg-3">
                <label class="col-form-label" for="checklist-locations"> Locations </label>
                <select id="checklist-locations" multiple>
                </select>
            </div>

            <div class="col-lg-3">
                <label class="col-form-label" for="fromdate-filter"> From Date </label>
                <input type="text" id="fromdate-filter" class="form-control" placeholder="Select Date"/>
            </div>

            <div class="col-lg-3">
                <label class="col-form-label" for="todate-filter"> To Date </label>
                <input type="text" id="todate-filter" class="form-control" placeholder="Select Date" />
            </div>

            <div class="col-lg-3">
                <label class="col-form-label" for="status-filter"> Status </label>
                <select id="status-filter">
                    <option value=""></option>
                    <option value="0"> Pending </option>
                    <option value="1"> Approved </option>
                    <option value="2"> Rejected </option>
                </select>
            </div>

            <div class="col-lg-3 mt-1">
                <button id="filter-data" class="btn btn-secondary" style="position: relative;top:34px;"> Search </button>
                <button id="filter-data-clear" class="btn btn-danger d-none" style="position: relative;top:34px;"> Clear </button>
            </div>
        </div>

        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table table-striped" id="role-table" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>Task</th>
                            <th>Location</th>
                            <th>Checklist</th>
                            <th>Actual Task Date</th>
                            <th>Reschedule Date</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div> 


<div class="modal fade" id="remark-viwer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Rescheduling Remarks</h5>
      </div>
      <div class="modal-body" id="remark-viwer-container">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@endsection


@push('js')
<script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
    
    $(document).ready(function($){

        $('#fromdate-filter').datetimepicker({
            format:'d-m-Y',
            timepicker: false,
        });

        $('#todate-filter').datetimepicker({
            format:'d-m-Y',
            timepicker: false,
        });

        let usersTable = new DataTable('#role-table', {
            pageLength: 50,
            ajax: {
                url: "{{ route('reschedules') }}",
                data: function ( d ) {
                    return $.extend( {}, d, {
                        locs: $('#checklist-locations').val(),
                        user: $('#user-filter').val(),
                        checker: $('#user-filter-checker').val(),
                        checklist: $('#checklist-filter').val(),
                        from : $('#fromdate-filter').val(),
                        to : $('#todate-filter').val(),
                        status: $('#status-filter').val()
                    });
                }
            },
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                 { data: 'taskcode' },
                 { data: 'taskstorename' },
                 { data: 'taskchecklistname' },                
                 { data: 'actual_date' },
                 { data: 'res_date' },
                 { data: 'remarks' },
                 { data: 'action' }
            ],
            initComplete: function(settings) {

            }
        });

        $(document).on('shown.bs.modal', '#remark-viwer', function (e) {
            if (e.namespace == 'bs.modal') {
                $('#remark-viwer-container').html($(e.relatedTarget).data('remarks'));                
            }
        });

        $(document).on('click', '.approve-task', function(e) {
            e.preventDefault();

            const url = $(this).data('href');

            Swal.fire({
                title: 'Are you sure?',
                text: "This rescheduling will be approved.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, approve it!'
            }).then((result) => {
                if (result.isConfirmed) {

                    const hiddenData = {
                        status: 1
                    };

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(hiddenData)
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        usersTable.ajax.reload();
                        Swal.fire('Success!', 'Hidden data submitted.', 'success');
                    })
                    .catch(error => {
                        Swal.fire('Error!', 'Submission failed.', 'error');
                    });
                }
            });
        });

        $(document).on('click', '.disapprove-task', function(e) {
            e.preventDefault();

            const url = $(this).data('href');

            Swal.fire({
                title: 'Are you sure?',
                text: "This rescheduling will be rejected.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, reject it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const hiddenData = {
                        status: 2
                    };

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(hiddenData)
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        usersTable.ajax.reload();
                        Swal.fire('Success!', 'Hidden data submitted.', 'success');
                    })
                    .catch(error => {
                        Swal.fire('Error!', 'Submission failed.', 'error');
                    });
                }
            });
        });





        $('#user-filter').select2({
            placeholder: "Select a User",
            allowClear: true,
            width: "100%",
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
                        roles: "{{ implode(',', [Helper::$roles['store-phone'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier'], Helper::$roles['divisional-operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['operations-manager']]) }}"
                    }
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
        });
       
        $('#user-filter-checker').select2({
            placeholder: "Select a User",
            allowClear: true,
            width: "100%",
            theme: 'classic',
            ajax: {
                url: "{{ route('users-list') }}",
                type: "POST",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        get_all_for_checker: 1,
                        page: params.page || 1,
                        _token: "{{ csrf_token() }}",
                        ignoreDesignation: 1,
                        roles: "{{ implode(',', [Helper::$roles['store-phone'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier'], Helper::$roles['divisional-operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['operations-manager']]) }}"
                    }
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
        });

        $('#checklist-filter').select2({
            placeholder: 'Select Checklist',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('checklists-list') }}",
                type: "POST",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,  
                        type: 1,
                        _token: "{{ csrf_token() }}"
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
        });

        $('#checklist-locations').select2({
            placeholder: 'Select Locations',
            allowClear: true,
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
                        type: 1,
                        _token: "{{ csrf_token() }}"
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
        });

        $('#status-filter').select2({
            placeholder: 'Select status',
            allowClear: true,
            width: '100%',
            theme: 'classic'
        });

        $(document).on('click', '#filter-data', function () {
            usersTable.ajax.reload();

            let locsFilter = $('#checklist-locations').val();
            let userFilter = $('#user-filter').val();
            let userFilterChecker = $('#user-filter-checker').val();
            let checklistFilter = $('#checklist-filter').val();
            let fromDate = $('#frequency-filter').val();
            let toDate = $('#frequency-filter').val();
            let status = $('#status-filter').val();

            if (anyIsset(userFilter, checklistFilter, fromDate, toDate, status, userFilterChecker, locsFilter)) {
                $('#filter-data-clear').removeClass('d-none');
            } else {
                $('#filter-data-clear').addClass('d-none');
            }
        });

        $(document).on('click', '#filter-data-clear', function () {
            if (!$('#filter-data-clear').hasClass('d-none')) {
                $('#filter-data-clear').addClass('d-none');
            }

            $('#checklist-locations').val(null).trigger('change');
            $('#user-filter').val(null).trigger('change');
            $('#user-filter-checker').val(null).trigger('change');
            $('#checklist-filter').val(null).trigger('change');
            $('#fromdate-filter').val(null).trigger('change');
            $('#todate-filter').val(null).trigger('change');
            $('#status-filter').val(null).trigger('change');

            usersTable.ajax.reload();
        });
        
    });
 </script>  
@endpush
