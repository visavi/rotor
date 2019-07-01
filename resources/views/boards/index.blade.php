@extends('layout')

@section('title')
    {{ trans('boards.title') }}
@stop

@section('header')
    @if (getUser())
        <div class="float-right">
            <a class="btn btn-success" href="/items/create?bid={{ $board->id ?? 0 }}">{{ trans('main.add') }}</a><br>
        </div><br>
    @endif

    @if ($board)
        <h1>{{ $board->name }} <small>({{ trans('boards.title') }}: {{ $board->count_items }})</small></h1>
    @else
        <h1>{{ trans('boards.title') }}</h1>
    @endif
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>

            @if ($board)
                <li class="breadcrumb-item"><a href="/boards">{{ trans('boards.title') }}</a></li>

                @if ($board->parent->id)
                    <li class="breadcrumb-item"><a href="/boards/{{ $board->parent->id }}">{{ $board->parent->name }}</a></li>
                @endif
                <li class="breadcrumb-item active">{{ $board->name }}</li>

                @if (isAdmin())
                    <li class="breadcrumb-item"><a href="/admin/boards/{{ $board->id  }}?page={{ $page->current }}">{{ trans('main.management') }}</a></li>
                @endif
            @else
                <li class="breadcrumb-item active">{{ trans('boards.title') }}</li>

                @if (isAdmin())
                    <li class="breadcrumb-item"><a href="/admin/boards?page={{ $page->current }}">{{ trans('main.management') }}</a></li>
                @endif
            @endif
        </ol>
    </nav>
@stop

@section('content')
    @if (getUser())
        <div class="mb-3">
            <i class="far fa-list-alt"></i> <a href="/boards/active">{{ trans('boards.my_items') }}</a>
        </div>
    @endif

    @if ($boards->isNotEmpty())
        <div class="row mb-3">
            @foreach ($boards->chunk(3) as $chunk)
                @foreach ($chunk as $board)
                    <div class="col-md-3 col-6">
                        <a href="/boards/{{ $board->id }}">{{ $board->name }}</a> {{ $board->count_items }}
                    </div>
                @endforeach
            @endforeach
        </div>
    @endif

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
                                    <p><i class="fa fa-user-circle"></i> {!! $item->user->getProfile() !!} / {{ dateFixed($item->created_at) }}</p>
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
