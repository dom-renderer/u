<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title> Task View </title>
      <link href="{!! url('assets/css/bootstrap.min.css') !!}" rel="stylesheet">
      <link href="{!! url('assets/css/my-style.css') !!}" rel="stylesheet">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.1/font/bootstrap-icons.css">
      <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.css') }}">
      <!-- code added by binal start--->
      <meta name="msapplication-TileColor" content="#da532c">
      <meta name="theme-color" content="#ffffff">
      <!-- code added by binal end--->
      <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" />
      <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
      <link href="{{ asset('assets/css/font-awesome.min.css') }}" rel="stylesheet" />
      <style type="text/css">
         .numberCircle {
         font-family: "OpenSans-Semibold", Arial, "Helvetica Neue", Helvetica, sans-serif;
         display: inline-block;
         color: #fff;
         text-align: center;
         line-height: 0px;
         border-radius: 50%;
         font-size: 12px;
         font-weight: 700;
         min-width: 38px;
         min-height: 38px;
         }
         .numberCircle span {
         display: inline-block;
         padding-top: 50%;
         padding-bottom: 50%;
         margin-left: 1px;
         margin-right: 1px;
         }
         /* Some Back Ground Colors */
         .clrTotal {
         background: #51a529;
         }
         .clrLike {
         background: #60a949;
         }
         .clrDislike {
         background: #bd3728;
         }
         .clrUnknown {
         background: #58aeee;
         }
         .clrStatusPause {
         color: #bd3728;
         }
         .clrStatusPlay {
         color: #60a949;
         }
         .LoaderSec {
         position: fixed;
         background: #465b97c7;
         width: 100%;
         height: 100%;
         left: 0;
         top: 0;
         z-index: 99999999999;
         }
         .LoaderSec .loader {
         width: 55px;
         height: 55px;
         border: 6px solid #fff;
         border-bottom-color: #5f0000;
         border-radius: 50%;
         display: inline-block;
         -webkit-animation: rotation 1s linear infinite;
         animation: rotation 1s linear infinite;
         position: fixed;
         z-index: 9999999999999;
         transform: translate(-50%, -50%);
         top: 50%;
         left: 50%;
         }
         .content-wrapper {
         margin-left: 0px !important;
         }
         @keyframes rotation {
         0% {
         transform: rotate(0deg);
         }
         100% {
         transform: rotate(360deg);
         }
         }
         .select2-container--classic .select2-selection--single {
         height: 40px !important;
         }
         .select2-container--classic .select2-selection--single .select2-selection__arrow {
         height: 38px !important;
         }
         .select2-container--classic .select2-selection--single .select2-selection__rendered {
         line-height: 39px !important;
         }
         .prod-card {
         background: #fde8ec;
         border-radius: 10px;
         padding: 12px;
         margin: 10px 0px;
         box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
         }
         .prod-title {
         font-weight: 700;
         letter-spacing: .4px;
         font-size: 14px;
         text-transform: uppercase;
         display: flex;
         justify-content: space-between;
         border-bottom: 1px solid rgba(0, 0, 0, .1);
         padding-bottom: 6px;
         margin-bottom: 8px;
         }
         .uom-row {
         display: flex;
         justify-content: space-between;
         padding: 2px 0;
         font-size: 13px;
         }
         .category-title {
         font-weight: 700;
         font-size: 18px;
         margin: 18px 0 8px;
         }
         .cards-wrap {
         display: flex;
         flex-wrap: wrap;
         }
         .wastage-material {
         color: red;
         }
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
      </style>
   </head>
   @php
   $namesToBeIgnored = array_combine(\App\Helpers\Helper::$namesToBeIgnored, \App\Helpers\Helper::$namesToBeIgnored);
   if (is_string($task->data)) {
   $data = json_decode($task->data, true);
   } else if (is_array($task->data)) {
   $data = $task->data;
   } else {
   $data = [];
   }
   $groupedData = [];
   if (isset($task->parent->parent->checklist_id) && in_array($task->parent->parent->checklist_id, [106, 107])) {
   foreach ($data as $item) {
   if (!isset($namesToBeIgnored[$item->name])) {
   $groupedData[$item->className][] = $item;
   }
   }
   } else {
   foreach ($data as $item) {
   $groupedData[$item->className][] = $item;
   }
   }
   $varients = \App\Helpers\Helper::categorizePoints($task->data ?? []);
   $total = count(\App\Helpers\Helper::selectPointsQuestions($task->data));
   $toBeCounted = $total - count($varients['na']);
   $failed = abs(count(array_column($varients['negative'], 'value')));
   $achieved = $toBeCounted - abs($failed);
   if ($failed <= 0) {
   $achieved=array_sum(array_column($varients['positive'], 'value' ));
   }
   if ($toBeCounted> 0) {
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
   <body>
      <div class="wrapper">
         <div class="LoaderSec d-none">
            <span class="loader"></span>
         </div>
         <div class="content-wrapper">
            <div class="container-fluid">
               <div class="bg-light p-4 rounded">
                  <div class="container-for-data">
                     <div class="bg-light p-4 rounded">
                        <table class="table table-bordered table-stripped gallery">
                           <tbody>
                              @forelse ($groupedData as $className => $fields)
                              <tr>
                                 @php
                                 $label = Helper::getQuestionField($fields);
                                 @endphp
                                 <td>{!! $label !!}</td>
                                 @foreach ($fields as $field)
                                 @if(property_exists($field, 'isFile') && $field->isFile)
                                 @if(is_array($field->value))
                                 <td>
                                    @foreach ($field->value as $thisImg)
                                    @php
                                    $tImage = str_replace('assets/app/public/workflow-task-uploads/', '', $thisImg);
                                    $hasImages = true;
                                    @endphp
                                    <img data-index="{{ $globalCounter->value++ }}" class="thumbnail" src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}" style="height: 100px;width:100px;object-fit:cover;">
                                    @endforeach
                                 </td>
                                 @else
                                 <td>
                                    @php
                                    $tImage = str_replace('assets/app/public/workflow-task-uploads/', '', $field->value);
                                    $hasImages = true;
                                    @endphp
                                    <img data-index="{{ $globalCounter->value++ }}" class="thumbnail" src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}" style="height: 100px;width:100px;object-fit:cover;">
                                 </td>
                                 @endif
                                 @else
                                 @if(property_exists($field, 'value_label'))
                                 @if($isPointChecklist)
                                 @if(is_array($field->value_label))
                                 <td> {!! implode(',', $field->value_label) !!} </td>
                                 @else
                                 <td> {!! $field->value_label !!} ({{ is_array($field->value) ? implode(',', $field->value) : $field->value }}) </td>
                                 @endif
                                 @else
                                 @if(is_array($field->value_label))
                                 <td> {!! implode(',', $field->value_label) !!} </td>
                                 @else
                                 <td> {!! $field->value_label !!} {{ is_array($field->value) ? implode(',', $field->value) : $field->value }} </td>
                                 @endif
                                 @endif
                                 @else
                                 @if(is_array($field->value))
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
                        @if(isset($task->parent->parent->checklist_id) && in_array($task->parent->parent->checklist_id, [106, 107]))
                        @php
                        $c1_nos_2000 = \App\Helpers\Helper::getObjectsByName($data, 'c1-nos-2000');
                        $c1_amt_2000 = \App\Helpers\Helper::getObjectsByName($data, 'c1-amount-2000');
                        $c1_nos_500 = \App\Helpers\Helper::getObjectsByName($data, 'c1-nos-500');
                        $c1_amt_500 = \App\Helpers\Helper::getObjectsByName($data, 'c1-amount-500');
                        $c1_nos_200 = \App\Helpers\Helper::getObjectsByName($data, 'c1-nos-200');
                        $c1_amt_200 = \App\Helpers\Helper::getObjectsByName($data, 'c1-amount-200');
                        $c1_nos_100 = \App\Helpers\Helper::getObjectsByName($data, 'c1-nos-100');
                        $c1_amt_100 = \App\Helpers\Helper::getObjectsByName($data, 'c1-amount-100');
                        $c1_nos_50 = \App\Helpers\Helper::getObjectsByName($data, 'c1-nos-50');
                        $c1_amt_50 = \App\Helpers\Helper::getObjectsByName($data, 'c1-amount-50');
                        $c1_nos_20 = \App\Helpers\Helper::getObjectsByName($data, 'c1-nos-20');
                        $c1_amt_20 = \App\Helpers\Helper::getObjectsByName($data, 'c1-amount-20');
                        $c1_nos_10 = \App\Helpers\Helper::getObjectsByName($data, 'c1-nos-10');
                        $c1_amt_10 = \App\Helpers\Helper::getObjectsByName($data, 'c1-amount-10');
                        $c1_nos_5 = \App\Helpers\Helper::getObjectsByName($data, 'c1-nos-5');
                        $c1_amt_5 = \App\Helpers\Helper::getObjectsByName($data, 'c1-amount-5');
                        $c1_nos_2 = \App\Helpers\Helper::getObjectsByName($data, 'c1-nos-2');
                        $c1_amt_2 = \App\Helpers\Helper::getObjectsByName($data, 'c1-amount-2');
                        $c1_nos_1 = \App\Helpers\Helper::getObjectsByName($data, 'c1-nos-1');
                        $c1_amt_1 = \App\Helpers\Helper::getObjectsByName($data, 'c1-amount-1');
                        $c1_total = \App\Helpers\Helper::getObjectsByName($data, 'final-denomination-total');
                        $cash_zreport = \App\Helpers\Helper::getObjectsByName($data, 'cash-zreport-c1');
                        $cash_actual = \App\Helpers\Helper::getObjectsByName($data, 'cash-actual-c1');
                        $cash_source = \App\Helpers\Helper::getObjectsByName($data, 'cash-source-c1');
                        $cash_diff = \App\Helpers\Helper::getObjectsByName($data, 'cash-difference-c1');
                        $cash_reason = \App\Helpers\Helper::getObjectsByName($data, 'cash-reason-c1');
                        $card_zreport = \App\Helpers\Helper::getObjectsByName($data, 'card-zreport-c1');
                        $card_actual = \App\Helpers\Helper::getObjectsByName($data, 'card-actual-c1');
                        $card_source = \App\Helpers\Helper::getObjectsByName($data, 'card-source-c1');
                        $card_diff = \App\Helpers\Helper::getObjectsByName($data, 'card-difference-c1');
                        $card_reason = \App\Helpers\Helper::getObjectsByName($data, 'card-reason-c1');
                        $swiggy_zreport = \App\Helpers\Helper::getObjectsByName($data, 'swiggy-zreport-c1');
                        $swiggy_actual = \App\Helpers\Helper::getObjectsByName($data, 'swiggy-actual-c1');
                        $swiggy_source = \App\Helpers\Helper::getObjectsByName($data, 'swiggy-source-c1');
                        $swiggy_diff = \App\Helpers\Helper::getObjectsByName($data, 'swiggy-difference-c1');
                        $swiggy_reason = \App\Helpers\Helper::getObjectsByName($data, 'swiggy-reason-c1');
                        $zomato_zreport = \App\Helpers\Helper::getObjectsByName($data, 'zomato-zreport-c1');
                        $zomato_actual = \App\Helpers\Helper::getObjectsByName($data, 'zomato-actual-c1');
                        $zomato_source = \App\Helpers\Helper::getObjectsByName($data, 'zomato-source-c1');
                        $zomato_diff = \App\Helpers\Helper::getObjectsByName($data, 'zomato-difference-c1');
                        $zomato_reason = \App\Helpers\Helper::getObjectsByName($data, 'zomato-reason-c1');
                        $paytm_zreport = \App\Helpers\Helper::getObjectsByName($data, 'paytm-zreport-c1');
                        $paytm_actual = \App\Helpers\Helper::getObjectsByName($data, 'paytm-actual-c1');
                        $paytm_source = \App\Helpers\Helper::getObjectsByName($data, 'paytm-source-c1');
                        $paytm_diff = \App\Helpers\Helper::getObjectsByName($data, 'paytm-difference-c1');
                        $paytm_reason = \App\Helpers\Helper::getObjectsByName($data, 'paytm-reason-c1');
                        $bharatpe_zreport = \App\Helpers\Helper::getObjectsByName($data, 'bharatpe-zreport-c1');
                        $bharatpe_actual = \App\Helpers\Helper::getObjectsByName($data, 'bharatpe-actual-c1');
                        $bharatpe_source = \App\Helpers\Helper::getObjectsByName($data, 'bharatpe-source-c1');
                        $bharatpe_diff = \App\Helpers\Helper::getObjectsByName($data, 'bharatpe-difference-c1');
                        $bharatpe_reason = \App\Helpers\Helper::getObjectsByName($data, 'bharatpe-reason-c1');
                        $phonepe_zreport = \App\Helpers\Helper::getObjectsByName($data, 'phonepe-zreport-c1');
                        $phonepe_actual = \App\Helpers\Helper::getObjectsByName($data, 'phonepe-actual-c1');
                        $phonepe_source = \App\Helpers\Helper::getObjectsByName($data, 'phonepe-source-c1');
                        $phonepe_diff = \App\Helpers\Helper::getObjectsByName($data, 'phonepe-difference-c1');
                        $phonepe_reason = \App\Helpers\Helper::getObjectsByName($data, 'phonepe-reason-c1');
                        $vouchers_zreport = \App\Helpers\Helper::getObjectsByName($data, 'vouchers-zreport-c1');
                        $vouchers_actual = \App\Helpers\Helper::getObjectsByName($data, 'vouchers-actual-c1');
                        $vouchers_source = \App\Helpers\Helper::getObjectsByName($data, 'vouchers-source-c1');
                        $vouchers_diff = \App\Helpers\Helper::getObjectsByName($data, 'vouchers-difference-c1');
                        $vouchers_reason = \App\Helpers\Helper::getObjectsByName($data, 'vouchers-reason-c1');
                        $creditsale_zreport = \App\Helpers\Helper::getObjectsByName($data, 'creditsale-zreport-c1');
                        $creditsale_actual = \App\Helpers\Helper::getObjectsByName($data, 'creditsale-actual-c1');
                        $creditsale_source = \App\Helpers\Helper::getObjectsByName($data, 'creditsale-source-c1');
                        $creditsale_diff = \App\Helpers\Helper::getObjectsByName($data, 'creditsale-difference-c1');
                        $creditsale_reason = \App\Helpers\Helper::getObjectsByName($data, 'creditsale-reason-c1');
                        $pinelabs_zreport = \App\Helpers\Helper::getObjectsByName($data, 'pinelabs-zreport-c1');
                        $pinelabs_actual = \App\Helpers\Helper::getObjectsByName($data, 'pinelabs-actual-c1');
                        $pinelabs_source = \App\Helpers\Helper::getObjectsByName($data, 'pinelabs-source-c1');
                        $pinelabs_diff = \App\Helpers\Helper::getObjectsByName($data, 'pinelabs-difference-c1');
                        $pinelabs_reason = \App\Helpers\Helper::getObjectsByName($data, 'pinelabs-reason-c1');
                        $other_zreport = \App\Helpers\Helper::getObjectsByName($data, 'other-zreport-c1');
                        $other_actual = \App\Helpers\Helper::getObjectsByName($data, 'other-actual-c1');
                        $other_source = \App\Helpers\Helper::getObjectsByName($data, 'other-source-c1');
                        $other_diff = \App\Helpers\Helper::getObjectsByName($data, 'other-difference-c1');
                        $other_reason = \App\Helpers\Helper::getObjectsByName($data, 'other-reason-c1');
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
                        $opening_cash = \App\Helpers\Helper::getObjectsByName($data, 'cal-1-a-text-1758616033754-0');
                        $cash_sale = \App\Helpers\Helper::getObjectsByName($data, 'cal-1-b-text-1758616033754-0');
                        $expenses = \App\Helpers\Helper::getObjectsByName($data, 'cal-1-c-text-1758616033754-0');
                        $banking = \App\Helpers\Helper::getObjectsByName($data, 'cal-1-d-text-1758616033754-0');
                        $cash_in_hand = \App\Helpers\Helper::getObjectsByName($data, 'cal-1-e-text-1758616033754-0');
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
                                       <th colspan="4"></th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                    <tr>
                                       <td>2000</td>
                                       <td>{{ number_format(floatval($c1_nos_2000), 0) }}</td>
                                       <td>{{ number_format(floatval($c1_amt_2000), 2) }}</td>
                                       <td>Cash</td>
                                       <td>{{ number_format(floatval($cash_zreport), 2) }}</td>
                                       <td>{{ $cash_actual }}</td>
                                       <td>{{ number_format(floatval($cash_source), 2) }}</td>
                                       <td>{{ $cash_diff }}</td>
                                       <td>{{ $cash_reason }}</td>
                                    </tr>
                                    <tr>
                                       <td>500</td>
                                       <td>{{ number_format(floatval($c1_nos_500), 0) }}</td>
                                       <td>{{ number_format(floatval($c1_amt_500), 2) }}</td>
                                       <td>Card</td>
                                       <td>{{ number_format(floatval($card_zreport), 2) }}</td>
                                       <td>{{ $card_actual }}</td>
                                       <td>{{ number_format(floatval($card_source), 2) }}</td>
                                       <td>{{ $card_diff }}</td>
                                       <td>{{ $card_reason }}</td>
                                    </tr>
                                    <tr>
                                       <td>200</td>
                                       <td>{{ number_format(floatval($c1_nos_200), 0) }}</td>
                                       <td>{{ number_format(floatval($c1_amt_200), 2) }}</td>
                                       <td>Swiggy</td>
                                       <td>{{ number_format(floatval($swiggy_zreport), 2) }}</td>
                                       <td>{{ $swiggy_actual }}</td>
                                       <td>{{ number_format(floatval($swiggy_source), 2) }}</td>
                                       <td>{{ $swiggy_diff }}</td>
                                       <td>{{ $swiggy_reason }}</td>
                                    </tr>
                                    <tr>
                                       <td>100</td>
                                       <td>{{ number_format(floatval($c1_nos_100), 0) }}</td>
                                       <td>{{ number_format(floatval($c1_amt_100), 2) }}</td>
                                       <td>Zomato</td>
                                       <td>{{ number_format(floatval($zomato_zreport), 2) }}</td>
                                       <td>{{ $zomato_actual }}</td>
                                       <td>{{ number_format(floatval($zomato_source), 2) }}</td>
                                       <td>{{ $zomato_diff }}</td>
                                       <td>{{ $zomato_reason }}</td>
                                    </tr>
                                    <tr>
                                       <td>50</td>
                                       <td>{{ number_format(floatval($c1_nos_50), 0) }}</td>
                                       <td>{{ number_format(floatval($c1_amt_50), 2) }}</td>
                                       <td>Paytm</td>
                                       <td>{{ number_format(floatval($paytm_zreport), 2) }}</td>
                                       <td>{{ $paytm_actual }}</td>
                                       <td>{{ number_format(floatval($paytm_source), 2) }}</td>
                                       <td>{{ $paytm_diff }}</td>
                                       <td>{{ $paytm_reason }}</td>
                                    </tr>
                                    <tr>
                                       <td>20</td>
                                       <td>{{ number_format(floatval($c1_nos_20), 0) }}</td>
                                       <td>{{ number_format(floatval($c1_amt_20), 2) }}</td>
                                       <td>Bharatpe</td>
                                       <td>{{ number_format(floatval($bharatpe_zreport), 2) }}</td>
                                       <td>{{ $bharatpe_actual }}</td>
                                       <td>{{ number_format(floatval($bharatpe_source), 2) }}</td>
                                       <td>{{ $bharatpe_diff }}</td>
                                       <td>{{ $bharatpe_reason }}</td>
                                    </tr>
                                    <tr>
                                       <td>10</td>
                                       <td>{{ number_format(floatval($c1_nos_10), 0) }}</td>
                                       <td>{{ number_format(floatval($c1_amt_10), 2) }}</td>
                                       <td>PhonePe</td>
                                       <td>{{ number_format(floatval($phonepe_zreport), 2) }}</td>
                                       <td>{{ $phonepe_actual }}</td>
                                       <td>{{ number_format(floatval($phonepe_source), 2) }}</td>
                                       <td>{{ $phonepe_diff }}</td>
                                       <td>{{ $phonepe_reason }}</td>
                                    </tr>
                                    <tr>
                                       <td>5</td>
                                       <td>{{ number_format(floatval($c1_nos_5), 0) }}</td>
                                       <td>{{ number_format(floatval($c1_amt_5), 2) }}</td>
                                       <td>Vouchers</td>
                                       <td>{{ number_format(floatval($vouchers_zreport), 2) }}</td>
                                       <td>{{ $vouchers_actual }}</td>
                                       <td>{{ number_format(floatval($vouchers_source), 2) }}</td>
                                       <td>{{ $vouchers_diff }}</td>
                                       <td>{{ $vouchers_reason }}</td>
                                    </tr>
                                    <tr>
                                       <td>2</td>
                                       <td>{{ number_format(floatval($c1_nos_2), 0) }}</td>
                                       <td>{{ number_format(floatval($c1_amt_2), 2) }}</td>
                                       <td>Credit Sale</td>
                                       <td>{{ number_format(floatval($creditsale_zreport), 2) }}</td>
                                       <td>{{ $creditsale_actual }}</td>
                                       <td>{{ number_format(floatval($creditsale_source), 2) }}</td>
                                       <td>{{ $creditsale_diff }}</td>
                                       <td>{{ $creditsale_reason }}</td>
                                    </tr>
                                    <tr>
                                       <td>1</td>
                                       <td>{{ number_format(floatval($c1_nos_1), 0) }}</td>
                                       <td>{{ number_format(floatval($c1_amt_1), 2) }}</td>
                                       <td>Pinelabs</td>
                                       <td>{{ number_format(floatval($pinelabs_zreport), 2) }}</td>
                                       <td>{{ $pinelabs_actual }}</td>
                                       <td>{{ number_format(floatval($pinelabs_source), 2) }}</td>
                                       <td>{{ $pinelabs_diff }}</td>
                                       <td>{{ $pinelabs_reason }}</td>
                                    </tr>
                                    <tr>
                                       <td>Other</td>
                                       <td>N/A</td>
                                       <td>N/A</td>
                                       <td>Others</td>
                                       <td>{{ number_format(floatval($other_zreport), 2) }}</td>
                                       <td>{{ $other_actual }}</td>
                                       <td>{{ number_format(floatval($other_source), 2) }}</td>
                                       <td>{{ $other_diff }}</td>
                                       <td>{{ $other_reason }}</td>
                                    </tr>
                                    <tr>
                                       <td colspan="2" class="text-center fw-bold">Total</td>
                                       <td>{{ number_format(floatval($c1_total), 2) }}</td>
                                       <td>Total</td>
                                       <td>{{ number_format(floatval($total_zreport), 2) }}</td>
                                       <td>{{ $total_actual }}</td>
                                       <td></td>
                                       <td>{{ $total_diff }}</td>
                                       <td></td>
                                    </tr>
                                 </tbody>
                              </table>
                           </div>
                           <table class="table table-bordered">
                              <tbody>
                                 <tr>
                                    <td>Opening Cash of the day (A)</td>
                                    <td>{{ number_format(floatval($opening_cash), 2) }}</td>
                                 </tr>
                                 <tr>
                                    <td>Total Cash Sale (B)</td>
                                    <td>{{ number_format(floatval($cash_sale), 2) }}</td>
                                 </tr>
                                 <tr>
                                    <td>Expenses for the day (C)</td>
                                    <td>{{ number_format(floatval($expenses), 2) }}</td>
                                 </tr>
                                 <tr>
                                    <td>Banking Done for the day (D)</td>
                                    <td>{{ number_format(floatval($banking), 2) }}</td>
                                 </tr>
                                 <tr class="fw-bold">
                                    <td>Cash In Hand for the day (E = A + B - C - D)</td>
                                    <td>{{ number_format(floatval($cash_in_hand), 2) }}</td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                        @endif
                        @if($isPointChecklist)
                        <table class="table table-striped table-bordered">
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
                                 <td>{{ $percentage > 80 ? "Pass" : "Fail" }}</td>
                              </tr>
                           </tbody>
                        </table>
                        @endif
                     </div>
                  </div>
               </div>
               @if($hasImages)
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
            </div>
         </div>
      </div>
   </body>
