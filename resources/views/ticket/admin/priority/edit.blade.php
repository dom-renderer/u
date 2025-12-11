@extends('ticket.layouts.master')
@section('title', "Edit Priority: ".ucwords($priority->name)." - ".Helper::setting()->name)
@section('page_title', "Edit Priority: ".ucwords($priority->name))

@section('ticketit_content')
    {!! CollectiveForm::model($priority, [
                                'route' => [$setting->grab('admin_route').'.priority.update', $priority->id],
                                'method' => 'PATCH'
                                ]) !!}
        @include('ticket.admin.priority.form', ['update', true])
    {!! CollectiveForm::close() !!}
@stop
