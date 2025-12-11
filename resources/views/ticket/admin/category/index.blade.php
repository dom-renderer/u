@extends('ticket.layouts.master')

@section('title', "Categories Management"." - ".Helper::setting()->name)
@section('page_title', 'Categories Management')

@section('ticketit_header')
{!! link_to_route(
    $setting->grab('admin_route').'.category.create',
    "Create new category", null,
    ['class' => 'btn btn-primary'])
!!}
@stop

@section('ticketit_content_parent_class', 'p-0')

@section('ticketit_content')
    @if ($categories->isEmpty())
        <h3 class="text-center">There are no categories,
            {!! link_to_route($setting->grab('admin_route').'.category.create', "create new category") !!}
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
            @foreach($categories as $ckey => $category)
                <tr>
                    <td style="vertical-align: middle">
                        {{ $ckey + 1 }}
                    </td>
                    <td style="color: {{ $category->color }}; vertical-align: middle">
                        {{ $category->name }}
                    </td>
                    <td>
                    <a href="{{ route($setting->grab('admin_route').'.category.edit', [$category->id]) }}" class='btn btn-sm btn-warning' title="Edit"><i class='bi bi-pen-fill'></i></a>
                    <a href="{{ route($setting->grab('admin_route').'.category.destroy', [$category->id]) }}" class='btn btn-sm btn-danger deleteit' form="delete-{{$category->id}}" node="{{$category->name}}" title="Delete"><i class='tio-delete'></i></a>
                        {!! CollectiveForm::open([
                                        'method' => 'DELETE',
                                        'route' => [
                                                    $setting->grab('admin_route').'.category.destroy',
                                                    $category->id
                                                    ],
                                        'id' => "delete-$category->id"
                                        ])
                        !!}
                        {!! CollectiveForm::close() !!}
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
            if (confirm("Are you sure you want to delete the category: " + $(this).attr("node") + " ?"))
            {
                var form = $(this).attr("form");
                $("#" + form).submit();
            }

        });
    </script>
@append
