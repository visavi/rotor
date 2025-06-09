@extends('layout')

@section('title', __('index.forums'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.forums') }}</li>
        </ol>
    </nav>
@stop

@section('header')
    <div class="float-end">
        <a class="btn btn-light" href="{{ route('forums.index') }}"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ __('index.forums') }}</h1>
@stop

@section('content')
    @if ($forums->isNotEmpty())
        @foreach ($forums as $forum)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-file-alt fa-lg text-muted"></i>
                    <a href="{{ route('admin.forums.forum', ['id' => $forum->id]) }}">{{ $forum->title }}</a>
                    <span class="badge bg-adaptive">{{ $forum->count_topics }}/{{ $forum->count_posts }}</span>

                    @if (isAdmin('boss'))
                        <div class="float-end">
                            <a href="{{ route('admin.forums.edit', ['id' => $forum->id]) }}"><i class="fa fa-pencil-alt"></i></a>
                            <a href="{{ route('admin.forums.delete', ['id' => $forum->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('forums.confirm_delete_forum') }}')"><i class="fa fa-times"></i></a>
                        </div>
                    @endif

                    @if ($forum->description)
                        <div class="section-description text-muted fst-italic small">{{ $forum->description }}</div>
                    @endif
                </div>

                <div class="section-content">
                    @if ($forum->children->isNotEmpty())
                        @foreach ($forum->children as $child)
                            <i class="fa fa-copy text-muted"></i> <b><a href="{{ route('admin.forums.forum', ['id' => $child->id ]) }}">{{ $child->title }}</a></b>
                            <span class="badge bg-adaptive">{{ $child->count_topics }}/{{ $child->count_posts }}</span>

                            @if (isAdmin('boss'))
                                <a href="{{ route('admin.forums.edit', ['id' => $child->id]) }}"><i class="fa fa-pencil-alt"></i></a>
                                <a href="{{ route('admin.forums.delete', ['id' => $child->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('forums.confirm_delete_forum') }}')"><i class="fa fa-times"></i></a>
                            @endif
                            <br>
                        @endforeach
                    @endif

                    @if ($forum->lastTopic->lastPost->id)
                            {{ __('forums.topic') }}: <a href="{{ route('topics.topic', ['id' => $forum->lastTopic->id]) }}">{{ $forum->lastTopic->title }}</a>
                        <br>
                            {{ __('forums.post') }}: {{ $forum->lastTopic->lastPost->user->getName() }} ({{ dateFixed($forum->lastTopic->lastPost->created_at) }})
                    @else
                        {{ __('forums.empty_posts') }}
                    @endif
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('forums.empty_forums')) }}
    @endif

    @if (isAdmin('boss'))
        <div class="section-form mb-3 shadow">
            <form action="{{ route('admin.forums.create') }}" method="post">
                @csrf
                <div class="input-group{{ hasError('title') }}">
                    <input type="text" class="form-control" id="title" name="title" maxlength="50" value="{{ getInput('title') }}" placeholder="{{ __('forums.forum') }}" required>
                    <button class="btn btn-primary">{{ __('forums.create_forum') }}</button>
                </div>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </form>
        </div>

        <i class="fa fa-sync"></i> <a href="{{ route('admin.forums.restatement', ['_token' => csrf_token()]) }}">{{ __('main.recount') }}</a><br>
    @endif
@stop
