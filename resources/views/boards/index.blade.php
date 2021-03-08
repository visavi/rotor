@extends('layout')

@section('title', __('index.boards'))

@section('header')
    <div class="float-right">
        @if (getUser())
            <a class="btn btn-success" href="/items/create?bid={{ $board->id ?? 0 }}">{{ __('main.add') }}</a>
        @endif

        @if (isAdmin())
            <a class="btn btn-light" href="/admin/boards?page={{ $items->currentPage() }}"><i class="fas fa-wrench"></i></a>
        @endif
    </div>

    @if ($board)
        <h1>{{ $board->name }} <small>({{ __('index.boards') }}: {{ $board->count_items }})</small></h1>
    @else
        <h1>{{ __('index.boards') }}</h1>
    @endif
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>

            @if ($board)
                <li class="breadcrumb-item"><a href="/boards">{{ __('index.boards') }}</a></li>

                @if ($board->parent->id)
                    <li class="breadcrumb-item"><a href="/boards/{{ $board->parent->id }}">{{ $board->parent->name }}</a></li>
                @endif
                <li class="breadcrumb-item active">{{ $board->name }}</li>

                @if (isAdmin())
                    <li class="breadcrumb-item"><a href="/admin/boards/{{ $board->id  }}?page={{ $items->currentPage() }}">{{ __('main.management') }}</a></li>
                @endif
            @else
                <li class="breadcrumb-item active">{{ __('index.boards') }}</li>
            @endif
        </ol>
    </nav>
@stop

@section('content')
    @if (getUser())
        <div class="mb-3">
            <i class="far fa-list-alt"></i> <a href="/boards/active">{{ __('boards.my_items') }}</a>
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
                                        <small class="section-date text-muted font-italic">
                                            {{ dateFixed($item->created_at) }}
                                        </small>
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
