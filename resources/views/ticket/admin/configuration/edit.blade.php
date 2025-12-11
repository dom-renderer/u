@extends('ticket.layouts.master')

@section('title', "Edit Setting"." - ".Helper::setting()->name)
@section('page_title', "Edit Setting")

@section('ticketit_header')
{!! link_to_route(
    $setting->grab('admin_route').'.configuration.index',
    "Back", null,
    ['class' => 'btn btn-secondary'])
!!}
@stop

@section('ticketit_content')

<style>
    .cus_input{
        height: 20px;
        width: 20px;
    }
</style>
    {!! CollectiveForm::model($configuration, ['route' => [$setting->grab('admin_route').'.configuration.update', $configuration->id], 'method' => 'patch']) !!}
        <div class="card bg-light mb-3">
            <div class="card-body">
            <b>Tools:</b>
            <br>
            <a href="https://www.functions-online.com/unserialize.html" target="_blank">
            Get the array values, and change the values
            </a>
            <br>
            <a href="https://www.functions-online.com/serialize.html" target="_blank">
            Get the serialized string of the changed values (to be entered in the field)
            </a>
            </div>
        </div>

        <!-- ID Field -->
        <div class="form-group row">
          {!! CollectiveForm::label('id', "ID:", ['class' => 'col-sm-2 col-form-label']) !!}
          <div class="col-sm-9">
              {!! CollectiveForm::text('id', null, ['class' => 'form-control', 'disabled']) !!}
          </div>
        </div>

        <!-- Slug Field -->
        <div class="form-group row">
          {!! CollectiveForm::label('slug', "Slug:", ['class' => 'col-sm-2 col-form-label']) !!}
          <div class="col-sm-9">
              {!! CollectiveForm::text('slug', null, ['class' => 'form-control', 'disabled']) !!}
          </div>
        </div>

        <div class="form-group row">
          {!! CollectiveForm::label('default', "Default value:", ['class' => 'col-sm-2 col-form-label']) !!}
          <div class="col-sm-9">
              @if(!$default_serialized)
                  {!! CollectiveForm::text('default', null, ['class' => 'form-control', 'disabled']) !!}
              @else
                  <pre>{{var_export(unserialize($configuration->default), true)}}</pre>
              @endif
          </div>
        </div>


        <!-- Value Field -->
        <div class="form-group row">
          {!! CollectiveForm::label('value', "My value:", ['class' => 'col-sm-2 col-form-label']) !!}
          <div class="col-sm-9">
              @if(!$should_serialize)
                    {!! CollectiveForm::text('value', null, ['class' => 'form-control']) !!}
              @else
                  {!! CollectiveForm::textarea('value', var_export(unserialize($configuration->value), true), ['class' => 'form-control']) !!}
              @endif
          </div>
        </div>

        <!-- Serialize Field -->
        <div class="form-group row">
            {!! CollectiveForm::label('serialize', "Serialize:", ['class' => 'col-sm-2 col-form-label']) !!}
            <div class="col-sm-9" style="align-self: center;">
                {!! CollectiveForm::checkbox('serialize', 1, $should_serialize, ['class' => 'cus_input', 'onchange' =>  'changeSerialize(this)',]) !!}
                <span class="form-text" style="color: red;">When checked, the server will run eval()! Don't use this if eval() is disabled on your server or if you don't exactly know what you are doing! Exact code executed: <code>eval('$value = serialize(' . $value . ');')</code></span>
            </div>
        </div>

        <!-- Password Field -->
        <div id="serialize-password" class="form-group row">
            {!! CollectiveForm::label('password', "Re-enter your password:", ['class' => 'col-sm-2 col-form-label']) !!}
            <div class="col-sm-9">
                {!! CollectiveForm::password('password', ['class' => 'form-control']) !!}
            </div>
        </div>

        <!-- Lang Field -->
        <div class="form-group row">
          {!! CollectiveForm::label('lang', "Language:", ['class' => 'col-sm-2 col-form-label']) !!}
          <div class="col-sm-9">
              {!! CollectiveForm::text('lang', null, ['class' => 'form-control']) !!}
          </div>
        </div>

        <!-- Submit Field -->
        <div class="form-group row">
          <div class="col-sm-10 col-sm-offset-2">
            {!! CollectiveForm::submit("Submit", ['class' => 'btn btn-primary']) !!}
          </div>
        </div>

    {!! CollectiveForm::close() !!}


    <script>
        function changeSerialize(e){
            document.querySelector("#serialize-password").style.display = e.checked ? 'flex' : 'none';
            document.querySelector(".form-text").style.display = e.checked ? 'block' : 'none';
        }

        changeSerialize(document.querySelector("input[name='serialize']"));


    </script>


    @if($should_serialize)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/{{ \App\Helpers\Cdn::CodeMirror }}/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/{{ \App\Helpers\Cdn::CodeMirror }}/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/{{ \App\Helpers\Cdn::CodeMirror }}/mode/php/php.min.js"></script>


    <script>

        loadCSS({!! '"'.asset('https://cdnjs.cloudflare.com/ajax/libs/codemirror/' . \App\Helpers\Cdn::CodeMirror . '/codemirror.min.css').'"' !!});
        loadCSS({!! '"'.asset('https://cdnjs.cloudflare.com/ajax/libs/codemirror/' . \App\Helpers\Cdn::CodeMirror . '/theme/monokai.min.css').'"' !!});

        window.addEventListener('load', function(){
            CodeMirror.fromTextArea( document.querySelector("textarea[name='value']"), {
                lineNumbers: true,
                mode: 'text/x-php',
                theme: 'monokai',
                indentUnit: 2,
                lineWrapping: true
            });
        });

    </script>
    @endif

@stop
