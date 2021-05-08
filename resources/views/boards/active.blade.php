@extends('layout')

@section('title', __('boards.my_items'))

@section('header')
    <h1>{{ __('boards.my_items') }} <small>({{ __('index.boards') }}: {{ $items->total() }})</small></h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/boards">{{ __('index.boards') }}</a></li>
            <li class="breadcrumb-item active">{{ __('boards.my_items') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($items->isNotEmpty())
        @foreach ($items as $item)
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="/items/{{ $item->id }}">{{ $item->getFirstImage() }}</a>
                                </div>
                                <div class="col-md-7">
                                    <h5><a href="/items/{{ $item->id }}">{{ $item->title }}</a></h5>
                                    <small><i class="fas fa-angle-right"></i> <a href="/boards/{{ $item->category->id }}">{{ $item->category->name }}</a></small>
                                    <div class="section-message mb-3">
                                        {{ $item->shortText() }}
                                    </div>
                                    <div>
                                        <i class="fa fa-user-circle"></i> {{ $item->user->getProfile() }}
                                        <small class="section-date text-muted font-italic">{{ dateFixed($item->created_at) }}</small>
                                        <br>

                                        @if ($item->expires_at > SITETIME)
                                            <i class="fas fa-clock"></i> {{ __('boards.expires_in') }} {{ formatTime($item->expires_at - SITETIME) }}
                                        @else
                                            <span class="badge bg-danger">{{ __('boards.item_not_active') }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    @if ($item->price)
                                        <button type="button" class="btn btn-info">{{ $item->price }} {{ setting('currency') }}</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('boards.empty_items')) }}
    @endif

    {{ $items->links() }}
@stop
