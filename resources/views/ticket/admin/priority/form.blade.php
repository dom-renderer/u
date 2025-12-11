<div class="form-group">
    {!! CollectiveForm::label('name', "Name:", ['class' => '']) !!}
    {!! CollectiveForm::text('name', isset($priority->name) ? $priority->name : null, ['class' => 'form-control']) !!}
</div>
<div class="form-group">
    {!! CollectiveForm::label('color', "Color:", ['class' => '']) !!}
    <input class="form-control" name="color" type="color" @if(isset($priority->color)) value="{{$priority->color}}" @else value="#000000" @endif" id="color">
</div>

{!! link_to_route($setting->grab('admin_route').'.priority.index', "Back", null, ['class' => 'btn btn-link']) !!}
@if(isset($priority))
    {!! CollectiveForm::submit("Update", ['class' => 'btn btn-primary']) !!}
@else
    {!! CollectiveForm::submit("Submit", ['class' => 'btn btn-primary']) !!}
@endif
