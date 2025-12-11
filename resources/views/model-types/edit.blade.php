@extends('layouts.app-master')

@section('content')
    <div class="bg-light p-4 rounded">

        <div class="container mt-4">

            <form method="POST" action="{{ route('model-types.update', $id) }}">
                @csrf @method('PUT')
              
                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger"> * </span> </label>
                    <input type="text" class="form-control" name="name" id="name" value="{{ old('name', $storetype->name) }}" placeholder="Enter name">

                    @if ($errors->has('name'))
                        <span class="text-danger text-left">{{ $errors->first('name') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description <span class="text-danger"> * </span> </label>
                    <textarea name="description" class="form-control" placeholder="Description" style="resize: vertical!important;" required>{{ old('description', $storetype->description) }}</textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('model-types.index') }}" class="btn btn-default">Back</a>
            </form>
        </div>

    </div>
@endsection

@push('js')
    <script type="text/javascript">
        $(document).ready(function() {

        });
    </script>
@endpush