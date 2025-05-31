@extends('layout')

@section('title', __('boards.my_items'))

@section('header')
    <div class="float-end">
        @if (isAdmin() || (getUser() && setting('board_create')))
            <a class="btn btn-success" href="/items/create">{{ __('main.add') }}</a>
        @endif
    </div>

    <h1>{{ __('boards.my_items') }}</h1>
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
    <div class="mb-3">
        @if ($type === 'active')
            <a class="btn btn-primary btn-sm" href="{{ route('boards.active', ['type' => 'active', 'sort' => $sort]) }}">{{ __('boards.active') }} <span class="badge bg-adaptive">{{ $items->total() }}</span></a>
            <a class="btn btn-light btn-sm" href="{{ route('boards.active', ['type' => 'archive', 'sort' => $sort]) }}">{{ __('boards.archive') }} <span class="badge bg-adaptive">{{ $otherCount }}</span></a>
        @else
            <a class="btn btn-light btn-sm" href="{{ route('boards.active', ['type' => 'active', 'sort' => $sort]) }}">{{ __('boards.active') }} <span class="badge bg-adaptive">{{ $otherCount }}</span></a>
            <a class="btn btn-primary btn-sm" href="{{ route('boards.active', ['type' => 'archive', 'sort' => $sort]) }}">{{ __('boards.archive') }} <span class="badge bg-adaptive">{{ $items->total() }}</span></a>
        @endif
    </div>

    {{ __('main.sort') }}:
    <?php $active = ($sort === 'date') ? 'success' : 'adaptive'; ?>
    <a href="{{ route('boards.active', ['type' => $type, 'sort' => 'date']) }}" class="badge bg-{{ $active }}">{{ __('main.date') }}</a>

    <?php $active = ($sort === 'price') ? 'success' : 'adaptive'; ?>
    <a href="{{ route('boards.active', ['type' => $type, 'sort' => 'price']) }}" class="badge bg-{{ $active }}">{{ __('main.cost') }}</a>
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
                                    <small><i class="fas fa-angle-right"></i> <a href="/boards/{{ $item->category->id }}">{{ $item->category->name }}</a></small>
                                    <div class="section-message">
                                        {{ $item->shortText() }}
                                    </div>
                                    <div>
                                        <i class="fa fa-user-circle"></i> {{ $item->user->getProfile() }}
                                        <small class="section-date text-muted fst-italic">{{ dateFixed($item->created_at) }}</small>
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
