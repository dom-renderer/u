<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Tea Post Inspection Report - {{ $task->code ?? '-' }} </title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }
        .header {
            background-color: #174C3C;
            color: #fff;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            position: relative;
            height: 32px;
        }
        .header img {
            width: 50px;
            height: auto;
            position: absolute;
            left: 20px;
            top: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 0;
            font-size: 15px;
            float: right;
            position: relative;
            bottom: 23px;
        }
        .summary {
            display: flex;
            justify-content: space-between;
            background-color: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .summary div {
            flex: 1;
            text-align: center;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin-top: 20px;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #174C3C;
            color: white;
            text-transform: uppercase;
        }
        .pass {
            background-color: #c8e6c9;
        }
        .fail {
            background-color: #ffccbc;
        }
        .bolder {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('assets/logo.webp') }}" alt="Tea Post Logo">
        <h1> {{ $task->parent->parent->checklist->name ?? '' }} </h1>
        <p> {{ $task->code }} </p>
    </div>
    
    @php
    $date1 = \Carbon\Carbon::parse($task->started_at);
    $date2 = \Carbon\Carbon::parse($task->completion_date);
    $diff = $date1->diff($date2);

    $versionedForm = \App\Helpers\Helper::getVersionForm($task->version_id);
    $isPointChecklist = \App\Helpers\Helper::isPointChecklist($versionedForm);
    @endphp


    <table style="margin-top:5px!important;">
        <tbody>
            <tr class="pass">
                <td> 
                    <center>
                        <strong>
                            {{ $task->parent->actstore->name ?? '' }} - {{ $task->parent->actstore->code ?? '' }}
                        </strong>
                    </center>    
                </td>
            </tr>
        </tbody>
    </table>

    <table style="margin-top:5px!important;">
        <tbody>
            <tr class="pass">
                <td> <span class="bolder"> Start Time </span>: {{ $date1->format('d F Y H:i') }} </td>
                <td>  <span class="bolder"> End Time </span> : {{ $task->status == 1 ? '-' : $date2->format('d F Y H:i') }} </td>
                <td> <span class="bolder"> Ops Time: </span> 
                    @if($task->status == 1)
                        -
                    @else
                        @if($diff->d > 0)
                        {{ $diff->d }} days,
                        @endif
                        @if($diff->h > 0)
                        {{ $diff->h }} hours,
                        @endif
                        @if($diff->i > 0)
                        {{ $diff->i }} minutes
                        @endif
                        @if($diff->d <= 0 && $diff->h <= 0 && $diff->i <= 0)
                            -
                        @endif
                    @endif
                   </td>
            </tr>
            <tr class="pass">
                <td colspan="2"> <span class="bolder"> Conducted By: </span> {{ $task->parent->user->name ?? '' }} </td>
                <td> <span class="bolder"> Total Questions: </span> {{ $toBeCounted }} </td>
            </tr>
        </tbody>
    </table>

    <table style="margin-top:5px!important;">
        <tbody>

            @if($isPointChecklist)
            <tr class="pass">
                <td> <span class="bolder"> Pass </span> </td>
                <td> {{ $finalResultData['passed'] }} </td>
            </tr>
            <tr class="pass">
                <td>  <span class="bolder"> N/A </span>  </td>
                <td> {{ $finalResultData['na'] }} </td>
            </tr>
            <tr class="pass">
                <td>  <span class="bolder"> Fail </span> </td>
                <td> {{ $finalResultData['failed'] }} </td>
            </tr>
            <tr class="pass">
                <td>  <span class="bolder"> Percentage </span>  </td>
                <td> {{ $finalResultData['percentage'] }} </td>
            </tr>
            <tr @if($finalResultData['final_result'] == 'Pass') class="pass" @else class="fail" @endif>
                <td>  <span class="bolder"> Result </span>  </td>
                <td> {{ $finalResultData['final_result'] }} </td>
            </tr>
            @endif
        </tbody>
    </table>

    @php
        if (empty($data)) {
            $maxColumns = 3;
        } else {
            $maxColumns = max(array_map('count', $data));
        }
    @endphp

    {{-- FAILED ITEMS --}}

    <br>
    @if(isset($task->parent->parent->checklist->is_point_checklist) && $task->parent->parent->checklist->is_point_checklist == 1)

    @php
        $hasSectionWise = collect($task->data ?? [])->groupBy('page')->values()->toArray();
    @endphp

    @if(count($hasSectionWise) > 0)
    {{-- SECTION WISE RESULT --}}
    <center>
        <span class="bolder" > --- SECTION WISE RESULT --- </span>
    </center>

    <table class="table table-bordered">
        <thead>
            <tr>
                <td class="pass">
                    <strong>
                        Section
                    </strong>
                </td>
                <td class="pass">
                    <strong>
                        Result
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

                    $titleOfSection = 'Page ' . ($pKey + 1);

                    if (is_array($versionedForm) && isset($versionedForm[$pKey])) {
                        $titleOfSection = collect($versionedForm[$pKey])->where('type', 'header')->get(0)->label ?? ('Page ' . ($pKey + 1));
                    }

                @endphp

                    <tr>
                        <td class="@if($thisPer > 80) pass @else fail @endif">
                            {{ html_entity_decode($titleOfSection) }}
                        </td>
                        <td class="@if($thisPer > 80) pass @else fail @endif">
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
    {{-- SECTION WISE RESULT --}}
    @endif

    <br><br>

    <center>
        <span class="bolder" > --- FAILED ITEMS --- </span>
    </center>

    <table>
        <thead>
            <tr>
                <th>Inspection Item</th>
                <th>Result</th>
                <th colspan="{{ $maxColumns - 2 }}">Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
                @if(is_string($row[1]) && (strtolower($row[1]) == 'no' || strtolower($row[1]) == 'fail'))
                    <tr class="fail">
                        @foreach ($row as $key => $value)
                            <td colspan="{{ $loop->last && count($row) < $maxColumns ? $maxColumns - count($row) + 1 : 1 }}"
                                style="font-weight: {{ $loop->first ? 'bold' : 'normal' }}">
                                @if(is_string($value) && strpos($value, 'SIGN-20') !== false)
                                    @php $webpToPng = str_replace(".webp", ".png", $value); @endphp
                                    @if(file_exists(storage_path("app/public/workflow-task-uploads-thumbnails/{$webpToPng}")) && is_file(storage_path("app/public/workflow-task-uploads-thumbnails/{$webpToPng}")))
                                        <img src="{{ public_path("storage/workflow-task-uploads-thumbnails/{$webpToPng}") }}" style="height: 100px;">
                                    @else
                                        <img src="{{ public_path("no-image-found.png") }}" style="height: 100px;">                                    
                                    @endif
                                @elseif(is_array($value))
                                    @foreach ($value as $vl)
                                        @if(strpos($vl, 'SIGN-20') !== false)
                                            @php $webpToPng = str_replace(".webp", ".png", $vl); @endphp
                                            @if(file_exists(storage_path("app/public/workflow-task-uploads-thumbnails/{$webpToPng}")) && is_file(storage_path("app/public/workflow-task-uploads-thumbnails/{$webpToPng}")))
                                                <img src="{{ public_path("storage/workflow-task-uploads-thumbnails/{$webpToPng}") }}" style="height: 100px;">
                                            @else
                                                <img src="{{ public_path("no-image-found.png") }}" style="height: 100px;">
                                            @endif
                                        @else
                                            {!! $vl !!}
                                        @endif
                                    @endforeach
                                @else
                                    {!! $value !!}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    @endif
    {{-- FAILED ITEMS --}}

    {{-- FULL REPORT --}}
    <div style="page-break-before:always !important;">
    <br>

    <center>
        <span class="bolder" > --- FULL REPORT --- </span>
    </center>    

        <table>
            <thead>
                <tr>
                    <th>Inspection Item</th>
                    <th>Result</th>
                    <th colspan="{{ $maxColumns - 2 }}">Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $row)
                    <tr 
                    @if(is_string($row[1]) && (strtolower($row[1]) == 'pass' || strtolower($row[1]) == 'yes')) 
                    class="pass" 
                    @elseif(is_string($row[1]) && (strtolower($row[1]) == 'no' || strtolower($row[1]) == 'fail'))
                    class="fail" 
                    @else 
                    class="pass" 
                    @endif 
                    >
                        @foreach ($row as $key => $value)
                            <td colspan="{{ $loop->last && count($row) < $maxColumns ? $maxColumns - count($row) + 1 : 1 }}"
                                style="font-weight: {{ $loop->first ? 'bold' : 'normal' }}">
                                @if(is_string($value) && strpos($value, 'SIGN-20') !== false)
                                    @php $webpToPng = str_replace(".webp", ".png", $value); @endphp
                                    @if(file_exists(storage_path("app/public/workflow-task-uploads-thumbnails/{$webpToPng}")) && is_file(storage_path("app/public/workflow-task-uploads-thumbnails/{$webpToPng}")))
                                        <img src="{{ public_path("storage/workflow-task-uploads-thumbnails/{$webpToPng}") }}" style="height: 100px;">
                                    @else
                                        <img src="{{ public_path("no-image-found.png") }}" style="height: 100px;">                                    
                                    @endif
                                @elseif(is_array($value))
                                    @foreach ($value as $vl)
                                        @if(strpos($vl, 'SIGN-20') !== false)
                                            @php $webpToPng = str_replace(".webp", ".png", $vl); @endphp
                                            @if(file_exists(storage_path("app/public/workflow-task-uploads-thumbnails/{$webpToPng}")) && is_file(storage_path("app/public/workflow-task-uploads-thumbnails/{$webpToPng}")))
                                                <img src="{{ public_path("storage/workflow-task-uploads-thumbnails/{$webpToPng}") }}" style="height: 100px;">
                                            @else
                                                <img src="{{ public_path("no-image-found.png") }}" style="height: 100px;">
                                            @endif
                                        @else
                                            {!! $vl !!}
                                        @endif
                                    @endforeach
                                @else
                                    {!! $value !!}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>    
    </div>
    {{-- FULL REPORT --}}

        @if(isset($task->parent->parent->checklist_id) && in_array($task->parent->parent->checklist_id, [106, 107]))

        @php
            if (is_string($task->data)) {
                $dataCal1 = json_decode($task->data, true);
            } else if (is_array($task->data)) {
                $dataCal1 = $task->data;
            } else {
                $dataCal1 = [];
            }

            $c1_nos_2000 = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-nos-2000');
            $c1_amt_2000 = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-amount-2000');

            $c1_nos_500  = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-nos-500');
            $c1_amt_500  = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-amount-500');

            $c1_nos_200  = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-nos-200');
            $c1_amt_200  = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-amount-200');

            $c1_nos_100  = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-nos-100');
            $c1_amt_100  = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-amount-100');

            $c1_nos_50   = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-nos-50');
            $c1_amt_50   = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-amount-50');

            $c1_nos_20   = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-nos-20');
            $c1_amt_20   = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-amount-20');

            $c1_nos_10   = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-nos-10');
            $c1_amt_10   = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-amount-10');

            $c1_nos_5    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-nos-5');
            $c1_amt_5    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-amount-5');

            $c1_nos_2    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-nos-2');
            $c1_amt_2    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-amount-2');

            $c1_nos_1    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-nos-1');
            $c1_amt_1    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'c1-amount-1');

            $c1_total    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'final-denomination-total');

            $cash_zreport     = \App\Helpers\Helper::getObjectsByName($dataCal1, 'cash-zreport-c1');
            $cash_actual      = \App\Helpers\Helper::getObjectsByName($dataCal1, 'cash-actual-c1');
            $cash_source      = \App\Helpers\Helper::getObjectsByName($dataCal1, 'cash-source-c1');
            $cash_diff        = \App\Helpers\Helper::getObjectsByName($dataCal1, 'cash-difference-c1');
            $cash_reason      = \App\Helpers\Helper::getObjectsByName($dataCal1, 'cash-reason-c1');

            $card_zreport     = \App\Helpers\Helper::getObjectsByName($dataCal1, 'card-zreport-c1');
            $card_actual      = \App\Helpers\Helper::getObjectsByName($dataCal1, 'card-actual-c1');
            $card_source      = \App\Helpers\Helper::getObjectsByName($dataCal1, 'card-source-c1');
            $card_diff        = \App\Helpers\Helper::getObjectsByName($dataCal1, 'card-difference-c1');
            $card_reason      = \App\Helpers\Helper::getObjectsByName($dataCal1, 'card-reason-c1');

            $swiggy_zreport   = \App\Helpers\Helper::getObjectsByName($dataCal1, 'swiggy-zreport-c1');
            $swiggy_actual    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'swiggy-actual-c1');
            $swiggy_source    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'swiggy-source-c1');
            $swiggy_diff      = \App\Helpers\Helper::getObjectsByName($dataCal1, 'swiggy-difference-c1');
            $swiggy_reason    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'swiggy-reason-c1');

            $zomato_zreport   = \App\Helpers\Helper::getObjectsByName($dataCal1, 'zomato-zreport-c1');
            $zomato_actual    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'zomato-actual-c1');
            $zomato_source    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'zomato-source-c1');
            $zomato_diff      = \App\Helpers\Helper::getObjectsByName($dataCal1, 'zomato-difference-c1');
            $zomato_reason    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'zomato-reason-c1');

            $paytm_zreport   = \App\Helpers\Helper::getObjectsByName($dataCal1, 'paytm-zreport-c1');
            $paytm_actual    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'paytm-actual-c1');
            $paytm_source    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'paytm-source-c1');
            $paytm_diff      = \App\Helpers\Helper::getObjectsByName($dataCal1, 'paytm-difference-c1');
            $paytm_reason    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'paytm-reason-c1');

            $bharatpe_zreport   = \App\Helpers\Helper::getObjectsByName($dataCal1, 'bharatpe-zreport-c1');
            $bharatpe_actual    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'bharatpe-actual-c1');
            $bharatpe_source    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'bharatpe-source-c1');
            $bharatpe_diff      = \App\Helpers\Helper::getObjectsByName($dataCal1, 'bharatpe-difference-c1');
            $bharatpe_reason    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'bharatpe-reason-c1');

            $phonepe_zreport   = \App\Helpers\Helper::getObjectsByName($dataCal1, 'phonepe-zreport-c1');
            $phonepe_actual    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'phonepe-actual-c1');
            $phonepe_source    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'phonepe-source-c1');
            $phonepe_diff      = \App\Helpers\Helper::getObjectsByName($dataCal1, 'phonepe-difference-c1');
            $phonepe_reason    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'phonepe-reason-c1');

            $vouchers_zreport   = \App\Helpers\Helper::getObjectsByName($dataCal1, 'vouchers-zreport-c1');
            $vouchers_actual    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'vouchers-actual-c1');
            $vouchers_source    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'vouchers-source-c1');
            $vouchers_diff      = \App\Helpers\Helper::getObjectsByName($dataCal1, 'vouchers-difference-c1');
            $vouchers_reason    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'vouchers-reason-c1');

            $creditsale_zreport   = \App\Helpers\Helper::getObjectsByName($dataCal1, 'creditsale-zreport-c1');
            $creditsale_actual    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'creditsale-actual-c1');
            $creditsale_source    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'creditsale-source-c1');
            $creditsale_diff      = \App\Helpers\Helper::getObjectsByName($dataCal1, 'creditsale-difference-c1');
            $creditsale_reason    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'creditsale-reason-c1');

            $pinelabs_zreport   = \App\Helpers\Helper::getObjectsByName($dataCal1, 'pinelabs-zreport-c1');
            $pinelabs_actual    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'pinelabs-actual-c1');
            $pinelabs_source    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'pinelabs-source-c1');
            $pinelabs_diff      = \App\Helpers\Helper::getObjectsByName($dataCal1, 'pinelabs-difference-c1');
            $pinelabs_reason    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'pinelabs-reason-c1');

            $other_zreport   = \App\Helpers\Helper::getObjectsByName($dataCal1, 'other-zreport-c1');
            $other_actual    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'other-actual-c1');
            $other_source    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'other-source-c1');
            $other_diff      = \App\Helpers\Helper::getObjectsByName($dataCal1, 'other-difference-c1');
            $other_reason    = \App\Helpers\Helper::getObjectsByName($dataCal1, 'other-reason-c1');

            $total_zreport = 
                (is_numeric($cash_zreport) ? $cash_zreport : 0) +
                (is_numeric($card_zreport) ? $card_zreport : 0) +
                (is_numeric($swiggy_zreport) ? $swiggy_zreport : 0) +
                (is_numeric($zomato_zreport) ? $zomato_zreport : 0) +
                (is_numeric($paytm_zreport) ? $paytm_zreport : 0) +
                (is_numeric($bharatpe_zreport) ? $bharatpe_zreport : 0) +
                (is_numeric($phonepe_zreport) ? $phonepe_zreport : 0) +
                (is_numeric($vouchers_zreport) ? $vouchers_zreport : 0) +
                (is_numeric($creditsale_zreport) ? $creditsale_zreport : 0) +
                (is_numeric($pinelabs_zreport) ? $pinelabs_zreport : 0) +
                (is_numeric($other_zreport) ? $other_zreport : 0);

            $total_actual = 
                (is_numeric($cash_actual) ? $cash_actual : 0) +
                (is_numeric($card_actual) ? $card_actual : 0) +
                (is_numeric($swiggy_actual) ? $swiggy_actual : 0) +
                (is_numeric($zomato_actual) ? $zomato_actual : 0) +
                (is_numeric($paytm_actual) ? $paytm_actual : 0) +
                (is_numeric($bharatpe_actual) ? $bharatpe_actual : 0) +
                (is_numeric($phonepe_actual) ? $phonepe_actual : 0) +
                (is_numeric($vouchers_actual) ? $vouchers_actual : 0) +
                (is_numeric($creditsale_actual) ? $creditsale_actual : 0) +
                (is_numeric($pinelabs_actual) ? $pinelabs_actual : 0) +
                (is_numeric($other_actual) ? $other_actual : 0);

            $total_diff = 
                (is_numeric($cash_diff) ? $cash_diff : 0) +
                (is_numeric($card_diff) ? $card_diff : 0) +
                (is_numeric($swiggy_diff) ? $swiggy_diff : 0) +
                (is_numeric($zomato_diff) ? $zomato_diff : 0) +
                (is_numeric($paytm_diff) ? $paytm_diff : 0) +
                (is_numeric($bharatpe_diff) ? $bharatpe_diff : 0) +
                (is_numeric($phonepe_diff) ? $phonepe_diff : 0) +
                (is_numeric($vouchers_diff) ? $vouchers_diff : 0) +
                (is_numeric($creditsale_diff) ? $creditsale_diff : 0) +
                (is_numeric($pinelabs_diff) ? $pinelabs_diff : 0) +
                (is_numeric($other_diff) ? $other_diff : 0);

            $opening_cash     = \App\Helpers\Helper::getObjectsByName($dataCal1, 'cal-1-a-text-1758616033754-0');
            $cash_sale        = \App\Helpers\Helper::getObjectsByName($dataCal1, 'cal-1-b-text-1758616033754-0');
            $expenses         = \App\Helpers\Helper::getObjectsByName($dataCal1, 'cal-1-c-text-1758616033754-0');
            $banking          = \App\Helpers\Helper::getObjectsByName($dataCal1, 'cal-1-d-text-1758616033754-0');
            $cash_in_hand     = \App\Helpers\Helper::getObjectsByName($dataCal1, 'cal-1-e-text-1758616033754-0');
        @endphp

            <div class="row">
                <h5 class="text-center mb-4">Cash Register (As per CLS z report details)</h5>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th colspan="3">Cash Denomination Summary</th>
                                <th>Particulars</th>
                                <th>As per Z Report</th>
                                <th>Actual</th>
                                <th>Source</th>
                                <th>Difference</th>
                                <th>Reason for Difference</th>
                            </tr>
                            <tr>
                                <th>Deno.</th>
                                <th>Count</th>
                                <th>Amount</th>
                                <th colspan="6"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td class="pass">2000</td><td class="pass">{{ number_format(floatval($c1_nos_2000), 0) }}</td><td class="pass">{{ number_format(floatval($c1_amt_2000), 2) }}</td><td class="pass">Cash</td><td class="pass">{{ number_format(floatval($cash_zreport), 2) }}</td><td class="pass">{{ $cash_actual }}</td><td class="pass">{{ number_format(floatval($cash_source), 2) }}</td><td class="pass">{{ $cash_diff }}</td><td class="pass">{{ $cash_reason }}</td></tr>
                            <tr><td class="pass">500</td><td class="pass">{{ number_format(floatval($c1_nos_500), 0) }}</td><td class="pass">{{ number_format(floatval($c1_amt_500), 2) }}</td><td class="pass">Card</td><td class="pass">{{ number_format(floatval($card_zreport), 2) }}</td><td class="pass">{{ $card_actual }}</td><td class="pass">{{ number_format(floatval($card_source), 2) }}</td><td class="pass">{{ $card_diff }}</td><td class="pass">{{ $card_reason }}</td></tr>
                            <tr><td class="pass">200</td><td class="pass">{{ number_format(floatval($c1_nos_200), 0) }}</td><td class="pass">{{ number_format(floatval($c1_amt_200), 2) }}</td><td class="pass">Swiggy</td><td class="pass">{{ number_format(floatval($swiggy_zreport), 2) }}</td><td class="pass">{{ $swiggy_actual }}</td><td class="pass">{{ number_format(floatval($swiggy_source), 2) }}</td><td class="pass">{{ $swiggy_diff }}</td><td class="pass">{{ $swiggy_reason }}</td></tr>
                            <tr><td class="pass">100</td><td class="pass">{{ number_format(floatval($c1_nos_100), 0) }}</td><td class="pass">{{ number_format(floatval($c1_amt_100), 2) }}</td><td class="pass">Zomato</td><td class="pass">{{ number_format(floatval($zomato_zreport), 2) }}</td><td class="pass">{{ $zomato_actual }}</td><td class="pass">{{ number_format(floatval($zomato_source), 2) }}</td><td class="pass">{{ $zomato_diff }}</td><td class="pass">{{ $zomato_reason }}</td></tr>
                            <tr><td class="pass">50</td><td class="pass">{{ number_format(floatval($c1_nos_50), 0) }}</td><td class="pass">{{ number_format(floatval($c1_amt_50), 2) }}</td><td class="pass">Paytm</td><td class="pass">{{ number_format(floatval($paytm_zreport), 2) }}</td><td class="pass">{{ $paytm_actual }}</td><td class="pass">{{ number_format(floatval($paytm_source), 2) }}</td><td class="pass">{{ $paytm_diff }}</td><td class="pass">{{ $paytm_reason }}</td></tr>
                            <tr><td class="pass">20</td><td class="pass">{{ number_format(floatval($c1_nos_20), 0) }}</td><td class="pass">{{ number_format(floatval($c1_amt_20), 2) }}</td><td class="pass">Bharatpe</td><td class="pass">{{ number_format(floatval($bharatpe_zreport), 2) }}</td><td class="pass">{{ $bharatpe_actual }}</td><td class="pass">{{ number_format(floatval($bharatpe_source), 2) }}</td><td class="pass">{{ $bharatpe_diff }}</td><td class="pass">{{ $bharatpe_reason }}</td></tr>
                            <tr><td class="pass">10</td><td class="pass">{{ number_format(floatval($c1_nos_10), 0) }}</td><td class="pass">{{ number_format(floatval($c1_amt_10), 2) }}</td><td class="pass">PhonePe</td><td class="pass">{{ number_format(floatval($phonepe_zreport), 2) }}</td><td class="pass">{{ $phonepe_actual }}</td><td class="pass">{{ number_format(floatval($phonepe_source), 2) }}</td><td class="pass">{{ $phonepe_diff }}</td><td class="pass">{{ $phonepe_reason }}</td></tr>
                            <tr><td class="pass">5</td><td class="pass">{{ number_format(floatval($c1_nos_5), 0) }}</td><td class="pass">{{ number_format(floatval($c1_amt_5), 2) }}</td><td class="pass">Vouchers</td><td class="pass">{{ number_format(floatval($vouchers_zreport), 2) }}</td><td class="pass">{{ $vouchers_actual }}</td><td class="pass">{{ number_format(floatval($vouchers_source), 2) }}</td><td class="pass">{{ $vouchers_diff }}</td><td class="pass">{{ $vouchers_reason }}</td></tr>
                            <tr><td class="pass">2</td><td class="pass">{{ number_format(floatval($c1_nos_2), 0) }}</td><td class="pass">{{ number_format(floatval($c1_amt_2), 2) }}</td><td class="pass">Credit Sale</td><td class="pass">{{ number_format(floatval($creditsale_zreport), 2) }}</td><td class="pass">{{ $creditsale_actual }}</td><td class="pass">{{ number_format(floatval($creditsale_source), 2) }}</td><td class="pass">{{ $creditsale_diff }}</td><td class="pass">{{ $creditsale_reason }}</td></tr>
                            <tr><td class="pass">1</td><td class="pass">{{ number_format(floatval($c1_nos_1), 0) }}</td><td class="pass">{{ number_format(floatval($c1_amt_1), 2) }}</td><td class="pass">Pinelabs</td><td class="pass">{{ number_format(floatval($pinelabs_zreport), 2) }}</td><td class="pass">{{ $pinelabs_actual }}</td><td class="pass">{{ number_format(floatval($pinelabs_source), 2) }}</td><td class="pass">{{ $pinelabs_diff }}</td><td class="pass">{{ $pinelabs_reason }}</td></tr>
                            <tr><td class="pass">Other</td><td class="pass">N/A</td><td class="pass">N/A</td><td class="pass">Others</td><td class="pass">{{ number_format(floatval($other_zreport), 2) }}</td><td class="pass">{{ $other_actual }}</td><td class="pass">{{ number_format(floatval($other_source), 2) }}</td><td class="pass">{{ $other_diff }}</td><td class="pass">{{ $other_reason }}</td></tr>
                            <tr><td colspan="2" class="text-center fw-bold pass">Total</td><td class="pass">{{ number_format(floatval($c1_total), 2) }}</td><td class="pass">Total</td><td class="pass">{{ number_format(floatval($total_zreport), 2) }}</td><td class="pass">{{ $total_actual }}</td><td class="pass"></td><td class="pass">{{ $total_diff }}</td><td class="pass"></td></tr>
                        </tbody>
                    </table>
                </div>

                <table class="table table-bordered">
                    <tbody>
                        <tr><td class="pass">Opening Cash of the day (A)</td><td class="pass">{{ number_format(floatval($opening_cash), 2) }}</td></tr>
                        <tr><td class="pass">Total Cash Sale (B)</td><td class="pass">{{ number_format(floatval($cash_sale), 2) }}</td></tr>
                        <tr><td class="pass">Expenses for the day (C)</td><td class="pass">{{ number_format(floatval($expenses), 2) }}</td></tr>
                        <tr><td class="pass">Banking Done for the day (D)</td><td class="pass">{{ number_format(floatval($banking), 2) }}</td></tr>
                        <tr class="fw-bold pass"><td class="pass">Cash In Hand for the day (E = A + B - C - D)</td><td class="pass">{{ number_format(floatval($cash_in_hand), 2) }}</td></tr>
                    </tbody>
                </table>
            </div>

        @endif

    <br><br>

    <center>
        <span class="bolder" > --- End of Report --- </span>
    </center>

</body>
</html>