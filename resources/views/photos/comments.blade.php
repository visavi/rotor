@extends('layout')

@section('title', sprintf('%s - %s (%s)', $photo->title, __('main.comments'), __('main.page_num', ['page' => $comments->currentPage()])))

@section('header')
    <h1>{{ $photo->title }} - {{ __('main.comments') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('photos.index') }}">{{ __('index.photos') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('photos.view', ['id' => $photo->id]) }}">{{ $photo->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('main.comments') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @foreach ($comments as $comment)
        @include('app/_comment_item', ['editRoute' => 'photos.edit-comment', 'parentId' => $photo->id])
    @endforeach

    {{ $comments->links() }}

    @include('app/_comment_form', [
        'action' => route('photos.comments', ['id' => $photo->id]),
        'closed' => $photo->closed,
    ])
@stop
