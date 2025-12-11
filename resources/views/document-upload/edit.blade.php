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

            <form method="POST" action="{{ route( 'document-upload.update', encrypt( $documentupload->id ) ) }}" id="documentUploadForm" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label for="zp_document_file" class="form-label">Document File<span class="text-danger"> *</span></label>
                    <input type="file" name="zp_document_file" id="zp_document_file" class="form-control">
                    <a href="{{ $documentupload->attachment_path }}" target="_blank" rel="noopener noreferrer">View</a>
                    <input type="hidden" name="zp_old_document_file" value="{{ $documentupload->file_name }}">

                    @if ( $errors->has( 'zp_document_file' ) )
                        <span class="text-danger text-left">{{ $errors->first( 'zp_document_file' ) }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="zp_document" class="form-label">Document<span class="text-danger"> *</span></label>
                    <select name="zp_document" id="zp_document" class="form-control">
                        <option value=""></option>
                        @if( !empty($document_arr) )
                            @foreach ( $document_arr as $document_row )
                                <option value="{{ $document_row->id }}" {{ $documentupload->document_id == $document_row->id ? 'selected' : '' }}>{{ $document_row->name }}</option>
                            @endforeach
                        @endif
                    </select>

                    @if ( $errors->has( 'zp_document' ) )
                        <span class="text-danger text-left">{{ $errors->first( 'zp_document' ) }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_location">Location<span class="text-danger"> *</span></label>
                    <select name="zp_location" id="zp_location">
                        @if ( !empty($documentupload->store) )
                            <option value="{{ $documentupload->store->id }}" selected>{{ $documentupload->store->code }} - {{ $documentupload->store->name }}</option>
                        @endif
                    </select>

                    @if ( $errors->has( 'zp_location' ) )
                        <span class="text-danger text-left">{{ $errors->first( 'zp_location' ) }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_perpetual">Perpetual</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="zp_perpetual" id="zp_perpetual" value="1" {{ old('zp_perpetual', $documentupload->perpetual) ? 'checked' : '' }}>
                        <label class="form-check-label" for="zp_perpetual">Enable Perpetual (No Expiry Date)</label>
                    </div>
                </div>

                <div class="mb-3" id="expiry_date_container">
                    <label class="form-label" for="zp_expiry_date">Expiry Date<span class="text-danger"> *</span></label>
                    <input type="text" class="form-control zp_datepicker" placeholder="Select Expiry Date" name="zp_expiry_date" id="zp_expiry_date" autocomplete="off" value="{{ old( 'zp_expiry_date', $documentupload->expiry_date ) }}">

                    @if ( $errors->has( 'zp_expiry_date' ) )
                        <span class="text-danger text-left">{{ $errors->first( 'zp_expiry_date' ) }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_issue_date">Issue Date<span class="text-danger"> *</span></label>
                    <input type="text" class="form-control zp_datepicker" placeholder="Select Issue Date" name="zp_issue_date" id="zp_issue_date" autocomplete="off" value="{{ old( 'zp_issue_date', $documentupload->issue_date ) }}">

                    @if ( $errors->has( 'zp_issue_date' ) )
                        <span class="text-danger text-left">{{ $errors->first( 'zp_issue_date' ) }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_remark">Remark<span class="text-danger"> *</span></label>
                    <textarea name="zp_remark" id="zp_remark" class="form-control" placeholder="Enter Remark">{{ old( 'zp_remark', $documentupload->remark ) }}</textarea>

                    @if ( $errors->has( 'zp_remark' ) )
                        <span class="text-danger text-left">{{ $errors->first( 'zp_remark' ) }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_users">Notification Users</label>
                    <select name="zp_users[]" id="zp_users" multiple>
                        @if ( !empty($documentupload->users) )
                            @foreach ( $documentupload->users as $user_row )
                                <option value="{{ $user_row->id }}" selected>{{ (!empty($user_row->employee_id) ? "{$user_row->employee_id} - " : '') . "{$user_row->name} {$user_row->middle_name} {$user_row->last_name}" }}</option>
                            @endforeach
                        @endif
                    </select>

                    @if ( $errors->has( 'zp_users' ) )
                        <span class="text-danger text-left">{{ $errors->first( 'zp_users' ) }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_template">Template</label>
                    <select name="zp_template[]" id="zp_template" multiple>
                        @if( !empty($notification_template_arr) )
                            @foreach ( $notification_template_arr as $notification_template_row )
                                <option value="{{ $notification_template_row->id }}" {{ in_array( $notification_template_row->id, $notification_id_arr ) ? 'selected' : '' }}>{{ $notification_template_row->title }}</option>
                            @endforeach
                        @endif
                    </select>

                    @if ( $errors->has( 'zp_template' ) )
                        <span class="text-danger text-left">{{ $errors->first( 'zp_template' ) }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label" for="zp_additional_users">Additional Users</label>
                    <select name="zp_additional_users[]" id="zp_additional_users" multiple>
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
                            <input class="form-check-input" type="checkbox" role="switch" name="zp_enable_store_access" id="zp_enable_store_access" value="1" {{ old('zp_enable_store_access', $documentupload->enable_store_access) ? 'checked' : '' }}>
                            <label class="form-check-label" for="zp_enable_store_access">Enable</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Allow Access to DoM</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="zp_enable_dom_access" id="zp_enable_dom_access" value="1" {{ old('zp_enable_dom_access', $documentupload->enable_dom_access) ? 'checked' : '' }}>
                            <label class="form-check-label" for="zp_enable_dom_access">Enable</label>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Allow Access to Operational Manager</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="zp_enable_operation_manager_access" id="zp_enable_operation_manager_access" value="1" {{ old('zp_enable_operation_manager_access', $documentupload->enable_operation_manager_access) ? 'checked' : '' }}>
                            <label class="form-check-label" for="zp_enable_operation_manager_access">Enable</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="zp_status" id="zp_status" value="active" {{ old('zp_status', $documentupload->status) ? 'checked' : '' }}>
                            <label class="form-check-label" for="zp_status">Active</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('document-upload.index') }}" class="btn btn-default">Back</a>
            </form>
        </div>

    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            function toggleExpiryDateField() {
                if ($('#zp_perpetual').is(':checked')) {
                    $('#expiry_date_container').hide();
                    $('#zp_expiry_date').rules('remove', 'required');
                } else {
                    $('#expiry_date_container').show();
                    $('#zp_expiry_date').rules('add', { required: true });
                }
            }

            $('#documentUploadForm').validate({
                rules: {
                    zp_document_file: {
                        required: function(element) {
                            return $( '#zp_old_document_file' ).val() == '';
                        },
                    },
                    zp_document: { required: true },
                    zp_location: { required: true },
                    zp_expiry_date: { 
                        required: function() {
                            return !$('#zp_perpetual').is(':checked');
                        },
                        date: true 
                    },
                    zp_issue_date: { required: true, date: true },
                    zp_remark: { required: true, minlength: 5 }
                },
                errorPlacement: function(error, element) {
                    error.appendTo(element.parent("div"));
                }
            });

            $('#zp_perpetual').on('change', function() {
                toggleExpiryDateField();
            });
            toggleExpiryDateField();

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
                placeholder: 'Select Notification Users',
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
            $('#zp_additional_users').select2({
                placeholder: 'Select Additional Users',
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