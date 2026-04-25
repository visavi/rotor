@extends('layout')

@section('title', __('offers.editing_comment'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('offers.index', ['type' => $offer->type]) }}">{{ __('index.offers') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('offers.view', ['id' => $offer->id]) }}">{{ $offer->title }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('offers.comments', ['id' => $offer->id]) }}">{{ __('main.comments') }}</a></li>
            <li class="breadcrumb-item active">{{ __('offers.editing_comment') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @include('app/_comment_edit_form', [
        'action' => route('offers.edit-comment', ['id' => $comment->relate_id, 'cid' => $comment->id, 'page' => $page]),
    ])
@stop
