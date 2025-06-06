@extends('layout')

@section('title', $news->title)

@section('description', truncateDescription(bbCode($news->text, false)))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/news">{{ __('index.news') }}</a></li>
            <li class="breadcrumb-item active">{{ $news->title }}</li>
        </ol>
    </nav>
@stop

@section('header')
    @if (isAdmin())
        <div class="float-end">
            <div class="btn-group">
                <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-wrench"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="/admin/news/edit/{{ $news->id }}">{{ __('main.edit') }}</a>
                    <a class="dropdown-item" href="/admin/news/delete/{{ $news->id }}?_token={{ csrf_token() }}" onclick="return confirm('{{ __('news.confirm_delete') }}')">{{ __('main.delete') }}</a>
                </div>
            </div>
        </div>
    @endif

    <h1>{{ $news->title }}</h1>
@stop

@section('content')
    <div class="mb-3">
        <div class="section-content">
            <div class="section-message">
                {{ bbCode($news->text) }}
            </div>

            @if ($news->getImages()->isNotEmpty())
                @include('app/_viewer', ['model' => $news, 'files' => $news->getImages()])
            @endif

            @if ($news->getFiles()->isNotEmpty())
                @foreach ($news->getFiles() as $file)
                    <div class="media-file">
                        @if ($file->isVideo())
                            <div>
                                <video src="{{ $file->path }}" style="max-width:100%;" preload="metadata" controls playsinline></video>
                            </div>
                        @endif

                        @if ($file->isAudio())
                            <div>
                                <audio src="{{ $file->path }}" style="max-width:100%;" preload="metadata" controls></audio>
                            </div>
                        @endif

                        {{ icons($file->extension) }}
                        <a href="{{ $file->path }}">{{ $file->name }}</a> ({{ formatSize($file->size) }})
                    </div>
                @endforeach
            @endif
        </div>


        <div class="section-body">
            <div class="section-body">
                <span class="avatar-micro">{{ $news->user->getAvatarImage() }}</span> {{ $news->user->getProfile() }} <small class="section-date text-muted fst-italic">{{ dateFixed($news->created_at) }}</small>
            </div>

            <div class="js-rating">
                {{ __('main.rating') }}:
                @if (getUser() && getUser('id') !== $news->user_id)
                    <a class="post-rating-down{{ $news->vote === '-' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $news->id }}" data-type="{{ $news->getMorphClass() }}" data-vote="-" data-token="{{ csrf_token() }}"><i class="fas fa-arrow-down"></i></a>
                @endif
                <b>{{ formatNum($news->rating) }}</b>
                @if (getUser() && getUser('id') !== $news->user_id)
                    <a class="post-rating-up{{ $news->vote === '+' ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $news->id }}" data-type="{{ $news->getMorphClass() }}" data-vote="+" data-token="{{ csrf_token() }}"><i class="fas fa-arrow-up"></i></a>
                @endif
            </div>
        </div>
    </div>

    @if ($comments->isNotEmpty())
        <h5><i class="fa fa-comment"></i> {{ __('main.last_comments') }}</h5>

        @foreach ($comments as $comment)
            <div class="section mb-3 shadow">
                <div class="user-avatar">
                    {{ $comment->user->getAvatar() }}
                    {{ $comment->user->getOnline() }}
                </div>

                <div class="section-user d-flex align-items-center">
                    <div class="flex-grow-1">
                        {{ $comment->user->getProfile() }}

                        <small class="section-date text-muted fst-italic">{{ dateFixed($comment->created_at) }}</small><br>
                        <small class="fst-italic">{{ $comment->user->getStatus() }}</small>
                    </div>
                </div>

                <div class="section-body border-top">
                    <div class="section-message">
                        {{ bbCode($comment->text) }}
                    </div>

                    @if (isAdmin())
                        <div class="small text-muted fst-italic mt-2">{{ $comment->brow }}, {{ $comment->ip }}</div>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="p-3 mb-3 shadow">
            <i class="fas fa-comments"></i> <b><a href="{{ route('news.comments', ['id' => $news->id]) }}">{{ __('news.all_comments') }}</a></b> <span class="badge bg-adaptive">{{ $news->count_comments }}</span>
        </div>
    @endif

    @if (! $news->closed)
        @if ($comments->isEmpty())
            {{ showError(__('main.empty_comments')) }}
        @endif

        @if (getUser())
            <div class="section-form mb-3 shadow">
                <form action="{{ route('news.comments', ['id' => $news->id, 'read' => 1]) }}" method="post">
                    @csrf
                    <div class="mb-3{{ hasError('msg') }}">
                        <label for="msg" class="form-label">{{ __('main.message') }}:</label>
                        <textarea class="form-control markItUp" id="msg" rows="5" maxlength="{{ setting('comment_length') }}" name="msg" placeholder="{{ __('main.message') }}" required>{{ getInput('msg') }}</textarea>
                        <div class="invalid-feedback">{{ textError('msg') }}</div>
                        <span class="js-textarea-counter"></span>
                    </div>

                    <button class="btn btn-primary">{{ __('main.write') }}</button>
                </form>
            </div>

            <a href="/rules">{{ __('main.rules') }}</a> /
            <a href="/stickers">{{ __('main.stickers') }}</a> /
            <a href="/tags">{{ __('main.tags') }}</a><br><br>
        @else
            {{ showError(__('main.not_authorized')) }}
        @endif
    @else
        {{ showError(__('news.closed_news')) }}
    @endif
@stop
