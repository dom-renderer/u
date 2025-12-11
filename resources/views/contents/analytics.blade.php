@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
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

            <div class="col-4 col-xl-4 col-lg-6">
                <label class="col-form-label" for="content-filter"> Content</label>
                <select id="content-filter">
                </select>
            </div>

            <div class="col-4 col-xl-4 col-lg-6">
                <label class="col-form-label" for="user-filter"> User </label>
                <select id="user-filter">
                </select>
            </div>

            <div class="col-2 col-xl-2 col-lg-6">
                <label class="col-form-label" for="status-filter"> Status </label>
                <select id="status-filter">
                    <option value="" selected disabled></option>
                    <option value="pending"> Not Watched </option>
                    <option value="partially"> Partially Watched </option>
                    <option value="completed"> Fully Watched </option>
                </select>
            </div>

            <div class="col-2 col-xl-2 col-lg-6">
                <button id="filter-data" class="btn btn-secondary" style="position: relative;top:34px;"> Search </button>
                <button id="filter-data-clear" class="btn btn-danger d-none" style="position: relative;top:34px;"> Clear </button>
            </div>
        </div>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                <table class="table table-striped" id="role-table" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>Content</th>
                        <th>Video</th>
                        <th>User</th>
                        <th>Viewd Percentage</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        

    </div> 
@endsection


@push('js')
<script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
    
    $(document).ready(function($){



        let usersTable = new DataTable('#role-table', {
            pageLength: 50,
            ajax: {
                url: "{{ route('content-analytics') }}",
                data: function ( d ) {
                    return $.extend( {}, d, {
                        content: function () {
                            return $('#content-filter').val();
                        },
                        user: function () {
                            return $('#user-filter').val();
                        },
                        status: function () {
                            return $('#status-filter').val();
                        }
                    });
                }
            },
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                 { data: 'cntnt' },
                 { data: 'vid' },
                 { data: 'usr' },
                 { data: 'percent' },
                 { data: 'status' }
            ],
            initComplete: function(settings) {

            }
        });

        $(document).on('click', '#filter-data', function () {
            usersTable.ajax.reload();

            let user = $('#user-filter').val();
            let content = $('#content-filter').val();
            let status = $('#status-filter').val();
            
            if (anyIsset(user, content, status)) {
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
            $('#content-filter').val(null).trigger('change');
            $('#status-filter').val(null).trigger('change');

            usersTable.ajax.reload();
        });


        $('#content-filter').select2({
            placeholder: "Select a Content",
            allowClear: true,
            width: "100%",
            theme: 'classic',
            ajax: {
                url: "{{ route('content-list') }}",
                type: "POST",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,  
                        _token: "{{ csrf_token() }}"
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

        $('#status-filter').select2({
            placeholder: 'Select status',
            allowClear: true,
            width: '100%',
            theme: 'classic'
        });

        
    });
 </script>  
@endpush