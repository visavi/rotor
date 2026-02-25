@extends('layout')

@section('title', __('blogs.title_edit_article') . ' ' . $article->title)

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.blogs.index') }}">{{ __('index.blogs') }}</a></li>

            @foreach ($article->category->getParents() as $parent)
                <li class="breadcrumb-item"><a href="{{ route('admin.blogs.blog', ['id' => $parent->id]) }}">{{ $parent->name }}</a></li>
            @endforeach

            <li class="breadcrumb-item"><a href="{{ route('articles.view', ['slug' => $article->slug]) }}">{{ $article->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.title_edit_article') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (! $article->active && ! $article->draft)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> {{ __('blogs.article_pending_text') }}
        </div>
    @endif

    @if (! $article->isPublished())
        <div class="alert alert-info">
            <i class="fas fa-exclamation-triangle"></i> {{ __('blogs.article_delayed_text') }}<br>
        </div>
    @endif

    @if ($article->draft)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> {{ __('blogs.article_draft_text') }}<br>
        </div>
    @endif

    @if ($article->active)
        <i class="fa fa-pencil-alt"></i>
        <form action="{{ route('admin.articles.publish', ['id' => $article->id]) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('blogs.confirm_unpublish_article') }}')">
            @csrf
            <button class="btn btn-link p-0 me-3">{{ __('main.unpublish') }}</button>
        </form>
    @else
        <i class="fa fa-pencil-alt"></i>
        <form action="{{ route('admin.articles.publish', ['id' => $article->id]) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('blogs.confirm_publish_article') }}')">
            @csrf
            <button class="btn btn-link p-0 me-3">{{ __('main.publish') }}</button>
        </form>
    @endif

    <i class="fas fa-times"></i>
    <form action="{{ route('admin.articles.delete', ['id' => $article->id]) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('blogs.confirm_delete_article') }}')">
        @csrf
        @method('DELETE')
        <button class="btn btn-link p-0 me-3">{{ __('main.delete') }}</button>
    </form>
    <hr>

    <div class="section-form mb-3 shadow">
        @include('blogs/_form')
    </div>
@stop
