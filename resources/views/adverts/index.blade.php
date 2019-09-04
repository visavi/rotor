@extends('layout')

@section('title')
    {{ __('index.advertising') }}
@stop

@section('header')
    <div class="float-right">
        <a class="btn btn-success" href="/adverts/create">{{ __('adverts.create_advert') }}</a>
    </div><br>

    <h1>{{ __('index.advertising') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ __('main.menu') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.advertising') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($adverts->isNotEmpty())
        @foreach($adverts as $data)
            <div class="b">
                <i class="fa fa-check-circle"></i>
                <b><a href="{{ $data->site }}">{{ $data->name }}</a></b> ({!! $data->user->getProfile() !!})
            </div>

            {{ __('adverts.expires') }}: {{ dateFixed($data->deleted_at) }}<br>
            {{ __('adverts.color') }}: {!! $data->color ? '<span style="color:' . $data->color .'">'. $data->color .'</span>' : '<i class="fas fa-times text-danger"></i>' !!},
            {{ __('adverts.bold') }}: {!! $data->bold ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' !!}<br>
        @endforeach

        {!! pagination($page) !!}

        {{ __('adverts.total_links') }}: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError(__('adverts.empty_links')) !!}
    @endif
@stop
