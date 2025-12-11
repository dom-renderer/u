@extends('ticket.layouts.master')

@section('title', "Administrator Management"." - ".Helper::setting()->name)
@section('page_title', 'Administrator Management')

@section('ticketit_header')
{!! link_to_route(
    $setting->grab('admin_route').'.administrator.create',
    "Create new administrator", null,
    ['class' => 'btn btn-primary'])
!!}
@stop

@section('ticketit_content_parent_class', 'p-0')

@section('ticketit_content')
    @if ($administrators->isEmpty())
        <h3 class="text-center">{{ trans('ticketit::admin.administrator-index-no-administrators') }}
            {!! link_to_route($setting->grab('admin_route').'.administrator.create', trans('ticketit::admin.administrator-index-create-new')) !!}
        </h3>
    @else
        <div id="message"></div>
        <table class="table table-hover mb-0">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Remove from administrators</th>
            </tr>
            </thead>
            <tbody>
            @foreach($administrators as $akey => $administrator)
                <tr>
                    <td>
                        {{ $akey + 1 }}
                    </td>
                    <td>
                        {{ $administrator->name }}
                    </td>
                    <td>
                        {!! CollectiveForm::open([
                        'method' => 'DELETE',
                        'route' => [
                                    $setting->grab('admin_route').'.administrator.destroy',
                                    $administrator->id
                                    ],
                        'id' => "delete-$administrator->id"
                        ]) !!}
                        <button class="btn btn-sm btn-danger" type="submit" title="Delete"><i class='tio-delete'></i></button>
                        {!! CollectiveForm::close() !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    @endif

@stop
