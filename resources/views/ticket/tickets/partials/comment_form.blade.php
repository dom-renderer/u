{!! CollectiveForm::open(['method' => 'POST', 'route' => $setting->grab('main_route').'-comment.store', 'id' => 'comment_store']) !!}


{!! CollectiveForm::hidden('ticket_id', $ticket->id ) !!}


{!! CollectiveForm::textarea('content', null, ['class' => 'form-control summernote-editor', 'rows' => "3"]) !!}

{!! CollectiveForm::submit( "Reply", ['class' => 'btn btn-outline-primary pull-right replay mt-3 mb-3']) !!}

{!! CollectiveForm::close() !!}

