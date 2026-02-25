@extends('layout')

@section('title', $article->title)

@section('description', truncateDescription(bbCode($article->text, false)))
@section('canonical', route('articles.view', ['slug' => $article->slug]))

@section('header')
    @if (getUser())
        <div class="float-end">
            @if (getUser('id') === $article->user->id)
                <a class="btn btn-success" href="{{ route('articles.edit', ['id' => $article->id]) }}">{{ __('main.change') }}</a>
            @endif

            @if (isAdmin())
                <div class="btn-group">
                    <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-wrench"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('admin.articles.edit', ['id' => $article->id]) }}">{{ __('main.edit') }}</a>
                        <form action="{{ route('admin.articles.delete', ['id' => $article->id]) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('blogs.confirm_delete_article') }}')">
                            @csrf
                            <button class="btn btn-link dropdown-item">{{ __('main.delete') }}</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <h1>{{ $article->title }} <small>({{ __('main.rating') }}: {{ formatNum($article->rating) }})</small></h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">{{ __('index.blogs') }}</a></li>

            @foreach ($article->category->getParents() as $parent)
                <li class="breadcrumb-item"><a href="{{ route('blogs.blog', ['id' => $parent->id]) }}">{{ $parent->name }}</a></li>
            @endforeach

            <li class="breadcrumb-item active">{{ $article->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (! $article->active)
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> {{ __('blogs.article_not_active_text') }}<br>
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

    <i class="fas fa-print"></i> <a class="me-3" href="{{ route('articles.print', ['id' => $article->id]) }}">{{ __('main.print') }}</a>
    <i class="fas fa-rss"></i> <a href="{{ route('articles.rss-comments', ['id' => $article->id]) }}">{{ __('main.rss') }}</a>
    <hr>

    <div class="mb-3">
        <div class="section-message">
            {{ bbCode($article->text) }}
        </div>

        {{ __('main.added') }}: {{ $article->user->getProfile() }}
        <small class="section-date text-muted fst-italic">{{ dateFixed($article->created_at) }}</small><br>

        <div class="my-3 fst-italic">
            <i class="fa fa-tag"></i>
            @foreach ($article->tags as $tag)
                <a href="{{ route('blogs.tag', ['tag' => urlencode($tag->name)]) }}">{{ $tag->name }}</a> {{ ! $loop->last ? ', ' : '' }}
            @endforeach
        </div>

        <div class="js-rating">{{ __('main.rating') }}:
            @if (getUser() && getUser('id') !== $article->user_id)
                <a class="post-rating-down<?= $article->poll?->vote === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $article->id }}" data-type="{{ $article->getMorphClass() }}" data-vote="-"><i class="fa fa-arrow-down"></i></a>
            @endif
            <b>{{ formatNum($article->rating) }}</b>
            @if (getUser() && getUser('id') !== $article->user_id)
                <a class="post-rating-up<?= $article->poll?->vote === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $article->id }}" data-type="{{ $article->getMorphClass() }}" data-vote="+"><i class="fa fa-arrow-up"></i></a>
            @endif
        </div>

        <i class="fa fa-eye"></i> {{ __('main.views') }}: {{ $article->visits }}<br>
        <i class="fa fa-comment"></i> <a href="{{ route('articles.comments', ['id' => $article->id]) }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $article->count_comments }}</span>
    </div>
@stop
