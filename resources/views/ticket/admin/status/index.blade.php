@extends('ticket.layouts.master')

@section('title', "Statuses Management"." - ".Helper::setting()->name)
@section('page_title', "Statuses Management")

@section('ticketit_header')
{!! link_to_route(
    $setting->grab('admin_route').'.status.create',
    "Create new status", null,
    ['class' => 'btn btn-primary'])
!!}
@stop

@section('ticketit_content_parent_class', 'p-0')

@section('ticketit_content')
    @if ($statuses->isEmpty())
        <h3 class="text-center">There are no statues,
            {!! link_to_route($setting->grab('admin_route').'.status.create', "create new status") !!}
        </h3>
    @else
        <div id="message"></div>
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach($statuses as $skey => $status)
                <tr>
                    <td style="vertical-align: middle">
                        {{ $skey + 1 }}
                    </td>
                    <td style="color: {{ $status->color }}; vertical-align: middle">
                        {{ $status->name }}
                    </td>
                    <td>
                        <a href="{{ route($setting->grab('admin_route').'.status.edit', [$status->id]) }}" class='btn btn-sm btn-warning' title="Edit"><i class='bi bi-pen-fill'></i></a>
                        @if ($status->id > 6)
                        <a href="{{ route($setting->grab('admin_route').'.status.destroy', [$status->id]) }}" class='btn btn-sm btn-danger deleteit' form="delete-{{$status->id}}" node="{{$status->name}}"><i class='tio-delete' title="Delete"></i></a>
                        {!! CollectiveForm::open([
                                        'method' => 'DELETE',
                                        'route' => [
                                                    $setting->grab('admin_route').'.status.destroy',
                                                    $status->id
                                                    ],
                                        'id' => "delete-$status->id"
                                        ])
                        !!}
                        {!! CollectiveForm::close() !!}
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

@stop

@section('script')
    <script>
        $( ".deleteit" ).click(function( event ) {
            event.preventDefault();
            if (confirm("Are you sure you want to delete the status: " + $(this).attr("node") + " ?"))
            {
                $form = $(this).attr("form");
                $("#" + $form).submit();
            }

        });
    </script>
@append
