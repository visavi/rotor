@extends('layout')

@section('title', $offer->title . ' - ' . __('main.comments'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('offers.index', ['type' => $offer->type]) }}">{{ __('index.offers') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('offers.view', ['id' => $offer->id]) }}">{{ $offer->title }}</a></li>
            <li class="breadcrumb-item active">{{ __('main.comments') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @foreach ($comments as $comment)
        @include('app/_comment_item', ['editRoute' => 'offers.edit-comment', 'parentId' => $offer->id])
    @endforeach

    {{ $comments->links() }}

    @include('app/_comment_form', [
        'action' => route('offers.comments', ['id' => $offer->id]),
        'closed' => $offer->closed,
    ])
@stop
