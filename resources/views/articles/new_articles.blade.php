@extends('layout')

@section('title', sprintf('%s - %s (%s)', __('index.blogs'), __('blogs.new_articles'), __('main.page_num', ['page' => $articles->currentPage()])))

@section('header')
    <h1>{{ __('blogs.new_articles') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.new_articles') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($articles->isNotEmpty())
        <div class="sort-links border-bottom pb-3 mb-3">
            {{ __('main.sort') }}:
            @foreach ($sorting as $key => $option)
                <a href="{{ route('articles.index', ['sort' => $key, 'order' => $option['inverse'] ?? 'desc']) }}" class="badge bg-{{ $option['badge'] ?? 'adaptive' }}">
                    {{ $option['label'] }}{{ $option['icon'] ?? '' }}
                </a>
            @endforeach
        </div>

        @foreach ($articles as $article)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-pencil-alt"></i>
                    <a href="{{ route('articles.view', ['slug' => $article->slug]) }}">{{ $article->title }}</a> <span class="badge bg-adaptive">{{ formatNum($article->rating) }}</span>
                </div>

                <div class="section-content mb-2">
                    {{ __('blogs.blog') }}: <a href="{{ route('blogs.blog', ['id' => $article->category_id]) }}">{{ $article->category->name }}</a><br>
                    {{ __('main.views') }}: {{ $article->visits }}<br>

                    <div class="section-body">
                        <span class="avatar-micro">{{ $article->user->getAvatarImage() }}</span> {{ $article->user->getProfile() }}
                        <small class="section-date text-muted fst-italic">{{ dateFixed($article->created_at) }}</small><br>
                    </div>
                </div>

                <i class="fa-regular fa-comment"></i> <a href="{{ route('articles.comments', ['id' => $article->id]) }}">{{ __('main.comments') }}</a> <span class="badge bg-adaptive">{{ $article->count_comments }}</span>
            </div>
        @endforeach
    @else
        {{ showError(__('blogs.empty_articles')) }}
    @endif

    {{ $articles->links() }}
@stop
