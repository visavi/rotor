@extends('layout')

@section('title', __('index.blogs') . ' - ' . __('blogs.title_active_articles', ['user' => $user->getName()]) . ' (' . __('main.page_num', ['page' => $articles->currentPage()])  . ')')

@section('header')
    <h1>{{ __('blogs.title_active_articles', ['user' => $user->getName()]) }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.title_active_articles', ['user' => $user->getName()]) }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (getUser() && getUser('id') === $user->id)
            <?php $type = $active ? 'success' : 'adaptive'; ?>
        <a href="{{ route('articles.user-articles', ['active' => 1]) }}" class="btn btn-{{ $type }} btn-sm">{{ __('blogs.verified_articles') }} <span class="badge bg-adaptive">{{ $activeCount }}</span></a>

            <?php $type = ! $active ? 'success' : 'adaptive'; ?>
        <a href="{{ route('articles.user-articles', ['active' => 0]) }}" class="btn btn-{{ $type }} btn-sm">{{ __('blogs.pending_articles') }} <span class="badge bg-adaptive">{{ $delayCount }}</span></a>
        <hr>
    @endif

    @if ($articles->isNotEmpty())
        @foreach ($articles as $article)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-pencil-alt"></i>
                    <a href="{{ route('articles.view', ['slug' => $article->slug]) }}">{{ $article->title }}</a> <span class="badge bg-adaptive">{{ formatNum($article->rating) }}</span>
                </div>

                @if (! $article->active)
                    <span class="badge bg-danger">{{ __('blogs.not_active') }}</span>
                @endif

                @if (! $article->isPublished())
                    <span class="badge bg-info">{{ __('blogs.delayed') }}</span>
                @endif

                @if ($article->draft)
                    <span class="badge bg-warning">{{ __('blogs.draft') }}</span>
                @endif

                <div class="section-content">
                    <div class="mb-2">
                        {{ __('blogs.blog') }}: <a href="{{ route('blogs.blog', ['id' => $article->category->id]) }}">{{ $article->category->name }}</a><br>
                        {{ __('main.author') }}: {{ $article->user->getProfile() }} <small class="section-date text-muted fst-italic">{{ dateFixed($article->created_at) }}</small>
                    </div>

                    <i class="fa fa-comment"></i> <a href="{{ route('articles.comments', ['id' => $article->id]) }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $article->count_comments }}</span>
                </div>
            </div>
        @endforeach

        {{ $articles->links() }}

        <div class="mb-3">
            {{ __('blogs.total_articles') }}: <b>{{ $articles->total() }}</b>
        </div>
    @else
        {{ showError(__('blogs.empty_articles')) }}
    @endif
@stop
