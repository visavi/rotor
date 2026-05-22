@extends('layout')

@section('title', __('forums.title_move_topic') . ' ' . $topic->title)

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.forums.index') }}">{{ __('index.forums') }}</a></li>

            @foreach ($topic->forum->getParents() as $parent)
                <li class="breadcrumb-item"><a href="{{ route('admin.forums.forum', ['id' => $parent->id ]) }}">{{ $parent->title }}</a></li>
            @endforeach

            <li class="breadcrumb-item active">{{ __('forums.title_move_topic') }} {{ $topic->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ __('main.author') }}: {{ $topic->user->getProfile() }}<br>
    {{ __('main.messages') }}: {{ $topic->count_posts }}<br>
    {{ __('main.created') }}: {{ dateFixed($topic->created_at) }}<br>

    <div class="section-form mb-3 shadow">
        <form action="{{ route('admin.topics.move', ['id' => $topic->id]) }}" method="post">
            @csrf
            <div class="mb-3{{ hasError('fid') }}">
                <label for="fid" class="form-label">{{ __('forums.forum') }}:</label>
                <select class="form-select" id="fid" name="fid">
                    @foreach ($forums as $data)
                        <option value="{{ $data->id }}"{{ $data->closed || $topic->forum_id === $data->id ? ' disabled' : '' }}>
                            {{ str_repeat('–', $data->depth) }} {{ $data->title }}
                        </option>
                    @endforeach

                </select>
                <div class="invalid-feedback">{{ textError('fid') }}</div>
            </div>

            <button class="btn btn-primary">{{ __('main.move') }}</button>
        </form>
    </div>
@stop
