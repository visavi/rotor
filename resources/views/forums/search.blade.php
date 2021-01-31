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

            <div class="input-group-append">
                <button class="btn btn-primary">{{ __('main.search') }}</button>
            </div>
        </div>
        <div class="invalid-feedback">{{ textError('find') }}</div>
    </form>

    @if ($posts->isNotEmpty())
        <div class="my-3">{{ __('main.total_found') }}: {{ $posts->total() }}</div>

        @foreach ($posts as $post)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-file-alt"></i>
                    <a href="/topics/{{ $post->topic_id }}/{{ $post->id }}">{{ $post->topic->title }}</a>
                </div>

                <div class="section-message">
                    {!! bbCode($post->text) !!}<br>
                    {{ __('forums.forum') }}: <a href="/topics/{{ $post->topic->forum->id }}">{{ $post->topic->forum->title }}</a><br>
                    {{ __('main.posted') }}: {!! $post->user->getProfile() !!}
                    <small class="section-date text-muted font-italic">{{ dateFixed($post->created_at) }}</small>
                </div>
            </div>
        @endforeach

        {{ $posts->links() }}
    @endif
@stop
