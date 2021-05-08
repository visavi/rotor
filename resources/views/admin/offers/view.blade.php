@extends('layout')

@section('title', $offer->title)

@section('header')
    <div class="float-end">
        <a class="btn btn-light" href="/offers/{{ $offer->id }}"><i class="fas fa-wrench"></i></a>
    </div>

    <h1>{{ $offer->title }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/offers/{{ $offer->type }}">{{ __('index.offers') }}</a></li>
            <li class="breadcrumb-item active">{{ $offer->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3">
        <div class="section-content">
            <div class="float-end">
                <a href="/admin/offers/reply/{{ $offer->id }}" data-bs-toggle="tooltip" title="{{ __('main.reply') }}"><i class="fas fa-reply text-muted"></i></a>
                <a href="/admin/offers/edit/{{ $offer->id }}" data-bs-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                <a href="/admin/offers/delete?del={{ $offer->id }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('offers.confirm_delete') }}')" data-bs-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fas fa-times text-muted"></i></a>
            </div>

            <div class="section-message">
                {{ bbCode($offer->text) }}
            </div>
        </div>

        <div class="section-body">
            {{ __('main.added') }}: {{ $offer->user->getProfile() }}
            <small class="section-date text-muted fst-italic">{{ dateFixed($offer->created_at) }}</small>

            <div class="my-3">
                {{ $offer->getStatus() }}
            </div>

            <div class="js-rating">
                {{ __('main.rating') }}:
                <b>{{ formatNum($offer->rating) }}</b><br>
            </div>

            <a href="/offers/comments/{{ $offer->id }}">{{ __('main.comments') }}</a> ({{ $offer->count_comments }})
            <a href="/offers/end/{{ $offer->id }}">&raquo;</a><br>

            @if ($offer->closed)
                <span class="text-danger">{{ __('main.closed_comments') }}</span>
            @endif
        </div>
    </div>

    @if ($offer->reply)
        <div class="section mb-3 shadow">
            <h5>{{ __('offers.official_response') }}</h5>
            <div class="section-message">
                {{ bbCode($offer->reply) }}<br>
                {{ $offer->replyUser->getProfile() }}
                <small class="section-date text-muted fst-italic">{{ dateFixed($offer->updated_at) }}</small>
            </div>
        </div>
    @endif
@stop
