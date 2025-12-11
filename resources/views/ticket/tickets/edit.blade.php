<style>
    .modal-backdrop {
        z-index: 999;
    }
</style>
<div class="modal fade" id="ticket-edit-modal" tabindex="-1" role="dialog" aria-labelledby="ticket-edit-modal-Label" style="z-index: 9999;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            {!! CollectiveForm::model($ticket, [
                 'route' => [$setting->grab('main_route').'.update', $ticket->id],
                 'method' => 'PATCH',
                 'class' => 'form-horizontal'
             ]) !!}
            <div class="modal-header">
                <h5 class="modal-title" id="ticket-edit-modal-Label">{{ $ticket->subject }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <!-- <div class="form-group">
                    {!! CollectiveForm::text('subject', $ticket->subject, ['class' => 'form-control', 'required']) !!}
                </div>
                <div class="form-group">
                    <textarea class="form-control summernote-editor" rows="5" required name="content" cols="50">{!! htmlspecialchars($ticket->html) !!}</textarea>
                </div> -->

                <div class="form-group priority-id">
                    {!! CollectiveForm::label('priority_id', "Priority:", ['class' => '']) !!}
                    {!! CollectiveForm::select('priority_id', $priority_lists, $ticket->priority_id, ['class' => 'form-control']) !!}
                </div>

                {{--<div class="form-group">
                    {!! CollectiveForm::label('agent_id', "Agent:", [
                        'class' => ''
                    ]) !!}
                    {!! CollectiveForm::select(
                        'agent_id',
                        $agent_lists,
                        $ticket->agent_id,
                        ['class' => 'form-control']) !!}
                </div>--}}
                {!! CollectiveForm::hidden('agent_id', $ticket->agent_id) !!}

                <!-- <div class="form-group">
                    {!! CollectiveForm::label('category_id',  "Category:", [
                        'class' => ''
                    ]) !!}
                    {!! CollectiveForm::select('category_id', $category_lists, $ticket->category_id, ['class' => 'form-control']) !!}
                </div> -->

                <div class="form-group status-id">
                    {!! CollectiveForm::label('status_id', "Status:", [
                        'class' => ''
                    ]) !!}
                    {!! CollectiveForm::select('status_id', $status_lists, $ticket->status_id, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                {!! CollectiveForm::submit("Submit", ['class' => 'btn btn-primary']) !!}
            </div>
            {!! CollectiveForm::close() !!}
        </div>
    </div>
</div>

