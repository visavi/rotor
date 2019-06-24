@extends('layout')

@section('title')
    {{ trans('news.site_news') }} ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    <h1>{{ trans('news.site_news') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('news.site_news') }}</li>

            @if (isAdmin('moder'))
                <li class="breadcrumb-item"><a href="/admin/news">{{ trans('main.management') }}</a></li>
            @endif
        </ol>
    </nav>
@stop

@section('content')
    @if ($news->isNotEmpty())
        @foreach ($news as $data)
            <div class="b">
                <i class="fa fa-file-alt fa-lg text-muted"></i>
                <b><a href="/news/{{ $data->id }}">{{ $data->title }}</a></b><small> ({{ dateFixed($data->created_at) }})</small>
            </div>

            @if ($data->image)
                <div class="img">
                    <a href="{{ $data->image }}">{!! resizeImage($data->image, ['width' => 100, 'alt' => $data->title]) !!}</a>
                </div>
            @endif

            <div class="clearfix">{!! $data->shortText() !!}</div>
            <div>
                {{ trans('main.added') }}: {!! $data->user->getProfile() !!}<br>
                <a href="/news/comments/{{ $data->id }}">{{ trans('main.comments') }}</a> ({{ $data->count_comments }})
                <a href="/news/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('news.empty_news')) !!}
    @endif

    <i class="fa fa-rss"></i> <a href="/news/rss">{{ trans('main.rss') }}</a><br>
    <i class="fa fa-comment"></i> <a href="/news/allcomments">{{ trans('main.last_comments') }}</a><br>
@stop
