@extends('layouts.app-master')

@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/summernote.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
@endpush

@section('content')
    <div class="bg-light p-4 rounded">

        <div class="mt-4">

            <form method="POST" action="{{ route('notification-templates.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger"> * </span> </label>
                    <input type="text" name="name" class="form-control" id="name" value="{{ old('name') }}" placeholder="Enter name" required>
                    @if ($errors->has('name'))
                        <span class="text-danger text-left">{{ $errors->first('name') }}</span>
                    @endif
                </div>
                <div class="mb-3">
                    <label for="type" class="form-label"> Type <span class="text-danger"> * </span> </label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="0">Email</option>
                        <option value="1">Push Notification</option>
                    </select>
                    @if ($errors->has('type'))
                        <span class="text-danger text-left">{{ $errors->first('type') }}</span>
                    @endif
                </div>
                <div class="mb-3">
                    <label for="title" class="form-label"> Title <span class="text-danger"> * </span> </label>
                    <input type="text" name="title" class="form-control" id="title" value="{{ old('title') }}" placeholder="Enter title" required>
                    @if ($errors->has('title'))
                        <span class="text-danger text-left">{{ $errors->first('title') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label"> Status <span class="text-danger"> * </span> </label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="1">Active</option>
                        <option value="0">InActive</option>
                    </select>
                    @if ($errors->has('status'))
                        <span class="text-danger text-left">{{ $errors->first('status') }}</span>
                    @endif
                </div>
                
                <div class="mb-3">
                    <label for="placeholder" class="form-label"> Placeholders </label>
                    <div class="row">
                        <div class="col-lg-11 mn-w">
                            <select id="placeholder" class="form-control">
                                <option value="" selected></option>
                                @forelse (array_merge(Helper::$notificationTemplatePlaceholders, \App\Console\Commands\ExecuteTicketEscalations::$ticketVariables) as $variable => $variableName)
                                    <option value="{{ $variable }}"> {{ $variableName }} </option>
                                @empty   
                                @endforelse
                            </select>
                        </div>
                        <div class="col-lg-1 text-end">
                            <button type="button" class="btn btn-primary" id="copy"> Copy </button>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Content <span class="text-danger"> * </span> </label>
                    <textarea class="form-control m-input description" id="description" name="description"></textarea>
                    @if ($errors->has('description'))
                        <span class="text-danger text-left">{{ $errors->first('description') }}</span>
                    @endif
                </div>

                <div class="mb-3">
                    <input type="checkbox" name="completion" value="1" id="completion" style="height: 20px;width:20px;">
                    <label for="completion" class="form-label" style="position: relative;bottom: 4px;"> Checklist Completion Notification </label>
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('notification-templates.index') }}" class="btn btn-default">Back</a>
            </form>
        </div>

    </div>
@endsection

@push('js')
<script src="{{ asset('assets/js/summernote.min.js') }}"></script>
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {

            $(".description").summernote({
                height:150,
                callbacks: {
                    onChange: function (contents) {
                        if($('.description').summernote('isEmpty')) {
                            $(".description").summernote('code', ''); 
                        }
                        if(contents == "<p><br></p>") {
                            $(".description").summernote('code', '');
                        }
                    }
                }
            });

            $('#type').select2({
                placeholder: 'Select type',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });

            $('#placeholder').select2({
                placeholder: 'Select type',
                allowClear: true,
                width: '94%',
                theme: 'classic'
            });

            $('#copy').on('click', function () {
                let that = this;

                navigator.clipboard.writeText($('#placeholder option:selected').val()).then(function() {
                }, function(err) {
                });

                $(that).text('Copied');
                setTimeout(() => {
                    $(that).text('Copy');
                }, 1000);
            });

        });
    </script>
@endpush