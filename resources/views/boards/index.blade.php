@extends('layout')

@section('title', __('index.boards'))

@section('header')
    <div class="float-end">
        @if (isAdmin() || (getUser() && setting('board_create')))
            <a class="btn btn-success" href="{{ route('items.create', ['id' => $board ?? null]) }}">{{ __('main.add') }}</a>

            @if (isAdmin())
                <a class="btn btn-light" href="{{ route('admin.boards.index', ['id' => $board ?? null, 'page' => $items->currentPage()]) }}"><i class="fas fa-wrench"></i></a>
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
                <li class="breadcrumb-item"><a href="{{ route('boards.index') }}">{{ __('index.boards') }}</a></li>

                @foreach ($board->getParents() as $parent)
                    @if ($loop->last)
                        <li class="breadcrumb-item active">{{ $parent->name }}</li>
                    @else
                        <li class="breadcrumb-item"><a href="{{ route('boards.index', ['id' => $parent->id]) }}">{{ $parent->name }}</a></li>
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
            <i class="far fa-list-alt"></i> <a href="{{ route('boards.active') }}">{{ __('boards.my_items') }}</a>
        </div>
    @endif

    @if ($boards->isNotEmpty())
        <div class="row mb-3">
            @foreach ($boards->chunk(3) as $chunk)
                @foreach ($chunk as $child)
                    <div class="col-md-3 col-6">
                        <a href="{{ route('boards.index', ['id' => $child->id]) }}">{{ $child->name }}</a> {{ $child->count_items + $child->children->sum('count_items') }}
                    </div>
                @endforeach
            @endforeach
        </div>
    @endif

    @if ($items->isNotEmpty())
        <div class="sort-links border-bottom pb-3 mb-3">
            {{ __('main.sort') }}:
            @foreach ($sorting as $key => $option)
                <a href="{{ route('boards.index', ['id' => $board?->id, 'sort' => $key, 'order' => $option['inverse'] ?? 'desc']) }}" class="badge bg-{{ $option['badge'] ?? 'adaptive' }}">
                    {{ $option['label'] }}{{ $option['icon'] ?? '' }}
                </a>
            @endforeach
        </div>

        @foreach ($items as $item)
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="{{ route('items.view', ['id' => $item->id]) }}">{{ $item->getFirstImage() }}</a>
                                </div>
                                <div class="col-md-7">
                                    <h5><a href="{{ route('items.view', ['id' => $item->id]) }}">{{ $item->title }}</a></h5>

                                    <div class="small my-2">
                                        <i class="fas fa-angle-right"></i>
                                        <a href="{{ route('boards.index', ['id' => $item->category->id]) }}">{{ $item->category->name }}</a>
                                    </div>

                                    <div class="section-message">
                                        {{ $item->shortText() }}
                                    </div>

                                    <p class="card-text">
                                        <a href="tel:{{ $item->phone }}" class="text-decoration-none">
                                            <i class="fa-solid fa-phone fs-5 me-2"></i> {{ $item->phone }}
                                        </a>
                                    </p>

                                    <div>
                                        <i class="fa fa-user-circle"></i> {{ $item->user->getProfile() }}
                                        <small class="section-date text-muted fst-italic">
                                            {{ dateFixed($item->created_at) }}
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    @if ($item->price)
                                        <div class="float-end">
                                            <button type="button" class="btn btn-outline-info">{{ $item->price }} {{ setting('currency') }}</button>
                                        </div>
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