</html>
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery-ui.js') }}"></script>
<script src="{{ url('assets/js/jquery-validate.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
   $(document).ready(function($) {
   
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
   
       $(".thumbnail").click(function() {
           showLightbox($(this).data('index'));
       });
   
       $(".close").click(function() {
           $(".lightbox").fadeOut();
       });
   
       $(".prev").click(function() {
           if (currentIndex > 0) {
               showLightbox(currentIndex - 1);
           }
       });
   
       $(".next").click(function() {
           if (currentIndex < images.length - 1) {
               showLightbox(currentIndex + 1);
           }
       });
   
       $("#zoom-in").click(function() {
           scale += 0.2;
           applyTransform();
           if (scale > 1) {
               $("#lightbox-img").css("cursor", "grab");
           }
       });
   
       $("#zoom-out").click(function() {
           if (scale > 1) {
               scale -= 0.2;
               if (scale <= 1) {
                   resetImage();
               } else {
                   applyTransform();
               }
           }
       });
   
       $("#download").click(function() {
           let link = document.createElement('a');
           link.href = images[currentIndex];
           link.download = 'image.jpg';
           document.body.appendChild(link);
           link.click();
           document.body.removeChild(link);
       });
   
       $("#lightbox-img").on("mousedown", function(e) {
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
   
   });
</script>