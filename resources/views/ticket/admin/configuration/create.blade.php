@extends('ticket.layouts.master')
@section('title', "Create Setting"." - ".Helper::setting()->name)
@section('page_title', "Create Setting")

@section('ticketit_header')
{!! link_to_route(
    $setting->grab('admin_route').'.configuration.index',
    "Back", null,
    ['class' => 'btn btn-secondary'])
!!}
@stop

@section('ticketit_content')
    {!! CollectiveForm::open(['route' => $setting->grab('admin_route').'.configuration.store']) !!}

        <!-- Slug Field -->
        <div class="form-group row mb-0">
            {!! CollectiveForm::label('slug', "Slug:", ['class' => 'col-sm-3 col-form-label']) !!}
            <div class="col-sm-9 mb-3">
                {!! CollectiveForm::text('slug', null, ['class' => 'form-control']) !!}
            </div>
        </div>

        <!-- Default Field -->
        <div class="form-group row mb-0">
            {!! CollectiveForm::label('default', "Default value:", ['class' => 'col-sm-3 col-form-label']) !!}
            <div class="col-sm-9 mb-3">
                {!! CollectiveForm::text('default', null, ['class' => 'form-control']) !!}
            </div>
        </div>

        <!-- Value Field -->
        <div class="form-group row mb-0">
            {!! CollectiveForm::label('value', "My value:", ['class' => 'col-sm-3 col-form-label']) !!}
            <div class="col-sm-9 mb-3">
                {!! CollectiveForm::text('value', null, ['class' => 'form-control']) !!}
            </div>
        </div>

        <!-- Lang Field -->
        <div class="form-group row">
            {!! CollectiveForm::label('lang', "Language:", ['class' => 'col-sm-3 col-form-label']) !!}
            <div class="col-sm-9">
                {!! CollectiveForm::text('lang', null, ['class' => 'form-control']) !!}

            </div>
        </div>

        <!-- Submit Field -->
        <div class="form-group row">
            <div class="col-sm-10 offset-sm-3">
              {!! CollectiveForm::submit("Submit", ['class' => 'btn btn-primary']) !!}
            </div>
        </div>

    {!! CollectiveForm::close() !!}
@stop

@section('script')
    <script>
      $(document).ready(function() {
        $("#slug").bind('change', function() {
          var slugger = $('#slug').val();
              slugger = slugger
              .replace(/\W/g, '.')
              .toLowerCase();
          $("#slug").val(slugger);
        });

        $("#default").bind('keyup blur keypress change', function() {
          var duplicate = $('#default').val();
          $("#value").val(duplicate);
        });
      });
    </script>
@append
