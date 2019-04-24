@extends('layout')

@section('title')
    {{ trans('forums.title_move_topic') }} {{ $topic->title }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/forums">{{ trans('forums.forum') }}</a></li>

            @if ($topic->forum->parent->id)
                <li class="breadcrumb-item"><a href="/admin/forums/{{ $topic->forum->parent->id }}">{{ $topic->forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/admin/forums/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a></li>
            <li class="breadcrumb-item active">{{ trans('forums.title_move_topic') }} {{ $topic->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ trans('main.author') }}: {!! $topic->user->getProfile() !!}<br>
    {{ trans('main.messages') }}: {{ $topic->count_posts }}<br>
    {{ trans('main.created') }}: {{ dateFixed($topic->created_at) }}<br>

    <div class="form mb-3">
        <form action="/admin/topics/move/{{ $topic->id }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('fid') }}">
                <label for="fid">{{ trans('forums.forum') }}:</label>
                <select class="form-control" id="fid" name="fid">

                    @foreach ($forums as $data)
                        <option value="{{ $data->id }}"{{ $data->closed || $topic->forum_id === $data->id ? ' disabled' : '' }}>{{ $data->title }}</option>

                        @if ($data->children->isNotEmpty())
                            @foreach($data->children as $datasub)
                                <option value="{{ $datasub->id }}"{{ $datasub->closed || $topic->forum_id === $datasub->id ? ' disabled' : '' }}>– {{ $datasub->title }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                {!! textError('fid') !!}
            </div>

            <button class="btn btn-primary">{{ trans('main.move') }}</button>
        </form>
    </div>
@stop
