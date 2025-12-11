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

            <form method="POST" action="{{ route('checklists.store') }}" id="form-builder-pages">
                <input type="hidden" id="json" name="form_schema" class="form-control" />

                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger"> * </span> </label>
                    <input type="text" name="name" class="form-control" id="name" value="{{ old('name') }}" placeholder="Enter name" required>
                </div>

                <div class="mb-3">
                  <label for=""> Remove Media </label>
                  <select class="form-control" name="remove_media">
                    <option value="never">Never</option>
                    <option value="every_n_day">Every N Days</option>
                  </select>
                </div>

                <div class="mb-3" id="every_n_day_wrapper" style="display:none;">
                    <input type="number" id="every_n_day_input" name="every_n_day_input" class="form-control d-inline-block"
                          style="width:150px;" placeholder="Enter days" min="1">
                </div>

                <div class="mb-3">
                  <div class="row">
                    <div class="col-6">
                      <input type="checkbox" name="is_point_checklist" id="is_point_checklist" value="1" style="height:20px;width:20px;">
                      <label for="is_point_checklist" class="form-label" style="position: relative;bottom: 5px;left: 3px;"> Is point checklist </label>
                    </div>
                    <div class="col-6">
                      <input type="checkbox" name="is_geofencing_enabled" id="is_geofencing_enabled" value="1" style="height:20px;width:20px;">
                      <label for="is_geofencing_enabled" class="form-label" style="position: relative;bottom: 5px;left: 3px;"> Is geo-fencing enabled </label>
                    </div>
                  </div>
                </div>

                <div class="mb-3">

                        <ul id="tabs">
                          <li><a href="#page-1">Page 1</a></li>
                          <li id="add-page-tab"><a href="#new-page">+ Page</a></li>
                        </ul>
                        <div id="page-1" class="fb-editor"></div>
                        <div id="new-page"></div>

                </div>

                <div class="save-all-wrap">
                    <button id="save-all" type="button" class="btn btn-primary">Save</button>
                    <a href="{{ route('checklists.index') }}" class="btn btn-default">Back</a>
                </div>
            </form>
        </div>

    </div>


<div class="modal fade" id="multiFieldQuesiton" tabindex="-1" aria-labelledby="multiFieldQuesitonLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="multiFieldQuesitonLabel">Multi Field Question</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label for="page_no" class="form-label"> Page </label>
              <input type="number" min="0" class="form-control" value="1">
            </div>

            <div class="form-group mt-2 modal-form-wrapper">
              <label for="form_fields" class="form-label"> Form </label>
              <div id="form-builder-inside-modal"></div>
            </div>            
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Add to Page</button>
      </div>
    </div>
  </div>
</div>    
@endsection

@push('js')
<script src="{{ url('assets/form-builder/form-builder.min.js') }}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/form-builder-custom-fields.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {

      //$('#multiFieldQuesiton').on('shown.bs.modal', function () {
        //$('#form-builder-inside-modal').formBuilder(fieldsOption);
      //});

      $("select[name='remove_media']").on("change", function() {
          let value = $(this).val();

          if (value === "every_n_day") {
              $("#every_n_day_wrapper").show();
          } else {
              $("#every_n_day_wrapper").hide();
              $("#every_n_day_input").val("");
          }
      });

      $("#every_n_day_input").on("input", function() {
          let num = parseInt($(this).val(), 10);
          if (num <= 0 || isNaN(num)) {
              $(this).val("");
          }
      });

     
      var $fbPages = $(document.getElementById("form-builder-pages"));
      var addPageTab = document.getElementById("add-page-tab");
      var fbInstances = [];

      $fbPages.tabs({
        beforeActivate: function (event, ui) {
          if (ui.newPanel.selector === "#new-page") {
            return false;
          }
        }
      });

      $fbPages.tabs("option", "active", 0);

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

      fbInstances.push($(".fb-editor").formBuilder(fieldsOption));

      $(document.getElementById("save-all")).click(function () {
        const allData = fbInstances.map((fb) => {
          return fb.formData;
        });

        $.ajax({
            url: "{{ route('checklists.store') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                name: $('#name').val(),
                is_point_checklist: $('#is_point_checklist').is(':checked') ? 1 : 0,
                is_geofencing_enabled: $('#is_geofencing_enabled').is(':checked') ? 1 : 0,
                remove_media : function () {
                  return $("select[name='remove_media']").val();
                },
                every_n_day_input : function () {
                  return $('#every_n_day_input').val();
                },
                form_schema: allData,            },
            beforeSend: function () {
                $('body').find('.LoaderSec').removeClass('d-none');
            },
            success: function (response) {
                if (response.status) {
                    Swal.fire('Success', response.message, 'success');
                    window.location.replace("{{ route('checklists.index') }}");
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function (response) {            
                if ('responseJSON' in response && 'errors' in response.responseJSON) {
                    if ('name' in response.responseJSON.errors) {
                        if (response.responseJSON.errors.name.length > 0) {
                            Swal.fire('Error', response.responseJSON.errors.name[0], 'error');
                        }
                    }
                }
            },
            complete: function (response) {
                $('body').find('.LoaderSec').addClass('d-none');
            }
        }); 

      });

    });
</script>
@endpush