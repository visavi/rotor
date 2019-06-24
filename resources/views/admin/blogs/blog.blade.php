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
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/blogs">{{ trans('blogs.blogs') }}</a></li>

            @if ($category->parent->id)
                <li class="breadcrumb-item"><a href="/admin/blogs/{{ $category->parent->id }}">{{ $category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item active">{{ $category->name }}</li>

            @if (isAdmin())
                <li class="breadcrumb-item"><a href="/blogs/{{ $category->id }}?page={{ $page->current }}">Обзор</a></li>
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

                <div class="float-right">
                    <a href="/admin/articles/edit/{{ $data->id }}" title="{{ trans('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                    <a href="/admin/articles/move/{{ $data->id }}" title="{{ trans('main.move') }}"><i class="fa fa-arrows-alt text-muted"></i></a>
                    <a href="/admin/articles/delete/{{ $data->id }}?page={{ $page->current }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('{{ trans('blogs.confirm_delete_article') }}')" title="{{ trans('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                </div>

            </div>
            <div>
                {!! $data->shortText() !!}<br>
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
@stop
