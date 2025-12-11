@extends('layouts.app-master')

@section('content')
    <div class="bg-light p-4 rounded">

        <div class="container mt-4">

            <form method="POST" action="{{ route('store-categories.update', $id) }}" id="categoryForm">
                @csrf @method('PUT')
              
                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger"> * </span> </label>
                    <input type="text" class="form-control" name="name" id="name" value="{{ old('name', $storecategory->name) }}" placeholder="Enter name">

                    @if ($errors->has('name'))
                        <span class="text-danger text-left">{{ $errors->first('name') }}</span>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('store-categories.index') }}" class="btn btn-default">Back</a>
            </form>
        </div>

    </div>
@endsection

@push('js')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#categoryForm').validate({
                rules: {
                    name: { required: true }
                },
                messages: {
                    name: { required: 'Please enter category name' }
                },
                errorPlacement: function(error, element) {
                    error.appendTo(element.parent("div"));
                }
            });
        });
    </script>
@endpush