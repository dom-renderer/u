@extends('ticket.layouts.master')

@section('title', "Priorities Management"." - ".Helper::setting()->name)
@section('page_title', "Priorities Management")

@section('ticketit_header')
{!! link_to_route(
    $setting->grab('admin_route').'.priority.create',
    "Create new priority", null,
    ['class' => 'btn btn-primary'])
!!}
@stop

@section('ticketit_content_parent_class', 'p-0')

@section('ticketit_content')
    @if ($priorities->isEmpty())
        <h3 class="text-center">There are no priorities,
            {!! link_to_route($setting->grab('admin_route').'.priority.create', "create new priority") !!}
        </h3>
    @else
        <div id="message"></div>
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach($priorities as $pkey => $priority)
                <tr>
                    <td style="vertical-align: middle">
                        {{ $pkey + 1 }}
                    </td>
                    <td style="color: {{ $priority->color }}; vertical-align: middle">
                        {{ $priority->name }}
                    </td>
                    <td>
                    <a href="{{ route($setting->grab('admin_route').'.priority.edit', [$priority->id]) }}" class='btn btn-sm btn-warning' title="Edit"><i class='bi bi-pen-fill'></i></a>
                        @if(!in_array($priority->id,[1,2,3]))
                    <a href="{{ route($setting->grab('admin_route').'.priority.destroy', [$priority->id]) }}" class='btn btn-sm btn-danger deleteit' form="delete-{{$priority->id}}" node="{{$priority->name}}" title="Delete"><i class='tio-delete'></i></a>
                        {!! CollectiveForm::open([
                                        'method' => 'DELETE',
                                        'route' => [
                                                    $setting->grab('admin_route').'.priority.destroy',
                                                    $priority->id
                                                    ],
                                        'id' => "delete-$priority->id"
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
            if (confirm("Are you sure you want to delete the priority: " + $(this).attr("node") + " ?"))
            {
                $form = $(this).attr("form");
                $("#" + $form).submit();
            }

        });
    </script>
@append
