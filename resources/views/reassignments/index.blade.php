@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<style>
    .select2-selection--multiple {
        height: 40px!important;
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
            <div class="col-2 col-xl-2 col-lg-3">
                <label class="col-form-label" for="maker-filter"> Employee (Maker) </label>
                <select id="maker-filter" multiple>
                </select>
            </div>
            <div class="col-2 col-xl-2 col-lg-3">
                <label class="col-form-label" for="checker-filter"> Employee (Checker) </label>
                <select id="checker-filter" multiple>
                </select>
            </div>
            <div class="col-2 col-xl-2 col-lg-3">
                <label class="col-form-label" for="checklist-locations"> Locations </label>
                <select id="checklist-locations" multiple>
                </select>
            </div>
            <div class="col-4 col-xl-4 col-lg-3">
                <label class="col-form-label" for="checklist-filter"> Checklist </label>
                <select id="checklist-filter" multiple>
                </select>
            </div>
            <div class="col-2 col-xl-2 col-lg-6">
                <label class="col-form-label" for="date-filter"> Task Dates </label>
                <select id="date-filter">
                </select>
            </div>
            <div class="col-2 col-xl-2 col-lg-3">
                <label class="col-form-label" for="status-filter"> Status </label>
                <select id="status-filter">
                    <option value="" selected disabled></option>
                    <option value="pending"> Pending </option>
                    <option value="completed"> Completed </option>
                </select>
            </div>

            <div class="col-2 col-xl-2 col-lg-3 mt-1">
                <button id="filter-data" class="btn btn-secondary" style="position: relative;top:34px;"> Search </button>
                <button id="filter-data-clear" class="btn btn-danger d-none" style="position: relative;top:34px;"> Clear </button>
            </div>
        </div>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table table-striped" id="role-table" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>Code</th>
                            <th>Maker</th>
                            <th>Checker</th>
                            <th>Checklist</th>
                            <th>Location</th>
                            <th>Total Reassignments</th>
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
<script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
    
    $(document).ready(function($){

        let usersTable = new DataTable('#role-table', {
            pageLength: 50,
            ajax: {
                url: "{{ route('reassignments.index') }}",
                data: function ( d ) {
                    return $.extend( {}, d, {
                        loc: $('#checklist-locations').val(),
                        maker: $('#maker-filter').val(),
                        checker: $('#checker-filter').val(),
                        checklist: $('#checklist-filter').val(),
                        date: function () {
                            return $('#date-filter').val();
                        },
                        status: function () {
                            return $('#status-filter').val();
                        }
                    });
                }
            },
            searching: false,
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                 { data: 'code' },
                 { data: 'user_name' },
                 { data: 'checker_user_name' },
                 { data: 'checklist_name' },
                 { data: 'location_name' },                 
                 { data: 'total_reassingments' },
                 { data: 'status' },
                 { data: 'action' }
            ],
            initComplete: function(settings) {

            }
        });

        $(document).on('click', '#filter-data', function () {
            usersTable.ajax.reload();

            let locsFilter = $('#checklist-locations').val();
            let maker = $('#maker-filter').val();
            let checker = $('#checker-filter').val();
            let checklist = $('#checklist-filter').val();
            let date = $('#date-filter').val();
            let status = $('#status-filter').val();
            
            if (anyIsset(locsFilter, maker, checker, checklist, status, date)) {
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
            $('#maker-filter').val(null).trigger('change');
            $('#checker-filter').val(null).trigger('change');
            $('#checklist-filter').val(null).trigger('change');
            $('#date-filter').val(null).trigger('change');
            $('#status-filter').val(null).trigger('change');

            usersTable.ajax.reload();
        });

        $('#date-filter').select2({
            placeholder: 'Select date',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('checklist-dates-list') }}",
                type: "POST",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,  
                        _token: "{{ csrf_token() }}",
                        checklist_id: function () {
                            return $('#checklist-filter option:selected').val();
                        }
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

        $('#status-filter').select2({
            placeholder: 'Select status',
            allowClear: true,
            width: '100%',
            theme: 'classic'
        });

        $('#maker-filter').select2({
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

        $('#checker-filter').select2({
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


        
    });
 </script>
@endpush
