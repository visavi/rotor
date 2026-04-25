@extends('layout')

@section('title', sprintf('%s - %s (%s)', $article->title, __('main.comments'), __('main.page_num', ['page' => $comments->currentPage()])))

@section('header')
    <h1>{{ $article->title }} - {{ __('main.comments') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">{{ __('index.blogs') }}</a></li>

            @foreach ($article->category->getParents() as $parent)
                <li class="breadcrumb-item"><a href="{{ route('blogs.blog', ['id' => $parent->id]) }}">{{ $parent->name }}</a></li>
            @endforeach

            <li class="breadcrumb-item"><a href="{{ route('articles.view', ['slug' => $article->slug]) }}">{{ $article->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('main.comments') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fas fa-rss"></i> <a href="{{ route('articles.rss-comments', ['id' => $article->id]) }}">{{ __('main.rss') }}</a>
    <hr>

    @foreach ($comments as $comment)
        @include('app/_comment_item', ['editRoute' => 'articles.edit-comment', 'parentId' => $article->id])
    @endforeach

    {{ $comments->links() }}

    @include('app/_comment_form', ['action' => route('articles.comments', ['id' => $article->id])])
@stop
