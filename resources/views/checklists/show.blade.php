@extends('layouts.app-master')

@push('css')
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<style>
  .ui-widget-content {
      border: none!important;
      background: transparent!important;
      color: #333333;
  }

  .frmb {
    margin-right: 30px!important;
  }

  button.clear-all.btn.btn-danger,
  button.get-data.btn.btn-default,
  button.save-template.btn.btn-primary {
      display: none !important;
  }

  /* .modal-form-wrapper > .ui-widget-content {
    background: radial-gradient(#0000006b, #00000036)!important;
  } */

</style>
@endpush

@section('content')
    <div class="bg-light p-4 rounded">

        <div class="row mt-4">

            <form id="form-builder-pages">
                @csrf @method('PUT')
                <input type="hidden" id="json" name="form_schema" class="form-control" value="{{ json_encode($form->schema) }}" />

                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger"> * </span> </label>
                    <input type="text" name="name" class="form-control" id="name" disabled
                        value="{{ old('name', $form->name) }}" placeholder="Enter name">
                </div>

                <div class="mb-3">
                  <label for=""> Remove Media </label>
                  <select class="form-control" name="remove_media" disabled>
                    <option value="never" @if($form->remove_media_frequency == 'never') selected @endif>Never</option>
                    <option value="every_n_day" @if($form->remove_media_frequency == 'every_n_day') selected @endif>Every N Days</option>
                  </select>
                </div>

                <div class="mb-3">
                  <div class="row">
                    <div class="col-4">
                      <input disabled type="checkbox" name="is_point_checklist" id="is_point_checklist" value="1" style="height:20px;width:20px;" @if($form->is_point_checklist) checked @endif>
                      <label for="is_point_checklist" class="form-label" style="position: relative;bottom: 5px;left: 3px;"> Is point checklist </label>
                    </div>
                    <div class="col-4">
                      <input disabled type="checkbox" name="is_geofencing_enabled" id="is_geofencing_enabled" value="1" style="height:20px;width:20px;" @if($form->is_geofencing_enabled) checked @endif>
                      <label for="is_geofencing_enabled" class="form-label" style="position: relative;bottom: 5px;left: 3px;"> Is geo-fencing enabled </label>
                    </div>
                    <div class="col-4">
                      <input disabled type="checkbox" name="is_store_checklist" id="is_store_checklist" value="1" style="height:20px;width:20px;" @if($form->is_store_checklist) checked @endif>
                      <label for="is_store_checklist" class="form-label" style="position: relative;bottom: 5px;left: 3px;"> Is store checklist </label>
                    </div>
                  </div>
                </div>

                <div class="mb-3 position-relative" style="background: #b1b1b1df;padding: 10px;">
                    <div class="overlay"></div>
                    <ul id="tabs">
                        @foreach ($form->schema as $page)
                        <li><a href="#page-{{ $loop->iteration }}">Page {{ $loop->iteration }}</a></li>
                        @endforeach

                        <li id="add-page-tab"><a href="#new-page">+ Page</a></li>
                    </ul>
                    @foreach ($form->schema as $page)
                    <div id="page-{{ $loop->iteration }}" class="fb-editor"></div>
                    @endforeach

                </div>

                <div class="mb-3">
                  <input type="checkbox" name="amtosd" id="amtosd" value="1" @if(isset($form->allow_double_rescheduling) && $form->allow_double_rescheduling) checked @endif>
                  <label for="amtosd" class="form-label"> Allow Multiple Task on Same Day </label>
                </div>

                <hr>
                <h3> Set Notifications </h3>
                <hr> <br>

                <div class="mb-3">
                  <label for="not_1" class="form-label"> Hour before starting </label>
                  <select name="not_1[]" id="not_1" class="form-control" class="form-control" multiple>
                    @foreach($form->presetemplates()->where('type', 1)->get() as $notification)
                        <option value="{{ $notification->notification_template_id }}" selected> {{ ucwords(\App\Models\NotificationTemplate::typeOf($notification->ntemp->type)) }} - {{ $notification->ntemp->name }} </option>
                    @endforeach
                  </select>
                </div>

                <div class="mb-3">
                  <label for="not_2" class="form-label"> If the inspection is not initiated half past the starting time </label>
                  <select name="not_2[]" id="not_2" class="form-control" class="form-control" multiple>
                    @foreach($form->presetemplates()->where('type', 2)->get() as $notification)
                        <option value="{{ $notification->notification_template_id }}" selected> {{ ucwords(\App\Models\NotificationTemplate::typeOf($notification->ntemp->type)) }} - {{ $notification->ntemp->name }} </option>
                    @endforeach
                  </select>
                </div>

                <div class="mb-3">
                  <label for="not_3" class="form-label"> Quarter to ending time </label>
                  <select name="not_3[]" id="not_3" class="form-control" class="form-control" multiple>
                    @foreach($form->presetemplates()->where('type', 3)->get() as $notification)
                        <option value="{{ $notification->notification_template_id }}" selected> {{ ucwords(\App\Models\NotificationTemplate::typeOf($notification->ntemp->type)) }} - {{ $notification->ntemp->name }} </option>
                    @endforeach
                  </select>
                </div>

                <div class="mb-3">
                  <label for="not_4" class="form-label"> On reschedule request </label>
                  <select name="not_4[]" id="not_4" class="form-control" class="form-control" multiple>
                    @foreach($form->presetemplates()->where('type', 4)->get() as $notification)
                        <option value="{{ $notification->notification_template_id }}" selected> {{ ucwords(\App\Models\NotificationTemplate::typeOf($notification->ntemp->type)) }} - {{ $notification->ntemp->name }} </option>
                    @endforeach
                  </select>
                </div>

                <div class="mb-3">
                  <label for="not_5" class="form-label"> On reschedule approval </label>
                  <select name="not_5[]" id="not_5" class="form-control" class="form-control" multiple>
                    @foreach($form->presetemplates()->where('type', 5)->get() as $notification)
                        <option value="{{ $notification->notification_template_id }}" selected> {{ ucwords(\App\Models\NotificationTemplate::typeOf($notification->ntemp->type)) }} - {{ $notification->ntemp->name }} </option>
                    @endforeach
                  </select>
                </div>

                <div class="mb-3">
                  <label for="not_6" class="form-label"> On reschedule rejection </label>
                  <select name="not_6[]" id="not_6" class="form-control" class="form-control" multiple>
                    @foreach($form->presetemplates()->where('type', 6)->get() as $notification)
                        <option value="{{ $notification->notification_template_id }}" selected> {{ ucwords(\App\Models\NotificationTemplate::typeOf($notification->ntemp->type)) }} - {{ $notification->ntemp->name }} </option>
                    @endforeach
                  </select>
                </div>

                <div class="mb-3">
                  <label for="not_7" class="form-label"> On submission </label>
                  <select name="not_7[]" id="not_7" class="form-control" class="form-control" multiple>
                    @foreach($form->presetemplates()->where('type', 7)->get() as $notification)
                        <option value="{{ $notification->notification_template_id }}" selected> {{ ucwords(\App\Models\NotificationTemplate::typeOf($notification->ntemp->type)) }} - {{ $notification->ntemp->name }} </option>
                    @endforeach
                  </select>
                </div>

                <div class="mb-3">
                  <label for="not_8" class="form-label"> On reassignment </label>
                  <select name="not_8[]" id="not_8" class="form-control" class="form-control" multiple>
                    @foreach($form->presetemplates()->where('type', 8)->get() as $notification)
                        <option value="{{ $notification->notification_template_id }}" selected> {{ ucwords(\App\Models\NotificationTemplate::typeOf($notification->ntemp->type)) }} - {{ $notification->ntemp->name }} </option>
                    @endforeach
                  </select>
                </div>

                <div class="save-all-wrap">
                    <a href="{{ route('checklists.index') }}" class="btn btn-default">Back</a>
                </div>
            </form>
        </div>

    </div>
