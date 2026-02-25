@extends('layout')

@section('title', $item->title)

@section('header')
    @if (getUser())
        <div class="float-end">
            @if (getUser('id') === $item->user->id)
                <a class="btn btn-success" href="{{ route('items.edit', ['id' => $item->id]) }}">{{ __('main.change') }}</a>
            @endif

            @if (isAdmin())
                <div class="btn-group">
                    <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-wrench"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('admin.items.edit', ['id' => $item->id]) }}">{{ __('main.edit') }}</a>
                        <form action="{{ route('admin.items.delete', ['id' => $item->id]) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('boards.confirm_delete_item') }}')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-link dropdown-item">{{ __('main.delete') }}</button>
                        </form>
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
            <li class="breadcrumb-item"><a href="{{ route('boards.index') }}">{{ __('index.boards') }}</a></li>

            @foreach ($item->category->getParents() as $parent)
                <li class="breadcrumb-item"><a href="{{ route('boards.index', ['id' => $parent->id]) }}">{{ $parent->name }}</a></li>
            @endforeach

            <li class="breadcrumb-item active">{{ $item->title }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($item->expires_at <= SITETIME)
        <div class="alert alert-warning">{{ __('boards.item_not_active') }}</div>
    @endif

    <div class="row mb-3">
        <div class="col-md-12">
            @if ($item->files->isNotEmpty())
                <div class="row">
                    <div class="col-md-12">
                        <div class="float-end" data-bs-toggle="tooltip" title="{{ __('main.views') }}">
                            <i class="far fa-eye"></i> {{ $item->visits }}
                        </div>

                        @include('app/_image_viewer', ['model' => $item])
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-10">
                    <div class="section-message">
                        {{ bbCode($item->text) }}
                    </div>
                    <div>
                        @if ($item->phone)
                            <div class="d-flex align-items-start mb-3">
                                <a href="tel:{{ $item->phone }}" class="text-decoration-none fs-5"><i class="fa-solid fa-phone fs-5 me-2"></i> {{ $item->phone }}</a>
                            </div>
                        @endif

                        <i class="fa fa-user-circle"></i> {{ $item->user->getProfile() }} / {{ dateFixed($item->updated_at) }}<br>

                        @if ($item->expires_at > SITETIME)
                            <i class="fas fa-clock"></i> {{ __('boards.expires_in') }} {{ formatTime($item->expires_at - SITETIME) }}
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
@stop
