@extends('layout')

@section('title')
    Все публикации ({{ __('main.page_num', ['page' => $blogs->currentPage()]) }})
@stop

@section('header')

    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/blogs/create">{{ __('blogs.add') }}</a>
        </div><br>
    @endif

    <h1>Все публикации</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">Все публикации</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="card-title"><a href="/articles/{{ $data->id }}">{{ $data->title }}</a> <small>(Рейтинг: {!! formatNum($data->rating) !!})</small></h2>

                    @if ($data->category->parent->id)
                        <a href="/blogs/{{ $category->parent->id }}"><span class="badge badge-light">{{ $category->parent->name }}</span></a> /
                    @endif

                    <a href="/blogs/{{ $data->category->id }}"><span class="badge badge-light">{{ $data->category->name }}</span></a>

                    <p class="card-text">
                        {!! $data->shortText() !!}
                    </p>
                </div>
                <div class="card-footer text-muted">
                    {{ __('main.author') }}: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})
                    {{ __('main.views') }}: {{ $data->visits }}
                    <div class="float-right">
                        <a href="/articles/comments/{{ $data->id }}">{{ __('main.comments') }}</a> ({{ $data->count_comments }})
                        <a href="/articles/end/{{ $data->id }}">&raquo;</a>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        {!! showError(__('blogs.empty_articles')) !!}
    @endif

    {{ $blogs->links() }}

    <a href="/blogs/top">{{ __('blogs.top_articles') }}</a> /
    <a href="/blogs/tags">{{ __('blogs.tag_cloud') }}</a> /
    <a href="/blogs/search">{{ __('main.search') }}</a> /
    <a href="/blogs/authors">{{ __('blogs.authors') }}</a>
@stop
