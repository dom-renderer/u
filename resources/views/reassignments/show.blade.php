@extends('layouts.app-master')
@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
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
    $tempT = $task->task->data;

    if (is_string($tempT)) {
        $data = json_decode($tempT, true);
    } else if (is_array($tempT)) {
        $data = $tempT;
    } else {
        $data = [];
    }

    $groupedData = [];
    foreach ($data as $item) {
        if (in_array($item->className, $allClass)) {
            $groupedData[$item->className][] = $item;
        }
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
@endphp
@section('content')
<div class="bg-light p-4 rounded">

    <div class="container-for-data">
        <div class="bg-light p-4 rounded">
            <div class="row">
                <table class="table table-bordered table-striped gallery">
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th colspan="{{ $maxFieldCount }}"></th>
                            <th width="8%"> Status </th>
                            <th width="8%"> Reassignment Info </th>
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
                        @if(is_array($field->value_label))
                        <td> {!! implode(',', $field->value_label) !!} </td>
                        @else
                        <td> {!! $field->value_label !!} ({{ is_array($field->value) ? implode(',', $field->value) : $field->value }}) </td>
                        @endif
                        @else
                        @if(is_array($field->value))
                        <td> {!! implode(',', $field->value) !!} </td>
                        @else
                        <td> {!! $field->value !!} {{ $field->value }} </td>
                        @endif
                        @endif
                        @endif
                        @endforeach
                        <td>
                            @if($allData[$className]['status'] == 1)
                                <span class="badge bg-success"> Completed </span>
                            @else
                                <span class="badge bg-warning"> Pending </span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-primary"
                            data-bs-toggle="modal" data-bs-target="#redomodal"

                            data-class="{{ $className }}"
                            data-title="{{ $allData[$className]['title'] }}"
                            data-remark="{{ $allData[$className]['remarks'] }}"
                            data-start="{{ date('d-m-Y H:i', strtotime($allData[$className]['start_at'])) }}"
                            data-end="{{ date('d-m-Y H:i', strtotime($allData[$className]['completed_by'])) }}"
                            data-lsub="{{ $allData[$className]['do_not_allow_late_submission'] }}"
                            >
                            View
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $maxFieldCount + 1 }}">
                            No Data Found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div>
        <center>
            <a href="{{ route('reassignments.index') }}" class="btn btn-primary"> Back </a>
        </center>
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



<div class="modal fade" id="redomodal" tabindex="-1" aria-labelledby="redomodalLabel" aria-hidden="true" tabindex="-1"
  data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true" aria-hidden="true">
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
            <input type="text" class="form-control" id="modal-title" readonly>
          </div>

          <div class="mb-2">
            <label class="form-label"> Remarks </label>
            <textarea id="modal-remarks" class="form-control" readonly></textarea>
          </div>

          <div class="mb-2 row">
            <div class="col-6">
              <label class="form-label"> Start at </label>
              <input type="text" class="form-control" id="modal-from" readonly>
            </div>
            <div class="col-6">
              <label class="form-label"> Completed By </label>
              <input type="text" class="form-control" id="modal-to" readonly>
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
      </div>
    </div>
  </div>

</div>

@endsection
@push('js')
<script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
<script>
   $(document).ready(function($){

       let currentIndex = 0;
       let scale = 1;
       let isDragging = false;
       let startX = 0, startY = 0;
       let moveX = 0, moveY = 0;
       let images = $(".thumbnail").map(function() { return $(this).attr("src"); }).get();
   
       $(document).on('shown.bs.modal', '#redomodal', function (e) {
            if (e.namespace == 'bs.modal') {
                let data = $(e.relatedTarget);

                $('#modal-title').val(data.data('title'));
                $('#modal-remarks').val(data.data('remark'));
                $('#modal-from').val(data.data('start'));
                $('#modal-to').val(data.data('end'));
                $('#modal-lsub').prop('checked', data.data('lsub') == 1 ? true : false);

                let html = '';
                $(`.ximg-${data.data('class')}`).each(function (ind, el) {
                    html += `<div class="col-md-4"> <a target="_blank" href="${$(el).attr('src')}"> <img src="${$(el).attr('src')}" style="width: 300px;border: 1px solid;border-radius: 10px;cursor: pointer;height:100%;object-fit:cover;"> </a> </div>`;
                });                

                $(`#media-gal`).html(html);
            }
       });

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
   
   });
</script>  
@endpush
