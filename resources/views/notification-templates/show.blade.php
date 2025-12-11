@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/quill.snow.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
@endpush

@section('content')
    <div class="bg-light p-4 rounded">

        <div class="mt-4">

                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger"> * </span> </label>
                    <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $notification->name) }}" placeholder="Enter name" readonly>
                    @if ($errors->has('name'))
                        <span class="text-danger text-left">{{ $errors->first('name') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="type" class="form-label"> Type <span class="text-danger"> * </span> </label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="0" @if(!$notification->type) selected @endif>Email</option>
                        <option value="1" @if($notification->type) selected @endif>Push Notification</option>
                    </select>
                    @if ($errors->has('type'))
                        <span class="text-danger text-left">{{ $errors->first('type') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label"> Title <span class="text-danger"> * </span> </label>
                    <input type="text" name="title" class="form-control" id="title" value="{{ old('title', $notification->title) }}" placeholder="Enter title" readonly>
                    @if ($errors->has('title'))
                        <span class="text-danger text-left">{{ $errors->first('title') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label"> Status <span class="text-danger"> * </span> </label>
                    <select name="status" id="status" class="form-control" readonly>
                        <option value="1" @if($notification->status) selected @endif >Active</option>
                        <option value="0" @if(!$notification->status) selected @endif >InActive</option>
                    </select>
                    @if ($errors->has('status'))
                        <span class="text-danger text-left">{{ $errors->first('status') }}</span>
                    @endif
                </div>
                

                <div class="mb-3">
                    <label for="description" class="form-label">Description <span class="text-danger"> * </span> </label>
                    <div id="editor"></div>
                    {!! $notification->content !!}
                    @if ($errors->has('description'))
                        <span class="text-danger text-left">{{ $errors->first('description') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <input type="checkbox" name="completion" value="1" id="completion" style="height: 20px;width:20px;" @if($notification->completion_type) checked @endif disabled>
                    <label for="completion" class="form-label" style="position: relative;bottom: 4px;"> Checklist Completion Notification </label>
                </div>

                <a href="{{ route('notification-templates.index') }}" class="btn btn-default">Back</a>
        </div>

    </div>
@endsection

@push('js')
<script src="{{ asset('assets/js/quill.js') }}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {

            const quill = new Quill('#editor', {
                modules: {
                    toolbar: true,
                },
                theme: 'snow'
            });

            quill.setText("{!! strip_tags($notification->content) !!}");

            $('#type').select2({
                placeholder: 'Select type',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });
        });
    </script>
@endpush