@endsection

@push('js')
    <script src="{{ url('assets/form-builder/form-builder.min.js') }}"></script>
        <script src="{{ asset('assets/js/form-builder-custom-fields.js') }}"></script>
    <script type="text/javascript">
        jQuery(($) => {
            "use strict";
            var $fbPages = $(document.getElementById("form-builder-pages"));
            var addPageTab = document.getElementById("add-page-tab");
            var fbInstances = [];

            $fbPages.tabs({
                beforeActivate: function(event, ui) {
                    if (ui.newPanel.selector === "#new-page") {
                        return false;
                    }
                }
            });

            addPageTab.addEventListener(
                "click",
                (click) => {
                    const tabCount = document.getElementById("tabs").children.length;
                    const tabId = "page-" + tabCount.toString();
                    const $newPageTemplate = document.getElementById("new-page");
                    const $newTabTemplate = document.getElementById("add-page-tab");
                    const $newPage = $newPageTemplate.cloneNode(true);
                    $newPage.setAttribute("id", tabId);
                    $newPage.classList.add("fb-editor");
                    const $newTab = $newTabTemplate.cloneNode(true);
                    $newTab.removeAttribute("id");
                    const $tabLink = $newTab.querySelector("a");
                    $tabLink.setAttribute("href", "#" + tabId);
                    $tabLink.innerText = "Page " + tabCount;

                    $newPageTemplate.parentElement.insertBefore($newPage, $newPageTemplate);
                    $newTabTemplate.parentElement.insertBefore($newTab, $newTabTemplate);
                    $fbPages.tabs("refresh");
                    $fbPages.tabs("option", "active", tabCount - 1);
                    fbInstances.push($($newPage).formBuilder(fieldsOption));
                },
                false
            );


            @foreach ($form->schema as $key => $page)
            fbInstances.push($("#page-{{ $loop->iteration }}").formBuilder({
                formData: @json($form->schema[$key]),
                dataType: 'json',
                    fields: customFields,
                    templates: customFieldsTemplates,
                    disableFields: [], 
                    controlOrder: [
                        "radio-group", 
                        "file", 
                        "checkbox-group", 
                        "checkbox", 
                        "hidden", 
                        "select", 
                        "number", 
                        "date", 
                        "text", 
                        "textarea", 
                        "button", 
                        "autocomplete", 
                        "paragraph", 
                        "header", 
                        "signature"
                    ],
                    typeUserAttrs: {
                        signature: {
                            value: {
                                label: '',
                                type: 'text',
                                description: 'Signature'
                            }
                        }
                    },
                    i18n: {
                        locale: 'en-US',
                        extension: {
                            'signature': 'Signature'
                        }
                    }
            }));
            @endforeach
        });
    </script>
@endpush
