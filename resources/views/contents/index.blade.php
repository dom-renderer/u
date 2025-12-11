@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
@endpush

@section('content')

    <div class="bg-light p-4 rounded">
        <h1>{{ $page_title }} </h1>
        <div class="lead">
            {{ $page_description }}
            @if (auth()->user()->can('contents.create'))
                <a href="{{ route('contents.create') }}" class="btn btn-primary btn-sm float-end">Add Content</a>
            @endif
                <button type="button" class="btn btn-primary btn-sm btn-sort float-end" name="operation" value="sort" style="margin-right:20px;"> Sort Contents </button>            
        </div>
        
        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>

        <div class="row">
            <div class="col-2 col-lg-4 col-xl-2">
                <label class="col-form-label" for="category-filter"> Category </label>
                <select id="category-filter" multiple>
                </select>
            </div>

            <div class="col-2 col-lg-4 col-xl-2">
                <label class="col-form-label" for="status-filter"> Status </label>
                <select id="status-filter">
                    <option value="all" selected> All </option>
                    <option value="1"> Active </option>
                    <option value="2"> InActive </option>
                </select>
            </div>

            <div class="col-2 col-lg-4 col-xl-2">
                <label class="col-form-label" for="fromdate-filter"> Publish From </label>
                <input type="text" id="fromdate-filter" class="form-control" placeholder="Select Date" />
            </div>

            <div class="col-2 col-lg-6 col-xl-2">
                <label class="col-form-label" for="todate-filter"> Publish Date </label>
                <input type="text" id="todate-filter" class="form-control" placeholder="Select Date" />
            </div>

            <div class="col-2 col-lg-6 col-xl-2 ">
                <button id="filter-data" class="btn btn-secondary" style="position: relative;top:34px;"> Search </button>
                <button id="filter-data-clear" class="btn btn-danger d-none" style="position: relative;top:34px;"> Clear </button>
            </div>
        </div>

        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                <table class="table table-striped" id="contents-table" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Publish Date</th>
                        <th>Expiry Date</th>
                        <th>Action</th>
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
<script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
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

        $(document).on('click', '.status-changer', function(e) {
            e.preventDefault();
            Swal.fire({
                title: $(this).attr('data-description'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: $(this).attr('data-blable')
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).parents('form').submit();
                    return true;
                } else {
                    return false;
                }
            })
        });

        $(document).on('click', '.deleteGroup', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this Content?',
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

        let contentTable = new DataTable('#contents-table', {
            ajax: {
                url: "{{ route('contents.index') }}",
                data: function ( d ) {
                    return $.extend( {}, d, {
                        categories: $('#category-filter').val(),
                        from : $('#fromdate-filter').val(),
                        to : $('#todate-filter').val(),
                        status: $('#status-filter').val(),
                    });
                }
            },
            "aLengthMenu": [[50, 100, -1], [50, 100, 'All']],            
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                 { data: 'title' },
                 { data: 'cat', searchable: false },
                 { data: 'status' },
                 { data: 'pub_date' },
                 { data: 'exp_date' },
                 { data: 'action', searchable: false }
            ],
            initComplete: function(settings) {

            },
            createdRow: function(row, data, dataIndex) {
                $(row).attr('data-exclude', 'true')
                .attr('data-conid', data.id)
                .addClass('sortableTR');
            }            
        });

        $(document).on('click', '#filter-data', function () {
            contentTable.ajax.reload();

            let catFilter = $('#category-filter').val();
            let fromDate = $('#fromdate-filter').val();
            let toDate = $('#todate-filter').val();
            let status = $('#status-filter').val();

            if (anyIsset(catFilter, fromDate, toDate, status)) {
                $('#filter-data-clear').removeClass('d-none');
            } else {
                $('#filter-data-clear').addClass('d-none');
            }
        });

        $(document).on('click', '#filter-data-clear', function () {
            if (!$('#filter-data-clear').hasClass('d-none')) {
                $('#filter-data-clear').addClass('d-none');
            }

            $('#category-filter').val(null).trigger('change');
            $('#fromdate-filter').val(null).trigger('change');
            $('#todate-filter').val(null).trigger('change');
            $('#status-filter').val('all').trigger('change');

            contentTable.ajax.reload();
        });        

        $('#status-filter').select2({
            placeholder: "Select a status",
            width: '100%'
        });        
       
        $('#category-filter').select2({
            allowClear: true,
            placeholder: "Select a category",
            width: '100%',
            ajax: {
                url: "{{ route('topics-select2') }}",
                type: "POST",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        _token: "{{ csrf_token() }}",
                        onlyactive: 1
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
        
        function gatherRowData(that) {
            let allData = [];
            
            return new Promise(function(resolve, reject) {

                $(that).find("tr").each(function(index, el) {
                    var contId = $(el).attr('data-conid');
                    if (contId) {
                        allData.push(contId);
                    }
                });
                
                resolve(allData);
            });
        }

        $(".btn-sort").click(function() {
            alert("Drag and drop the contents to change their order.")
            $("#contents-table").sortable({
                items: '.sortableTR',
                cursor: 'move',
                axis: 'y',
                dropOnEmpty: false,
                start: function(e, ui) {
                    ui.item.addClass("selected");
                },
                stop: function(e, ui) {
                    ui.item.removeClass("selected");

                    gatherRowData(this).then(function(allData) {

                        $.ajax({
                            url: "{{ route('sort-contents') }}",
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                cat_ids: allData
                            },
                            success: function(response) {
                            }
                        });
                    }).catch(function(error) {

                    });
                }
            });
        });  

    });
 </script>  
@endpush