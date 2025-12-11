@extends('layouts.app-master')
@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
<style>
   .gallery img { width: 150px; cursor: pointer; margin: 5px; }
   .lightbox { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); text-align: center; }
   .lightbox img { max-width: 80%; max-height: 80%; margin-top: 5%; transition: transform 0.3s; }
   .controls { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); }
   .controls button { margin: 5px; padding: 10px; cursor: pointer; }
   .close { position: absolute; top: 10px; right: 20px; font-size: 30px; color: white; cursor: pointer; }
   .prev, .next { position: absolute; top: 50%; transform: translateY(-50%); font-size: 24px; color: white; background: rgba(0,0,0,0.5); border: none; padding: 10px; cursor: pointer; }
   .prev { left: 10px; display: none; }
   .next { right: 10px; display: none; }
   .cursor-pointer {cursor: pointer!important;}
</style>
@endpush
@php
    if (is_string($task->data)) {
        $data = json_decode($task->data, true);
    } else if (is_array($task->data)) {
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
    $maxFieldCount = 0;

    foreach ($groupedData as $fields) {
        $fieldCount = count($fields);
        if ($fieldCount > $maxFieldCount) {
            $maxFieldCount = $fieldCount;
        }
    }

    $colspan = $maxFieldCount;


$versionedForm = \App\Helpers\Helper::getVersionForm($task->version_id);
$isPointChecklist = \App\Helpers\Helper::isPointChecklist($versionedForm);

function hasApprovedField(array $fields) {
    foreach ($fields as $field) {
        if (property_exists($field, 'approved') && $field->approved == 'no') {
            return true;
        }
    }
    return false;
}

@endphp
@section('content')
<div class="bg-light p-4 rounded">


<div class="container-for-data mb-4">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Flagged Items</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Reassigned Items</button>
        </li>
      </ul>
      <div class="tab-content" id="myTabContent" style="padding-top: 0px;">
        <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
            {{-- Flagged Items --}}
            <div id="append-flagged-items">

            </div>
            {{-- Flagged Items --}}
        </div>
        <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
            {{-- Reassigned Items --}}
            @php
                $otherGroupedData = [];

                foreach ($groupedData as $className => $fields) {
                    if (hasApprovedField($fields) && isset($redoActionData[$className])) {
                        $otherGroupedData[$className] = $fields;
                    }
                }
            @endphp
            
            @if(count($otherGroupedData) > 0)
                <form action="{{ route('verify-each-fields', $id) }}" method="POST"> @csrf
            @endif
            <table class="table table-bordered table-striped gallery">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th colspan="{{ $maxFieldCount }}"></th>
                        <th width="8%"> Verify </th>
                    </tr>
                </thead>
            <tbody>
                @forelse ($otherGroupedData as $className => $fields)
                    <tr>
                        @php  
                        $label = Helper::getQuestionField($fields); 
                        $fieldCount = count($fields);
                        $colspan = $maxFieldCount - $fieldCount + 1;
                        @endphp
                        <td colspan="{{ $colspan }}">{!! $label !!}</td>
                        @foreach ($fields as $field)
                        @if(property_exists($field, 'isFile') &&  $field->isFile)
                        @if(is_array($field->value))
                        <td> 
                            @foreach ($field->value as $thisImg)
                            @php 
                            $tImage = str_replace('assets/app/public/workflow-task-uploads/', '', $thisImg);
                            $hasImages = true;
                            @endphp
                            <img data-index="{{ $globalCounter->value++ }}" class="thumbnail ximg-{{ $className }}" src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}" style="height: 100px;width:100px;object-fit:cover;">
                            @endforeach
                        </td>
                        @else
                        <td> 
                            @php 
                            $tImage = str_replace('assets/app/public/workflow-task-uploads/', '', $field->value);
                            $hasImages = true;
                            @endphp
                            <img data-index="{{ $globalCounter->value++ }}" class="thumbnail ximg-{{ $className }}" src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}" style="height: 100px;width:100px;object-fit:cover;">
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
                        <td>
                            <div>
                                <label for="approve-{{  $className  }}" class="cursor-pointer"> Approve </label>
                                <input type="radio" data-type="accept" data-lastdata="{{ property_exists($field, 'approved') ? ($field->approved == 'yes' ? 'yes' : 'no') : '' }}" data-classname="{{ $className }}" class="input-action input-approve" name="justify_field[{{ $className }}]" id="approve-{{ $className }}" value="approve">
                            </div>

                            <div>
                                <label for="decline-{{  $className  }}" class="cursor-pointer"> Reassign </label>
                                <input type="radio" data-type="decline" data-lastdata="{{ property_exists($field, 'approved') ? ($field->approved == 'no' ? 'no' : 'yes') : '' }}" data-classname="{{ $className }}" class="input-action input-decline" name="justify_field[{{ $className }}]" id="decline-{{ $className }}" value="decline" checked
                                
                                data-class="{{ $className }}"
                                data-title="{{ $redoActionData[$className]['title'] }}"
                                data-remark="{{ $redoActionData[$className]['remarks'] }}"
                                data-start="{{ date('d-m-Y H:i', strtotime($redoActionData[$className]['start_at'])) }}"
                                data-end="{{ date('d-m-Y H:i', strtotime($redoActionData[$className]['completed_by'])) }}"
                                data-lsub="{{ $redoActionData[$className]['do_not_allow_late_submission'] }}"
                                >

                                <i id="modal-{{ $className }}" class="bi bi-info-circle-fill"
                                data-bs-toggle="modal" data-bs-target="#redomodal"
                                data-class="{{ $className }}"

                                data-title="{{ $redoActionData[$className]['title'] }}"
                                data-remark="{{ $redoActionData[$className]['remarks'] }}"
                                data-start="{{ date('d-m-Y H:i', strtotime($redoActionData[$className]['start_at'])) }}"
                                data-end="{{ date('d-m-Y H:i', strtotime($redoActionData[$className]['completed_by'])) }}"
                                data-lsub="{{ $redoActionData[$className]['do_not_allow_late_submission'] }}"
                                ></i>

                                <input type="hidden" id="action-{{ $className }}" name="action[{{ $className }}]" value="{{ json_encode([
                                    'title' => $redoActionData[$className]['title'],
                                    'remark' => $redoActionData[$className]['remarks'],
                                    'start' => date('d-m-Y H:i', strtotime($redoActionData[$className]['start_at'])),
                                    'end' => date('d-m-Y H:i', strtotime($redoActionData[$className]['completed_by'])),
                                    'lsub' => $redoActionData[$className]['do_not_allow_late_submission']
                                ]) }}">
                            </div>
                        </td>
                    </tr>
                @empty
                <tr>
                    <td colspan="{{ $maxFieldCount + 2 }}">
                        <center>
                            No Data Found
                        </center>
                    </td>
                </tr>
                @endforelse
            </tbody>
            </table>

            @if(count($otherGroupedData) > 0)

            <center>
                <button type="submit" class="btn btn-success"> Update </button>
            </center>

                </form>
            @endif
            {{-- Reassigned Items --}}
        </div>
      </div>
