@extends('layout')

@section('title')
    Мои объявления
@stop

@section('header')
    <h1>Мои объявления <small>(Объявлений: {{ $page->total }})</small></h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/boards">Объявления</a></li>
            <li class="breadcrumb-item active">Мои объявления</li>
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
                                    <div class="message">{!! $item->cutText() !!}</div>
                                    <p>
                                        <i class="fa fa-user-circle"></i> {!! $item->user->getProfile() !!} / {{ dateFixed($item->created_at) }}<br>

                                        @if ($item->expires_at > SITETIME)
                                            <i class="fas fa-clock"></i> Истекает через {{ formatTime($item->expires_at - SITETIME) }}
                                        @else
                                            <span class="badge badge-danger">Объявление не активно</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="col-md-2">
                                    @if ($item->price)
                                        <button type="button" class="btn btn-info">{{ $item->price }} ₽</button>
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
        {!! showError('Объявлений еще нет!') !!}
    @endif
@stop
