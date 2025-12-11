@extends('layouts.app-master')

@push('css')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <div class="bg-light p-4 rounded">
        <h1>{{ $page_title }}</h1>
        <div class="mt-2">
            @include('layouts.partials.messages')
        </div>

        <div class="mt-4">
            <form method="POST" action="{{ route('issues.store') }}" id="issueForm">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger"> * </span></label>
                    <input type="text" name="name" class="form-control" id="name" value="{{ old('name') }}" placeholder="Enter name">
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="department" class="form-label">Department <span class="text-danger"> * </span></label>
                    <select name="department" id="department"></select>
                    @error('department')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="particular" class="form-label">Particular <span class="text-danger"> * </span></label>
                    <select name="particular" id="particular"></select>
                    @error('particular')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="1">Active</option>
                        <option value="0" {{ old('status')==='0' ? 'selected' : '' }}>InActive</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('issues.index') }}" class="btn btn-default">Back</a>
            </form>
        </div>
    </div>
@endsection

@push('js')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('#department').select2({
            placeholder: 'Select Department',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('departments-list') }}",
                type: "POST",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        _token: "{{ csrf_token() }}"
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item) { return { id: item.id, text: item.text }; }),
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            }
        });

        $('#particular').select2({
            placeholder: 'Select Particular',
            allowClear: true,
            width: '100%',
            theme: 'classic',
            ajax: {
                url: "{{ route('particulars-list') }}",
                type: "POST",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchQuery: params.term,
                        page: params.page || 1,
                        department_id: function () {
                            return $('#department').val();
                        },
                        select2: 'particulars',
                        _token: "{{ csrf_token() }}"
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: $.map(data.items, function(item) { return { id: item.id, text: item.text }; }),
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            }
        });

        $('#department').on('change', function(){
            $('#particular').val(null).trigger('change');
        });

        $('#issueForm').validate({
            rules: {
                name: { required: true },
                department: { required: true },
                particular: { required: true }
            }
        });
    });
</script>
@endpush