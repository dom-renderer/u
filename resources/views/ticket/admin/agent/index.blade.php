@extends('ticket.layouts.master')

@section('title', "Agent Management".' - '.Helper::setting()->name)
@section('page_title', "Agent Management")

@section('ticketit_header')
{!! link_to_route(
    $setting->grab('admin_route').'.agent.create',
    "Create new agent", null,
    ['class' => 'btn btn-primary'])
!!}
@stop

@section('ticketit_content_parent_class', 'p-0')

@section('ticketit_content')

<style>
    .join_ct_list {
        margin-right: 30px;
    }
    .form_join_ct_list {
        display: flex;
        flex-wrap: wrap;
    }
    .join_ct_list{
        position: relative;
    }
</style>
    @if ($agents->isEmpty())
        <h3 class="text-center">There are no agents,
            {!! link_to_route($setting->grab('admin_route').'.agent.create', "Create new agent") !!}
        </h3>
    @else
        <div id="message"></div>
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Categories</th>
                    <th>Joined Categories</th>
                    <th>Remove from agents</th>
                </tr>
            </thead>
            <tbody>
            @foreach($agents as $akey => $agent)
                <tr>
                    <td>
                        {{ $akey + 1 }}
                    </td>
                    <td>
                        {{ $agent->name }}
                    </td>
                    <td>
                        @foreach($agent->categories as $kxsz => $category)
                            @if($kxsz == 0)
                            <span style="color: {{ $category->color }}">{{ $category->name }}</span>
                            @else
                            <span style="color: {{ $category->color }}">, {{ $category->name }}</span>
                            @endif
                        @endforeach
                    </td>
                    <td>
                        {!! CollectiveForm::open([
                                        'method' => 'PATCH',
                                        'route' => [
                                                    $setting->grab('admin_route').'.agent.update',
                                                    $agent->id
                                                    ],
                                        'class' => 'form_join_ct_list'
                                        ]) !!}
                        @foreach(\App\Models\Category::all() as $agent_cat)
                        @php $num_id = mt_rand(000000000,999999999); @endphp
                        <div class="d-block join_ct_list">
                            <input name="agent_cats[]"
                                   type="checkbox"
                                   id="{{$num_id}}"
                                   class="form-check-input"
                                   value="{{ $agent_cat->id }}"
                                   {!! ($agent_cat->agents()->where("id", $agent->id)->count() > 0) ? "checked" : ""  !!}
                                   >
                                   <label for="{{$num_id}}">{{ $agent_cat->name }}</label>
                        </div>
                        @endforeach
                        <button class="btn btn-info btn-sm" type="submit" title="Link"><i class='tio-link'></i></button>
                        {!! CollectiveForm::close() !!}
                    </td>
                    <td>
                        {!! CollectiveForm::open([
                        'method' => 'DELETE',
                        'route' => [
                                    $setting->grab('admin_route').'.agent.destroy',
                                    $agent->id
                                    ],
                        'id' => "delete-$agent->id"
                        ]) !!}
                        <button class="btn btn-danger btn-sm" type="submit" title="Delete"><i class='tio-delete'></i></button>
                        {!! CollectiveForm::close() !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    @endif
@stop
