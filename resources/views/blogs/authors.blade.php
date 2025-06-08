@extends('layout')

@section('title', __('blogs.authors'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">{{ __('index.blogs') }}</a></li>
            <li class="breadcrumb-item active">{{ __('blogs.authors') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($articles->isNotEmpty())
        @foreach ($articles as $article)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa fa-pencil-alt"></i>
                    <a href="{{ route('blogs.user-articles', ['user' => $article->login]) }}">{{ $article->login }}</a>
                </div>

                {{ $article->cnt }} {{ __('blogs.all_articles') }} / {{ $article->count_comments }} {{ __('main.comments') }}
            </div>
        @endforeach

        {{ $articles->links() }}

        <div class="mb-3">
            {{ __('blogs.total_authors') }}: <b>{{ $articles->total() }}</b>
        </div>
    @else
        {{ showError(__('blogs.empty_articles')) }}
    @endif
@stop
