<div class="comment-item py-2" id="comment_{{ $comment->id }}" data-id="{{ $comment->id }}">
    <div class="d-flex gap-1">
        {{-- Левая колонка: аватар + кнопка +/- + линия --}}
        <div class="comment-left">
            <span class="avatar-mini flex-shrink-0">{{ $comment->user->getAvatarImage() }}</span>
            @if ($comment->children->isNotEmpty())
                <div class="comment-thread-ctrl" onclick="toggleComment({{ $comment->id }})" id="comment-ctrl-{{ $comment->id }}">
                    <span class="comment-thread-btn"><i class="fa fa-minus" style="font-size:8px"></i></span>
                    <div class="comment-thread-line"></div>
                </div>
            @endif
        </div>

        {{-- Правая колонка --}}
        <div class="comment-right">
            {{-- Заголовок (всегда виден) --}}
            <div class="comment-header">
                {{ $comment->user->getProfile() }}
                <small class="text-muted section-date" data-date="{{ dateFixed($comment->created_at, original: true) }}">{{ dateFixed($comment->created_at) }}</small>
            </div>

            {{-- «Раскрыть ветку» — вне comment-body, появляется рядом с [+] при сворачивании --}}
            @if ($comment->children->isNotEmpty())
                <span class="comment-expand-label d-none" id="comment-expand-{{ $comment->id }}" onclick="toggleComment({{ $comment->id }})">{{ __('main.expand_thread') }} ({{ $comment->countAllDescendants() }})</span>
            @endif

            {{-- Тело (сворачивается) --}}
            <div id="comment-body-{{ $comment->id }}" class="mt-1">
                <div class="section-message mb-2">
                    {{ $comment->getText() }}
                </div>

                @include('app/_media_viewer', ['model' => $comment])

                @if (isAdmin())
                    <div class="small text-muted fst-italic mb-1">{{ $comment->brow }}, {{ $comment->ip }}</div>
                @endif

                {{-- Действия --}}
                <div class="comment-actions d-flex align-items-center gap-3 flex-wrap">
                    <span class="d-flex align-items-center gap-1 js-rating">
                        @if (getUser() && getUser('id') !== $comment->user_id)
                            <a class="post-rating-down{{ $comment->vote === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $comment->id }}" data-type="{{ $comment->getMorphClass() }}" data-vote="-"><i class="fas fa-arrow-down fa-sm"></i></a>
                        @else
                            <i class="fas fa-arrow-down fa-sm text-muted opacity-25"></i>
                        @endif
                        <b class="small">{{ formatNum($comment->rating) }}</b>
                        @if (getUser() && getUser('id') !== $comment->user_id)
                            <a class="post-rating-up{{ $comment->vote === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $comment->id }}" data-type="{{ $comment->getMorphClass() }}" data-vote="+"><i class="fas fa-arrow-up fa-sm"></i></a>
                        @else
                            <i class="fas fa-arrow-up fa-sm text-muted opacity-25"></i>
                        @endif
                    </span>

                    @if (getUser())
                        @if (getUser('id') !== $comment->user_id && ! ($closed ?? false))
                            <a href="#" onclick="return openReplyForm({{ $comment->id }})">
                                <i class="fas fa-reply"></i> {{ __('main.reply') }}
                            </a>
                        @endif
                        @if (getUser('id') !== $comment->user_id)
                            <a href="#" onclick="return postQuote(this)">
                                <i class="fas fa-quote-left"></i> {{ __('main.quote') }}
                            </a>
                            <a href="#" onclick="return sendComplaint(this)" data-type="{{ $comment->relate->getMorphClass() }}" data-id="{{ $comment->id }}" rel="nofollow">
                                <i class="fas fa-flag"></i> {{ __('main.complain') }}
                            </a>
                        @endif
                        @if ($comment->created_at + 600 > SITETIME && getUser('id') === $comment->user_id)
                            <a href="#" onclick="return openEditModal(this)" data-id="{{ $comment->id }}" data-url="/comments">
                                <i class="fas fa-edit"></i> {{ __('main.edit') }}
                            </a>
                        @endif
                        @if (isAdmin())
                            <a href="#" onclick="return deleteComment(this)" data-id="{{ $comment->id }}">
                                <i class="fas fa-trash"></i> {{ __('main.delete') }}
                            </a>
                        @endif
                    @endif
                </div>

                {{-- Форма ответа --}}
                @if (getUser() && ! ($closed ?? false) && getUser('id') !== $comment->user_id)
                    <div class="reply-form d-none mt-2" id="reply-form-{{ $comment->id }}">
                        <form action="{{ $action }}" method="post">
                            @csrf
                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                            <div class="mb-1 reply-editor-wrap border rounded overflow-hidden">
                                <textarea class="form-control form-control-sm border-0 shadow-none rounded-0" id="reply-textarea-{{ $comment->id }}" name="msg" rows="4" maxlength="{{ setting('comment_text_max') }}" data-relate-type="{{ \App\Models\Comment::$morphName }}" data-relate-id="0" required></textarea>
                                <div class="d-flex justify-content-between align-items-center px-2 py-1">
                                    <button type="button" class="btn btn-sm btn-link p-0" onclick="toggleReplyToolbar(this)" title="Форматирование">
                                        <i class="fa fa-font text-muted fa-lg"></i>
                                    </button>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="closeReplyForm({{ $comment->id }})">{{ __('main.cancel') }}</button>
                                        <button class="btn btn-sm btn-success">{{ __('main.write') }}</button>
                                    </div>
                                </div>
                            </div>
                            <div class="reply-error text-danger small mt-1"></div>
                        </form>
                    </div>
                @endif

                {{-- Дочерние комментарии --}}
                @if ($comment->children->isNotEmpty())
                    <div class="mt-2" id="comment-children-{{ $comment->id }}">
                        @foreach ($comment->children as $child)
                            @include('app/_comment_item', ['comment' => $child, 'action' => $action, 'closed' => $closed])
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
