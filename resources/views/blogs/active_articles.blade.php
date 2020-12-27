@extends('layout')

@section('title', __('index.blogs') . ' - ' . __('blogs.title_active_articles', ['user' => $user->getName()]) . ' (' . __('main.page_num', ['page' => $articles->currentPage()])  . ')')

@section('header')
    <h1>{{ __('blogs.title_active_articles', ['user' => $user->getName()]) }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/blogs">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.title_active_articles', ['user' => $user->getName()]) }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($articles->isNotEmpty())
        @foreach ($articles as $article)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-pencil-alt"></i>
                    <a href="/articles/{{ $article->id }}">{{ $article->title }}</a> ({!! formatNum($article->rating) !!})
                </div>

                <div class="section-content">
                    {{ __('main.author') }}: {!! $article->user->getProfile() !!} ({{ dateFixed($article->created_at) }})<br>
                    <i class="fa fa-comment"></i> <a href="/articles/comments/{{ $article->id }}">{{ __('main.comments') }}</a> ({{ $article->count_comments }})
                    <a href="/articles/end/{{ $article->id }}">&raquo;</a>
                </div>
            </div>
        @endforeach

        {{ __('blogs.total_articles') }}: <b>{{ $articles->total() }}</b><br>
    @else
        {!! showError(__('blogs.empty_articles')) !!}
    @endif

    {{ $articles->links() }}
@stop
