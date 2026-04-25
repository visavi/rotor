@extends('layout')

@section('title', __('photos.edit_comment'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('photos.index') }}">{{ __('index.photos') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('photos.view', ['id' => $photo->id]) }}">{{ $photo->title }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('photos.comments', ['id' => $photo->id]) }}">{{ __('main.comments') }}</a></li>
            <li class="breadcrumb-item active">{{ __('photos.edit_comment') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @include('app/_comment_edit_form', [
        'action' => route('photos.edit-comment', ['id' => $comment->relate_id, 'cid' => $comment->id, 'page' => $page]),
    ])
@stop
