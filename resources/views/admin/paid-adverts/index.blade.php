@extends('layout')

@section('title', __('index.advertising'))

@section('header')
    <div class="float-right">
        <a class="btn btn-success" href="/admin/paid-adverts/create">{{ __('adverts.create_advert') }}</a>
    </div>

    <h1>{{ __('index.advertising') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.advertising') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3">
        @foreach ($places as $placeName)
            <?php $active = ($place === $placeName) ? 'primary' : 'light'; ?>
            <a class="btn btn-{{ $active }} btn-sm" href="/admin/paid-adverts?place={{ $placeName }}">{{ __('admin.paid_adverts.' . $placeName) }} <span class="badge badge-light">{{ $totals[$placeName] }}</span></a>
        @endforeach
    </div>

    @if ($adverts->isNotEmpty())
        @foreach ($adverts as $data)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fas fa-globe-americas"></i>
                    <a href="{{ $data->site }}">{{ $data->names[0] }}</a>
                    @if (count($data->names) > 1)
                        <span class="badge badge-info">{{ count($data->names) }}</span>
                    @endif

                    <div class="float-right">
                        <a href="/admin/paid-adverts/edit/{{ $data->id }}"><i class="fas fa-pencil-alt text-muted"></i></a>
                        <a href="/admin/paid-adverts/delete/{{ $data->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('{{ __('photos.confirm_delete_photo') }}')"><i class="fa fa-times text-muted"></i></a>
                    </div>
                </div>

                <div class="section-body border-top">
                    <div class="section-message">
                        {!! bbCode($data->comment ?? 'Нет комментария') !!}
                    </div>

                    <i class="far fa-user"></i> {!! $data->user->getProfile() !!}
                    <small class="section-date text-muted font-italic">{{ __('adverts.expires') }}: {{ dateFixed($data->deleted_at) }}</small>

                    <div class="small text-muted font-italic mt-2">
                        {{ __('adverts.color') }}: {!! $data->color ? '<span style="color:' . $data->color .'">'. $data->color .'</span>' : '<i class="fas fa-times text-danger"></i>' !!},
                        {{ __('adverts.bold') }}: {!! $data->bold ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' !!}
                    </div>
                </div>
            </div>
        @endforeach
    @else
        {!! showError(__('adverts.empty_links')) !!}
    @endif
@stop
