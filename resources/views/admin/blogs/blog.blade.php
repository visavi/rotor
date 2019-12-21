@extends('layout')

@section('title')
    {{ $category->name }} ({{ __('main.page_num', ['page' => $blogs->currentPage()]) }})
@stop

@section('header')
    @if (! $category->closed && getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/blogs/create?cid={{ $category->id }}">{{ __('blogs.add') }}</a>
        </div><br>
    @endif

    <h1>{{ $category->name }} <small>({{ __('blogs.all_articles') }}: {{ $category->count_blogs }})</small></h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/blogs">{{ __('index.blogs') }}</a></li>

            @if ($category->parent->id)
                <li class="breadcrumb-item"><a href="/admin/blogs/{{ $category->parent->id }}">{{ $category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item active">{{ $category->name }}</li>

            @if (isAdmin())
                <li class="breadcrumb-item"><a href="/blogs/{{ $category->id }}?page={{ $blogs->currentPage() }}">Обзор</a></li>
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
                    <a href="/admin/articles/edit/{{ $data->id }}" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                    <a href="/admin/articles/move/{{ $data->id }}" title="{{ __('main.move') }}"><i class="fa fa-arrows-alt text-muted"></i></a>
                    <a href="/admin/articles/delete/{{ $data->id }}?page={{ $blogs->currentPage() }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('blogs.confirm_delete_article') }}')" title="{{ __('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                </div>

            </div>
            <div>
                {!! $data->shortText() !!}<br>
                {{ __('main.author') }}: {!! $data->user->getProfile() !!} ({{ dateFixed($data->created_at) }})<br>
                {{ __('main.views') }}: {{ $data->visits }}<br>
                <a href="/articles/comments/{{ $data->id }}">{{ __('main.comments') }}</a> ({{ $data->count_comments }})
                <a href="/articles/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach
    @else
        {!! showError(__('blogs.empty_articles')) !!}
    @endif

    {{ $blogs->links() }}
@stop
