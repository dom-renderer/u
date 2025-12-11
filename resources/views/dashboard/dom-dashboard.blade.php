@extends('layouts.app-master')

@push('css')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
    <style>
        .section-d, .section-d2 {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
            background-color: #f8f9fa;
            box-shadow: 0 2px 4px #0000001a;
        }

        .section-d h2, .section-d2 h2 {
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

        .chartScroll {
            overflow: auto;
            scrollbar-width: thin;
            scrollbar-color: #065e2eb0 #f0f0f0;
        }

        .chartScroll::-webkit-scrollbar {
            width: 10px;
            height: 20px;
        }

        .chartScroll::-webkit-scrollbar-track {
            background: #f0f0f0;
            border-radius: 10px;
        }

        .chartScroll::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #065e2eb0, #065e2e);
            border-radius: 10px;
            border: 2px solid #f0f0f0;
            transition: background 0.3s ease;
        }

        .chartScroll::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #065e2e, #065e2eb0);
        }

        .section-d, .section-d2 {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            transition: all 0.6s ease;
            pointer-events: none;
        }

        .section-d2 {
            transform: translateX(100%);
            opacity: 0;
            z-index: 1;
        }

        .section-d.active {
            transform: translateX(0);
            opacity: 1;
            z-index: 2;
            pointer-events: auto;
        }

        .section-d.hide-left {
            transform: translateX(-100%);
            opacity: 0;
            z-index: 1;
        }

        .section-d2.active {
            transform: translateX(0);
            opacity: 1;
            z-index: 2;
            pointer-events: auto;
        }

        .section-d2.hide-right {
            transform: translateX(100%);
            opacity: 0;
            z-index: 1;
        }

        #row-for-section-ds {
            position: relative;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="">
            <div class="section">
                <h2>Filters</h2>
                <div class="row mb-3">
                    <div class="col-md-2 col-lg-4 col-xl-2 mb-2">
                        <label for="filterDom" class="form-label">DoM</label>
                        <select id="filterDom" class="form-select">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-2 col-lg-4 col-xl-2 mb-2">
                        <label for="filterChecklist" class="form-label">Checklist</label>
                        <select id="filterChecklist" class="form-select">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-2 col-lg-4 col-xl-2 mb-2">
                        <label for="filterState" class="form-label">State</label>
                        <select id="filterState">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-2 col-lg-3 col-xl-2 mb-2">
                        <label for="filterCity" class="form-label">City</label>
                        <select id="filterCity">
                            <option value="all" selected> All </option>
                        </select>
                    </div>      

                    <div class="col-md-2 col-lg-3 col-xl-2 mb-2">
                        <label for="filterLoc" class="form-label">Location</label>
                        <select id="filterLoc">
                            <option value="all" selected> All </option>
                            @forelse(\App\Models\Store::select('id', 'code', 'name')->when(!auth()->user()->isAdmin(), function ($builder) {
                                $builder->where('dom_id', auth()->user()->id);
                            })->get() as $store)
                                <option value="{{ $store->id }}"> {{ $store->code }} {{ $store->name }} </option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                    <div class="col-md-2 col-lg-3 col-xl-2 mb-2">
                        <label for="filterLtype" class="form-label">Location Type</label>
                        <select id="filterLtype">
                            <option value="all" selected> All </option>
                            @forelse(\App\Models\StoreType::get() as $lType)
                                <option value="{{ $lType->id }}"> {{ $lType->name }} </option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                    <div class="col-md-2 col-lg-3 col-xl-2 mb-2">
                        <label for="filterLmodel" class="form-label">Location Model</label>
                        <select id="filterLmodel">
                            <option value="all" selected> All </option>
                            @forelse(\App\Models\ModelType::get() as $lType)
                                <option value="{{ $lType->id }}"> {{ $lType->name }} </option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                    <div class="col-md-2 col-lg-4 col-xl-2 mb-2">
                        <label for="filterStart" class="form-label">Start Date</label>
                        <input type="text" id="filterStart" class="form-control" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('d-m-Y') }}">
                    </div>
                    <div class="col-md-2 col-lg-4 col-xl-2 mb-2">
                        <label for="filterEnd" class="form-label">End Date</label>
                        <input type="text" id="filterEnd" class="form-control" value="{{ date('d-m-Y') }}">
                    </div>
                </div>
            </div>


            <div id="row-for-section-ds">
                <div class="section-d active" id="section1">
                    <h2>Inspection Statistics</h2>
                    <div style="overflow-x: auto;width:100%;" class="chartScroll">
                        <div id="chart-container" class="bg-white rounded-2xl shadow-sm p-6">
                            <!-- Chart Header -->
                            <div id="chart-header" class="mb-6">
                                <div class="text-xs text-gray-400">Score</div>
                            </div>

                            <!-- Chart Area -->
                            <div id="chart-area" class="relative" style="padding-top: 80px;">
                                <!-- Y-Axis -->
                                <div id="y-axis" class="absolute left-0 top-0 h-80 flex flex-col justify-between text-xs text-gray-400 pr-4">
                                    <div>30K</div>
                                    <div>25K</div>
                                    <div>20K</div>
                                    <div>15K</div>
                                    <div>10K</div>
                                    <div>5K</div>
                                    <div>0K</div>
                                </div>

                                <!-- Grid Lines -->
                                <div id="grid-lines" class="absolute left-12 top-0 right-0 h-80">
                                    <div class="absolute w-full border-t border-gray-100" style="top: 0%"></div>
                                    <div class="absolute w-full border-t border-gray-100" style="top: 16.67%"></div>
                                    <div class="absolute w-full border-t border-gray-100" style="top: 33.33%"></div>
                                    <div class="absolute w-full border-t border-gray-100" style="top: 50%"></div>
                                    <div class="absolute w-full border-t border-gray-100" style="top: 66.67%"></div>
                                    <div class="absolute w-full border-t border-gray-100" style="top: 83.33%"></div>
                                    <div class="absolute w-full border-t border-gray-100" style="top: 100%"></div>
                                </div>

                                <!-- Chart Panels -->
                                <div id="chart-panels" class="ml-12 flex gap-6 h-80">
                                    <!-- Bars will be dynamically inserted here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-d2" id="section2">
                    <h2>
                        <button id="backToSection1" class="btn btn-sm btn-outline-secondary">⬅ Back</button>
                        <span id="n-store-title"> Store </span> Statistics
                    </h2>
                    <div id="chart-container-2" class="bg-white rounded-2xl shadow-sm p-6">
                        <!-- Chart Header -->
                        <div id="chart-header-2" class="mb-6">
                            <div class="text-xs text-gray-400">Score</div>
                        </div>

                        <!-- Chart Area -->
                        <div id="chart-area-2" class="relative" style="padding-top: 80px;">
                            <!-- Y-Axis -->
                            <div id="y-axis-2" class="absolute left-0 top-0 h-80 flex flex-col justify-between text-xs text-gray-400 pr-4">
                                <div>30K</div>
                                <div>25K</div>
                                <div>20K</div>
                                <div>15K</div>
                                <div>10K</div>
                                <div>5K</div>
                                <div>0K</div>
                            </div>

                            <!-- Grid Lines -->
                            <div id="grid-lines-2" class="absolute left-12 top-0 right-0 h-80">
                                <div class="absolute w-full border-t border-gray-100" style="top: 0%"></div>
                                <div class="absolute w-full border-t border-gray-100" style="top: 16.67%"></div>
                                <div class="absolute w-full border-t border-gray-100" style="top: 33.33%"></div>
                                <div class="absolute w-full border-t border-gray-100" style="top: 50%"></div>
                                <div class="absolute w-full border-t border-gray-100" style="top: 66.67%"></div>
                                <div class="absolute w-full border-t border-gray-100" style="top: 83.33%"></div>
                                <div class="absolute w-full border-t border-gray-100" style="top: 100%"></div>
                            </div>

                            <!-- Chart Panels -->
                            <div id="chart-panels-2" class="ml-12 flex gap-6 h-80">
                                <!-- Bars will be dynamically inserted here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        
        </div>



    </div>