</div>


<hr>
<h4> Submission Data </h4>
<hr>


<form action="{{ route('verify-each-fields', $id) }}" method="POST"> @csrf
    <div class="container-for-data">
        <div class="bg-light p-4 rounded">
            <div class="row">
                <table class="table table-bordered table-striped gallery">
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th colspan="{{ $maxFieldCount }}"></th>
                            <th width="8%"> Verify </th>
                        </tr>
                    </thead>
                <tbody>
                    @forelse ($groupedData as $className => $fields)
                    <tr>
                        @php  
                        $label = Helper::getQuestionField($fields); 
                        $fieldCount = count($fields);
                        $colspan = $maxFieldCount - $fieldCount + 1;
                        @endphp
                        <td colspan="{{ $colspan }}">{!! $label !!}</td>
                        @foreach ($fields as $field)
                        @if(property_exists($field, 'isFile') &&  $field->isFile)
                        @if(is_array($field->value))
                        <td> 
                            @foreach ($field->value as $thisImg)
                            @php 
                            $tImage = str_replace('assets/app/public/workflow-task-uploads/', '', $thisImg);
                            $hasImages = true;
                            @endphp
                            <img data-index="{{ $globalCounter->value++ }}" class="thumbnail ximg-{{ $className }}" src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}" style="height: 100px;width:100px;object-fit:cover;">
                            @endforeach
                        </td>
                        @else
                        <td> 
                            @php 
                            $tImage = str_replace('assets/app/public/workflow-task-uploads/', '', $field->value);
                            $hasImages = true;
                            @endphp
                            <img data-index="{{ $globalCounter->value++ }}" class="thumbnail ximg-{{ $className }}" src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}" style="height: 100px;width:100px;object-fit:cover;">
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
                        <td>
                            <div>
                                <label for="approve-{{  $className  }}" class="cursor-pointer"> Approve </label>
                                <input type="radio" data-type="accept" data-lastdata="{{ property_exists($field, 'approved') ? ($field->approved == 'yes' ? 'yes' : 'no') : '' }}" data-classname="{{ $className }}" class="input-action input-approve" name="justify_field[{{ $className }}]" id="approve-{{ $className }}" value="approve" @if(property_exists($field, 'approved') && $field->approved == 'yes') checked @endif>
                            </div>

                            <div>
                                <label for="decline-{{  $className  }}" class="cursor-pointer"> Reassign </label>
                                <input type="radio" data-type="decline" data-lastdata="{{ property_exists($field, 'approved') ? ($field->approved == 'no' ? 'no' : 'yes') : '' }}" data-classname="{{ $className }}" class="input-action input-decline" name="justify_field[{{ $className }}]" id="decline-{{ $className }}" value="decline" @if(property_exists($field, 'approved') && $field->approved == 'no') checked @endif
                                
                                @if(property_exists($field, 'approved') && $field->approved == 'no' && isset($redoActionData[$className]))
                                    data-class="{{ $className }}"
                                    data-title="{{ $redoActionData[$className]['title'] }}"
                                    data-remark="{{ $redoActionData[$className]['remarks'] }}"
                                    data-start="{{ date('d-m-Y H:i', strtotime($redoActionData[$className]['start_at'])) }}"
                                    data-end="{{ date('d-m-Y H:i', strtotime($redoActionData[$className]['completed_by'])) }}"
                                    data-lsub="{{ $redoActionData[$className]['do_not_allow_late_submission'] }}"                                
                                @endif
                                >

                                @if(property_exists($field, 'approved') && $field->approved == 'no' && isset($redoActionData[$className]))
                                    <i id="modal-{{ $className }}" class="bi bi-info-circle-fill"
                                    data-bs-toggle="modal" data-bs-target="#redomodal"
                                    data-class="{{ $className }}"

                                    data-title="{{ $redoActionData[$className]['title'] }}"
                                    data-remark="{{ $redoActionData[$className]['remarks'] }}"
                                    data-start="{{ date('d-m-Y H:i', strtotime($redoActionData[$className]['start_at'])) }}"
                                    data-end="{{ date('d-m-Y H:i', strtotime($redoActionData[$className]['completed_by'])) }}"
                                    data-lsub="{{ $redoActionData[$className]['do_not_allow_late_submission'] }}"
                                    ></i>

                                    <input type="hidden" id="action-{{ $className }}" name="action[{{ $className }}]" value="{{ json_encode([
                                        'title' => $redoActionData[$className]['title'],
                                        'remark' => $redoActionData[$className]['remarks'],
                                        'start' => date('d-m-Y H:i', strtotime($redoActionData[$className]['start_at'])),
                                        'end' => date('d-m-Y H:i', strtotime($redoActionData[$className]['completed_by'])),
                                        'lsub' => $redoActionData[$className]['do_not_allow_late_submission']
                                    ]) }}">
                                @else
                                    <i id="modal-{{ $className }}" class="bi bi-info-circle-fill d-none"
                                    data-bs-toggle="modal" data-bs-target="#redomodal"
                                    data-class="{{ $className }}"                                    

                                    data-title=""
                                    data-remark=""
                                    data-start=""
                                    data-end=""
                                    data-lsub=""                                    
                                    ></i>

                                    <input type="hidden" id="action-{{ $className }}" name="action[{{ $className }}]">
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $maxFieldCount + 1 }}">
                            <center>
                                No Data Found
                            </center>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                </table>
            </div>

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
    
    <div>
        <center>
            <a href="{{ route('scheduled-tasks.index') }}" class="btn btn-primary"> Back </a>
            <button type="submit" class="btn btn-success"> Update </button>
        </center>
    </div>
