@if ($comments->isNotEmpty())
    @foreach ($comments as $comment)
        <div class="post" id="comment_{{ $comment->id }}">
            <div class="b">
                <div class="img">
                    {!! $comment->user->getAvatar() !!}
                    {!! $comment->user->getOnline() !!}
                </div>

                @if (getUser())
                    <div class="float-right">
                        @if (getUser('id') !== $comment->user_id)
                            <a href="#" onclick="return postReply(this)" title="{{ __('main.reply') }}"><i class="fa fa-reply text-muted"></i></a>

                            <a href="#" onclick="return postQuote(this)" title="{{ __('main.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>

                            <a href="#" onclick="return sendComplaint(this)" data-type="{{ $model }}" data-id="{{ $comment->id }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $comments->currentPage() }}" rel="nofollow" title="{{ __('main.complain') }}"><i class="fa fa-bell text-muted"></i></a>
                        @endif

                        @if ($comment->created_at + 600 > SITETIME && $comment->user_id === getUser('id'))
                            <a href="/photos/edit/{{ $comment->relate_id }}/{{ $comment->id }}?page={{ $comments->currentPage() }}" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                        @endif

                        @if (isAdmin())
                            <a href="#" onclick="return deleteComment(this)" data-rid="{{ $comment->relate_id }}" data-id="{{ $comment->id }}" data-type="{{ $model }}" data-token="{{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                        @endif
                    </div>
                @endif

                <b>{!! $comment->user->getProfile() !!}</b> <small>({{ dateFixed($comment->created_at) }})</small><br>
                {!! $comment->user->getStatus() !!}
            </div>
            <div class="message">
                {!! bbCode($comment->text) !!}
            </div>

            @if (isAdmin())
                <span class="data">({{ $comment->brow }}, {{ $comment->ip }})</span>
            @endif
        </div>
    @endforeach
@else
    {!! showError(__('main.empty_comments')) !!}
@endif

{{ $comments->links() }}
