@extends('layout')

@section('title', __('index.boards'))

@section('header')
    <div class="float-end">
        @if (isAdmin() || (getUser() && setting('board_create')))
            <a class="btn btn-success" href="/items/create?bid={{ $board->id ?? 0 }}">{{ __('main.add') }}</a>

            @if (isAdmin())
                <a class="btn btn-light" href="/admin/boards?page={{ $items->currentPage() }}"><i class="fas fa-wrench"></i></a>
            @endif
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

                @foreach ($board->getParents() as $parent)
                    @if ($loop->last)
                        <li class="breadcrumb-item active">{{ $parent->name }}</li>
                    @else
                        <li class="breadcrumb-item"><a href="/boards/{{ $parent->id }}">{{ $parent->name }}</a></li>
                    @endif
                @endforeach
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
                @foreach ($chunk as $child)
                    <div class="col-md-3 col-6">
                        <a href="/boards/{{ $child->id }}">{{ $child->name }}</a> {{ $child->count_items + $child->children->sum('count_items') }}
                    </div>
                @endforeach
            @endforeach
        </div>
    @endif

    {{ __('main.sort') }}:
    <?php $active = ($sort === 'date') ? 'success' : 'light text-dark'; ?>
    <a href="{{ route('boards.index', ['id' => $board?->id]) }}?sort=date" class="badge bg-{{ $active }}">{{ __('main.date') }}</a>

    <?php $active = ($sort === 'price') ? 'success' : 'light text-dark'; ?>
    <a href="{{ route('boards.index', ['id' => $board?->id]) }}?sort=price" class="badge bg-{{ $active }}">{{ __('main.cost') }}</a>
    <hr>

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
                                    <small><i class="fas fa-angle-right"></i> <a
                                            href="/boards/{{ $item->category->id }}">{{ $item->category->name }}</a></small>
                                    <div class="section-message">
                                        {{ $item->shortText() }}
                                    </div>
                                    <div>
                                        <i class="fa fa-user-circle"></i> {{ $item->user->getProfile() }}
                                        <small class="section-date text-muted fst-italic">
                                            {{ dateFixed($item->created_at) }}
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    @if ($item->price)
                                        <button type="button"
                                                class="btn btn-info">{{ $item->price }} {{ setting('currency') }}</button>
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
