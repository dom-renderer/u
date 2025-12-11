@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/datatables/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/datatables/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}">
<style>
.bigger-check {
    height: 18px;
    width: 18px;
}
</style>
@endpush

@section('content')

    <div class="bg-light p-4 rounded">
        <h1>{{ $page_title }} </h1>
        <div class="lead">
            {{ $page_description }}
        </div>
        
        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>

        <div class="row">
            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="user-filter"> Employee (Maker) </label>
                <select id="user-filter" multiple>
                    @if(session()->has('scheduled_task_user'))
                        @foreach (session()->get('scheduled_task_user') as $thisUserId => $thisUserName)
                            <option value="{{ $thisUserId }}" selected> {{ $thisUserName }} </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="user-filter-checker"> Employee (Checker) </label>
                <select id="user-filter-checker" multiple>
                    @if(session()->has('scheduled_task_user_checker'))
                        @foreach (session()->get('scheduled_task_user_checker') as $thisUserId => $thisUserName)
                            <option value="{{ $thisUserId }}" selected> {{ $thisUserName }} </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="checklist-filter"> Checklist </label>
                <select id="checklist-filter" multiple>
                    @if(session()->has('scheduled_task_checklist'))
                        @foreach (session()->get('scheduled_task_checklist') as $thisChecklistId => $thisChecklistName)
                            <option value="{{ $thisChecklistId }}" selected> {{ $thisChecklistName }} </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="checklist-locations"> Locations </label>
                <select id="checklist-locations" multiple>
                    @if(session()->has('scheduled_task_loc'))
                        @foreach (session()->get('scheduled_task_loc') as $thisChecklistId => $thisChecklistName)
                            <option value="{{ $thisChecklistId }}" selected> {{ $thisChecklistName }} </option>
                        @endforeach
                    @endif
                </select>
            </div>

            @php
                $hasFSession = session()->has('scheduled_task_frequency');
                $FSession = session()->get('scheduled_task_frequency');
            @endphp

            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="frequency-filter"> Frequency </label>
                <select id="frequency-filter" multiple>
                    <option @if($hasFSession && in_array(12, $FSession)) selected @endif value="12"> Once </option>
                    <option @if($hasFSession && in_array(0, $FSession)) selected @endif value="0"> Every Hour </option>
                    <option @if($hasFSession && in_array(1, $FSession)) selected @endif value="1"> Every N Hours </option>
                    <option @if($hasFSession && in_array(2, $FSession)) selected @endif value="2"> Daily </option>
                    <option @if($hasFSession && in_array(3, $FSession)) selected @endif value="3"> Every N Days </option>
                    <option @if($hasFSession && in_array(4, $FSession)) selected @endif value="4"> Weekly </option>
                    <option @if($hasFSession && in_array(5, $FSession)) selected @endif value="5"> Biweekly </option>
                    <option @if($hasFSession && in_array(6, $FSession)) selected @endif value="6"> Monthly </option>
                    <option @if($hasFSession && in_array(7, $FSession)) selected @endif value="7"> Biomonthly </option>
                    <option @if($hasFSession && in_array(8, $FSession)) selected @endif value="8"> Quarterly </option>
                    <option @if($hasFSession && in_array(9, $FSession)) selected @endif value="9"> Semi Annually </option>
                    <option @if($hasFSession && in_array(10, $FSession)) selected @endif value="10"> Annually </option>
                    <option @if($hasFSession && in_array(11, $FSession)) selected @endif value="11"> Speicific Week Days </option>
                </select>
            </div>

            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="task-date-range-picker"> Date </label>
                <input type="text" id="task-date-range-picker" class="form-control" readonly />
            </div>

            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="status-filter"> Status </label>
                <select id="status-filter">
                    <option value=""></option>
                    <option value="0" @if(session()->has('scheduled_task_status') && 0 == session()->get('scheduled_task_status')) selected @endif> Pending </option>
                    <option value="1" @if(session()->has('scheduled_task_status') && 1 == session()->get('scheduled_task_status')) selected @endif> In-Progress </option>
                    <option value="2" @if(session()->has('scheduled_task_status') && 2 == session()->get('scheduled_task_status')) selected @endif> Pending Verification </option>
                    <option value="3" @if(session()->has('scheduled_task_status') && 3 == session()->get('scheduled_task_status')) selected @endif> Reassigned </option>
                    <option value="4" @if(session()->has('scheduled_task_status') && 4 == session()->get('scheduled_task_status')) selected @endif> Verifying </option>
                    <option value="5" @if(session()->has('scheduled_task_status') && 5 == session()->get('scheduled_task_status')) selected @endif> Verified </option>
                    <option value="6" @if(session()->has('scheduled_task_status') && 6 == session()->get('scheduled_task_status')) selected @endif> Completed </option>
                </select>
            </div>
            
            <div class="col-2 col-lg-3 col-xl-3 col-xxl-2">
                <label class="col-form-label" for="cancellation-filter"> Cancellation Status </label>
                <select id="cancellation-filter">
                    <option value=""> All </option>
                    <option value="1" @if(session()->has('cancellation_status') && 1 == session()->get('cancellation_status')) selected @endif> Cancelled </option>
                    <option value="2" @if(session()->has('cancellation_status') && 2 == session()->get('cancellation_status')) selected @endif> Non Cancelled </option>
                </select>
            </div>

            <div class="col-2 col-lg-6 col-xl-6 mt-1 col-xxl-2">
                <button id="filter-data" class="btn btn-secondary me-2" style="position: relative;top:34px;"> Search </button>
                <button id="filter-data-clear" class="btn btn-danger @if(!(session()->has('scheduled_task_user') || session()->has('scheduled_task_checklist') || session()->has('scheduled_task_frequency') || session()->has('scheduled_task_from') || session()->has('scheduled_task_to') || session()->has('scheduled_task_status'))) d-none @endif" style="position: relative;top:34px;"> Clear </button>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <button id="delete-selected" class="btn btn-danger btn-sm float-end" style="display: none;">Delete</button>
            </div>
        </div>
        
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table table-striped" id="role-table" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th><input type="checkbox" class="bigger-check" id="select-all"></th>
                            <th>Code</th>
                            <th>Location</th>
                            <th>Employee (Maker)</th>
                            <th>Employee (Checker)</th>
                            <th>Checklist</th>
                            <th>Date</th>
                            <th>Status</th>
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
@endsection


