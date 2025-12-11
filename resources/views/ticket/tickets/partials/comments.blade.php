
@if(!$comments->isEmpty())
    <h2 class="mt-5">Comments History</h2>
    @foreach($comments as $comment)
        <div id="comment-reference-{{ $comment->id }}" class="card mb-3 {!! (isset($comment->user_id) && isset(auth()->user()->id) && $comment->user_id == auth()->user()->id) ? "border-info" : "" !!}">
            <div class="card-header d-flex justify-content-between align-items-baseline flex-wrap">
                <div>{!! isset($comment->user->name) ? $comment->user->name : 'Admin' !!}</div>
                <div>{!! $comment->created_at->diffForHumans() !!}</div>
            </div>
            <div class="card-body pb-0 ticketbody">
                {!! $comment->html !!}
            </div>
        </div>
    @endforeach
@endif

