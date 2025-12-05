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
        <a class="me-3" href="{{ route('admin.articles.publish', ['id' => $article->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('blogs.confirm_unpublish_article') }}')">{{ __('main.unpublish') }}</a>
    @else
        <i class="fa fa-pencil-alt"></i>
        <a class="me-3" href="{{ route('admin.articles.publish', ['id' => $article->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('blogs.confirm_publish_article') }}')">{{ __('main.publish') }}</a>
    @endif

    <i class="fas fa-times"></i> <a class="me-3" href="{{ route('admin.articles.delete', ['id' => $article->id, '_token' => csrf_token()]) }}" onclick="return confirm('{{ __('blogs.confirm_delete_article') }}')">{{ __('main.delete') }}</a>
    <hr>

    <div class="section-form mb-3 shadow">
        @include('blogs/_form')
    </div>
@stop
