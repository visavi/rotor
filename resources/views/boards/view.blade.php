@extends('layout')

@section('title', $item->title)

@section('header')
    @if (getUser() && getUser('id') === $item->user->id)
        <div class="float-right">
            <a class="btn btn-success" href="/items/edit/{{ $item->id }}">{{ __('main.change') }}</a>
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

    @if (isAdmin())
        <div>
            <a href="/admin/items/edit/{{ $item->id }}">{{ __('main.edit') }}</a> /
            <a href="/admin/items/delete/{{ $item->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('boards.confirm_delete_item') }}')">{{ __('main.delete') }}</a>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">

                    @if ($item->files->isNotEmpty())
                        <div class="row">
                            <div class="col-md-12">
                                @include('app/_carousel', ['model' => $item])
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-10">
                            <div class="section-message">
                                {!! bbCode($item->text) !!}
                            </div>
                            <p>
                                @if ($item->phone)
                                    <span class="badge badge-pill badge-primary">{{ __('boards.phone') }}: {{ $item->phone }}</span><br>
                                @endif

                                <i class="fa fa-user-circle"></i> {!! $item->user->getProfile() !!} / {{ dateFixed($item->updated_at) }}<br>

                                @if ($item->expires_at > SITETIME)
                                    <i class="fas fa-clock"></i> {{ __('boards.expires_in') }} {{ formatTime($item->expires_at - SITETIME) }}
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
@stop
