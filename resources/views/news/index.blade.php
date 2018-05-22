@extends('layout')

@section('title')
    {{ trans('news.title', ['page' => $page->current]) }}
@stop

@section('content')

    <h1>{{ trans('news.header') }}</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('news.header') }}</li>

            @if (isAdmin('moder'))
                <li class="breadcrumb-item"><a href="/admin/news">{{ trans('common.management') }}</a></li>
            @endif
        </ol>
    </nav>

    @if ($news->isNotEmpty())
        @foreach ($news as $data)
            <div class="b">
                <i class="fa fa-file-alt fa-lg text-muted"></i>
                <b><a href="/news/{{ $data->id }}">{{ $data->title }}</a></b><small> ({{ dateFixed($data->created_at) }})</small>
            </div>

            @if ($data->image)
                <div class="img">
                    <a href="/uploads/news/{{ $data->image }}">{!! resizeImage('/uploads/news/' . $data->image, ['size' => 100, 'alt' => $data->title]) !!}</a>
                </div>
            @endif

            <div class="clearfix">{!! bbCode($data->shortText()) !!}</div>
            <div>
                {{ trans('news.added_by') }}: {!! profile($data->user) !!}<br>
                <a href="/news/comments/{{ $data->id }}">{{ trans('news.comments') }}</a> ({{ $data->count_comments }})
                <a href="/news/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('news.empty_news')) !!}
    @endif

    <i class="fa fa-rss"></i> <a href="/news/rss">{{ trans('news.rss') }}</a><br>
    <i class="fa fa-comment"></i> <a href="/news/allcomments">{{ trans('news.last_comments') }}</a><br>
@stop
