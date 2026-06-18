@php
    $hasChildren = $comment->children->isNotEmpty();
    $collapsed = $comment->rating < 0 && $hasChildren;
@endphp
<div class="comment-item d-flex py-2" id="comment_{{ $comment->id }}" data-id="{{ $comment->id }}">
    {{-- Левая колонка: аватар + кнопка +/- + линия --}}
    <div class="comment-left">
        <span class="avatar-mini flex-shrink-0">{{ $comment->user->getAvatarImage() }}</span>
        @if ($hasChildren)
            <div class="comment-thread-ctrl" onclick="toggleComment({{ $comment->id }})" id="comment-ctrl-{{ $comment->id }}">
                <span class="comment-thread-btn"><i class="fa {{ $collapsed ? 'fa-plus' : 'fa-minus' }}" style="font-size:8px"></i></span>
                <div class="comment-thread-line{{ $collapsed ? ' d-none' : '' }}"></div>
            </div>
        @endif
    </div>

    {{-- Правая колонка --}}
    <div class="comment-right">
        {{-- Заголовок (всегда виден) --}}
        <div class="comment-header">
            {{ $comment->user->getProfile() }}
            <small class="text-muted section-date" data-date="{{ dateFixed($comment->created_at, original: true) }}">{{ dateFixed($comment->created_at) }}</small>
            @if (isset($ownerId) && $ownerId === $comment->user_id)
                <span class="badge bg-info">{{ __('main.author') }}</span>
            @endif
        </div>

        {{-- Раскрыть ветку — вне comment-body, появляется рядом с [+] при сворачивании --}}
        @if ($hasChildren)
            <span class="comment-expand-label{{ $collapsed ? '' : ' d-none' }}" id="comment-expand-{{ $comment->id }}" onclick="toggleComment({{ $comment->id }})">{{ __('main.expand_thread') }} ({{ $comment->countAllDescendants() }})</span>
        @endif

        {{-- Тело (сворачивается) --}}
        <div id="comment-body-{{ $comment->id }}" class="comment-body mt-1{{ $collapsed ? ' d-none' : '' }}">
            <div class="comment-content">
            @if ($comment->deleted_at)
                <div class="comment-removed text-muted fst-italic small mb-2">{{ __('main.comment_removed') }}</div>
            @else
                <div class="section-message mb-2">
                    {{ $comment->getText() }}
                </div>

                @include('app/_media_viewer', ['model' => $comment])

                @if (isAdmin())
                    <div class="small text-muted fst-italic mb-1">{{ $comment->brow }}, {{ $comment->ip }}</div>
                @endif
            @endif

            {{-- Действия --}}
            <div class="comment-actions d-flex align-items-center flex-wrap">
                @if (! $comment->deleted_at)
                    @php $ownComment = getUser() && getUser('id') === $comment->user_id; @endphp

                    @include('app/_rating', ['model' => $comment, 'vote' => $comment->vote])

                    {{-- Инлайн: только ответить + цитировать (чужие комментарии) --}}
                    @if (getUser() && ! $ownComment)
                        @if (! ($closed ?? false))
                            <a href="#" onclick="return openReplyForm({{ $comment->id }})">
                                <i class="fa-regular fa-comment"></i> {{ __('main.reply') }}
                            </a>
                        @endif
                        <a href="#" onclick="return postQuote(this)">
                            <i class="fa-solid fa-quote-left"></i> {{ __('main.quote') }}
                        </a>
                    @endif

                    {{-- Остальное в меню --}}
                    <div class="dropdown comment-more-dropdown">
                        <button class="comment-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ $comment->getViewUrl() }}" onclick="copyToClipboard(this); return false" data-copy="{{ $comment->getViewUrl() }}">
                                    <i class="fas fa-link fa-fw me-1"></i> {{ __('main.copy_link') }}
                                </a>
                            </li>
                            @if (getUser())
                                @if ($ownComment)
                                    @if ($comment->created_at + 600 > SITETIME)
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="return openEditModal(this)" data-id="{{ $comment->id }}" data-url="/comments">
                                                <i class="fas fa-edit fa-fw me-1"></i> {{ __('main.edit') }}
                                            </a>
                                        </li>
                                    @endif
                                @else
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="return sendComplaint(this)" data-type="{{ $comment->getMorphClass() }}" data-id="{{ $comment->id }}" rel="nofollow">
                                            <i class="fas fa-flag fa-fw me-1"></i> {{ __('main.complain') }}
                                        </a>
                                    </li>
                                @endif
                                @if (isAdmin())
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" onclick="return deleteComment(this)" data-id="{{ $comment->id }}">
                                            <i class="fas fa-trash fa-fw me-1"></i> {{ __('main.delete') }}
                                        </a>
                                    </li>
                                @endif
                            @endif
                        </ul>
                    </div>
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
            </div>{{-- /.comment-content --}}

            {{-- Дочерние комментарии --}}
            @if ($hasChildren)
                <div class="mt-2 comment-children" id="comment-children-{{ $comment->id }}">
                    @foreach ($comment->children as $child)
                        @include('app/_comment_item', ['comment' => $child, 'action' => $action, 'closed' => $closed, 'ownerId' => $ownerId ?? null])
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