</form>


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







<div class="modal fade" id="redomodal" tabindex="-1" aria-labelledby="redomodalLabel" aria-hidden="true" tabindex="-1"
  data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true" aria-hidden="true">
  <form id="redoform">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="redomodalLabel">Reassignment Details</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">

          <div class="mb-2">
            <label class="form-label"> Title </label>
            <input type="text" class="form-control" id="modal-title" required>
          </div>

          <div class="mb-2">
            <label class="form-label"> Remarks </label>
            <textarea id="modal-remarks" class="form-control" required></textarea>
          </div>

          <div class="mb-2 row">
            <div class="col-6">
              <label class="form-label"> Start at </label>
              <input type="text" class="form-control" id="modal-from" required>
            </div>
            <div class="col-6">
              <label class="form-label"> Completed By </label>
              <input type="text" class="form-control" id="modal-to" required>
            </div>
          </div>

          <div class="mb-2">
            <input type="checkbox" id="modal-lsub" />
            <label class="form-label" for="modal-lsub"> Do not allow late submission </label>
          </div>

          <div class="mb-2 row" id="media-gal">

          </div>

        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </div>
  </div>
</form>
</div>
@endsection
@push('js')
<script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
<script>
   $(document).ready(function($){
   
       let currentClass = null;
       let currentFieldLastStatus = null;
       let modalClosed = false;

       let currentIndex = 0;
       let scale = 1;
       let isDragging = false;
       let startX = 0, startY = 0;
       let moveX = 0, moveY = 0;
       let images = $(".thumbnail").map(function() { return $(this).attr("src"); }).get();
   
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

        $('#modal-from').datetimepicker({
            format:'d-m-Y H:i'
        });

        $('#modal-to').datetimepicker({
            format:'d-m-Y H:i'
        });

       $('#redoform').on('submit', function (e) {
            e.preventDefault();
        
            $(`#modal-${currentClass}`).data('title', $('#modal-title').val());
            $(`#modal-${currentClass}`).data('remark', $('#modal-remarks').val());
            $(`#modal-${currentClass}`).data('start', $('#modal-from').val());
            $(`#modal-${currentClass}`).data('end', $('#modal-to').val());
            $(`#modal-${currentClass}`).data('lsub', $('#modal-lsub').is(':checked'));

            let obj = {
                'title' : $('#modal-title').val(),
                'remark' : $('#modal-remarks').val(),
                'start' : $('#modal-from').val(),
                'end' : $('#modal-to').val(),
                'lsub' : $('#modal-lsub').is(':checked')
            };

            modalClosed = true;
            $(`#action-${currentClass}`).val(JSON.stringify(obj));

            $('#redomodal').modal('hide');
            $('.modal-backdrop').remove();
            $('body').css({
                'overflow' : 'auto'
            });
       });

       $(document).on('change', '.input-action', function () {
            let className = $(this).data('classname');
            let type = $(this).data('type');
            let lastdata = $(this).data('lastdata');
            
            currentFieldLastStatus = lastdata;
            currentClass = className;

            if (type == 'decline') {
                $(`#modal-${className}`).removeClass('d-none');
                $('#redomodal').modal('show');
            } else {
                $(`#modal-${className}`).addClass('d-none');
            }
        
       });

       $(document).on('shown.bs.modal', '#redomodal', function (e) {
            if (e.namespace == 'bs.modal') {
                let data = $(e.relatedTarget);
                modalClosed = true;

                if (data.length == 0) {
                    data = $(`#modal-${currentClass}`);

                    $('#modal-title').val(data.data('title'));
                    $('#modal-remarks').val(data.data('remark'));
                    $('#modal-from').val(data.data('start'));
                    $('#modal-to').val(data.data('end'));
                    $('#modal-lsub').prop('checked', data.data('lsub') == 1 ? true : false);                    
                } else {
                    $('#modal-title').val(data.data('title'));
                    $('#modal-remarks').val(data.data('remark'));
                    $('#modal-from').val(data.data('start'));
                    $('#modal-to').val(data.data('end'));
                    $('#modal-lsub').prop('checked', data.data('lsub') == 1 ? true : false);
                }

                currentClass = data.data('class');

                let html = '';
                $(`.ximg-${currentClass}`).each(function (ind, el) {
                    html += `<div class="col-md-4"> <a target="_blank" href="${$(el).attr('src')}"> <img src="${$(el).attr('src')}" style="width: 300px;border: 1px solid;border-radius: 10px;cursor: pointer;height:100%;object-fit:cover;"> </a> </div>`;
                });                

                $(`#media-gal`).html(html);
            }
       });

        $(document).on('hidden.bs.modal', '#redomodal', function (e) {
            if (e.namespace == 'bs.modal') {
                if (currentClass != null && currentFieldLastStatus != null) {
                    if (currentFieldLastStatus == 'yes' && modalClosed === false) {
                        $(`#modal-${currentClass}`).addClass('d-none');
                        $(`#decline-${currentClass}`).prop('checked', false);
                        $(`#approve-${currentClass}`).prop('checked', true);
                    }
                }

                $('#modal-title').val('');
                $('#modal-remarks').val('');
                $('#modal-from').val('');
                $('#modal-to').val('');
                $('#modal-lsub').prop('checked', false);
                
                modalClosed = false;
            }
       });

       $(".thumbnail").click(function() {
           showLightbox($(this).data('index'));
       });
   
       $(".close").click(function() { $(".lightbox").fadeOut(); });
   
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

       function getFlaggedItems() {
            $.ajax({
                url: "{{ route('truthy-falsy') }}",
                type: 'GET',
                data: {
                    'type' : 'falsy',
                    'task_id' : "{{ $task->id }}"
                },
                beforeSend: function () {
                    $('body').find('.LoaderSec').removeClass('d-none');
                },
                success: function (response) {
                    if (response.status) {
                        $('#append-flagged-items').html(response.html);
                        // $('#append-flagged-items table.dataTable').DataTable().destroy();
                        // $('#append-flagged-items table').DataTable({
                        //     pageLength: 10,
                        // });
                    }
                },
                complete: function () {
                    $('body').find('.LoaderSec').addClass('d-none');
                }
            });
       }
   
       getFlaggedItems();
   });
</script>  
@endpush
