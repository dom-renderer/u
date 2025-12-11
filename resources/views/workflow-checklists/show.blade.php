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
                    <div class="col-6">
                      <input disabled type="checkbox" name="is_point_checklist" id="is_point_checklist" value="1" style="height:20px;width:20px;" @if($form->is_point_checklist) checked @endif>
                      <label for="is_point_checklist" class="form-label" style="position: relative;bottom: 5px;left: 3px;"> Is point checklist </label>
                    </div>
                    <div class="col-6">
                      <input disabled type="checkbox" name="is_geofencing_enabled" id="is_geofencing_enabled" value="1" style="height:20px;width:20px;" @if($form->is_geofencing_enabled) checked @endif>
                      <label for="is_geofencing_enabled" class="form-label" style="position: relative;bottom: 5px;left: 3px;"> Is geo-fencing enabled </label>
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
