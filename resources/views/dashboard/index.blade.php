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
    </style>
@endpush

@section('content')
    <div class="row">


        <div class="">
            <!-- Filters Section -->
            <div class="section">
                <h2>Filters</h2>
                <div class="row mb-3">
                    <div class="col-md-2 col-lg-3 col-xl-2">
                        <label for="filterStart" class="form-label">Start Date</label>
                        <input type="text" id="filterStart" class="form-control" value="{{ date('d-m-Y') }}">
                    </div>
                    <div class="col-md-2 col-lg-3 col-xl-2">
                        <label for="filterEnd" class="form-label">End Date</label>
                        <input type="text" id="filterEnd" class="form-control" value="{{ date('d-m-Y') }}">
                    </div>
                    <div class="col-md-2 col-lg-3 col-xl-2">
                        <label for="filterStore" class="form-label">Location</label>
                        <select id="filterStore">
                            <option value="all" selected> All </option>
                        </select>
                    </div>
                    <div class="col-md-3 col-lg-3 col-xl-3">
                        <label for="filterDom" class="form-label">DOM</label>
                        <select id="filterDom" class="form-select">
                            <option value="all" selected> All </option>
                        </select>
                    </div>
                    <div class="col-md-3 col-lg-3 col-xl-3">
                      <label for="filterSop" class="form-label">Checklist</label>
                      <select id="filterSop" class="form-select">
                          <option value="all" selected> All </option>
                          @foreach(\App\Models\DynamicForm::select('id', 'name')->inspection()->orderBy('ordering')->get() as $temp)
                            <option value="{{ $temp->id }}"> {{ $temp->name }} </option>
                          @endforeach
                      </select>
                  </div>
                </div>
            </div>

            <!-- Store Performance Insights -->
            <div class="section" style="height: 370px!important;">
                <h2>Score Comparison</h2>
                <center>
                    <canvas id="complianceChart" style="width: 100%!important;max-height:400px!important;"></canvas>
                </center>
            </div>

            <!-- Flagged Items Table -->
            <div class="section">
                <h2>Flagged Items</h2>
                <div class="table-responisve">
                    <table class="table table-striped" id="table2">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Inspected By</th>
                                <th>Checklist Name</th>
                                <th>Total Flagged Items</th>
                                <th>View More</th>
                            </tr>
                        </thead>
                        <tbody id="table2body">
                        </tbody>
                    </table>
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
    <script src="{{ url('assets/js/chart.js') }}"></script>
    <script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(document).ready(function() {

            let usersTable = new DataTable('#table2', {
                pageLength: 50,
                serverSide: false,
                ordering: false
            });

            const ctx = document.getElementById('complianceChart').getContext('2d');
            const complianceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Location A', 'Location B', 'Location C'],
                    datasets: [
                        {
                            label: 'MAX SCORE',
                            data: [10],
                            backgroundColor: ['#8dc1e9']
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        zoom: {
                            zoom: {
                                wheel: {
                                    enabled: true,
                                },
                                pinch: {
                                    enabled: true
                                },
                                mode: 'xy',
                            }
                        }
                    }
                }
            });

            function getResult() {
                let start = $('#filterStart').val();
                let end = $('#filterEnd').val();
                let store = $('#filterStore').val();
                let dom = $('#filterDom').val();
                let sop = $('#filterSop').val();

                $.ajax({
                    url: "{{ route('dom-dashboard-2') }}",
                    type: 'GET',
                    data: {
                        start: start,
                        end: end,
                        store: store,
                        dom: dom,
                        sop: sop
                    },
                    beforeSend: function() {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {

                        const tempObj = [];

                        for (i = 0; i < response.data.bar_chart_data.length; i++) {
                            tempObj.push({'data': response.data.bar_chart_data[i], 'label' : response.data.bar_chart_label_bar[i], 'backgroundColor' : response.data.bar_chart_label_bar_color[i]});
                        }
                        
                        complianceChart.data.datasets = tempObj;
                        complianceChart.data.labels = response.data.bar_chart_label;
                        complianceChart.update();

                        $('.flagged-items-count').text(`${response.data.flagged_items}`);

                        $('#table2body').html(response.data.flagged_items_table);
                    },
                    complete: function(response) {
                        $('body').find('.LoaderSec').addClass('d-none');

                        if ($.fn.DataTable.isDataTable("#table2")) {
                            $("#table2").DataTable().destroy();
                        }

                        if ($('#table2 tbody tr').length > 0) {
                            usersTable = DataTable('#table2', {
                                serverSide: false,
                                ordering: false
                            });
                        }

                        usersTable.draw();
                    }
                });
            }

            getResult();

            $(document).on('click', '.open-detail', function () {
                let id = $(this).data('id')

                $.ajax({
                    url: "{{ route('view-flagged-items-2') }}",
                    type: 'GET',
                    data: {
                        id: id
                    },
                    beforeSend: function() {
                        $('body').find('.LoaderSec').removeClass('d-none');
                        $('#detail-Body').html('');
                    },
                    success: function(response) {
                        if (response.status) {
                            $('#detail-Body').html(response.html);
                        } else {
                            $('#detail-Body').html(`
                            <table class="table w-100 table-striped table-bordered">
                            <thead>
                               <tr> <th> Questions </th>
                                <th> Answer </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td align="center" colspan="2">
                                        No Data Found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        `);
                        }
                    },
                    complete: function(response) {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });
            });

            $('#filterStart').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                    getResult();
                }
            });

            $('#filterEnd').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                    getResult();
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
                getResult();
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
                getResult();
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
                            roles: "{{ implode(',', [Helper::$roles['store-phone'], Helper::$roles['store-manager'],Helper::$roles['store-employee'], Helper::$roles['store-cashier'], Helper::$roles['divisional-operations-manager'], Helper::$roles['operations-manager'], Helper::$roles['head-of-department']  ]) }}",
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
                getResult();
            });



            $('#filterSop').select2({
                placeholder: 'Select Checklist',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            }).on('change', function() {
                getResult();
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
                getResult();
            });
        });
    </script>
@endpush
