@extends('layout')

@section('title', sprintf('%s - %s (%s)', $news->title, __('news.comments_title'), __('main.page_num', ['page' => $comments->currentPage()])))

@section('header')
    <h1>{{ $news->title }} - {{ __('main.comments') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('news.index') }}">{{ __('index.news') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('news.view', ['id' => $news->id]) }}">{{ $news->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('main.comments') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @foreach ($comments as $comment)
        @include('app/_comment_item', ['editRoute' => 'news.edit-comment', 'parentId' => $news->id])
    @endforeach

    {{ $comments->links() }}

    @include('app/_comment_form', [
        'action'  => route('news.comments', ['id' => $news->id]),
        'closed'  => $news->closed,
    ])
@stop
