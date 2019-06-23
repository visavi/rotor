@extends('layout')

@section('title')
    Все публикации ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')

    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/blogs/create">{{ trans('blogs.add') }}</a>
        </div><br>
    @endif

    <h1>Все публикации</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ trans('blogs.blogs') }}</a></li>
            <li class="breadcrumb-item active">Все публикации</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <div class="card mb-3">
                {!! $data->getFirstImage() !!}
                <div class="card-body">
                    <h2 class="card-title"><a href="/articles/{{ $data->id }}">{{ $data->title }}</a> <small>(Рейтинг: {!! formatNum($data->rating) !!})</small></h2>

                    @if ($data->category->parent->id)
                        <a href="/blogs/{{ $category->parent->id }}"><span class="badge badge-light">{{ $category->parent->name }}</span></a> /
                    @endif

                    <a href="/blogs/{{ $data->category->id }}"><span class="badge badge-light">{{ $data->category->name }}</span></a>

                    <p class="card-text">
                        {!! bbCodeTruncate($data->text, 100) !!}
                    </p>
                    <a href="/articles/{{ $data->id }}" class="btn btn-sm btn-light border">Читать дальше →</a>
                </div>
                <div class="card-footer text-muted">
                    {{ trans('main.author') }}: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})
                    {{ trans('main.views') }}: {{ $data->visits }}
                    <div class="float-right">
                        <a href="/articles/comments/{{ $data->id }}">{{ trans('main.comments') }}</a> ({{ $data->count_comments }})
                        <a href="/articles/end/{{ $data->id }}">&raquo;</a>
                    </div>
                </div>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError(trans('blogs.empty_articles')) !!}
    @endif

    <a href="/blogs/top">{{ trans('blogs.top_articles') }}</a> /
    <a href="/blogs/tags">{{ trans('blogs.tag_cloud') }}</a> /
    <a href="/blogs/search">{{ trans('main.search') }}</a> /
    <a href="/blogs/authors">{{ trans('blogs.authors') }}</a>
@stop
