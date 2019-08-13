@extends('layout')

@section('title')
    {{ trans('boards.my_items') }}
@stop

@section('header')
    <h1>{{ trans('boards.my_items') }} <small>({{ trans('index.boards') }}: {{ $page->total }})</small></h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/boards">{{ trans('index.boards') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('boards.my_items') }}</li>
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
                                    <a href="/items/{{ $item->id }}">{!! $item->getFirstImage() !!}</a>
                                </div>
                                <div class="col-md-7">
                                    <h5><a href="/items/{{ $item->id }}">{{ $item->title }}</a></h5>
                                    <small><i class="fas fa-angle-right"></i> <a href="/boards/{{ $item->category->id }}">{{ $item->category->name }}</a></small>
                                    <div class="message">{!! $item->shortText() !!}</div>
                                    <p>
                                        <i class="fa fa-user-circle"></i> {!! $item->user->getProfile() !!} / {{ dateFixed($item->created_at) }}<br>

                                        @if ($item->expires_at > SITETIME)
                                            <i class="fas fa-clock"></i> {{ trans('boards.expires_in') }} {{ formatTime($item->expires_at - SITETIME) }}
                                        @else
                                            <span class="badge badge-danger">{{ trans('boards.not_active_item') }}</span>
                                        @endif
                                    </p>
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

        {!! pagination($page) !!}

    @else
        {!! showError(trans('boards.empty_items')) !!}
    @endif
@stop
