@extends('layout')

@section('title')
    {{ $category->name }} ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    @if (! $category->closed && getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/blogs/create?cid={{ $category->id }}">{{ trans('blogs.add') }}</a>
        </div><br>
    @endif

    <h1>{{ $category->name }} <small>({{ trans('blogs.all_articles') }}: {{ $category->count_blogs }})</small></h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ trans('blogs.blogs') }}</a></li>

            @if ($category->parent->id)
                <li class="breadcrumb-item"><a href="/blogs/{{ $category->parent->id }}">{{ $category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item active">{{ $category->name }}</li>

            @if (isAdmin())
                <li class="breadcrumb-item"><a href="/admin/blogs/{{ $category->id }}?page={{ $page->current }}">{{ trans('main.management') }}</a></li>
            @endif
        </ol>
    </nav>
@stop

@section('content')
    @if ($blogs->isNotEmpty())
        @foreach ($blogs as $data)
            <div class="b">
                <i class="fa fa-pencil-alt"></i>
                <b><a href="/articles/{{ $data->id }}">{{ $data->title }}</a></b> ({!! formatNum($data->rating) !!})
            </div>
            <div>
                {!! truncateWord(bbCode($data->text), 50) !!}<br>
                {{ trans('main.author') }}: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})<br>
                {{ trans('main.views') }}: {{ $data->visits }}<br>
                <a href="/articles/comments/{{ $data->id }}">{{ trans('main.comments') }}</a> ({{ $data->count_comments }})
                <a href="/articles/end/{{ $data->id }}">&raquo;</a>
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
