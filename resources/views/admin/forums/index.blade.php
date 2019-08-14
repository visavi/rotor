@extends('layout')

@section('title')
    {{ trans('index.forums') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.forums') }}</li>
            <li class="breadcrumb-item"><a href="/forums">{{ trans('main.review') }}</a></li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($forums->isNotEmpty())
        @foreach ($forums as $forum)
            <div class="b">
                <i class="fa fa-file-alt fa-lg text-muted"></i>
                <b><a href="/admin/forums/{{ $forum->id }}">{{ $forum->title }}</a></b>
                ({{ $forum->count_topics }}/{{ $forum->count_posts }})

                @if ($forum->description)
                    <p><small>{{ $forum->description }}</small></p>
                @endif

                @if (isAdmin('boss'))
                    <div class="float-right">
                        <a href="/admin/forums/edit/{{ $forum->id }}"><i class="fa fa-pencil-alt"></i></a>
                        <a href="/admin/forums/delete/{{ $forum->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('forums.confirm_delete_forum') }}')"><i class="fa fa-times"></i></a>
                    </div>
                @endif
            </div>

            <div>
                @if ($forum->children->isNotEmpty())
                    @foreach ($forum->children as $child)
                        <i class="fa fa-copy text-muted"></i> <b><a href="/admin/forums/{{ $child->id }}">{{ $child->title }}</a></b>
                        ({{ $child->count_topics }}/{{ $child->count_posts }})

                        @if (isAdmin('boss'))
                            <a href="/admin/forums/edit/{{ $child->id }}"><i class="fa fa-pencil-alt"></i></a>
                            <a href="/admin/forums/delete/{{ $child->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('forums.confirm_delete_forum') }}')"><i class="fa fa-times"></i></a>
                        @endif
                        <br/>
                    @endforeach
                @endif

                @if ($forum->lastTopic->lastPost->id)
                        {{ trans('forums.topic') }}: <a href="/admin/topics/end/{{ $forum->lastTopic->id }}">{{ $forum->lastTopic->title }}</a>
                    <br/>
                        {{ trans('forums.post') }}: {{ $forum->lastTopic->lastPost->user->getName() }} ({{ dateFixed($forum->lastTopic->lastPost->created_at) }})
                @else
                    {{ trans('forums.empty_posts') }}
                @endif
            </div>
        @endforeach
    @else
        {!! showError(trans('forums.empty_forums')) !!}
    @endif

    @if (isAdmin('boss'))
        <div class="form my-3">
            <form action="/admin/forums/create" method="post">
                @csrf
                <div class="form-inline">
                    <div class="form-group{{ hasError('title') }}">
                        <input type="text" class="form-control" id="title" name="title" maxlength="50" value="{{ getInput('title') }}" placeholder="{{ trans('forums.forum') }}" required>
                    </div>

                    <button class="btn btn-primary">{{ trans('forums.create_forum') }}</button>
                </div>
                <div class="invalid-feedback">{{ textError('title') }}</div>
            </form>
        </div>

        <i class="fa fa-sync"></i> <a href="/admin/forums/restatement?token={{ $_SESSION['token'] }}">{{ trans('main.recount') }}</a><br>
    @endif
@stop
