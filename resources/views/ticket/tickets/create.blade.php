@extends('ticket.layouts.master')
@section('title', 'New Ticket Form'." - ".Helper::setting()->name)
@section('page_title', 'Create New Ticket')

@section('ticketit_content')
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />

    {!! CollectiveForm::open([
                    'route'=>$setting->grab('main_route').'.store',
                    'method' => 'POST',
                    'enctype' => 'multipart/form-data',
                    'id' => 'tocket_form'
                    ]) !!}
        <div class="form-group row">
            {!! CollectiveForm::label('subject', 'Subject:', ['class' => 'col-lg-2 col-form-label']) !!}
            <div class="col-lg-10">
                {!! CollectiveForm::text('subject', null, ['class' => 'form-control']) !!}
                <small class="form-text text-muted">A brief of your issue ticket</small>
                <div style="color:red"><b id="subjectErr"></b></div>
            </div>
        </div>

        <div class="form-group row mb-2">
            {!! CollectiveForm::label('priority', 'Priority:', ['class' => 'col-lg-2 col-form-label']) !!}
            <div class="col-lg-10">
                <select name="priority_id" id="priority_id" class="form-control" required="required">
                    @foreach($priorities as $priority)
                        <option value="{{$priority->id}}" data-color="transparent">{{$priority->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group row mb-2">
            {!! CollectiveForm::label('department', 'Department:', ['class' => 'col-lg-2 col-form-label']) !!}
            <div class="col-lg-10">
                <select name="department_id" id="department_id" class="form-control" required="required">
                    @foreach($departments as $department)
                        <option value="{{$department->id}}">{{$department->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group row mb-2">
            {!! CollectiveForm::label('content', 'Description:', ['class' => 'col-lg-2 col-form-label']) !!}
            <div class="col-lg-10">
                {!! CollectiveForm::textarea('content', null, ['class' => 'form-control summernote-editor', 'rows' => '5']) !!}
                <small class="form-text text-muted">Describe your issue here in details</small>
                <div style="color:red"><b id="contentErr"></b></div>

            </div>
        </div>

        <div class="form-group row mb-2">
            {!! CollectiveForm::label('content', 'Attachments:', ['class' => 'col-lg-2 col-form-label']) !!}
            <div class="col-lg-10">
                <input type="file" name="attachments[]" class="form-control" accept="image/*" multiple>
            </div>
        </div>

        {{-- <div class="form-row mb-2">
                {!! CollectiveForm::label('agent_id', 'Assign to:', ['class' => 'col-lg-2 col-form-label']) !!}
                <div class="col-lg-12 mb-2">
                    {!! CollectiveForm::select('agent_id[]', [], null, ['class' => 'form-control', 'id' => 'user-select-2', 'multiple' => 'multiple', 'required' => 'required']) !!}
                </div>
        </div> --}}
        <br>
        <div class="form-group row">
            <div class="col-lg-12">
                {!! link_to_route($setting->grab('main_route').'.index', 'Back', null, ['class' => 'btn btn-link']) !!}
                {!! CollectiveForm::submit('Submit', ['class' => 'btn btn-primary ticketForm']) !!}
            </div>
        </div>
    {!! CollectiveForm::close() !!}
@endsection

@section('script')
    @include('ticket.tickets.partials.summernote')
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function () {

            $('#user-select-2').select2({
                placeholder: 'Select User',
                allowClear: true,
                width: '100%',
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
                            ignoreDesignation: 1,
                            department: function () {
                                return $('#department_id option:selected').val();
                            }
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

        });
    </script>
@append