@endsection

@push('js')
    <script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(document).ready(function() {

        $("#backToSection1").on("click", function () {
            $("#section2").removeClass("active").addClass("hide-right");
            $("#section1").removeClass("hide-left").addClass("active");
        });

            let chartStoreIds = [0, 0, 0];
            let nStoreId = 0;

            // Function to update Y-axis labels based on max value
            function updateYAxis(maxValue, axisId) {
                const yAxis = document.getElementById(axisId);
                const steps = 6;
                const stepValue = Math.ceil(maxValue / steps);
                const labels = [];
                
                for (let i = steps; i >= 0; i--) {
                    const value = stepValue * i;
                    labels.push(value >= 1000 ? `${Math.round(value / 1000)}K` : value);
                }
                
                yAxis.innerHTML = labels.map(label => `<div>${label}</div>`).join('');
            }

            // Function to render chart bars
            function renderChart(labels, data, backgroundColor, panelsId, storeIds, clickable = true) {
                const panels = document.getElementById(panelsId);
                panels.innerHTML = '';
                
                if (!data || data.length === 0) {
                    panels.innerHTML = '<div class="text-gray-400 text-center w-full">No data available</div>';
                    return;
                }
                
                const maxValue = Math.max(...data);
                const yAxisId = panelsId === 'chart-panels' ? 'y-axis' : 'y-axis-2';
                updateYAxis(maxValue, yAxisId);
                
                labels.forEach((label, index) => {
                    const value = data[index] || 0;
                    const heightPercent = maxValue > 0 ? (value / maxValue * 100) : 0;
                    const bgColor = Array.isArray(backgroundColor) ? backgroundColor[index] : backgroundColor;
                    
                    const barPanel = document.createElement('div');
                    barPanel.className = 'relative flex flex-col items-center';
                    barPanel.style.minWidth = '100px';
                    
                    barPanel.innerHTML = `
                        <div class="flex justify-center items-end h-80 w-full" style="position: relative;">
                            <div class="relative flex flex-col items-center" style="position: relative;">
                                <!-- Detailed Tooltip -->
                                <div class="chart-tooltip" style="position: absolute; top: -70px; left: 50%; transform: translateX(-50%); background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 8px; padding: 8px 16px; white-space: nowrap; z-index: 1000; border: 1px solid #e5e7eb; opacity: 0; pointer-events: none; transition: opacity 0.2s;">
                                    <div style="font-size: 11px; color: #6b7280; margin-bottom: 4px;">${label}</div>
                                    <div style="font-size: 14px; font-weight: 600; color: #1f2937;">${value.toLocaleString()} <span style="font-size: 11px; font-weight: 400; color: #6b7280;">%</span></div>
                                </div>
                                <div class="absolute left-1/2 transform -translate-x-1/2 text-[10px] sm:text-xs text-gray-700 font-semibold" style="bottom: 100.43%;">${value.toLocaleString()}%</div>
                                <div class="chart-bar" style="width: 48px; height: 288px; background-color: #f3f4f6; border-radius: 9999px; position: relative; ${clickable ? 'cursor: pointer;' : ''}">
                                    <div style="position: absolute; bottom: 0; width: 100%; border-radius: 9999px; transition: all 0.3s; height: ${heightPercent}%; background-color: ${bgColor};">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Label Section -->
                        <div class="flex flex-col items-center mt-2 w-full">
                            <div style="width: 80px; height: 1px; background-color: #d1d5db; margin-bottom: 8px;"></div>
                            <div style="font-size: 11px; color: #9ca3af; text-align: center; word-wrap: break-word; padding: 0 4px; max-width: 100px;">${label}</div>
                        </div>
                    `;
                    
                    const barElement = barPanel.querySelector('.chart-bar');
                    const tooltip = barPanel.querySelector('.chart-tooltip');
                    const valueTop = barPanel.querySelector('.chart-value-top');
                    
                    barElement.addEventListener('mouseenter', () => {
                        tooltip.style.opacity = '1';
                        valueTop.style.opacity = '1';
                    });
                    
                    barElement.addEventListener('mouseleave', () => {
                        tooltip.style.opacity = '0';
                        valueTop.style.opacity = '0';
                    });
                    
                    if (clickable && storeIds && storeIds[index]) {
                        barElement.addEventListener('click', () => {
                            nStoreId = storeIds[index];
                            loadSingleStoreStatistics();
                        });
                    }
                    
                    panels.appendChild(barPanel);
                });
            }            

            function getResult() {
                let start = $('#filterStart').val();
                let end = $('#filterEnd').val();
                let dom = $('#filterDom').val();
                let clist = $('#filterChecklist').val();
                let ltype = $('#filterLtype').val();
                let loc = $('#filterLoc').val();
                let lmodel = $('#filterLmodel').val();
                let state = $('#filterState').val();
                let city = $('#filterCity').val();

                $.ajax({
                    url: "{{ route('dom-dashboard') }}",
                    type: 'GET',
                    data: {
                        start: start,
                        end: end,
                        clist: clist,
                        dom: dom,
                        state: state,
                        city: city,
                        loc: loc,
                        ltype: ltype,
                        lmodel: lmodel
                    },
                    beforeSend: function() {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {

                        const labels = response.data.bar_chart_label;
                        const data = response.data.bar_chart_data.length > 0 ? response.data.bar_chart_data[0] : [];
                        chartStoreIds = response.data.bar_chart_store_ids;
                        
                        // Render the chart with the data
                        renderChart(labels, data, '#8dc1e9', 'chart-panels', chartStoreIds, true);

                        loadSingleStoreStatistics();
                    },
                    complete: function(response) {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });
            }

            function loadSingleStoreStatistics() {
                storeId = nStoreId;
                if (!isNaN(storeId) && storeId > 0) {

                let start = $('#filterStart').val();
                let end = $('#filterEnd').val();
                let clist = $('#filterChecklist').val();
                storeId = storeId;

                    $.ajax({
                        url: "{{ route('dom-dashboard-2-specific-store') }}",
                        type: 'GET',
                        data: {
                            store: storeId,
                            start: start,
                            end: end,
                            clist: clist
                        },
                        beforeSend: function() {
                            $('body').find('.LoaderSec').removeClass('d-none');
                        },
                        success: function(response) {

                            $('#n-store-title').text(response.store_name);

                            // Render the second chart with the data
                            renderChart(response.labels, response.data, '#03A9F4', 'chart-panels-2', null, false);

                        },
                        complete: function(response) {
                            $('body').find('.LoaderSec').addClass('d-none');

                            $("#section1").removeClass("active").addClass("hide-left");
                            $("#section2").removeClass("hide-right").addClass("active");                            
                        }
                    });                    
                }
            }

            getResult();

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

                if ($("#filterCity option[value='all']").length === 0) {
                    $('#filterCity').append('<option value="all">All</option>'); 
                }

                $('#filterCity').val('all').trigger('change');
                
                getResult();
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

            $('#filterChecklist').select2({
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
                            onlyPoint: 1,
                            getall: 1,
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
            }).on('change', function() {
                getResult();
            });

            $('#filterLoc').select2({
                placeholder: 'Select Location',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            }).on('change', function() {
                getResult();
            });

            $('#filterLtype').select2({
                placeholder: 'Select Location Type',
                width: '100%',
                theme: 'classic'
            }).on('change', function() {
                getResult();
            });

            $('#filterLmodel').select2({
                placeholder: 'Select Location Model',
                width: '100%',
                theme: 'classic'
            }).on('change', function() {
                getResult();
            });
            
            $('#nStoreStatusFilter').select2({
                placeholder: 'Select Status',
                width: '100%',
                theme: 'classic'
            }).on('change', function() {
                loadSingleStoreStatistics();
            });

            $('#nStoreStartDateFilter').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                    loadSingleStoreStatistics();
                }
            });

            $('#nStoreEndDateFilter').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                    loadSingleStoreStatistics();
                }
            });            

        });
    </script>
@endpush
