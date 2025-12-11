@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/datatables/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/datatables/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}">
@endpush

@section('content')
<div class="bg-light p-4 rounded">
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $page_title }}</h1>
        <div>
            <button id="exportTicketsBtn" class="btn btn-primary me-2">
                <i class="bi bi-download"></i> Export to Excel
            </button>
            @can('ticket-management.create')
                <a href="{{ route('ticket-management.create') }}" class="btn btn-success">Create Ticket</a>
            @endcan
        </div>
    </div>

    <div class="mt-2">
        @include('layouts.partials.messages')
    </div>

<div class="accordion accordion-flush" id="accordionFlushExample">
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
        Filters
      </button>
    </h2>
    <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
      <div class="accordion-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="filterDepartment" class="form-label">Department</label>
                <select id="filterDepartment"></select>
            </div>
            <div class="col-md-3">
                <label for="filterParticular" class="form-label">Particular</label>
                <select id="filterParticular"></select>
            </div>
            <div class="col-md-3">
                <label for="filterIssue" class="form-label">Issue</label>
                <select id="filterIssue"></select>
            </div>
            <div class="col-md-3">
                <label for="filterStore" class="form-label"> Location </label>
                <select id="filterStore"></select>
            </div>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-3">
                <label for="filterCreatedBy" class="form-label">Ticket Created By</label>
                <select id="filterCreatedBy"></select>
            </div>
            <div class="col-md-3">
                <label for="filterAssignedTo" class="form-label"> Ticket Assigned To </label>
                <select id="filterAssignedTo"></select>
            </div>
            <div class="col-md-3">
                <label for="task-date-range-picker" class="form-label"> Date </label>
                <input type="text" id="task-date-range-picker" class="form-control" readonly />
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button id="filter-data" class="btn btn-secondary me-2"> Search </button>
                <button id="resetFilters" class="btn btn-danger d-none"> Clear </button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

    <ul class="nav nav-tabs mt-4" id="ticketTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="true">Pending <span id="pending-count">0</span> </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab" aria-controls="active" aria-selected="false">Accepted <span id="accepted-count">0</span></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="inprogress-tab" data-bs-toggle="tab" data-bs-target="#inprogress" type="button" role="tab" aria-controls="inprogress" aria-selected="false">In Progress <span id="inprogress-count">0</span></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="closed-tab" data-bs-toggle="tab" data-bs-target="#closed" type="button" role="tab" aria-controls="closed" aria-selected="false">Closed <span id="closed-count">0</span></button>
        </li>
    </ul>
    <div class="tab-content" id="ticketTabsContent">
        <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
            <div class="table-responsive mt-3">
                <table class="table table-striped" id="pendingTicketsTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Subject</th>
                            <th>Department</th>
                            <th>Particular</th>
                            <th>Issue</th>
                            <th>Location</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="inprogress" role="tabpanel" aria-labelledby="inprogress-tab">
            <div class="table-responsive mt-3">
                <table class="table table-striped" id="inprogressTicketsTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Subject</th>
                            <th>Department</th>
                            <th>Particular</th>
                            <th>Issue</th>
                            <th>Location</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="active" role="tabpanel" aria-labelledby="active-tab">
            <div class="table-responsive mt-3">
                <table class="table table-striped" id="activeTicketsTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Subject</th>
                            <th>Department</th>
                            <th>Particular</th>
                            <th>Issue</th>
                            <th>Location</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="closed" role="tabpanel" aria-labelledby="closed-tab">
            <div class="table-responsive mt-3">
                <table class="table table-striped" id="closedTicketsTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Subject</th>
                            <th>Department</th>
                            <th>Particular</th>
                            <th>Issue</th>
                            <th>Location</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="assignUsersModal" tabindex="-1" aria-labelledby="assignUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignUsersModalLabel">Accept & Assign</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="assignTicketEncryptedId" />
                <div class="mb-3">
                    <label for="assign_users" class="form-label">Assign Users</label>
                    <select id="assign_users" multiple style="width: 100%"></select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="assignUsersSaveBtn" class="btn btn-primary">Save</button>
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

    const csrfToken = "{{ csrf_token() }}";
    var startTaskDate = moment().startOf('month');
    var endTaskDate = moment().endOf('month');

    $(document).ready(function($){

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
        }); 

        const departmentFilter = $('#filterDepartment').select2({
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('departments-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        _token: csrfToken
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function (item) {
                            return { id: item.id, text: item.text };
                        }),
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            }
        });

        const particularFilter = $('#filterParticular').select2({
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('particulars-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        department_id: function () {
                            return $('#filterDepartment').val();
                        },
                        select2: 'particulars',
                        _token: csrfToken
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function (item) {
                            return { id: item.id, text: item.text };
                        }),
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            }
        });

        const issueFilter = $('#filterIssue').select2({
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('issues-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        particular_id: function () {
                            return $('#filterParticular').val();
                        },
                        _token: csrfToken
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function (item) {
                            return { id: item.id, text: item.text };
                        }),
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            }
        });

        const storeFilter = $('#filterStore').select2({
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('stores-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        _token: csrfToken
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function (item) {
                            return { id: item.id, text: item.text };
                        }),
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            }
        });

        const ticketAssignedTo = $('#filterAssignedTo').select2({
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('users-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        ignoreDesignation: 1,
                        _token: csrfToken
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function (item) {
                            return { id: item.id, text: item.text };
                        }),
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            }
        });

        const createdByFilter = $('#filterCreatedBy').select2({
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('users-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        ignoreDesignation: 1,
                        _token: csrfToken
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function (item) {
                            return { id: item.id, text: item.text };
                        }),
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            }
        });

        departmentFilter.on('change', function () {
            particularFilter.val(null).trigger('change');
            issueFilter.val(null).trigger('change');
        });

        particularFilter.on('change', function () {
            issueFilter.val(null).trigger('change');
        });

        $('#resetFilters').on('click', function () {
            departmentFilter.val(null).trigger('change');
            particularFilter.val(null).trigger('change');
            storeFilter.val(null).trigger('change');
            issueFilter.val(null).trigger('change');
            createdByFilter.val(null).trigger('change');
            ticketAssignedTo.val(null).trigger('change');
            reloadTables();
            $('#resetFilters').addClass('d-none');
        });



        $(document).on('click', '#filter-data', function () {
            reloadTables();
            $('#resetFilters').removeClass('d-none');
        });

        const tableOptions = function (tab) {
            return {
                processing: true,
                serverSide: true,
                searching: true,
                lengthChange: true,
                pageLength: 50,
                ajax: {
                    url: "{{ route('ticket-management.index') }}",
                    data: function (d) {
                        d.tab = tab;
                        d.department_id = $('#filterDepartment').val();
                        d.particular_id = $('#filterParticular').val();
                        d.issue_id = $('#filterIssue').val();
                        d.created_from = startTaskDate.format('DD-MM-YYYY');
                        d.created_to = endTaskDate.format('DD-MM-YYYY');
                        d.created_by = $('#filterCreatedBy').val();
                        d.assigned = $('#filterAssignedTo').val();
                        d.location = $('#filterStore').val();
                    }
                },
                columns: [
                    { data: 'ticket_number', name: 'ticket_number' },
                    { data: 'subject', name: 'subject' },
                    { data: 'department', name: 'department', orderable: false, searchable: false },
                    { data: 'particular', name: 'particular', orderable: false, searchable: false },
                    { data: 'issue', name: 'issue', orderable: false, searchable: false },
                    { data: 'operator', name: 'operator', orderable: false, searchable: false },
                    { data: 'priority', name: 'priority', orderable: false, searchable: false },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'created_by', name: 'created_by', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                drawCallback: function (settings) {
                    $('#pending-count').text(`(${pendingTable.page.info().recordsTotal})`);
                    $('#accepted-count').text(`(${activeTable.page.info().recordsTotal})`);
                    $('#inprogress-count').text(`(${inProgressTable.page.info().recordsTotal})`);
                    $('#closed-count').text(`(${closedTable.page.info().recordsTotal})`);
                }
            };
        };

        const pendingTable = $('#pendingTicketsTable').DataTable(tableOptions('pending'));
        const activeTable = $('#activeTicketsTable').DataTable(tableOptions('active'));
        const inProgressTable = $('#inprogressTicketsTable').DataTable(tableOptions('inprogress'));
        const closedTable = $('#closedTicketsTable').DataTable(tableOptions('closed'));

        function reloadTables() {
            pendingTable.ajax.reload(null, false);
            activeTable.ajax.reload(null, false);
            inProgressTable.ajax.reload(null, false);
            closedTable.ajax.reload(null, false);
        }

        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
            pendingTable.columns.adjust();
            activeTable.columns.adjust();
            inProgressTable.columns.adjust();
            closedTable.columns.adjust();
        });

        $(document).on('submit', '.acceptTicketForm', function () {
            return confirm('Are you sure you want to accept this ticket?');
        });

        var assignUsersModal = new bootstrap.Modal(document.getElementById('assignUsersModal'));
        const assignUsersSelect = $('#assign_users').select2({
            placeholder: 'Select users',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#assignUsersModal'),
            theme: 'classic',
            ajax: {
                url: "{{ route('users-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        ignoreDesignation: 1,
                        _token: csrfToken
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function (item) {
                            return { id: item.id, text: item.text };
                        }),
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            }
        });

        $(document).on('click', 'a.dropdown-item.text-info', function (e) {
            const href = $(this).attr('href');
            const encId = $(this).data('tid');
            if (!href) return;
            e.preventDefault();

            $('#assignTicketEncryptedId').val(encId || '');
            assignUsersSelect.val(null).trigger('change');

            $.ajax({
                url: href,
                method: 'GET',
                dataType: 'json',
                success: function (resp) {
                    if (resp && Array.isArray(resp.users)) {
                        resp.users.forEach(function (u) {
                            var option = new Option(u.text, u.id, true, true);
                            $('#assign_users').append(option);
                        });
                        $('#assign_users').trigger('change');
                    }
                    assignUsersModal.show();
                },
                error: function () {
                    alert('Failed to load assigned users.');
                }
            });
        });

        $(document).on('click', '#assignUsersSaveBtn', function () {
            const encId = $('#assignTicketEncryptedId').val();
            if (!encId) {
                assignUsersModal.hide();
                return;
            }
            const url = `{{ url('tickets') }}/${encId}/assign-users`;
            const selectedUsers = $('#assign_users').val() || [];
            $.ajax({
                url: url,
                method: 'POST',
                dataType: 'json',
                data: {
                    _token: csrfToken,
                    users: selectedUsers
                },
                success: function () {
                    assignUsersModal.hide();
                    reloadTables();
                },
                error: function (xhr) {
                    let msg = 'Failed to save assigned users.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    alert(msg);
                }
            });
        });

        $('#exportTicketsBtn').on('click', function() {
            
            const params = new URLSearchParams({
                department_id: $('#filterDepartment').val() || '',
                particular_id: $('#filterParticular').val() || '',
                issue_id: $('#filterIssue').val() || '',
                location: $('#filterStore').val() || '',
                assigned: $('#filterAssignedTo').val() || '',
                created_from: startTaskDate.format('DD-MM-YYYY'),
                created_to: endTaskDate.format('DD-MM-YYYY'),
                created_by: $('#filterCreatedBy').val() || ''
            });

            for (const [key, value] of [...params.entries()]) {
                if (!value) {
                    params.delete(key);
                }
            }

            window.location.href = "{{ route('ticket-management.export') }}?" + params.toString();
        });
    });
</script>
@endpush