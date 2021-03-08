@extends('layout')

@section('title', __('index.boards'))

@section('header')
    <div class="float-right">
        <a class="btn btn-light" href="/boards?page={{ $items->currentPage() }}"><i class="fas fa-wrench"></i></a>
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
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>

            @if ($board)
                <li class="breadcrumb-item"><a href="/admin/boards">{{ __('index.boards') }}</a></li>

                @if ($board->parent->id)
                    <li class="breadcrumb-item"><a href="/admin/boards/{{ $board->parent->id }}">{{ $board->parent->name }}</a></li>
                @endif
                <li class="breadcrumb-item active">{{ $board->name }}</li>

                @if (isAdmin())
                    <li class="breadcrumb-item"><a href="/boards/{{ $board->id  }}?page={{ $items->currentPage() }}">{{ __('main.review') }}</a></li>
                @endif
            @else
                <li class="breadcrumb-item active">{{ __('index.boards') }}</li>
            @endif
        </ol>
    </nav>
@stop

@section('content')
    @if ($boards->isNotEmpty())
        <div class="row mb-3">
            @foreach ($boards->chunk(3) as $chunk)
                @foreach ($chunk as $board)
                    <div class="col-md-3 col-6">
                        <a href="/admin/boards/{{ $board->id }}">{{ $board->name }}</a> {{ $board->count_items }}
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

                                    <div class="float-right">
                                        <a href="/admin/items/edit/{{ $item->id }}" data-toggle="tooltip" title="{{ __('main.edit') }}"><i class="fa fa-pencil-alt"></i></a>
                                        <a href="/admin/items/delete/{{ $item->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('boards.confirm_delete') }}')" data-toggle="tooltip" title="{{ __('main.delete') }}"><i class="fa fa-times"></i></a>
                                    </div>

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

    @if (isAdmin('boss'))
        <i class="far fa-list-alt"></i> <a href="/admin/boards/categories">{{ __('boards.categories') }}</a><br>
        <i class="fa fa-sync"></i> <a href="/admin/boards/restatement?token={{ $_SESSION['token'] }}">{{ __('main.recount') }}</a><br>
    @endif
@stop
