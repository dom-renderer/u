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

        .pagination-controls .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .page-info {
            font-weight: 500;
            color: #495057;
            white-space: nowrap;
        }

        .items-per-page {
            display: flex;
            align-items: center;
        }

        .pagination-controls {
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
            margin-top: 15px;
        }        
    </style>
@endpush

@section('content')
    <div class="row">



        <div class="">
            <!-- All Store Performance Insights -->
            <div class="section">
                <h2>Filters</h2>
                <div class="row mb-3">


                    <div class="col-md-2 col-lg-4 col-xl-2">
                        <label for="filterStart" class="form-label">Start Date</label>
                        <input type="text" id="filterStart" class="form-control" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('d-m-Y') }}">
                    </div>
                    
                    <div class="col-md-2 col-lg-4 col-xl-2">
                        <label for="filterEnd" class="form-label">End Date</label>
                        <input type="text" id="filterEnd" class="form-control" value="{{ date('d-m-Y') }}">
                    </div>

                    <div class="col-md-2 col-lg-4 col-xl-2">
                      <label for="filterSop" class="form-label">Checklist</label>
                      <select id="filterSop" class="form-select">
                          <option value="all" selected> All </option>
                          @foreach(\App\Models\DynamicForm::select('id', 'name')->inspection()->orderBy('ordering')->get() as $temp)
                            <option value="{{ $temp->id }}"> {{ $temp->name }} </option>
                          @endforeach
                      </select>
                  </div>

                    <div class="col-md-2 col-lg-4 col-xl-2">
                        <label for="filterDom" class="form-label">DoM</label>
                        <select id="filterDom" class="form-select">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-2 col-lg-4 col-xl-2">
                        <label for="filterState" class="form-label">State</label>
                        <select id="filterState">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-2 col-lg-4 col-xl-2">
                        <label for="filterCity" class="form-label">City</label>
                        <select id="filterCity">
                            <option value="all" selected> All </option>
                        </select>
                    </div>      

                       <div class="col-12">
                        <button class="btn btn-success w-100 mt-2" id="exportExcel"> Export </button>
                       </div>



                </div>
            </div>


            <div class="section">
                @if($reports->isEmpty())
                    <div class="alert alert-info">
                        No reports found.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" id="mrtable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Checklist</th>
                                    <th>DOM</th>
                                    <th>State</th>
                                    <th>City</th>
                                    <th>Generated at</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reports as $index => $report)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $report->user->name ?? '' }} {{ $report->user->middle_name ?? '' }} {{ $report->user->last_name ?? '' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($report->start_date)->format('d M Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($report->end_date)->format('d M Y') }}</td>
                                        <td>{{  $report->checklist != 'all' ? ($checklists[$report->checklist] ?? 'N/A') : 'all' }}</td>
                                        <td>{{ $report->dom != 'all' ? ($users[$report->dom] ?? 'N/A') : 'all' }}</td>
                                        <td>{{ $report->state }}</td>
                                        <td>{{ $report->city != 'all' ? $cities[$report->city] ?? 'N/A': 'all' }}</td>
                                        <td>{{ $report->created_at->format('d M Y, h:i A') }}</td>
                                        <td>
                                            <a class="btn btn-success btn-sm" href="{{ asset('storage/monthly-report/' . $report->file) }}" download="">
                                                Download
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif                
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

            new DataTable('#mrtable');

            $('#exportExcel').on('click', function () {
                let start = $('#filterStart').val();
                let end = $('#filterEnd').val();
                let sop = $('#filterSop').val();
                let dom = $('#filterDom').val();
                let state = $('#filterState').val();
                let city = $('#filterCity').val();

                $.ajax({
                    url: "{{ route('monthly-report-dom-checklists-export') }}",
                    type: 'GET',
                    cache: false,
                    xhrFields:{
                        responseType: 'blob'
                    },
                    data: {
                        start : start,
                        end : end,
                        sop : sop,
                        dom : dom,
                        state : state,
                        city : city
                    },
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function (response) {
                        var url = window.URL || window.webkitURL;
                        var objectUrl = url.createObjectURL(response);
                        var a = $("<a />", {
                            href: objectUrl,
                            download: "monthly-reports.xlsx"
                        }).appendTo("body")
                        a[0].click()
                        a.remove()
                    },
                    complete: function () {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });
            });

            $('#filterSop').select2({
                placeholder: 'Select Checklist',
                width: '100%',
                theme: 'classic'
            }).on('change', function() {
            });


            $('#filterStart').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                }
            });

            $('#filterEnd').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                }
            });

            $('#filterState').select2({
                placeholder: 'Select State',
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
                $('#filterCity').val(null).trigger('change');
            });

            $('#filterCity').select2({
                placeholder: 'Select City',
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
            });

            $('#filterDom').select2({
                placeholder: 'Select DOM',
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
            });

        });
    </script>
@endpush
