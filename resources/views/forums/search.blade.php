@extends('layout')

@section('title', __('forums.title_search'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ __('index.forums') }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_search') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <form method="get">
        <div class="input-group{{ hasError('find') }}">
            <input name="find" class="form-control" id="inputFind" minlength="3" maxlength="64" placeholder="{{ __('main.request') }}" value="{{ getInput('find', $find) }}" required>
            <button class="btn btn-primary">{{ __('main.search') }}</button>
        </div>

        <div class="form-check my-1">
            <input type="checkbox" class="form-check-input" value="title" name="type" id="type"{{ $type === 'title' ? ' checked' : '' }}>
            <label class="form-check-label" for="type">{{ __('forums.search_in_topics') }}</label>
        </div>
        <div class="invalid-feedback">{{ textError('find') }}</div>
        <span class="text-muted fst-italic"><?= __('main.request_requirements') ?></span>
    </form>

    @if ($data->isNotEmpty())
        <div class="my-3">{{ __('main.total_found') }}: {{ $data->total() }}</div>

        @if ($type === 'text')
            @foreach ($data as $post)
                <div class="section mb-3 shadow">
                    <div class="section-title">
                        <i class="fa fa-file-alt"></i>
                        <a href="/topics/{{ $post->topic_id }}/{{ $post->id }}">{{ $post->topic->title }}</a>
                    </div>

                    <div class="section-message">
                        {{ bbCode($post->text) }}<br>
                        {{ __('forums.forum') }}: <a href="/topics/{{ $post->topic->forum->id }}">{{ $post->topic->forum->title }}</a><br>
                        {{ __('main.posted') }}: {{ $post->user->getProfile() }}
                        <small class="section-date text-muted fst-italic">{{ dateFixed($post->created_at) }}</small>
                    </div>
                </div>
            @endforeach
        @else
            @foreach ($data as $topic)
                <div class="section mb-3 shadow">
                    <div class="section-title">
                        <i class="fa {{ $topic->getIcon() }} text-muted"></i>
                        <a href="/topics/{{ $topic->id }}">{{ $topic->title }}</a> ({{ $topic->count_posts }})
                    </div>

                    <div class="section-message">
                        {{ $topic->pagination() }}
                        {{ __('forums.forum') }}: <a href="/topics/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a><br>
                        {{ __('forums.post') }}: {{ $topic->lastPost->user->getName() }}
                        <small class="section-date text-muted fst-italic">{{ dateFixed($topic->lastPost->created_at) }}</small>
                    </div>
                </div>
            @endforeach
        @endif

        {{ $data->links() }}
    @endif
@stop
