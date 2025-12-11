@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/datatables/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/datatables/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}">
<style>
  .wizard-progress {
    padding: 20px 0;
  }
  .wizard-step {
    text-align: center;
    position: relative;
  }
  .step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    font-weight: bold;
    transition: all 0.3s ease;
  }
  .wizard-step.active .step-circle {
    background-color: #0d6efd;
    color: #fff;
  }
  .wizard-step.completed .step-circle {
    background-color: #198754;
    color: #fff;
  }
  .step-title {
    font-size: 14px;
    color: #6c757d;
  }
  .wizard-step.active .step-title {
    color: #0d6efd;
    font-weight: 500;
  }
  .wizard-connector {
    width: 100px;
    height: 2px;
    background-color: #e9ecef;
    margin-top: 20px;
  }
</style>
@endpush

@section('content')
<div class="bg-light p-4 rounded">
    <h1>{{ $page_title }} </h1>
    <div class="lead">
        {{ $page_description }}
        @if (auth()->user()->can('document-upload.create'))
            <a href="{{ route('document-upload.create') }}" class="btn btn-primary btn-sm float-end">Add Document Upload</a>
        @endif
        @if (auth()->user()->can('document-upload.import'))
        <a href="{{ route('document-upload.import') }}" class="btn btn-success btn-sm float-end me-2" data-bs-toggle="modal" data-bs-target="#staticBackdrop">Import Documents</a>
        <a href="{{ asset('document-import.xlsx') }}" class="btn btn-info btn-sm float-end me-2">Download Import Template</a>
        @endif
    </div>
    
    <div class="mt-2">
        @include('layouts.partials.messages')
    </div>

    <div class="accordion accordion-flush" id="accordionFilters">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseFilters" aria-expanded="false" aria-controls="flush-collapseFilters">
                    Filters
                </button>
            </h2>
            <div id="flush-collapseFilters" class="accordion-collapse collapse" data-bs-parent="#accordionFilters">
                <div class="accordion-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="filterDocumentType" class="form-label">Document Type</label>
                            <select id="filterDocumentType"></select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterLocation" class="form-label">Location</label>
                            <select id="filterLocation"></select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterPerpetual" class="form-label">Perpetual</label>
                            <select id="filterPerpetual"></select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterExpiryDate" class="form-label">Expiry Date</label>
                            <input type="text" id="filterExpiryDate" class="form-control" readonly placeholder="Select date range" />
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-3">
                            <label for="filterIssueDate" class="form-label">Issue Date</label>
                            <input type="text" id="filterIssueDate" class="form-control" readonly placeholder="Select date range" />
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button id="filter-data" class="btn btn-secondary me-2">Search</button>
                            <button id="resetFilters" class="btn btn-danger d-none">Clear</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-content mt-3" id="myTabContent">
        <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
            <table class="table table-striped" id="documentUploadTable" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Document Type</th>
                    <th>Document File</th>
                    <th>Location</th>
                    <th>Expiry Date</th>
                    <th>Issue Date</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div> 


<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="staticBackdropLabel">Import Document</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="documentImportForm" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">

          <div class="wizard-progress mb-4">
            <div class="d-flex justify-content-center">
              <div class="wizard-step active" id="wizard-step-1">
                <div class="step-circle">1</div>
                <div class="step-title">Upload Excel</div>
              </div>
              <div class="wizard-connector"></div>
              <div class="wizard-step" id="wizard-step-2">
                <div class="step-circle">2</div>
                <div class="step-title">Upload ZIP</div>
              </div>
            </div>
          </div>

          <div id="step-1-container">
            <div class="mb-3">
              <label for="excel_file" class="form-label">Excel File (.xlsx)<span class="text-danger"> *</span></label>
              <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xlsx">
              <small class="text-muted">Only .xlsx files allowed. Maximum size: 10MB</small>
            </div>
          </div>

          <div id="step-2-container" style="display: none;">
            <div class="mb-3">
              <label for="zip_file" class="form-label">ZIP File (.zip)<span class="text-danger"> *</span></label>
              <input type="file" name="zip_file" id="zip_file" class="form-control" accept=".zip">
              <small class="text-muted">Only .zip files allowed. Maximum size: 100MB</small>
            </div>
          </div>

          <div id="import-result-container" style="display: none;">
            <div class="alert alert-info" id="import-result-message"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-secondary" id="btn-prev-step" style="display: none;">Previous</button>
          <button type="button" class="btn btn-primary" id="btn-next-step">Next</button>
          <button type="submit" class="btn btn-success" id="btn-upload" style="display: none;">Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection


