@extends('layout')

@section('title', $category->name . ' (' . __('main.page_num', ['page' => $articles->currentPage()]) . ')')

@section('header')
    <div class="float-end">
        @if (! $category->closed)
            <a class="btn btn-success" href="/blogs/create?cid={{ $category->id }}">{{ __('blogs.add') }}</a>
        @endif

        <a class="btn btn-light" href="/blogs/{{ $category->id }}?page={{ $articles->currentPage() }}"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ $category->name }} <small>({{ __('blogs.all_articles') }}: {{ $category->count_articles }})</small></h1>
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
        </ol>
    </nav>
@stop

@section('content')
    @if ($articles->isNotEmpty())
        @foreach ($articles as $article)
            <div class="section mb-3 shadow">
                <div class="section-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="section-title">
                            <i class="fa fa-pencil-alt"></i>
                            <a href="/articles/{{ $article->id }}">{{ $article->title }}</a> ({{ formatNum($article->rating) }})
                        </div>
                    </div>

                    <div class="text-end">
                        <a href="/admin/articles/edit/{{ $article->id }}" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt text-muted"></i></a>
                        <a href="/admin/articles/move/{{ $article->id }}" title="{{ __('main.move') }}"><i class="fa fa-arrows-alt text-muted"></i></a>
                        <a href="/admin/articles/delete/{{ $article->id }}?page={{ $articles->currentPage() }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('blogs.confirm_delete_article') }}')" title="{{ __('main.delete') }}"><i class="fa fa-times text-muted"></i></a>
                    </div>
                </div>

                <div class="section-content">
                    {{ $article->shortText() }}<br>
                    {{ __('main.author') }}: {{ $article->user->getProfile() }} ({{ dateFixed($article->created_at) }})<br>
                    {{ __('main.views') }}: {{ $article->visits }}<br>
                    <a href="/articles/comments/{{ $article->id }}">{{ __('main.comments') }}</a> ({{ $article->count_comments }})
                    <a href="/articles/end/{{ $article->id }}">&raquo;</a>
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('blogs.empty_articles')) }}
    @endif

    {{ $articles->links() }}
@stop