@push('js')
<script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/daterangepicker.min.js') }}"></script>
<script>
    
    $(document).ready(function($){

        @if(session()->has('scheduled_task_from') && !empty(session()->get('scheduled_task_from')))
            var startTaskDate = moment("{{ session()->get('scheduled_task_from') }}", 'DD-MM-YYYY');
        @else
            var startTaskDate = moment().startOf('month');
        @endif

        @if(session()->has('scheduled_task_to') && !empty(session()->get('scheduled_task_to')))
            var endTaskDate = moment("{{ session()->get('scheduled_task_to') }}", 'DD-MM-YYYY');
        @else
            var endTaskDate = moment().endOf('month');
        @endif

        function cb(start, end) {
            $('#task-date-range-picker').val(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
        }

        $('#task-date-range-picker').daterangepicker({
            startDate: startTaskDate,
            endDate: endTaskDate,
            locale: {
                format: 'DD-MM-YYYY'
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);

        cb(startTaskDate, endTaskDate);

        $('#task-date-range-picker').on('apply.daterangepicker', function(ev, picker) {
            startTaskDate = picker.startDate;
            endTaskDate = picker.endDate

            tasksDataTable.ajax.reload();
        });        

        $(document).on('click', '.deleteGroup', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this Scheduled Task?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).parents('form').submit();
                    return true;
                } else {
                    return false;
                }
            })
        });


        $.fn.dataTable.ext.errMode = 'none';
        
        let tasksDataTable = new DataTable('#role-table', {
            "aLengthMenu": [[50, 100, 250], [50, 100, 250]],
            ajax: {
                url: "{{ route('scheduled-tasks.index') }}",
                data: function ( d ) {
                    return $.extend( {}, d, {
                        locs: $('#checklist-locations').val(),
                        user: $('#user-filter').val(),
                        checker: $('#user-filter-checker').val(),
                        checklist: $('#checklist-filter').val(),
                        frequency: $('#frequency-filter').val(),
                        from : startTaskDate.format('DD-MM-YYYY'),
                        to : endTaskDate.format('DD-MM-YYYY'),
                        status: $('#status-filter').val(),
                        showCancelled: $('#cancellation-filter').val()
                    });
                },
                beforeSend: function () {
                    $('body').find('.LoaderSec').removeClass('d-none');
                },
                complete: function () {
                    $('body').find('.LoaderSec').addClass('d-none');
                }
            },
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `<input type="checkbox" class="row-checkbox bigger-check" value="${data}">`;
                    }
                },
                 { data: 'code' },
                 { data: 'store_name' },
                 { data: 'user_name' },
                 { data: 'checker_user_name' },
                 { data: 'checklist_name' },
                 { data: 'date' },
                 { data: 'status' },
                 { data: 'action' }
            ],
            initComplete: function(settings) {

            },
            createdRow: function(row, data, dataIndex) {
                if (data.cancelled == 1) {
                    $(row).find('td').css('background-color', '#f4433680').css('color', 'white');
                }
            }
        });

        $('#select-all').on('click', function() {
            let checked = this.checked;
            $('.row-checkbox').prop('checked', checked);
        });

        function getSelectedIds() {
            let ids = [];
            $('.row-checkbox:checked').each(function() {
                ids.push($(this).val());
            });
            return ids;
        }

        function toggleDeleteButton() {
            let selectedCount = $('.row-checkbox:checked').length;
            if (selectedCount > 0) {
                $('#delete-selected').show();
            } else {
                $('#delete-selected').hide();
            }
        }

        $(document).on('change', '.row-checkbox', function() {
            toggleDeleteButton();
        });

        $('#select-all').on('change', function() {
            $('.row-checkbox').prop('checked', this.checked);
            toggleDeleteButton();
        });

        $('#delete-selected').on('click', function() {
            let ids = getSelectedIds();
            if (ids.length === 0) {
                alert("No rows selected!");
                return;
            }

            if (confirm('Are you sure you want to delete selected tasks?')) {
                $.ajax({
                    url: "{{ route('scheduled-tasks.bulk-delete') }}",
                    method: 'POST',
                    data: {
                        ids: ids,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#select-all').prop('checked', false);
                        alert('Deleted successfully.');
                    },
                    error: function(xhr) {
                        alert('Something went wrong.');
                    },
                    complete: function () {
                        tasksDataTable.ajax.reload();
                    }
                });
            }
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
                        page: params.page || 1,  
                        get_all_for_checker: 1,
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

        $('#frequency-filter').select2({
            placeholder: 'Select Frequency',
            allowClear: true,
            width: '100%',
            theme: 'classic'
        });

        $('#status-filter').select2({
            placeholder: 'Select status',
            allowClear: true,
            width: '100%',
            theme: 'classic'
        });

        $('#cancellation-filter').select2({
            placeholder: 'Select cancellation status',
            allowClear: true,
            width: '100%',
            theme: 'classic'
        });

        $(document).on('click', '#filter-data', function () {
            tasksDataTable.ajax.reload();

            let locsFilter = $('#checklist-locations').val();
            let userFilter = $('#user-filter').val();
            let userFilterChecker = $('#user-filter-checker').val();
            let checklistFilter = $('#checklist-filter').val();
            let frequencytFilter = $('#frequency-filter').val();
            let status = $('#status-filter').val();
            let canecllationStatus = $('#cancellation-filter').val();

            if (anyIsset(userFilter, checklistFilter, frequencytFilter, status, userFilterChecker, locsFilter, canecllationStatus)) {
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
            $('#frequency-filter').val(null).trigger('change');
            $('#status-filter').val(null).trigger('change');
            $('#cancellation-filter').val(null).trigger('change');

            tasksDataTable.ajax.reload();
        });

        $(document).on('change', '.change-status', function () {
            let orderId = $(this).data('id');
            let status = $(this).val();
            let that = $(this);

            if (!confirm('Are you sure you want to change the status?')) {
                $(this).val($(this).data('last-selected'));
                return false;
            }

            $.ajax({
                url: "{{ route('task-status-change') }}",
                type: 'GET',
                data: {
                    id: orderId,
                    status: status
                },
                beforeSend: function () {
                    $('body').find('.LoaderSec').removeClass('d-none');
                },
                success: function (response) {
                    if (response.status) {
                        Swal.fire('success', 'Status updated successfully.', 'success');
                        tasksDataTable.ajax.reload();
                    } else {
                        $(that).val($(this).data('last-selected'));                            
                    }
                },
                complete: function (response) {
                    $('body').find('.LoaderSec').addClass('d-none');
                }
            });
        });
        

        $(document).on('click', '.reschedule-task', function(e) {
            e.preventDefault();

            const url = $(this).data('href');

            Swal.fire({
                title: 'Reschedule Task',
                html: `
                    <input type="text" id="reschedule-date" class="swal2-input" placeholder="Select date & time" readonly>
                    <textarea id="reschedule-remark" class="swal2-textarea" placeholder="Enter remark"></textarea>
                `,
                showCancelButton: true,
                confirmButtonText: 'Submit',
                didOpen: () => {
                    $('#reschedule-date').datetimepicker({
                        format:'d-m-Y H:i'
                    });
                },
                preConfirm: () => {
                    const date = $('#reschedule-date').val();
                    const remark = $('#reschedule-remark').val();

                    if (!date || !remark) {
                        Swal.showValidationMessage('Both date/time and remark are required.');
                        return false;
                    }

                    return fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ date, remark })
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error}`);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Success!', 'Task rescheduled.', 'success');
                    tasksDataTable.ajax.reload();
                }
            });
        });
       
        $(document).on('click', '.cancel-task', function(e) {
            e.preventDefault();

            const url = $(this).data('href');

            Swal.fire({
                title: 'Cancel Task',
                html: `
                    <textarea id="cancellation-remark" class="swal2-textarea" placeholder="Cancellation Note"></textarea>
                `,
                showCancelButton: true,
                confirmButtonText: 'Cancel Task',
                didOpen: () => {

                },
                preConfirm: () => {
                    const remark = $('#cancellation-remark').val();

                    if (!remark) {
                        Swal.showValidationMessage('Add cancellation note.');
                        return false;
                    }

                    return fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ remark })
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error}`);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Success!', 'Task rescheduled.', 'success');
                    tasksDataTable.ajax.reload();
                }
            });
        });

        $(document).on('click', '.cancellation-note', function(e) {
            e.preventDefault();

            const note = $(this).data('note');

            Swal.fire({
                title: 'Cancellation Note',
                html: `${note}`,
            });
        });

        $(document).on('click', '.edit-task-date', function(e) {
            e.preventDefault();

            const url = $(this).data('href');
            const thisDate = $(this).data('currdate');

            Swal.fire({
                title: 'Edit Task Date',
                html: `
                    <input type="text" id="edit-schedule-date" class="swal2-input" placeholder="Select date & time" value="${thisDate}">
                `,
                showCancelButton: true,
                confirmButtonText: 'Submit',
                didOpen: () => {
                    $('#edit-schedule-date').datetimepicker({
                        format:'d-m-Y H:i',
                        minDate: '+1'
                    });
                },
                preConfirm: () => {
                    const date = $('#edit-schedule-date').val();

                    if (!date) {
                        Swal.showValidationMessage('Date/time is required.');
                        return false;
                    }

                    return fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ date, '_method' : 'PUT' })
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error}`);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Success!', 'Task date changed successfully.', 'success');
                    tasksDataTable.ajax.reload();
                }
            });
        });



        $(document).on('click', '.alter-task-start-end-time', function(e) {
            e.preventDefault();

            const url = $(this).data('href');
            const startTime = $(this).data('current-start');
            const endTime = $(this).data('current-end');

            Swal.fire({
                title: 'Edit Task Start & End Time',
                html: `
                    <label class="form-label"> Start Time </label>
                    <input type="time" id="start-schedule-time" class="swal2-input" placeholder="Select start time" value="${startTime}">
                    </br> <label class="form-label"> End Time </label>
                    <input type="time" id="end-schedule-time" class="swal2-input" placeholder="Select end time" value="${endTime}">
                `,
                showCancelButton: true,
                confirmButtonText: 'Submit',
                preConfirm: () => {
                    const startD = $('#start-schedule-time').val();
                    const endD = $('#end-schedule-time').val();

                    if (!startD || !endD) {
                        Swal.showValidationMessage('Start & End time is required.');
                        return false;
                    }

                    const start = new Date(`1970-01-01T${startD}:00`);
                    const end = new Date(`1970-01-01T${endD}:00`);

                    if (end <= start) {
                        Swal.showValidationMessage('End time must be greater than start time.');
                        return false;
                    }

                    return fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ startD, endD, '_method': 'PUT' })
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error}`);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Success!', 'Task start & end time changed successfully.', 'success');
                    tasksDataTable.ajax.reload();
                }
            });
        });



    });
 </script>  
@endpush
