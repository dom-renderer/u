@extends('layouts.app-master')

@section('content')
    <div class="bg-light p-4 rounded">

        <div class="container mt-4">

            <form method="POST" action="{{ route('documents.store') }}" id="documentForm">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger"> * </span> </label>
                    <input type="text" name="name" class="form-control" id="name" value="{{ old('name') }}" placeholder="Enter name">

                    @if ($errors->has('name'))
                        <span class="text-danger text-left">{{ $errors->first('name') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug <span class="text-danger"> * </span> </label>
                    <input type="text" name="slug" class="form-control" id="slug" value="{{ old('slug') }}" placeholder="Enter slug">

                    @if ($errors->has('slug'))
                        <span class="text-danger text-left">{{ $errors->first('slug') }}</span>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('documents.index') }}" class="btn btn-default">Back</a>
            </form>
        </div>

    </div>
@endsection

@push('js')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#documentForm').validate({
                rules: {
                    name: { required: true },
                    slug: { required: true }
                },
                messages: {
                    name: { required: 'Please enter document name' },
                    slug: { required: 'Please enter a slug'}
                },
                errorPlacement: function(error, element) {
                    error.appendTo(element.parent("div"));
                }
            });
        });
    </script>
@endpush