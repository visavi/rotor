@extends('layout')

@section('title', $topic->title . ' (' . __('main.page_num', ['page' => $posts->currentPage()]) . ')')

@section('description', $description)

@section('header')
    @if (isAdmin())
        <div class="btn-group float-end">
            <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-wrench"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                @if ($topic->closed)
                    <form action="{{ route('admin.topics.action', ['id' => $topic->id, 'type' => 'open', 'page' => $posts->currentPage()]) }}" method="post">
                        @csrf
                        <button class="btn btn-link dropdown-item">{{ __('main.open') }}</button>
                    </form>
                @else
                    <form action="{{ route('admin.topics.action', ['id' => $topic->id, 'type' => 'closed', 'page' => $posts->currentPage()]) }}" method="post">
                        @csrf
                        <button class="btn btn-link dropdown-item">{{ __('main.close') }}</button>
                    </form>
                @endif

                @if ($topic->locked)
                    <form action="{{ route('admin.topics.action', ['id' => $topic->id, 'type' => 'unlocked', 'page' => $posts->currentPage()]) }}" method="post">
                        @csrf
                        <button class="btn btn-link dropdown-item">{{ __('main.unlock') }}</button>
                    </form>
                @else
                    <form action="{{ route('admin.topics.action', ['id' => $topic->id, 'type' => 'locked', 'page' => $posts->currentPage()]) }}" method="post">
                        @csrf
                        <button class="btn btn-link dropdown-item">{{ __('main.lock') }}</button>
                    </form>
                @endif

                <a class="dropdown-item" href="{{ route('admin.topics.edit', ['id' => $topic->id]) }}">{{ __('main.change') }}</a>
                <a class="dropdown-item" href="{{ route('admin.topics.move', ['id' => $topic->id]) }}">{{ __('main.move') }}</a>
                <form action="{{ route('admin.topics.delete', ['id' => $topic->id]) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('forums.confirm_delete_topic') }}')">
                    @csrf
                    <button class="btn btn-link dropdown-item">{{ __('main.delete') }}</button>
                </form>
                <a class="dropdown-item" href="{{ route('admin.topics.topic', ['id' => $topic->id, 'page' => $posts->currentPage()]) }}">{{ __('main.management') }}</a>
            </div>
        </div>
    @endif

    <h1>{{ $topic->title }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('forums.index') }}">{{ __('index.forums') }}</a></li>

            @foreach ($topic->forum->getParents() as $parent)
                <li class="breadcrumb-item"><a href="{{ route('forums.forum', ['id' => $parent->id]) }}">{{ $parent->title }}</a></li>
            @endforeach

            <li class="breadcrumb-item active">{{ $topic->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fas fa-print"></i> <a class="me-3" href="{{ route('topics.print', ['id' => $topic->id]) }}">{{ __('main.print') }}</a>
    <i class="fas fa-rss"></i> <a class="me-3" href="{{ route('topics.rss', ['id' => $topic->id]) }}">{{ __('main.rss') }}</a>

    @if (getUser())
        @if (! $topic->closed && getUser('id') === $topic->user->id && getUser('point') >= setting('editforumpoint'))
            <i class="fas fa-lock"></i>
            <form action="{{ route('topics.close', ['id' => $topic->id]) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('forums.confirm_close_topic') }}')">
                @csrf
                <button class="btn btn-link p-0 me-3">{{ __('main.close') }}</button>
            </form>
            <i class="fas fa-pencil-alt"></i> <a class="me-3" href="{{ route('topics.edit', ['id' => $topic->id]) }}">{{ __('main.edit') }}</a>
        @endif

        @if ($topic->closed && getUser('id') === $topic->closeUser->id)
            <i class="fas fa-unlock"></i>
            <form action="{{ route('topics.open', ['id' => $topic->id]) }}" method="post" class="d-inline">
                @csrf
                <button class="btn btn-link p-0 me-3">{{ __('main.open') }}</button>
            </form>
        @endif

        <?php $bookmark = $topic->bookmark_posts ? __('forums.from_bookmarks') : __('forums.to_bookmarks'); ?>
        <i class="fas fa-bookmark"></i> <a class="me-3" href="#" onclick="return bookmark(this)" data-tid="{{ $topic->id }}" data-from="{{ __('forums.from_bookmarks') }}"  data-to="{{ __('forums.to_bookmarks') }}">{{ $bookmark }}</a>
    @endif

    <div class="float-end" data-bs-toggle="tooltip" title="{{ __('main.views') }}">
        <i class="far fa-eye"></i> {{ $topic->visits }}
    </div>

    @if ($topic->curators)
       <div class="mt-3">
            <span class="badge bg-adaptive">
                <i class="fa fa-wrench"></i> {{ __('forums.topic_curators') }}:
                @foreach ($topic->curators as $key => $curator)
                    <?php $comma = (empty($key)) ? '' : ', '; ?>
                    {{ $comma }}{{ $curator->getProfile() }}
                @endforeach
            </span>
        </div>
    @endif

    @if ($topic->note)
        <div class="section-form my-1">{{ bbCode($topic->note) }}</div>
    @endif
    <hr>

    @if ($vote)
        <h5>{{ $vote->title }}</h5>

        <div class="mb-3">
            @if ($vote->poll || $vote->closed || ! getUser())
                @foreach ($vote->voted as $key => $value)
                    <?php $proc = round(($value * 100) / $vote->sum, 1); ?>
                    <?php $maxproc = round(($value * 100) / $vote->max); ?>

                    <b>{{ $key }}</b> ({{ __('forums.votes') }}: {{ $value }})<br>
                    {{ progressBar($maxproc, $proc . '%') }}
                @endforeach
            @else
                <form class="mb-3" action="{{ route('topics.vote', ['id' => $topic->id, 'page' => $posts->currentPage()]) }}" method="post">
                    @csrf
                    @foreach ($vote->answers as $answer)
                        <label><input name="poll" type="radio" value="{{ $answer->id }}"> {{ $answer->answer }}</label><br>
                    @endforeach
                    <button class="btn btn-sm btn-primary mt-3">{{ __('forums.vote') }}</button>
                </form>
            @endif

            {{ __('forums.total_votes') }}: {{ $vote->count }}
        </div>
    @endif

    @if ($topic->isModer)
        <form action="{{ route('topics.delete', ['id' => $topic->id, 'page' => $posts->currentPage()]) }}" method="post">
            @csrf
    @endif

    @if ($posts->isNotEmpty())
        @foreach ($posts as $post)
            <div class="section mb-3 shadow" id="post_{{ $post->id }}">
                <div class="user-avatar">
                    {{ $post->user->getAvatar() }}
                    {{ $post->user->getOnline() }}
                </div>

                <div class="section-user d-flex align-items-start">
                    <div class="flex-grow-1">
                        {{ $post->user->getProfile() }}
                        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
                        @if ($topic->user->id === $post->user->id)
                            <span class="badge bg-info">{{ __('main.author') }}</span>
                        @endif
                        <br>
                        <small class="fst-italic">{{ $post->user->getStatus() }}</small>
                    </div>

                    <div class="text-end">
                        @if (getUser())
                            <div class="section-action">
                            @if (getUser('id') !== $post->user_id)
                                <a href="#" onclick="return postReply(this)" title="{{ __('main.reply') }}"><i class="fa fa-reply text-muted"></i></a>

                                <a href="#" onclick="return postQuote(this)" title="{{ __('main.quote') }}"><i class="fa fa-quote-right text-muted"></i></a>

                                <a href="#" onclick="return sendComplaint(this)" data-type="{{ $post->getMorphClass() }}" data-id="{{ $post->id }}" data-page="{{ $posts->currentPage() }}" rel="nofollow" title="{{ __('main.complain') }}"><i class="fa fa-bell text-muted"></i></a>
                            @endif

                            @if ($topic->isModer || (getUser('id') === $post->user_id && $post->created_at + 600 > SITETIME))
                                <a href="{{ route('posts.edit', ['id' => $post->id, 'page' => $posts->currentPage()]) }}" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                                @if ($topic->isModer)
                                    <input type="checkbox" class="form-check-input" name="del[]" value="{{ $post->id }}">
                                @endif
                            @endif
                            </div>
                        @endif

                        <div class="section-action js-rating">
                            @if (getUser() && getUser('id') !== $post->user_id)
                                <a class="post-rating-down{{ $post->vote === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $post->id }}" data-type="{{ $post->getMorphClass() }}" data-vote="-"><i class="fas fa-arrow-down"></i></a>
                            @endif
                            <b>{{ formatNum($post->rating) }}</b>
                            @if (getUser() && getUser('id') !== $post->user_id)
                                <a class="post-rating-up{{ $post->vote === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $post->id }}" data-type="{{ $post->getMorphClass() }}" data-vote="+"><i class="fas fa-arrow-up"></i></a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="section-body border-top">
                    <div class="section-message">
                        {{ bbCode($post->text) }}
                    </div>

                    @include('app/_media_viewer', ['model' => $post])

                    @if ($post->edit_user_id)
                        <div class="small">
                            <i class="fa fa-exclamation-circle text-danger"></i> {{ __('main.changed') }}: {{ $post->editUser->getName() }} <small class="section-date text-muted fst-italic">{{ dateFixed($post->updated_at) }}</small>
                        </div>
                    @endif

                    @if (isAdmin())
                        <div class="small text-muted fst-italic mt-2">{{ $post->brow }}, {{ $post->ip }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('forums.empty_posts')) }}
    @endif

    @if ($topic->isModer)
            <div class="clearfix mb-3">
                <button class="btn btn-sm btn-danger float-end">{{ __('main.delete_selected') }}</button>
            </div>
        </form>
    @endif

    {{ $posts->links() }}

    @if (getUser())
        @if (empty($topic->closed))
            <div class="section-form mb-3 shadow">
                <form action="{{ route('topics.create', ['id' => $topic->id]) }}" method="post">
                    @csrf
                    <div class="mb-3{{ hasError('msg') }}">
                        <label for="msg" class="form-label">{{ __('forums.post') }}:</label>
                        <textarea class="form-control markItUp" maxlength="{{ setting('forum_text_max') }}" id="msg" rows="5" name="msg" placeholder="{{ __('forums.post') }}" required>{{ getInput('msg') }}</textarea>
                        <div class="invalid-feedback">{{ textError('msg') }}</div>
                        <span class="js-textarea-counter"></span>
                    </div>

                    @include('app/_upload_file', [
                        'model' => App\Models\Post::getModel(),
                        'files' => $files,
                    ])

                    <button class="btn btn-primary">{{ __('main.write') }}</button>
                </form>
            </div>
        @else
            {{ showError(trans_choice('forums.topic_closed_user', $topic->closeUser->id, ['login' => $topic->closeUser->getProfile()])) }}
        @endif
    @else
        {{ showError(__('main.not_authorized')) }}
    @endif

    <a href="/stickers">{{ __('main.stickers') }}</a>  /
    <a href="/tags">{{ __('main.tags') }}</a>  /
    <a href="/rules">{{ __('main.rules') }}</a><br>
@stop
