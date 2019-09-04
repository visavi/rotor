@extends('layout')

@section('title')
    {{ __('forums.title_move_topic') }} {{ $topic->title }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/forums">{{ __('index.forums') }}</a></li>

            @if ($topic->forum->parent->id)
                <li class="breadcrumb-item"><a href="/admin/forums/{{ $topic->forum->parent->id }}">{{ $topic->forum->parent->title }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/admin/forums/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_move_topic') }} {{ $topic->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ __('main.author') }}: {!! $topic->user->getProfile() !!}<br>
    {{ __('main.messages') }}: {{ $topic->count_posts }}<br>
    {{ __('main.created') }}: {{ dateFixed($topic->created_at) }}<br>

    <div class="form mb-3">
        <form action="/admin/topics/move/{{ $topic->id }}" method="post">
            @csrf
            <div class="form-group{{ hasError('fid') }}">
                <label for="fid">{{ __('forums.forum') }}:</label>
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
                <div class="invalid-feedback">{{ textError('fid') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.move') }}</button>
        </form>
    </div>
@stop
