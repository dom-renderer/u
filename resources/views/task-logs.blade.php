@extends('layouts.app-master')

@push('css')
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/css/custom-select-style.css') }}" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
@endpush
@php
$task = !empty($task) ? $task : null;
$isPointChecklist = \App\Helpers\Helper::isPointChecklist($task->data ?? []);
@endphp
@section('content')

    <div class="bg-light p-4 rounded">
        <h1>{{ isset($page_title) ? $page_title : '' }} </h1>
        
        @if($task != null)
            @forelse($task->audits()->where('event', 'updated')->latest()->get() as $logIndex => $log)

            @php
                $deviceInfoEloquent = \App\Models\TaskDeviceInformation::where('eloquent', \App\Models\ChecklistTask::class)->where('eloquent_id', $task->id)->latest()->offset($logIndex)->limit(1)->first();

                $deviceIfno = [
                    'device_model' => $deviceInfoEloquent->device_model ?? 'N/A',
                    'network_speed' => $deviceInfoEloquent->network_speed ?? 'N/A',
                    'device_version' => $deviceInfoEloquent->device_version ?? 'N/A'
                ];
            @endphp

            <div>
                <strong> {{ isset($log->user()->first()->name) ? $log->user()->first()->name : 'User' }} </strong> made changes on <strong> {{ date('d F Y', strtotime($log->created_at)) }} </strong> at <strong> {{ date('H:i', strtotime($log->created_at)) }} </strong> using version <strong> {{ $deviceIfno['device_version'] }} </strong>,  model number <strong> {{ $deviceIfno['device_model'] }} </strong> network speed of <strong> {{ $deviceIfno['network_speed'] }} </strong>.
                <table class="table w-100 table-bordered table-striped">
                    <thead>
                        <tr>
                            <td>*</td>
                            <td>Old</td>
                            <td>New</td>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($log->old_values as $key => $value)
                            <tr>
                                <td>
                                    {{ ucwords(str_replace(' id', '', str_replace('_', ' ', $key))) }}
                                </td>
                                <td>
                                    @if($key == 'status')
                                        @if($value == 1)
                                            In-Progress
                                        @elseif($value == 2)
                                            Pending Verification
                                        @elseif($value == 3)
                                            Verified
                                        @else
                                            Pending
                                        @endif
                                    @elseif($key == 'data')
                                        @php
                                            if (is_string($value)) {
                                                $data = json_decode($value, true);
                                            } else if (is_array($value) || is_object($value)) {
                                                $data = $value;
                                            } else {
                                                $data = [];
                                            }

                                            $groupedData = [];
                                            foreach ($data as $item) {
                                                if (is_object($item)) {
                                                    $groupedData[$item->className][] = $item;
                                                } else {
                                                    $groupedData[$item['className']][] = $item;
                                                }
                                            }

                                            $groupedData = json_decode(json_encode($groupedData));
                                        @endphp

                                        <table class="table table-bordered table-stripped">
                                            <tbody>
                                                @forelse ($groupedData as $className => $fields)
                                                <tr>
                                                    @php  $label = isset($fields[0]->label) ? $fields[0]->label : 'N/A'; @endphp
                                                    <td>{!! $label !!}</td>
                                                    
                                                        @foreach ($fields as $field)
                                                        @if(property_exists($field, 'isFile') && $field->isFile)
                                                            @if(is_array($field->value))
                                                            <td> 
                                                                @foreach ($field->value as $thisImg)
                                                                    @php 
                                                                        $tImage = str_replace('assets/app/public/workflow-task-uploads/', '', $thisImg);
                                                                    @endphp
                                                                    <a target="_blank" href="{{ asset("storage/workflow-task-uploads/{$tImage}") }}">
                                                                        <img src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}" style="height: 100px;width:100px;object-fit:cover;">
                                                                    </a>
                                                                @endforeach
                                                            </td>
                                                            @else
                                                            <td> 
                                                                @php 
                                                                    $tImage = str_replace('assets/app/public/workflow-task-uploads/', '', $field->value);
                                                                @endphp
                                                                <a target="_blank" href="{{ asset("storage/workflow-task-uploads/{$tImage}") }}">
                                                                    <img src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}" style="height: 100px;width:100px;object-fit:cover;">
                                                                </a>
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

                                    @else
                                        {!! $value !!}
                                    @endif
                                </td>
                                <td>
                                    @php $newVal = isset($log->new_values[$key]) ? $log->new_values[$key] : '';  @endphp
                                    @if($key == 'status')
                                        @if($newVal == 1)
                                            In-Progress
                                        @elseif($newVal == 2)
                                            Pending Verification
                                        @elseif($newVal == 3)
                                            Verified
                                        @else
                                            Pending
                                        @endif
                                        @elseif($key == 'data')
                                        @php
                                            if (is_string($newVal)) {
                                                $data = json_decode($newVal, true);
                                            } else if (is_array($newVal) || is_object($newVal)) {
                                                $data = $newVal;
                                            } else {
                                                $data = [];
                                            }

                                            $groupedData = [];
                                            foreach ($data as $item) {
                                                if (is_object($item)) {
                                                    $groupedData[$item->className][] = $item;
                                                } else {
                                                    $groupedData[$item['className']][] = $item;
                                                }
                                            }

                                            $groupedData = json_decode(json_encode($groupedData));
                                        @endphp

                                        <table class="table table-bordered table-stripped">
                                            <tbody>
                                                @forelse ($groupedData as $className => $fields)
                                                <tr>
                                                    @php  $label = isset($fields[0]->label) ? $fields[0]->label : 'N/A'; @endphp
                                                    <td>{!! $label !!}</td>
                                                    
                                                        @foreach ($fields as $field)
                                                        @if(property_exists($field, 'isFile') &&  $field->isFile)
                                                            @if(is_array($field->value))
                                                            <td> 
                                                                @foreach ($field->value as $thisImg)
                                                                    @php 
                                                                        $tImage = str_replace('assets/app/public/workflow-task-uploads/', '', $thisImg);
                                                                    @endphp
                                                                    <a target="_blank" href="{{ asset("storage/workflow-task-uploads/{$tImage}") }}">
                                                                        <img src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}" style="height: 100px;width:100px;object-fit:cover;">
                                                                    </a>
                                                                @endforeach
                                                            </td>
                                                            @else
                                                            <td> 
                                                                @php 
                                                                    $tImage = str_replace('assets/app/public/workflow-task-uploads/', '', $field->value);
                                                                @endphp
                                                                <a target="_blank" href="{{ asset("storage/workflow-task-uploads/{$tImage}") }}">
                                                                    <img src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}" style="height: 100px;width:100px;object-fit:cover;">
                                                                </a>
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

                                    @else
                                        {!! $newVal !!}
                                    @endif
                                </td>
                            </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
            @empty                    
            <tr>
                <td colspan="3">
                    No activity found for this task yet.
                </td>
            </tr>
            @endforelse
        @endif

    </div> 
@endsection


@push('js')
<script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
    
    $(document).ready(function($){
       

    });
 </script>  
@endpush