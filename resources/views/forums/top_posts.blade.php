@extends('layout')

@section('title')
    {{ __('forums.title_top_posts') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ __('index.forums') }}</a></li>
            <li class="breadcrumb-item active">{{ __('forums.title_top_posts') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ __('main.period') }}:
    <?php $active = ($period === 1) ? 'success' : 'light'; ?>
    <a href="/forums/top/posts?period=1" class="badge badge-{{ $active }}">{{ __('main.last_day') }}</a>

    <?php $active = ($period === 7) ? 'success' : 'light'; ?>
    <a href="/forums/top/posts?period=7" class="badge badge-{{ $active }}">{{ __('main.last_week') }}</a>

    <?php $active = ($period === 30) ? 'success' : 'light'; ?>
    <a href="/forums/top/posts?period=30" class="badge badge-{{ $active }}">{{ __('main.last_month') }}</a>

    <?php $active = ($period === 365) ? 'success' : 'light'; ?>
    <a href="/forums/top/posts?period=365" class="badge badge-{{ $active }}">{{ __('main.last_year') }}</a>

    <?php $active = (empty($period)) ? 'success' : 'light'; ?>
    <a href="/forums/top/posts" class="badge badge-{{ $active }}">{{ __('main.all_time') }}</a>
    <hr>

    @if ($posts->isNotEmpty())
        @foreach ($posts as $data)
            <div class="section mb-3 shadow">
                <i class="fa fa-file-alt"></i> <b><a href="/topics/{{ $data->topic_id }}/{{ $data->id }}">{{ $data->topic->title }}</a></b>
                ({{ __('main.rating') }}: {{ $data->rating }})

                <div class="post-message">
                    {!! bbCode($data->text) !!}<br>

                    {{ __('main.posted') }}: {{ $data->user->login }} <small>({{ dateFixed($data->created_at) }})</small>

                    @if (isAdmin())
                        <div class="small text-muted font-italic mt-2">({{ $data->brow }}, {{ $data->ip }})</div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        {!! showError(__('forums.empty_posts')) !!}
    @endif

    {{ $posts->links() }}
@stop
