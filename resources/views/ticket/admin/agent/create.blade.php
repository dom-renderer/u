@extends('ticket.layouts.master')
@section('title', "Add agent".' - '.Helper::setting()->name)
@section('page_title', "Add agent")

@section('ticketit_content')
    @if ($users->isEmpty())
        <h3 class="text-center">There are no user accounts, create user accounts first.</h3>
    @else
        {!! CollectiveForm::open(['route'=> $setting->grab('admin_route').'.agent.store', 'method' => 'POST', 'class' => '']) !!}
        <p>Select user accounts to be added as agents</p>
        <table class="table table-hover">
            <tbody>
            @foreach($users as $user)
            @php $numx = mt_rand(000000000,999999999); @endphp
                <tr>
                    <td>
                        <div class="form-check form-check-inline">
                            <input name="agents[]" type="checkbox" class="form-check-input" value="{{ $user->id }}" id="{{$numx}}" {!! $user->ticketit_agent ? "checked" : "" !!}>
                            <label class="form-check-label" for="{{$numx}}">{{ $user->name }}</label>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {!! link_to_route($setting->grab('admin_route').'.agent.index', "Back", null, ['class' => 'btn btn-link']) !!}
        {!! CollectiveForm::submit("Submit", ['class' => 'btn btn-primary']) !!}
        {!! CollectiveForm::close() !!}
    @endif

    {!! $users->render("pagination::bootstrap-4") !!}
@stop
