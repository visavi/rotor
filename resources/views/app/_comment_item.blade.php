<div class="section mb-3 shadow" id="comment_{{ $comment->id }}">
    <div class="user-avatar">
        {{ $comment->user->getAvatar() }}
        {{ $comment->user->getOnline() }}
    </div>

    <div class="section-user d-flex align-items-start">
        <div class="flex-grow-1">
            {{ $comment->user->getProfile() }}
            <small class="section-date text-muted fst-italic" data-date="{{ dateFixed($comment->created_at, original: true) }}">{{ dateFixed($comment->created_at) }}</small><br>
            <small class="fst-italic">{{ $comment->user->getStatus() }}</small>
        </div>

        @if (getUser())
            <div class="text-end">
                <div class="section-action">
                    @if (getUser('id') !== $comment->user_id)
                        <a href="#" onclick="return postReply(this)" data-bs-toggle="tooltip" title="{{ __('main.reply') }}"><i class="fa fa-reply text-muted"></i></a>

                        <a href="#" onclick="return postQuote(this)" data-bs-toggle="tooltip" title="{{ __('main.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>

                        <a href="#" onclick="return sendComplaint(this)" data-type="{{ $comment->relate->getMorphClass() }}" data-id="{{ $comment->id }}" data-page="{{ $comments->currentPage() }}" rel="nofollow" data-bs-toggle="tooltip" title="{{ __('main.complain') }}"><i class="fa fa-bell text-muted"></i></a>
                    @endif

                    @if ($comment->created_at + 600 > SITETIME && getUser('id') === $comment->user_id)
                        <a href="#" onclick="return openEditModal(this)" data-id="{{ $comment->id }}" data-url="/comments" data-bs-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                    @endif

                    @if (isAdmin())
                        <a href="#" onclick="return deleteComment(this)" data-id="{{ $comment->id }}" data-bs-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                    @endif
                </div>

                <div class="section-action js-rating">
                    @if (getUser('id') !== $comment->user_id)
                        <a class="post-rating-down{{ $comment->vote === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $comment->id }}" data-type="{{ $comment->getMorphClass() }}" data-vote="-"><i class="fas fa-arrow-down"></i></a>
                    @endif
                    <b>{{ formatNum($comment->rating) }}</b>
                    @if (getUser('id') !== $comment->user_id)
                        <a class="post-rating-up{{ $comment->vote === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $comment->id }}" data-type="{{ $comment->getMorphClass() }}" data-vote="+"><i class="fas fa-arrow-up"></i></a>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="section-body border-top">
        <div class="section-message">
            {{ $comment->getText() }}
        </div>

        @include('app/_media_viewer', ['model' => $comment])

        @if (isAdmin())
            <div class="small text-muted fst-italic mt-2">{{ $comment->brow }}, {{ $comment->ip }}</div>
        @endif
    </div>
</div>