@push('js')
<script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/daterangepicker.min.js') }}"></script>

<script>
    const csrfToken = "{{ csrf_token() }}";
    var expiryStartDate = null;
    var expiryEndDate = null;
    var issueStartDate = null;
    var issueEndDate = null;

    $(document).ready(function($){

        const documentTypeFilter = $('#filterDocumentType').select2({
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('documents-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        _token: csrfToken
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function (item) {
                            return { id: item.id, text: item.text };
                        }),
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            }
        });

        const locationFilter = $('#filterLocation').select2({
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('stores-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        _token: csrfToken
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function (item) {
                            return { id: item.id, text: item.text };
                        }),
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            }
        });

        const perpetualFilter = $('#filterPerpetual').select2({
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            data: [
                { id: 'all', text: 'All' },
                { id: 'yes', text: 'Yes' },
                { id: 'no', text: 'No' }
            ]
        });

        $('#filterExpiryDate').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'DD-MM-YYYY',
                cancelLabel: 'Clear'
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        $('#filterExpiryDate').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
            expiryStartDate = picker.startDate;
            expiryEndDate = picker.endDate;
        });

        $('#filterExpiryDate').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            expiryStartDate = null;
            expiryEndDate = null;
        });

        $('#filterIssueDate').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'DD-MM-YYYY',
                cancelLabel: 'Clear'
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        $('#filterIssueDate').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
            issueStartDate = picker.startDate;
            issueEndDate = picker.endDate;
        });

        $('#filterIssueDate').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            issueStartDate = null;
            issueEndDate = null;
        });

        $(document).on('click', '.deleteGroup', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this Uploaded Document?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).parents('form').submit();
                    return true;
                } else {
                    return false;
                }
            })
        });

        let usersTable = new DataTable('#documentUploadTable', {
            "dom": '<"d-flex justify-content-between mb-2"<"user-role-table-filter-container">f>rt<"d-flex flex-column float-start mt-3"pi><"clear">',
            ajax: {
                url: "{{ route('document-upload.index') }}",
                data: function ( d ) {
                    return $.extend( {}, d, {
                        document_type_id: $('#filterDocumentType').val(),
                        location_id: $('#filterLocation').val(),
                        perpetual: $('#filterPerpetual').val(),
                        expiry_from: expiryStartDate ? expiryStartDate.format('DD-MM-YYYY') : '',
                        expiry_to: expiryEndDate ? expiryEndDate.format('DD-MM-YYYY') : '',
                        issue_from: issueStartDate ? issueStartDate.format('DD-MM-YYYY') : '',
                        issue_to: issueEndDate ? issueEndDate.format('DD-MM-YYYY') : ''
                    });
                }
            },
            processing: false,
            ordering: false,
            serverSide: true,
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'document_name', name: 'document_name' },
                { data: 'attachment', name: 'file_name', orderable: false, searchable: false },
                { data: 'location', name: 'location' },
                { data: 'expiry_date', name: 'expiry_date' },
                { data: 'issue_date', name: 'issue_date' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            initComplete: function(settings) {

            }
        });

        $(document).on('click', '#filter-data', function () {
            usersTable.ajax.reload();
            $('#resetFilters').removeClass('d-none');
        });

        $('#resetFilters').on('click', function () {
            documentTypeFilter.val(null).trigger('change');
            locationFilter.val(null).trigger('change');
            perpetualFilter.val(null).trigger('change');
            $('#filterExpiryDate').val('');
            $('#filterIssueDate').val('');
            expiryStartDate = null;
            expiryEndDate = null;
            issueStartDate = null;
            issueEndDate = null;
            usersTable.ajax.reload();
            $('#resetFilters').addClass('d-none');
        });

        let currentStep = 1;

        jQuery.validator.addMethod("extension", function(value, element, param) {
            param = typeof param === "string" ? param.replace(/,/g, '|') : "xlsx";
            return this.optional(element) || value.match(new RegExp("\\.(" + param + ")$", "i"));
        }, "Please select a file with a valid extension.");

        jQuery.validator.addMethod("filesize", function(value, element, param) {
            if (element.files.length > 0) {
                return element.files[0].size <= param;
            }
            return true;
        }, "File size exceeds the allowed limit.");

        let importValidator = $('#documentImportForm').validate({
            rules: {
                excel_file: {
                    required: true,
                    extension: "xlsx",
                    filesize: 10485760
                },
                zip_file: {
                    required: true,
                    extension: "zip",
                    filesize: 104857600
                }
            },
            messages: {
                excel_file: {
                    required: "Please select an Excel file",
                    extension: "Only .xlsx files are allowed",
                    filesize: "File size must not exceed 10MB"
                },
                zip_file: {
                    required: "Please select a ZIP file",
                    extension: "Only .zip files are allowed",
                    filesize: "File size must not exceed 100MB"
                }
            },
            errorPlacement: function(error, element) {
                error.addClass('text-danger');
                error.insertAfter(element.parent().find('small'));
            },
            submitHandler: function(form, event) {
                event.preventDefault();
                
                let formData = new FormData(form);

                $.ajax({
                    url: "{{ route('document-upload.import') }}",
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $('#btn-upload').prop('disabled', true).text('Uploading...');
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {
                        $('body').find('.LoaderSec').addClass('d-none');
                        $('#btn-upload').prop('disabled', false).text('Upload');
                        
                        if (response.status) {
                            $('#import-result-container').show();
                            $('#import-result-message').removeClass('alert-danger').addClass('alert-success').html(response.message);
                            
                            setTimeout(function() {
                                $('#staticBackdrop').modal('hide');
                                resetWizard();
                                usersTable.ajax.reload();
                            }, 2000);
                            
                            Swal.fire('Success', response.message, 'success');
                        } else {
                            let errorHtml = response.message;
                            if (response.errors && response.errors.length > 0) {
                                errorHtml += '<ul class="mt-2 mb-0">';
                                response.errors.forEach(function(err) {
                                    errorHtml += '<li>' + err + '</li>';
                                });
                                errorHtml += '</ul>';
                            }
                            $('#import-result-container').show();
                            $('#import-result-message').removeClass('alert-success').addClass('alert-danger').html(errorHtml);
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        $('body').find('.LoaderSec').addClass('d-none');
                        $('#btn-upload').prop('disabled', false).text('Upload');
                        
                        let errorMessage = 'An error occurred while importing documents.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', errorMessage, 'error');
                    }
                });
            }
        });

        function goToStep(step) {
            currentStep = step;
            
            if (step === 1) {
                $('#step-1-container').show();
                $('#step-2-container').hide();
                $('#btn-next-step').show();
                $('#btn-prev-step').hide();
                $('#btn-upload').hide();
                $('#wizard-step-1').addClass('active').removeClass('completed');
                $('#wizard-step-2').removeClass('active');
            } else if (step === 2) {
                $('#step-1-container').hide();
                $('#step-2-container').show();
                $('#btn-next-step').hide();
                $('#btn-prev-step').show();
                $('#btn-upload').show();
                $('#wizard-step-1').removeClass('active').addClass('completed');
                $('#wizard-step-2').addClass('active');
            }
            
            $('#import-result-container').hide();
        }

        function resetWizard() {
            currentStep = 1;
            $('#documentImportForm')[0].reset();
            importValidator.resetForm();
            goToStep(1);
            $('#import-result-container').hide();
        }

        $('#btn-next-step').on('click', function() {
            if ($('#excel_file').valid()) {
                goToStep(2);
            }
        });

        $('#btn-prev-step').on('click', function() {
            goToStep(1);
        });

        $('#staticBackdrop').on('hidden.bs.modal', function() {
            resetWizard();
        });
        
    });
 </script>  
@endpush