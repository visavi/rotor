@extends('layout')

@section('title', __('news.edit_title'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('news.index') }}">{{ __('index.news') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('news.view', ['id' => $news->id]) }}">{{ $news->title }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('news.comments', ['id' => $news->id]) }}">{{ __('main.comments') }}</a></li>
            <li class="breadcrumb-item active">{{ __('news.edit_title') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @include('app/_comment_edit_form', [
        'action' => route('news.edit-comment', ['id' => $comment->relate_id, 'cid' => $comment->id, 'page' => $page]),
    ])
@stop
