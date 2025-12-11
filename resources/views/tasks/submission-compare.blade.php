@extends('layouts.app-master')

@push('css')
    <style>
        .gallery img {
            width: 150px;
            cursor: pointer;
            margin: 5px;
        }

        .lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            text-align: center;
        }

        .lightbox img {
            max-width: 80%;
            max-height: 80%;
            margin-top: 5%;
            transition: transform 0.3s;
        }

        .controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
        }

        .controls button {
            margin: 5px;
            padding: 10px;
            cursor: pointer;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 30px;
            color: white;
            cursor: pointer;
        }

        .prev,
        .next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 24px;
            color: white;
            background: rgba(0, 0, 0, 0.5);
            border: none;
            padding: 10px;
            cursor: pointer;
        }

        .prev {
            left: 10px;
            display: none;
        }

        .next {
            right: 10px;
            display: none;
        }

        .custom-legend {
            text-align: center;
            margin-top: 10px;
            font-weight: bold;
            font-size: 14px;
        }

        .custom-legend .legend-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            background-color: orange;
            border-radius: 50%;
            margin-right: 5px;
        }
    </style>
@endpush


@php

    if (is_string($task->data)) {
        $data = json_decode($task->data, true);
    } elseif (is_array($task->data)) {
        $data = $task->data;
    } else {
        $data = [];
    }

    $groupedData = [];
    foreach ($data as $item) {
        $groupedData[$item->className][] = $item;
    }

    $varients = \App\Helpers\Helper::categorizePoints($task->data ?? []);

    $total = count(\App\Helpers\Helper::selectPointsQuestions($task->data));
    $toBeCounted = $total - count($varients['na']);

    $failed = abs(count(array_column($varients['negative'], 'value')));
    $achieved = $toBeCounted - abs($failed);

    if ($failed <= 0) {
        $achieved = array_sum(array_column($varients['positive'], 'value'));
    }

    if ($toBeCounted > 0) {
        $percentage = ($achieved / $toBeCounted) * 100;
    } else {
        $percentage = 0;
    }

    $hasImages = false;

    $globalCounter = new \stdClass();
    $globalCounter->value = 0;

    $versionedForm = \App\Helpers\Helper::getVersionForm($task->version_id);
    $isPointChecklist = \App\Helpers\Helper::isPointChecklist($versionedForm);
@endphp

