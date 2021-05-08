@extends('layout')

@section('title', __('blogs.title_search'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.title_search') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3">
        <form method="get">
            <div class="input-group{{ hasError('find') }}">
                <input name="find" class="form-control" id="inputFind" minlength="3" maxlength="64" placeholder="{{ __('main.request') }}" value="{{ getInput('find', $find) }}" required>
                <button class="btn btn-primary">{{ __('main.search') }}</button>
            </div>
            <div class="invalid-feedback">{{ textError('find') }}</div>
            <span class="text-muted fst-italic"><?= __('main.request_requirements') ?></span>
        </form>
    </div>

    @if ($articles->isNotEmpty())
        <div class="mb-3">{{ __('main.total_found') }}: {{ $articles->total() }}</div>

        @foreach ($articles as $article)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-pencil-alt"></i>
                    <a href="/articles/{{ $article->id }}">{{ $article->title }}</a> ({{ formatNum($article->rating) }})
                </div>

                <div class="section-content">
                    {{ $article->shortText() }}

                    {{ __('blogs.blog') }}: <a href="/blogs/{{ $article->category->id }}">{{ $article->category->name }}</a><br>
                    {{ __('main.author') }}: {{ $article->user->getProfile() }}
                    <small class="section-date text-muted fst-italic">{{ dateFixed($article->created_at) }}</small>
                </div>
            </div>
        @endforeach

        {{ $articles->links() }}
    @endif
@stop
