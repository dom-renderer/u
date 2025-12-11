@extends('ticket.layouts.master')
@section('title', "Create New Status"." - ".Helper::setting()->name)
@section('page_title', "Create New Status")

@section('ticketit_content')
    {!! CollectiveForm::open(['route'=> $setting->grab('admin_route').'.status.store', 'method' => 'POST', 'class' => '']) !!}
        @include('ticket.admin.status.form')
    {!! CollectiveForm::close() !!}
@stop
