@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/css/quill.snow.css') }}" rel="stylesheet" />
<link href="https://unpkg.com/filepond@4/dist/filepond.min.css" rel="stylesheet" />
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
@endpush

@section('content')
<div class="bg-light p-4 rounded">
    <h1>{{ $page_title }}</h1>
    <div class="mt-2">
        @include('layouts.partials.messages')
    </div>

    <form method="POST" action="{{ route('ticket-management.store') }}" id="ticketCreateForm" enctype="multipart/form-data" class="mt-4">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                <select name="department_id" id="department_id"></select>
                @error('department_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="particular_id" class="form-label">Particular <span class="text-danger">*</span></label>
                <select name="particular_id" id="particular_id"></select>
                @error('particular_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="issue_id" class="form-label">Issue <span class="text-danger">*</span></label>
                <select name="issue_id" id="issue_id"></select>
                @error('issue_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="shop_operator_id" class="form-label">Location <span class="text-danger">*</span></label>
                <select name="shop_operator_id" id="shop_operator_id"></select>
                @error('shop_operator_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="mb-3">
            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
            <input type="text" name="subject" id="subject" class="form-control" value="{{ old('subject') }}" placeholder="Subject">
            @error('subject')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Description <span class="text-danger">*</span></label>
            <div id="ticketDescriptionEditor" style="height: 220px;"></div>
            <input type="hidden" name="description" id="ticketDescription">
            @error('description')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label for="attachments" class="form-label">Upload Images</label>
            <input type="file" name="attachments[]" id="attachments" class="filepond" accept="image/*" multiple>
            <small class="text-muted d-block mt-1">The file size should not be more than 3MB.</small>
            @error('attachments')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            @error('attachments.*')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
            <select name="priority" id="priority" class="form-control">
                @foreach($priorities as $value => $label)
                    <option value="{{ $value }}" {{ old('priority') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('priority')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('ticket-management.index') }}" class="btn btn-secondary me-2">Back</a>
            <button type="submit" class="btn btn-success">Create Ticket</button>
        </div>
    </form>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/quill.min.js') }}"></script>
<script src="https://unpkg.com/filepond@4/dist/filepond.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
<script>
    $(function () {
        const csrfToken = "{{ csrf_token() }}";

        const departmentSelect = $('#department_id').select2({
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('departments-list') }}",
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

        const particularSelect = $('#particular_id').select2({
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('particulars-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        department_id: function () {
                            return $('#department_id').val();
                        },
                        select2: 'particulars',
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

        const issueSelect = $('#issue_id').select2({
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('issues-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        particular_id: function () {
                            return $('#particular_id').val();
                        },
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

        const operatorUser = $('#extra_users').select2({
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('users-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        ignoreDesignation: 1,
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

        const operatorSelect = $('#shop_operator_id').select2({
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
                        ignoreDesignation: 1,
                        _token: csrfToken,
                        for_ticket: 1,
                        strict_stores: 1
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

        departmentSelect.on('change', function () {
            particularSelect.val(null).trigger('change');
            issueSelect.val(null).trigger('change');
        });

        particularSelect.on('change', function () {
            issueSelect.val(null).trigger('change');
        });

        const quill = new Quill('#ticketDescriptionEditor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });

        FilePond.registerPlugin(FilePondPluginImagePreview);
        FilePond.registerPlugin(FilePondPluginFileValidateSize);

        const filepondInput = document.querySelector('#attachments');
        const pond = FilePond.create(filepondInput, {
            maxFileSize: '3MB',
            imagePreviewHeight: 170,
            server: null,
            storeAsFile: true,
            allowMultiple: true,
            acceptedFileTypes: ['image/*']
        });

        $('#ticketCreateForm').on('submit', function (e) {
            const html = quill.root.innerHTML.trim();
            const plain = quill.getText().trim();

            if (plain.length === 0) {
                e.preventDefault();
                alert('Description is required.');
                return false;
            }

            $('#ticketDescription').val(html);
            
            return true;
        });
    });
</script>
@endpush