@section('content')

    <div class="bg-light p-4 rounded">

        <button class="btn btn-success float-end" id="export-excel"> Export </button>

        <canvas id="spiChart" width="800" height="300"></canvas>
        <div class="custom-legend">
            <span class="legend-box"></span> Current Task
        </div>

        <div class="p-4">
            <table class="table table-bordered mt-2">
                <tr>
                    <td>
                        Checklist
                    </td>
                    <td>
                        {{ $task->parent->parent->checklist->name ?? '' }}
                    </td>
                </tr>
                <tr>
                    <td>
                        Store
                    </td>
                    <td>
                        {{ $task->parent->actstore->name ?? '' }} - {{ $task->parent->actstore->code ?? '' }}
                    </td>
                </tr>
            </table>
        </div>

        <div class="container-for-data">
            <div class="bg-light p-4 rounded" id="comparison-container">

                @if ($isPointChecklist)
                @php
                    $hasSectionWise = collect($data)->groupBy('page')->values()->toArray();
                @endphp

                    @if(count($hasSectionWise) > 0)
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <td>
                                    <strong>
                                        Section
                                    </strong>
                                </td>
                                <td>
                                    <strong>
                                        {{ date('d F Y', strtotime($task->date)) }}
                                    </strong>
                                </td>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($hasSectionWise as $pKey => $totalSectionsRow)
                                @if(!($loop->first || $loop->iteration == 2))
                                @php
                                    $thisVarients = \App\Helpers\Helper::categorizePoints($totalSectionsRow ?? []);
                                    $thisTotal = count(\App\Helpers\Helper::selectPointsQuestions($totalSectionsRow));
                                    $thisToBeCounted = $thisTotal - count($thisVarients['na']);

                                    $thisFailed = abs(count(array_column($thisVarients['negative'], 'value')));
                                    $thisAchieved = $thisToBeCounted - abs($thisFailed);

                                    if ($thisFailed <= 0) {
                                        $thisAchieved = array_sum(array_column($thisVarients['positive'], 'value'));
                                    }
                                    
                                    if ($thisToBeCounted > 0) {
                                        $thisPer = number_format(($thisAchieved / $thisToBeCounted) * 100, 2);
                                    } else {
                                        $thisPer = 0;
                                    }

                                    $titleOfSection = '';

                                    if (is_array($versionedForm) && isset($versionedForm[$pKey])) {
                                        $titleOfSection = collect($versionedForm[$pKey])->where('type', 'header')->get(0)->label ?? '';
                                    }

                                @endphp

                                    <tr>
                                        <td>
                                            {{ html_entity_decode($titleOfSection) }}
                                        </td>
                                        <td style="@if($thisPer > 80) background:#c8e6c9; @else background:#ffccbc; @endif">
                                            {{ number_format($thisPer, 2) }}%
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td>
                                        No Data Found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @endif
                @endif

                <table class="table table-bordered gallery">
                    <thead>
                        <tr>
                            <th> Date </th>
                            <th> {{ date('d F Y', strtotime($task->date)) }} </th>
                        </tr>
                        <tr>
                            <th> Inspection Item </th>
                            <th> Submission </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($groupedData as $className => $fields)
                            <tr>
                                @php  $label = isset($fields[0]->label) ? $fields[0]->label : 'N/A'; @endphp
                                <td>{!! $label !!}</td>

                                @foreach ($fields as $field)
                                    @if (property_exists($field, 'isFile') && $field->isFile)
                                        @if (is_array($field->value))
                                            <td>
                                                @foreach ($field->value as $thisImg)
                                                    @php
                                                        $tImage = str_replace(
                                                            'assets/app/public/workflow-task-uploads/',
                                                            '',
                                                            $thisImg,
                                                        );
                                                        $hasImages = true;
                                                    @endphp
                                                    <img data-index="{{ $globalCounter->value++ }}" class="thumbnail"
                                                        src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}"
                                                        style="height: 100px;width:100px;object-fit:cover;">
                                                @endforeach
                                            </td>
                                        @else
                                            <td>
                                                @php
                                                    $tImage = str_replace(
                                                        'assets/app/public/workflow-task-uploads/',
                                                        '',
                                                        $field->value,
                                                    );
                                                    $hasImages = true;
                                                @endphp
                                                <img data-index="{{ $globalCounter->value++ }}" class="thumbnail"
                                                    src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}"
                                                    style="height: 100px;width:100px;object-fit:cover;">
                                            </td>
                                        @endif
                                    @else
                                        @if (property_exists($field, 'value_label'))
                                            @if ($isPointChecklist)
                                                @if (is_array($field->value_label))
                                                    <td> {!! implode(',', $field->value_label) !!} </td>
                                                @else
                                                    <td> {!! $field->value_label !!} ({{ is_array($field->value) ? implode(',', $field->value) : $field->value }}) </td>
                                                @endif
                                            @else
                                                @if (is_array($field->value_label))
                                                    <td> {!! implode(',', $field->value_label) !!} </td>
                                                @else
                                                    <td> {!! $field->value_label !!} {{ is_array($field->value) ? implode(',', $field->value) : $field->value }} </td>
                                                @endif
                                            @endif
                                        @else
                                            @if (is_array($field->value))
                                                <td> {!! implode(',', $field->value) !!} </td>
                                            @else
                                                <td> {!! $field->value !!} </td>
                                            @endif
                                        @endif
                                    @endif
                                @endforeach

                            </tr>
                        @empty
                            <tr>
                                <td>
                                    No Data Found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($isPointChecklist)
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td>Total Questions</td>
                                <td>{{ $total }}</td>
                            </tr>
                            <tr>
                                <td>Passed</td>
                                <td>{{ $achieved }}</td>
                            </tr>
                            <tr>
                                <td>Failed</td>
                                <td>{{ count($varients['negative']) }}</td>
                            </tr>
                            <tr>
                                <td>N/A</td>
                                <td>{{ count($varients['na']) }}</td>
                            </tr>
                            <tr>
                                <td>Percentage</td>
                                <td>{{ number_format($percentage, 2) }}%</td>
                            </tr>
                            <tr>
                                <td>Final Result</td>
                                <td>{{ $percentage > 80 ? 'Pass' : 'Fail' }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endif

                </table>
            </div>
        </div>

    </div>


    @if ($hasImages)
        <div class="lightbox">
            <span class="close">&times;</span>
            <button class="prev">&#10094;</button>
            <img id="lightbox-img" src="">
            <button class="next">&#10095;</button>
            <div class="controls">
                <button class="btn btn-sm btn-secondary" id="zoom-in">Zoom In</button>
                <button class="btn btn-sm btn-secondary" id="zoom-out">Zoom Out</button>
                <button class="btn btn-sm btn-secondary" id="download">Download</button>
            </div>
        </div>
    @endif
@endsection

@push('js')
    <script src="{{ asset('assets/js/chart.js') }}"></script>
    <script>
        $(document).ready(function($) {

            const ctx = document.getElementById('spiChart').getContext('2d');

            const labels = ["{{ date('F Y') }}"];

            let currentTaskId = {{ $task->id }};
            let dataPoints = [0];
            let dataIds = [currentTaskId];
            let comparing = [];
            let activePoints = [];

            let currentIndex = 0;
            let scale = 1;
            let isDragging = false;
            let startX = 0,
                startY = 0;
            let moveX = 0,
                moveY = 0;
            let images = $(".thumbnail").map(function() {
                return $(this).attr("src");
            }).get();

            $(document).on('click', '#export-excel', function () {
                $.ajax({
                    url: "{{ route('export-comparison') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        ids : dataIds
                    },
                    cache: false,
                    xhrFields:{
                        responseType: 'blob'
                    },
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function (response) {
                        var url = window.URL || window.webkitURL;
                        var objectUrl = url.createObjectURL(response);
                        var a = $("<a />", {
                            href: objectUrl,
                            download: "export.xlsx"
                        }).appendTo("body")
                        a[0].click()
                        a.remove()
                    },
                    complete: function () {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });
            })

            function showLightbox(index) {
                currentIndex = index;
                scale = 1;
                resetImage();
                $("#lightbox-img").attr("src", images[currentIndex]);
                $(".lightbox").fadeIn();
                updateNavButtons();
            }

            function updateNavButtons() {
                $(".prev").toggle(currentIndex > 0);
                $(".next").toggle(currentIndex < images.length - 1);
            }

            $(document).on('click', '.thumbnail', function() {
                showLightbox($(this).data('index'));
            });

            $(document).on('click', '.close', function() {
                $(".lightbox").fadeOut();
            });

            $(document).on('click', '.prev', function() {
                if (currentIndex > 0) {
                    showLightbox(currentIndex - 1);
                }
            });

            $(document).on('click', '.next', function() {
                if (currentIndex < images.length - 1) {
                    showLightbox(currentIndex + 1);
                }
            });

            $(document).on('click', '#zoom-in', function() {
                scale += 0.2;
                applyTransform();
                if (scale > 1) {
                    $("#lightbox-img").css("cursor", "grab");
                }
            });

            $(document).on('click', '#zoom-out', function() {
                if (scale > 1) {
                    scale -= 0.2;
                    if (scale <= 1) {
                        resetImage();
                    } else {
                        applyTransform();
                    }
                }
            });

            $(document).on('click', '#download', function() {
                let link = document.createElement('a');
                link.href = images[currentIndex];
                link.download = 'image.jpg';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            $(document).on('mousedown', '#lightbox-img', function(e) {
                if (scale > 1) {
                    isDragging = true;
                    startX = e.clientX - moveX;
                    startY = e.clientY - moveY;
                    $(this).css("cursor", "grabbing");
                }
            });

            $(document).on("mousemove", function(e) {
                if (isDragging) {
                    moveX = e.clientX - startX;
                    moveY = e.clientY - startY;
                    applyTransform();
                }
            });

            $(document).on("mouseup", function() {
                isDragging = false;
                $("#lightbox-img").css("cursor", "grab");
            });

            function applyTransform() {
                $("#lightbox-img").css("transform", `scale(${scale}) translate(${moveX}px, ${moveY}px)`);
            }

            function resetImage() {
                scale = 1;
                moveX = 0;
                moveY = 0;
                $("#lightbox-img").css({
                    "transform": `scale(1) translate(0px, 0px)`,
                    "cursor": "default"
                });
            }

            const pointColors = dataPoints.map((value, index) => {
                return 'orange';
            });

            const data = {
                labels: labels,
                datasets: [{
                    label: 'SPI Score',
                    data: dataPoints,
                    fill: true,
                    borderColor: 'darkgreen',
                    backgroundColor: 'rgba(0, 128, 0, 0.2)',
                    tension: 0.3,
                    pointRadius: 6,
                    pointBackgroundColor: pointColors,
                    pointBorderColor: 'black',
                    pointBorderWidth: 1,
                }]
            };

            const options = {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            boxWidth: 20,
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Store Performance Index (SPI) Trend',
                        font: {
                            size: 20,
                            weight: 'bold'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'SP Score (%)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Inspection Month'
                        }
                    }
                },
                onClick: (event, elements) => {
                    if (elements.length > 0) {
                        const index = elements[0].index;

                        if (dataIds[index] == currentTaskId) {
                            return false;
                        }

                        if (activePoints.includes(index)) {
                            activePoints = activePoints.filter(i => i !== index);
                        } else {
                            activePoints.push(index);
                        }

                        const newRadii = spiChart.data.datasets[0].data.map((_, i) =>
                            activePoints.includes(i) || dataIds[i] == currentTaskId ? 25 : 6
                        );

                        const newBorders = spiChart.data.datasets[0].data.map((_, i) =>
                            activePoints.includes(i) ? 3 : 1
                        );

                        spiChart.data.datasets[0].pointRadius = newRadii;
                        spiChart.data.datasets[0].pointBorderWidth = newBorders;

                        spiChart.update();

                        if (Array.isArray(datapoints) && typeof datapoints[index] != undefined) {
                            fetchTaskData(datapoints[index])
                        }
                    }
                }
            };

            let spiChart = new Chart(ctx, {
                type: 'line',
                data: data,
                options: options
            });

            function fetchTaskData(taskId) {
                if (!comparing.includes(taskId)) {
                    if (currentTaskId != taskId) {
                        comparing.push(taskId);
                    }
                } else {
                    comparing = comparing.filter(item => item !== taskId);
                }

                if (Array.isArray(comparing)) {
                    $.ajax({
                        url: "{{ route('fetch-task-data-to-compare') }}",
                        type: 'GET',
                        data: {
                            tasks: comparing,
                            current: currentTaskId
                        },
                        success: function(response) {
                            if (response.status) {
                                $('#comparison-container').html(response.html);
                            }
                        },
                        complete: function () {
                            images = $(".thumbnail").map(function() {
                                return $(this).attr("src");
                            }).get();
                        }
                    });
                }
            }

            $(document).on('click', '.remove-col', function() {
                fetchTaskData($(this).data('task-id'));
            });

            function getComparisonData() {
                $.ajax({
                    url: "{{ route('compare-checklist') }}",
                    type: 'GET',
                    data: {
                        id: "{{ $task->parent->parent->checklist_id ?? 0 }}",
                        store_id: "{{ $task->parent->store_id ?? 0 }}"
                    },
                    success: function(response) {
                        if (response.status) {
                            spiChart.data.datasets[0].data = response.data;
                            spiChart.data.labels = response.label;
                            datapoints = response.datapoints;
                            dataIds = response.ids;

                            const pointColors = response.data.map((value, index) => {
                                if (response.ids[index] == currentTaskId) return 'orange';
                                if (value === 0) return 'red';
                                return 'green';
                            });

                            const pointRds = response.data.map((value, index) => {
                                if (response.ids[index] == currentTaskId) return 25;
                                return 6;
                            });

                            spiChart.data.datasets[0].pointBackgroundColor = pointColors;
                            spiChart.data.datasets[0].pointRadius = pointRds;
                            spiChart.update();
                        } else {
                            Swal.fire('Error', 'Something went wrong!', 'error');
                        }
                    }

                });
            }

            getComparisonData();

        });



    </script>
@endpush
