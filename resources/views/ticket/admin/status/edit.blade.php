@extends('ticket.layouts.master')
@section('title', "Edit Status: ".ucwords($status->name)." - ".Helper::setting()->name)
@section('page_title', "Edit Status: ".ucwords($status->name))

@section('ticketit_content')
    {!! CollectiveForm::model($status, [
                                    'route' => [$setting->grab('admin_route').'.status.update', $status->id],
                                    'method' => 'PATCH'
                                    ]) !!}
        @include('ticket.admin.status.form', ['update', true])
    {!! CollectiveForm::close() !!}
@stop
