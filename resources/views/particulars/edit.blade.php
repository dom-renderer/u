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
            <form method="POST" action="{{ route('particulars.update', $id) }}" id="particularForm">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger"> * </span></label>
                    <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $particular->name) }}" placeholder="Enter name">
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="department" class="form-label">Department <span class="text-danger"> * </span></label>
                    <select name="department" id="department">
                        @if($particular && $particular->department_id)
                            @php $dep = \App\Models\Department::withTrashed()->find($particular->department_id); @endphp
                            @if($dep)
                                <option value="{{ $dep->id }}" selected>{{ $dep->name }}</option>
                            @endif
                        @endif
                    </select>
                    @error('department')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="1" {{ $particular->status ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$particular->status ? 'selected' : '' }}>InActive</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('particulars.index') }}" class="btn btn-default">Back</a>
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
                        results: $.map(data.items, function(item) {
                            return { id: item.id, text: item.text };
                        }),
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            },
            templateResult: function(data) {
                if (data.loading) { return data.text; }
                var $result = $('<span></span>');
                $result.text(data.text);
                return $result;
            }
        });

        $('#particularForm').validate({
            rules: {
                name: { required: true },
                department: { required: true }
            }
        });
    });
</script>
@endpush