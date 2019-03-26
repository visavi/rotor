@extends('layout')

@section('title')
    {{ trans('news.site_news') }}
@stop

@section('header')
    <div class="float-right">
        <a class="btn btn-success" href="/admin/news/create">{{ trans('main.add') }}</a>
    </div><br>

    <h1>{{ trans('news.site_news') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('news.site_news') }}</li>
            <li class="breadcrumb-item"><a href="/news">{{ trans('main.review') }}</a></li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($news->isNotEmpty())
        @foreach ($news as $data)

            <div class="b">
                <div class="float-right">
                    @if ($data->top)
                        <div class="right"><span style="color:#ff0000">{{ trans('news.on_homepage') }}</span></div>
                    @endif
                </div>

                <i class="fa {{ $data->getIcon() }} text-muted"></i>

                <b><a href="/news/{{ $data->id }}">{{ $data->title }}</a></b><small> ({{ dateFixed($data->created_at) }})</small><br>

                <div class="float-right">
                    <a href="/admin/news/edit/{{ $data->id }}?page={{ $page->current }}" data-toggle="tooltip" title="{{ trans('main.edit') }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                    <a href="/admin/news/delete/{{ $data->id }}?token={{ $_SESSION['token'] }}" data-toggle="tooltip" title="{{ trans('main.delete') }}" onclick="return confirm('{{ trans('news.confirm_delete') }}')"><i class="fas fa-times text-muted"></i></a>
                </div>

            </div>

            @if ($data->image)
                <div class="img">
                    <a href="{{ $data->image }}">{!! resizeImage($data->image, ['width' => 100, 'alt' => $data->title]) !!}</a>
                </div>
            @endif

            <div class="clearfix">{!! bbCode($data->shortText()) !!}</div>

            <div>{{ trans('main.added') }}: {!! $data->user->getProfile() !!}<br>
                <a href="/news/comments/{{  $data->id }}">{{ trans('main.comments') }}</a> ({{ $data->count_comments }})
                <a href="/news/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}

        {{ trans('news.total_news') }}: <b>{{ $news->count() }}</b><br><br>
    @else
        {!! showError(trans('news.empty_news')) !!}
    @endif

    @if (isAdmin('boss'))
        <i class="fa fa-sync"></i> <a href="/admin/news/restatement?token={{ $_SESSION['token'] }}">{{ trans('main.recount') }}</a><br>
    @endif
@stop
