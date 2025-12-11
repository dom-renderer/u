@extends('layouts.app-master')

@push('css')
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<style>
    .select2-container .select2-search--inline .select2-search__field {
        height: 20px !important;
    }

    .select2-container--classic .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
    }

    .select2-container--classic .select2-selection--single {
        height: 40px !important;
    }

    .select2-container--classic .select2-selection--single .select2-selection__clear {
        height: 37px !important;
    }

    .select2-container--classic .select2-selection--single .select2-selection__rendered {
        line-height: 39px !important;
    }    
    
    .select2-container {
        background: none;
        border: none;
    }
</style>
@endpush

@section('content')
    <div class="bg-light p-4 rounded">

        <div class="container mt-4">

            <form>
                <div class="mb-3">
                    <label for="zp_document_file" class="form-label">Document File<span class="text-danger"> *</span></label>
                    <br><a href="{{ $documentupload->attachment_path }}" target="_blank" rel="noopener noreferrer">View</a>
                </div>

                <div class="mb-3">
                    <label for="zp_document" class="form-label">Document<span class="text-danger"> *</span></label>
                    <select name="zp_document" id="zp_document" class="form-control" disabled>
                        <option value=""></option>
                        @if( !empty($document_arr) )
                            @foreach ( $document_arr as $document_row )
                                <option value="{{ $document_row->id }}" {{ $documentupload->document_id == $document_row->id ? 'selected' : '' }}>{{ $document_row->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_location">Location<span class="text-danger"> *</span></label>
                    <select name="zp_location" id="zp_location" disabled>
                        @if ( !empty($documentupload->store) )
                            <option value="{{ $documentupload->store->id }}" selected>{{ $documentupload->store->code }} - {{ $documentupload->store->name }}</option>
                        @endif
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_perpetual">Perpetual</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="zp_perpetual" {{ $documentupload->perpetual ? 'checked' : '' }} disabled>
                        <label class="form-check-label" for="zp_perpetual">{{ $documentupload->perpetual ? 'Yes (No Expiry Date)' : 'No' }}</label>
                    </div>
                </div>

                @if (!$documentupload->perpetual)
                <div class="mb-3">
                    <label class="form-label" for="zp_expiry_date">Expiry Date<span class="text-danger"> *</span></label>
                    <input type="text" class="form-control" value="{{ $documentupload->expiry_date }}" disabled>
                </div>
                @endif

                <div class="mb-3">
                    <label class="form-label" for="zp_issue_date">Issue Date<span class="text-danger"> *</span></label>
                    <input type="text" class="form-control zp_datepicker" placeholder="Select Issue Date" name="zp_issue_date" id="zp_issue_date" autocomplete="off" value="{{ old( 'zp_issue_date', $documentupload->issue_date ) }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_remark">Remark<span class="text-danger"> *</span></label>
                    <textarea name="zp_remark" id="zp_remark" class="form-control" placeholder="Enter Remark" disabled>{{ old( 'zp_remark', $documentupload->remark ) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_users">Users</label>
                    <select name="zp_users[]" id="zp_users" multiple disabled>
                        @if ( !empty($documentupload->users) )
                            @foreach ( $documentupload->users as $user_row )
                                <option value="{{ $user_row->id }}" selected>{{ (!empty($user_row->employee_id) ? "{$user_row->employee_id} - " : '') . "{$user_row->name} {$user_row->middle_name} {$user_row->last_name}" }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_template">Template</label>
                    <select name="zp_template[]" id="zp_template" multiple disabled>
                        @if( !empty($notification_template_arr) )
                            @foreach ( $notification_template_arr as $notification_template_row )
                                <option value="{{ $notification_template_row->id }}" {{ in_array( $notification_template_row->id, $notification_id_arr ) ? 'selected' : '' }}>{{ $notification_template_row->title }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_additional_users">Additional Users</label>
                    <select name="zp_additional_users[]" id="zp_additional_users" multiple disabled>
                        @if ( !empty($documentupload->addusers) )
                            @foreach ( $documentupload->addusers as $user_row )
                                <option value="{{ $user_row->id }}" selected>{{ (!empty($user_row->employee_id) ? "{$user_row->employee_id} - " : '') . "{$user_row->name} {$user_row->middle_name} {$user_row->last_name}" }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Allow Access to Store</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="zp_enable_store_access" {{ $documentupload->enable_store_access ? 'checked' : '' }} disabled>
                            <label class="form-check-label" for="zp_enable_store_access">{{ $documentupload->enable_store_access ? 'Enabled' : 'Disabled' }}</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Allow Access to DoM</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="zp_enable_dom_access" {{ $documentupload->enable_dom_access ? 'checked' : '' }} disabled>
                            <label class="form-check-label" for="zp_enable_dom_access">{{ $documentupload->enable_dom_access ? 'Enabled' : 'Disabled' }}</label>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Allow Access to Operational Manager</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="zp_enable_operation_manager_access" {{ $documentupload->enable_operation_manager_access ? 'checked' : '' }} disabled>
                            <label class="form-check-label" for="zp_enable_operation_manager_access">{{ $documentupload->enable_operation_manager_access ? 'Enabled' : 'Disabled' }}</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="zp_status" {{ $documentupload->status ? 'checked' : '' }} disabled>
                            <label class="form-check-label" for="zp_status">{{ ucfirst($documentupload->status) }}</label>
                        </div>
                    </div>
                </div>

                <a href="{{ route('document-upload.index') }}" class="btn btn-default">Back</a>
            </form>
        </div>

    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#documentUploadForm').validate({
                rules: {
                    zp_document_file: {
                        required: function(element) {
                            return $( '#zp_old_document_file' ).val() == '';
                        },
                    },
                    zp_document: { required: true },
                    zp_location: { required: true },
                    zp_expiry_date: { required: true, date: true },
                    zp_issue_date: { required: true, date: true },
                    zp_remark: { required: true, minlength: 5 }
                },
                errorPlacement: function(error, element) {
                    error.appendTo(element.parent("div"));
                },
                submitHandler: function(form) {
                    return false;
                }
            });
            $('.select2').select2({
                width: '100%',
                theme: 'classic',
            });
            $('#zp_document').select2({
                placeholder: 'Select document',
                allowClear: true,
                width: '100%',
                theme: 'classic',
            });
            $('#zp_template').select2({
                placeholder: 'Select Notification Template',
                allowClear: true,
                width: '100%',
                theme: 'classic',
            });
            $('#zp_location').select2({
                placeholder: 'Select location',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('stores-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,  
                            _token: "{{ csrf_token() }}",
                            showCode: 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            });
            $('#zp_users').select2({
                placeholder: 'Select Users',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('users-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            ignoreDesignation: 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            });
            $('.zp_datepicker').datepicker({
                dateFormat: "yy-mm-dd",
                todayBtn: true,
                todayHighlight: true,
                orientation: "bottom auto",
                autoclose: true,
                startDate: '1d'
            });
        });
    </script>
@endpush