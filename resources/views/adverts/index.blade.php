@extends('layout')

@section('title', __('index.advertising'))

@section('header')
    <div class="float-right">
        @if (getUser())
            <a class="btn btn-success" href="/adverts/create">{{ __('adverts.create_advert') }}</a>
        @endif

        @if (isAdmin())
            <a class="btn btn-light" href="/admin/adverts"><i class="fas fa-wrench"></i></a>
        @endif
    </div>

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
        @foreach ($adverts as $data)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fas fa-globe-americas"></i>
                    <a href="{{ $data->site }}">{{ $data->name }}</a>
                </div>

                <div class="section-body border-top">
                    <i class="far fa-user"></i> {!! $data->user->getProfile() !!}
                    <small class="section-date text-muted font-italic">{{ __('adverts.expires') }}: {{ dateFixed($data->deleted_at) }}</small>

                    <div class="small text-muted font-italic mt-2">
                        {{ __('adverts.color') }}: {!! $data->color ? '<span style="color:' . $data->color .'">'. $data->color .'</span>' : '<i class="fas fa-times text-danger"></i>' !!},
                        {{ __('adverts.bold') }}: {!! $data->bold ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' !!}
                    </div>
                </div>
            </div>
        @endforeach

        {{ __('adverts.total_links') }}: <b>{{ $adverts->total() }}</b><br>
    @else
        {!! showError(__('adverts.empty_links')) !!}
    @endif

    {{ $adverts->links() }}
@stop
