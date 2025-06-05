@extends('layout')

@section('title', __('index.blogs') . ' - ' . __('blogs.new_articles') . ' (' . __('main.page_num', ['page' => $articles->currentPage()])  . ')')

@section('header')
    <h1>{{ __('blogs.new_articles') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.new_articles') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($articles->isNotEmpty())
        @foreach ($articles as $article)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-pencil-alt"></i>
                    <a href="/articles/{{ $article->id }}">{{ $article->title }}</a> <span class="badge bg-adaptive">{{ formatNum($article->rating) }}</span>
                </div>

                <div class="section-content">
                    {{ __('blogs.blog') }}: <a href="/blogs/{{ $article->category_id }}">{{ $article->category->name }}</a><br>
                    {{ __('main.views') }}: {{ $article->visits }}<br>
                    {{ __('main.author') }}: {{ $article->user->getProfile() }}
                    <small class="section-date text-muted fst-italic">{{  dateFixed($article->created_at) }}</small>
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('blogs.empty_articles')) }}
    @endif

    {{ $articles->links() }}
@stop
