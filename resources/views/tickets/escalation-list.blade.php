@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
<link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="bg-light p-4 rounded">
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $page_title }}</h1>
        @can('ticket-escalations.create')
            <a href="{{ route('ticket-escalations.create') }}" class="btn btn-success">Create Escalation</a>
        @endcan
    </div>

    <div class="mt-2">
        @include('layouts.partials.messages')
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="filterDepartment" class="form-label">Department</label>
                    <select id="filterDepartment"></select>
                </div>
                <div class="col-md-4">
                    <label for="filterParticular" class="form-label">Particular</label>
                    <select id="filterParticular"></select>
                </div>
                <div class="col-md-4">
                    <label for="filterIssue" class="form-label">Issue</label>
                    <select id="filterIssue"></select>
                </div>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-md-2 d-flex align-items-end">
                    <button id="filter-data" class="btn btn-secondary me-2"> Search </button>
                    <button id="resetFilters" class="btn btn-danger d-none"> Clear </button>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <table class="table table-striped" id="pendingEscalationsTable" style="width:100%;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Department</th>
                    <th>Particular</th>
                    <th>Issue</th>
                    <th>Level 1</th>
                    <th>Level 2</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>

</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
<script>
    const csrfToken = "{{ csrf_token() }}";
    $(document).ready(function(){
        const departmentFilter = $('#filterDepartment').select2({
            placeholder: 'Please Select', allowClear: true, width: '100%', theme: 'classic',
            ajax: { url: "{{ route('departments-list') }}", type: 'POST', dataType: 'json', delay: 250,
                data: function (params) { return { searchQuery: params.term, page: params.page || 1, _token: csrfToken }; },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return { results: $.map(data.items, function (item) { return { id: item.id, text: item.text }; }), pagination: { more: data.pagination.more } };
                }, cache: true }
        });

        const particularFilter = $('#filterParticular').select2({
            placeholder: 'Please Select', allowClear: true, width: '100%', theme: 'classic',
            ajax: { url: "{{ route('particulars-list') }}", type: 'POST', dataType: 'json', delay: 250,
                data: function (params) { return { searchQuery: params.term, page: params.page || 1, department_id: function () { return $('#filterDepartment').val(); }, select2: 'particulars', _token: csrfToken }; },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return { results: $.map(data.items, function (item) { return { id: item.id, text: item.text }; }), pagination: { more: data.pagination.more } };
                }, cache: true }
        });

        const issueFilter = $('#filterIssue').select2({
            placeholder: 'Please Select', allowClear: true, width: '100%', theme: 'classic',
            ajax: { url: "{{ route('issues-list') }}", type: 'POST', dataType: 'json', delay: 250,
                data: function (params) { return { searchQuery: params.term, page: params.page || 1, particular_id: function () { return $('#filterParticular').val(); }, _token: csrfToken }; },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return { results: $.map(data.items, function (item) { return { id: item.id, text: item.text }; }), pagination: { more: data.pagination.more } };
                }, cache: true }
        });

        $('#resetFilters').on('click', function () {
            departmentFilter.val(null).trigger('change');
            particularFilter.val(null).trigger('change');
            issueFilter.val(null).trigger('change');
            reloadTables();
            $('#resetFilters').addClass('d-none');
        });

        $(document).on('click', '#filter-data', function () { reloadTables(); $('#resetFilters').removeClass('d-none'); });

        const tableOptions = function () {
            return {
                processing: true,
                serverSide: true,
                searching: true,
                lengthChange: true,
                pageLength: 50,
                ajax: {
                    url: "{{ route('ticket-escalations.index') }}",
                    data: function (d) {
                        d.department_id = $('#filterDepartment').val();
                        d.particular_id = $('#filterParticular').val();
                        d.issue_id = $('#filterIssue').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'department', name: 'department', orderable: false, searchable: false },
                    { data: 'particular', name: 'particular', orderable: false, searchable: false },
                    { data: 'issue', name: 'issue', orderable: false, searchable: false },
                    { data: 'level1', name: 'level1', orderable: false, searchable: false },
                    { data: 'level2', name: 'level2', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']]
            };
        };

        const pendingTable = $('#pendingEscalationsTable').DataTable(tableOptions());

        function reloadTables() {
            pendingTable.ajax.reload(null, false);
        }

        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
            pendingTable.columns.adjust();
        });

        $(document).on('submit', '.escalationDeleteForm', function () {
            return confirm('Are you sure you want to delete this escalation?');
        });
    });
</script>
@endpush
