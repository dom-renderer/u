@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<style>
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

    label.error {
        color: red;
    }
</style>
@endpush

@section('content')

    <div class="bg-light p-4 rounded">
        <h1>{{ $page_title }} </h1>
        <div class="lead">
            {{ $page_description }}
            @if (auth()->user()->can('checklist-scheduling.create'))
                <a href="{{ route('checklist-scheduling.create') }}" class="btn btn-primary btn-sm float-end">Schedule</a>
            @endif
        </div>
        
        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>

        <div class="row">

            <div class="col-2">
                <label class="col-form-label" for="checklist-filter"> Checklist </label>
                <select id="checklist-filter" multiple>
                    @if(session()->has('scheduled_checklist'))
                        @foreach (session()->get('scheduled_checklist') as $thisChecklistId => $thisChecklistName)
                            <option value="{{ $thisChecklistId }}" selected> {{ $thisChecklistName }} </option>
                        @endforeach
                    @endif
                </select>
            </div>

            @php
                $hasFSession = session()->has('scheduled_frequency');
                $FSession = session()->get('scheduled_frequency');
            @endphp

            <div class="col-2">
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
                    <option @if($hasFSession && in_array(7, $FSession)) selected @endif value="7"> Bimonthly </option>
                    <option @if($hasFSession && in_array(8, $FSession)) selected @endif value="8"> Quarterly </option>
                    <option @if($hasFSession && in_array(9, $FSession)) selected @endif value="9"> Semi Annually </option>
                    <option @if($hasFSession && in_array(10, $FSession)) selected @endif value="10"> Annually </option>
                    <option @if($hasFSession && in_array(11, $FSession)) selected @endif value="11"> Speicific Week Days </option>
                </select>
            </div>

            <div class="col-2">
                <label class="col-form-label" for="location-filter"> Location </label>
                <select id="location-filter" multiple>
                </select>
            </div>

            <div class="col-2">
                <label class="col-form-label" for="maker-filter"> Maker </label>
                <select id="maker-filter" multiple>
                </select>
            </div>

            <div class="col-2">
                <label class="col-form-label" for="checker-filter"> Checker </label>
                <select id="checker-filter" multiple>
                </select>
            </div>

            <div class="col-2">
                <button id="filter-data" class="btn btn-secondary" style="position: relative;top:34px;"> Search </button>
                <button id="filter-data-clear" class="btn btn-danger @if(!(session()->has('scheduled_user') || session()->has('scheduled_checklist') || session()->has('scheduled_frequency'))) d-none @endif" style="position: relative;top:34px;"> Clear </button>
            </div>

            @if(request()->has('template'))
            <div class="col-6">
                <a href="{{ route('checklist-scheduling.index') }}" class="btn btn-success float-end" style="position: relative;top:34px;"> List all </a>
            </div>
            @endif
        </div>
        
        <div class="mt-2 mb-2">
            <button id="bulk-delete-btn" class="btn btn-danger d-none">Delete Selected</button>
        </div>

        <table class="table table-striped" id="role-table" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th>Checklist</th>
                    <th>Locations</th>
                    <th>Makers</th>
                    <th>Checker</th>
                    <th>Frequency</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

        

    </div> 
@endsection


@push('js')
<script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
    
    $(document).ready(function($){
        $(document).on('click', '.deleteGroup', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this Checklist scheduling?',
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

        let usersTable = new DataTable('#role-table', {
            pageLength: 50,
            ajax: {
                url: "{{ route('checklist-scheduling.index') }}",
                data: function ( d ) {
                    return $.extend({}, d, {
                        id: "{{ $id }}",
                        frequency: function () {
                            return $('#frequency-filter').val();
                        },
                        checklist: function () {
                            return $('#checklist-filter').val();
                        },
                        locations: function () {
                            return $('#location-filter').val();
                        },
                        makers: function () {
                            return $('#maker-filter').val();
                        },
                        checkers: function () {
                            return $('#checker-filter').val();
                        }
                    });
                }
            },
            processing: false,
            searching: false,
            ordering: false,
            serverSide: true,
            columns: [
                {
                    data: 'id',
                    render: function(data) {
                        return `<input type="checkbox" class="row-checkbox" value="${data}">`;
                    },
                    orderable: false,
                    searchable: false
                },
                { data: 'checklist_name' },
                { data: 'locs' },
                { data: 'mks' },
                { data: 'chk' },
                { data: 'freq' },
                { data: 'action' }
            ],
            initComplete: function() {
                $('#select-all').on('click', function() {
                    $('.row-checkbox').prop('checked', this.checked).trigger('change');
                });
            }
        });

        $(document).on('change', '.row-checkbox', function () {
            const allChecked = $('.row-checkbox').length === $('.row-checkbox:checked').length;
            $('#select-all').prop('checked', allChecked);

            $('#bulk-delete-btn').toggleClass('d-none', $('.row-checkbox:checked').length === 0);
        });

        $('#bulk-delete-btn').on('click', function () {
            let ids = $('.row-checkbox:checked').map(function () {
                return $(this).val();
            }).get();

            if (ids.length === 0) return;

            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete ${ids.length} scheduled checklists.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete them!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('checklist-scheduling.bulk-delete') }}",
                        method: 'POST',
                        data: {
                            ids: ids,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire('Deleted!', response.message, 'success');
                            usersTable.ajax.reload();
                            $('#bulk-delete-btn').addClass('d-none');
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        }
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
                        roles: "{{ implode(',', [Helper::$roles['store-phone'], Helper::$roles['store-manager'],Helper::$roles['store-employee'], Helper::$roles['store-cashier'], Helper::$roles['divisional-operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['operations-manager']  ]) }}"
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
                        _token: "{{ csrf_token() }}",
                        type: 1
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

        $('#location-filter').select2({
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

        $('#maker-filter').select2({
            placeholder: 'Select Makers',
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
                        roles: "{{ implode(',', [Helper::$roles['store-phone'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier'], Helper::$roles['divisional-operations-manager'], Helper::$roles['operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['admin']]) }}"
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
            placeholder: 'Select Checkers',
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
                        roles: "{{ implode(',', [Helper::$roles['store-phone'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier'], Helper::$roles['divisional-operations-manager'], Helper::$roles['operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['admin']]) }}"
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

        $(document).on('click', '#filter-data', function () {
            usersTable.ajax.reload();

            let userFilter = $('#user-filter').val();
            let checklistFilter = $('#checklist-filter').val();
            let frequencytFilter = $('#frequency-filter').val();

            if (userFilter !== '' || checklistFilter !== '' || frequencytFilter != '') {
                $('#filter-data-clear').removeClass('d-none');
            } else {
                $('#filter-data-clear').addClass('d-none');
            }
        });

        $(document).on('click', '#filter-data-clear', function () {
            if (!$('#filter-data-clear').hasClass('d-none')) {
                $('#filter-data-clear').addClass('d-none');
            }

            $('#user-filter').val(null).trigger('change');
            $('#checklist-filter').val(null).trigger('change');
            $('#frequency-filter').val(null).trigger('change');

            usersTable.ajax.reload();
        });
        
    });
 </script>  
@endpush
