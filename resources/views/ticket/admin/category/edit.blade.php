@extends('ticket.layouts.master')
@section('title', "Edit Category: ".ucwords($category->name)." - ".Helper::setting()->name)
@section('page_title', "Edit Category: ".ucwords($category->name))

@section('ticketit_content')
    {!! CollectiveForm::model($category, [
                                'route' => [$setting->grab('admin_route').'.category.update', $category->id],
                                'method' => 'PATCH',
                                'class' => ''
                                ]) !!}
        @include('ticket.admin.category.form', ['update', true])
    {!! CollectiveForm::close() !!}
@stop
