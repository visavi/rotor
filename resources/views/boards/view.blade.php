@extends('layout')

@section('title', $item->title)

@section('header')
    @if (getUser())
        <div class="float-end">
            @if (getUser('id') === $item->user->id)
                <a class="btn btn-success" href="/items/edit/{{ $item->id }}">{{ __('main.change') }}</a>
            @endif

            @if (isAdmin())
                <div class="btn-group">
                    <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-wrench"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="/admin/items/edit/{{ $item->id }}">{{ __('main.edit') }}</a>
                        <a class="dropdown-item" href="/admin/items/delete/{{ $item->id }}?_token={{ csrf_token() }}" onclick="return confirm('{{ __('boards.confirm_delete_item') }}')">{{ __('main.delete') }}</a>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <h1>{{ $item->title }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/boards">{{ __('index.boards') }}</a></li>

            @if ($item->category->parent->id)
                <li class="breadcrumb-item"><a href="/boards/{{ $item->category->parent->id }}">{{ $item->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/boards/{{ $item->category->id }}">{{ $item->category->name }}</a></li>
            <li class="breadcrumb-item active">{{ $item->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($item->expires_at <= SITETIME)
        <div class="alert alert-danger">{{ __('boards.item_not_active') }}</div>
    @endif

    <div class="row mb-3">
        <div class="col-md-12">
            @if ($item->files->isNotEmpty())
                <div class="row">
                    <div class="col-md-12">
                        @include('app/_carousel', ['model' => $item])
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-10">
                    <div class="section-message mb-3">
                        {{ bbCode($item->text) }}
                    </div>
                    <div>
                        @if ($item->phone)
                            <span class="badge rounded-pill bg-primary mb-3">{{ __('boards.phone') }}: {{ $item->phone }}</span><br>
                        @endif

                        <i class="fa fa-user-circle"></i> {{ $item->user->getProfile() }} / {{ dateFixed($item->updated_at) }}<br>

                        @if ($item->expires_at > SITETIME)
                            <i class="fas fa-clock"></i> {{ __('boards.expires_in') }} {{ formatTime($item->expires_at - SITETIME) }}
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
@stop
