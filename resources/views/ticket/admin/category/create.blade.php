@extends('ticket.layouts.master')

@section('title', "Create New Category"." - ".Helper::setting()->name)
@section('page_title', 'Create New Category')

@section('ticketit_content')
    {!! CollectiveForm::open(['route'=> $setting->grab('admin_route').'.category.store', 'method' => 'POST', 'class' => '']) !!}
        @include('ticket.admin.category.form')
    {!! CollectiveForm::close() !!}
@stop
