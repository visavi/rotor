@extends('layout')

@section('title')
    {{ trans('forums.title_top_posts') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/forums">{{ trans('forums.forum') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('forums.title_top_posts') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ trans('main.period') }}:
    <?php $active = ($period === 1) ? 'success' : 'light'; ?>
    <a href="/forums/top/posts?period=1" class="badge badge-{{ $active }}">{{ trans('main.last_day') }}</a>

    <?php $active = ($period === 7) ? 'success' : 'light'; ?>
    <a href="/forums/top/posts?period=7" class="badge badge-{{ $active }}">{{ trans('main.last_week') }}</a>

    <?php $active = ($period === 30) ? 'success' : 'light'; ?>
    <a href="/forums/top/posts?period=30" class="badge badge-{{ $active }}">{{ trans('main.last_month') }}</a>

    <?php $active = ($period === 365) ? 'success' : 'light'; ?>
    <a href="/forums/top/posts?period=365" class="badge badge-{{ $active }}">{{ trans('main.last_year') }}</a>

    <?php $active = (empty($period)) ? 'success' : 'light'; ?>
    <a href="/forums/top/posts" class="badge badge-{{ $active }}">{{ trans('main.all_time') }}</a>
    <hr>

    @if ($posts->isNotEmpty())
        @foreach ($posts as $data)
            <div class="b">
                <i class="fa fa-file-alt"></i> <b><a href="/topics/{{ $data->topic_id }}/{{ $data->id }}">{{ $data->topic->title }}</a></b>
                ({{ trans('main.rating') }}: {{ $data->rating }})
            </div>
            <div>
                {!! bbCode($data->text) !!}<br>

                {{ trans('main.posted') }}: {{ $data->user->login }} <small>({{ dateFixed($data->created_at) }})</small><br>

                @if (isAdmin())
                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                @endif

            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('forums.empty_posts')) !!}
    @endif
@stop
