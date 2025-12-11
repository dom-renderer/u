@extends('ticket.layouts.master')
@section('title', 'Ticket: ' . $ticket->subject . ' - ' . Helper::setting()->name)
@section('page_title', $ticket->ticket_number)

@section('ticketit_header')
    <div>
        {{-- @if (!$ticket->completed_at && $close_perm == 'yes' && (auth()->user()->ticketit_admin == 0 && auth()->user()->ticketit_agent == 0)) --}}
        @if (isset(auth()->user()->roles[0]->id) && auth()->user()->roles[0]->id==\App\Helpers\Helper::$roles['admin'] && $ticket->completed_at == NUll)
            {!! link_to_route($setting->grab('main_route') . '.complete', 'Mark Complete', $ticket->id, ['class' => 'btn btn-success']) !!}
        @elseif($ticket->completed_at && $reopen_perm == 'yes')
            {!! link_to_route($setting->grab('main_route') . '.reopen', 'Reopen Ticket', $ticket->id, ['class' => 'btn btn-success']) !!}
        @endif
        @if ( ($u->isAgent() || $u->isAdmin()) && $ticket->completed_at == NUll)
            @if((isset(auth()->user()->roles[0]->id) && auth()->user()->roles[0]->id==\App\Helpers\Helper::$roles['admin'] && $ticket->completed_at == NUll))
            <button type="button" class="btn btn-info edit-ticket-status" >
                Manage Status
            </button>
            <button type="button" class="btn btn-info edit-ticket-priority" >
                Manage Priority
            </button>
            @endif
        @endif
        @if(isset(auth()->user()->roles[0]->id) && auth()->user()->roles[0]->id==\App\Helpers\Helper::$roles['admin'] && $ticket->completed_at == NUll && $ticket->estimate_time == null)
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#ticket-add-estimatetime">Add Estimate Date</button>
        @endif
        @if(isset(auth()->user()->roles[0]->id) && auth()->user()->roles[0]->id==\App\Helpers\Helper::$roles['admin'] && $ticket->completed_at == NUll && $ticket->estimate_time != null)
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#ticket-add-estimatetime">Change Estimate Date</button>
        @endif
        @if ($u->isAdmin())

            @if((isset(auth()->user()->roles[0]->id) && auth()->user()->roles[0]->id==\App\Helpers\Helper::$roles['admin'] && $ticket->completed_at == NUll))

                @if ($setting->grab('delete_modal_type') == 'builtin')
                    {!! link_to_route($setting->grab('main_route') . '.destroy', 'Delete', $ticket->id, [
                    'class' => 'btn btn-danger deleteit',
                    'form' => "delete-ticket-$ticket->id",
                    'node' => $ticket->subject,
                ]) !!}
                @elseif($setting->grab('delete_modal_type') == 'modal')
                    {{-- // OR; Modal Window: 1/2 --}}
                    {!! CollectiveForm::open([
                        'route' => [$setting->grab('main_route') . '.destroy', $ticket->id],
                        'method' => 'delete',
                        'style' => 'display:inline',
                    ]) !!}
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#confirmDelete"
                        data-title="{!! trans('Delete Ticket', ['id' => $ticket->id]) !!}" data-message="{!! trans('Are you sure you want to delete ticket: :subject?', ['subject' => $ticket->subject]) !!}">
                        Delete
                    </button>
                @endif
                {!! CollectiveForm::close() !!}
                {{-- // END Modal Window: 1/2 --}}

            @endif

        @endif
    </div>
@stop

@section('ticketit_content')
    @include('ticket.tickets.partials.ticket_body')
@endsection

@section('ticketit_extra_content')
    @if(!empty($histories))
        <style>
            .StepProgress {
                position: relative;
                padding-left: 45px;
                list-style: none;
            }
            .StepProgress::before {
                display: inline-block;
                content: '';
                position: absolute;
                top: 0;
                left: 15px;
                width: 10px;
                height: 100%;
                border-left: 2px solid #CCC;
            }

            .StepProgress-item {
                position: relative;
                counter-increment: list;
            }

            .StepProgress-item:not(:last-child) {
                padding-bottom: 20px;
            }

            .StepProgress-item::before {
                display: inline-block;
                content: '';
                position: absolute;
                left: -30px;
                height: 100%;
                width: 10px;
            }

            .StepProgress-item::after {
                content: '';
                display: inline-block;
                position: absolute;
                top: 5px;
                left: -35px;
                width: 12px;
                height: 12px;
                border: 2px solid #CCC;
                border-radius: 50%;
                background-color: #FFF;
            }

            .StepProgress-item.is-done::before {
                border-left: 2px solid #132144;
            }

            .StepProgress-item.is-done::after {
                content: "✔";
                font-size: 10px;
                color: #FFF;
                text-align: center;
                border: 2px solid #132144;
                background-color: #132144;
            }

            .StepProgress-item.current::before {
                border-left: 2px solid #132144;
            }

            .StepProgress-item.current::after {
                content: counter(list);
                padding-top: 1px;
                width: 19px;
                height: 18px;
                top: -4px;
                left: -40px;
                font-size: 14px;
                text-align: center;
                color: #132144;
                border: 2px solid #132144;
                background-color: white;
            }

            .StepProgress strong {
                display: block;
            }

            .ticketbody img {
                width:100%
            }

        </style>

        @if ($ticket->completed_at == null)
            <h2 class="mt-5">Comment</h2>
            @include('ticket.tickets.partials.comment_form')
        @endif
        <h2 class="mt-5">Ticket History</h2>
        <ul class="StepProgress">
            @foreach ($histories as $history)
            <li class="StepProgress-item">
                @if($history['type'] == 1 && $history['model'] == 'App\Models\Comment')
                    <a href="#comment-reference-{{ $history['model_id'] }}">
                        <strong>{!! $history['description'] !!}</strong>
                    </a>                    
                @else
                    <strong>{!! $history['description'] !!}</strong>
                @endif
                on {{date('d-M-Y H:i', strtotime($history['updated_at']))}}
                @if($history['type'] == 1 && $history['model'] == 'App\Models\TicketAttachment')
                    @if($history['model_id'] == 0)
                    <div class="container text-center">
                        <div class="row">
                            @foreach (\App\Models\TicketAttachment::where('ticket_id', $ticket->id)->where('history_id', $history['id'])->get() as $attachment)
                            <div class="col">
                                <a href="{{ asset('storage/ticket-uploads/' . $attachment['file']) }}" target="_blank">
                                    <img src="{{ asset('storage/ticket-uploads/' . $attachment['file']) }}" alt="{{ $attachment['file'] }}" style="height: 100px;width:100px;object-fit:cover;">
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="container text-center">
                        <div class="row">
                            @foreach (\App\Models\TicketAttachment::where('ticket_id', $ticket->id)->where(function ($builder) {
                                $builder->whereNull('history_id')->orWhere('history_id', 0)->orWhere('history_id', '');
                            })->get() as $attachment)
                            <div class="col">
                                <a href="{{ asset('storage/ticket-uploads/' . $attachment['file']) }}" target="_blank">
                                    <img src="{{ asset('storage/ticket-uploads/' . $attachment['file']) }}" alt="{{ $attachment['file'] }}" style="height: 100px;width:100px;object-fit:cover;">
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endif
            </li>
            @endforeach
        </ul>
    @endif

    @include('ticket.tickets.partials.comments')
    {!! $comments->render('pagination::bootstrap-4') !!}
    <br>
@stop

<input type="hidden" id="ticket_id" name="ticket_id" value="{{$ticket->id}}">

@section('script')
    <script>
        $(document).ready(function() {
            let modal = $('#ticket-edit-modal');
            $(document).on('click','.edit-ticket-status, .edit-ticket-priority',function(){
                modal.data('edit-ticket-type',$(this).hasClass('edit-ticket-status') ? 'status' : 'priority');
                modal.modal('show');
            });
            modal.on('show.bs.modal', function (e) {
                let type  = $('#ticket-edit-modal').data('edit-ticket-type');
                if(type == 'status') {
                    $('.priority-id').hide();
                    $('.status-id').show();
                } else {
                    $('.priority-id').show();
                    $('.status-id').hide();
                }
            })

            $('#comment_store').validate({


            submitHandler: function(form) {
                $(':input[type="submit"]').prop('disabled', true);
                form.submit();

            },

        });



            $(".deleteit").click(function(event) {

                event.preventDefault();
                if (confirm("{!! trans('Are you sure you want to delete: ') !!}" + $(this).attr("node") + " ?")) {
                    var form = $(this).attr("form");
                    $("#" + form).submit();
                }

            });


            $('#category_id').change(function() {
                var loadpage = "{!! route($setting->grab('main_route') . 'agentselectlist') !!}/" + $(this).val() + "/{{ $ticket->id }}";
                $('#agent_id').load(loadpage);
            });
            $('#confirmDelete').on('show.bs.modal', function(e) {
                $message = $(e.relatedTarget).attr('data-message');
                $(this).find('.modal-body p').text($message);
                $title = $(e.relatedTarget).attr('data-title');
                $(this).find('.modal-title').text($title);

                // Pass form reference to modal for submission on yes/ok
                var form = $(e.relatedTarget).closest('form');
                $(this).find('.modal-footer #confirm').data('form', form);
            });

            $('#confirmDelete').find('.modal-footer #confirm').on('click', function() {
                $(this).data('form').submit();
            });

            $('#estimate_time').datepicker({
                format: "yyyy-mm-dd",
                todayBtn: true,
                todayHighlight: true,
                orientation: "bottom auto",
                autoclose: true,
                startDate: '1d'
            });

            $("#add_estimatetime").on('click', function(){
                var estimate_time = $("#estimate_time").val();
                var ticket_id = $("#ticket_id").val();
                $("#estimate_time_error").text("");
                if(estimate_time == "" || estimate_time == null){
                    $("#estimate_time_error").text("Estimate date is required");
                } else {
                    $("#add_estimatetime").attr('disabled',true);
                    $("#estimate_time_error").text("");

                    $.ajax({
                        url: "{{ url('ticket-system/add-estimatetime') }}",
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            _token: "{{ csrf_token() }}",
                            estimate_time: estimate_time,
                            ticket_id: ticket_id
                        },
                        success: function(res) {
                            location.reload();
                        }
                    });
                }
            });

            // $("#estimate_time").datePicker().val().trigger('change');

        });
    </script>
    @include('ticket.tickets.partials.summernote')
@append
