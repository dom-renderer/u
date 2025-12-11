@extends('ticket.layouts.master')
@section('title', "Create New Priority"." - ".Helper::setting()->name)
@section('page_title', "Create New Priority")

@section('ticketit_content')
    {!! CollectiveForm::open(['route'=> $setting->grab('admin_route').'.priority.store', 'method' => 'POST', 'class' => '']) !!}
        @include('ticket.admin.priority.form')
    {!! CollectiveForm::close() !!}
@stop
