@extends('layout')

@section('title', $article->title)

@section('description', truncateDescription(bbCode($article->text, false)))

@section('header')
    @if (getUser())
        <div class="float-end">
            @if (getUser('id') === $article->user->id)
                <a class="btn btn-success" href="/articles/edit/{{ $article->id }}">{{ __('main.change') }}</a>
            @endif

            @if (isAdmin())
                <div class="btn-group">
                    <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-wrench"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="/admin/articles/edit/{{ $article->id }}">{{ __('main.edit') }}</a>
                        <a class="dropdown-item" href="/admin/articles/move/{{ $article->id }}">{{ __('main.move') }}</a>
                        <a class="dropdown-item" href="/admin/articles/delete/{{ $article->id }}?_token={{ csrf_token() }}" onclick="return confirm('{{ __('blogs.confirm_delete_article') }}')">{{ __('main.delete') }}</a>
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
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>

            @foreach ($article->category->getParents() as $parent)
                <li class="breadcrumb-item"><a href="/blogs/{{ $parent->id }}">{{ $parent->name }}</a></li>
            @endforeach

            <li class="breadcrumb-item active">{{ $article->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fas fa-print"></i> <a class="me-3" href="/articles/print/{{ $article->id }}">{{ __('main.print') }}</a>
    <i class="fas fa-rss"></i> <a href="/articles/rss/{{ $article->id }}">{{ __('main.rss') }}</a>
    <hr>

    <div class="mb-3">
        <div class="section-message">
            {{ bbCode($article->text) }}
        </div>

        {{ __('main.added') }}: {{ $article->user->getProfile() }}
        <small class="section-date text-muted fst-italic">{{ dateFixed($article->created_at) }}</small><br>

        <div class="my-3 fst-italic">
            <i class="fa fa-tag"></i> {!! $tags !!}
        </div>

        <div class="js-rating">{{ __('main.rating') }}:
            @if (getUser() && getUser('id') !== $article->user_id)
                <a class="post-rating-down<?= $article->vote === '-' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $article->id }}" data-type="{{ $article->getMorphClass() }}" data-vote="-" data-token="{{ csrf_token() }}"><i class="fa fa-arrow-down"></i></a>
            @endif
            <b>{{ formatNum($article->rating) }}</b>
            @if (getUser() && getUser('id') !== $article->user_id)
                <a class="post-rating-up<?= $article->vote === '+' ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $article->id }}" data-type="{{ $article->getMorphClass() }}" data-vote="+" data-token="{{ csrf_token() }}"><i class="fa fa-arrow-up"></i></a>
            @endif
        </div>

        <i class="fa fa-eye"></i> {{ __('main.views') }}: {{ $article->visits }}<br>
        <i class="fa fa-comment"></i> <a href="/articles/comments/{{ $article->id }}">{{ __('main.comments') }}</a> ({{  $article->count_comments }})
        <a href="/articles/end/{{ $article->id }}">&raquo;</a>
    </div>
@stop
