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
        <a class="btn btn-light" href="/forums"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ __('index.forums') }}</h1>
@stop

@section('content')
    @if ($forums->isNotEmpty())
        @foreach ($forums as $forum)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-file-alt fa-lg text-muted"></i>
                    <a href="/admin/forums/{{ $forum->id }}">{{ $forum->title }}</a>
                    ({{ $forum->count_topics }}/{{ $forum->count_posts }})

                    @if (isAdmin('boss'))
                        <div class="float-end">
                            <a href="/admin/forums/edit/{{ $forum->id }}"><i class="fa fa-pencil-alt"></i></a>
                            <a href="/admin/forums/delete/{{ $forum->id }}?_token={{ csrf_token() }}" onclick="return confirm('{{ __('forums.confirm_delete_forum') }}')"><i class="fa fa-times"></i></a>
                        </div>
                    @endif

                    @if ($forum->description)
                        <div class="small fst-italic">{{ $forum->description }}</div>
                    @endif
                </div>

                <div class="section-content">
                    @if ($forum->children->isNotEmpty())
                        @foreach ($forum->children as $child)
                            <i class="fa fa-copy text-muted"></i> <b><a href="/admin/forums/{{ $child->id }}">{{ $child->title }}</a></b>
                            ({{ $child->count_topics }}/{{ $child->count_posts }})

                            @if (isAdmin('boss'))
                                <a href="/admin/forums/edit/{{ $child->id }}"><i class="fa fa-pencil-alt"></i></a>
                                <a href="/admin/forums/delete/{{ $child->id }}?_token={{ csrf_token() }}" onclick="return confirm('{{ __('forums.confirm_delete_forum') }}')"><i class="fa fa-times"></i></a>
                            @endif
                            <br>
                        @endforeach
                    @endif

                    @if ($forum->lastTopic->lastPost->id)
                            {{ __('forums.topic') }}: <a href="/admin/topics/end/{{ $forum->lastTopic->id }}">{{ $forum->lastTopic->title }}</a>
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
            <form action="/admin/forums/create" method="post">
                @csrf
                <div class="input-group{{ hasError('title') }}">
                    <input type="text" class="form-control" id="title" name="title" maxlength="50" value="{{ getInput('title') }}" placeholder="{{ __('forums.forum') }}" required>
                    <button class="btn btn-primary">{{ __('forums.create_forum') }}</button>
                </div>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </form>
        </div>

        <i class="fa fa-sync"></i> <a href="/admin/forums/restatement?_token={{ csrf_token() }}">{{ __('main.recount') }}</a><br>
    @endif
@stop
