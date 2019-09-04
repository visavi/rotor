@extends('layout')

@section('title')
    {{ __('pages.online') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('pages.online') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    {{ __('pages.total_online') }}: <b>{{ $all }}</b><br>
    {{ __('pages.authorized') }}:  <b>{{ $total }}</b><br><br>


    @if ($online->isNotEmpty())

        @foreach ($online as $data)
            <div class="b">
                <div class="img">
                    {!! $data->user->getAvatar() !!}
                </div>

                <b>{!! $data->user->getProfile() !!}</b> ({{ __('pages.time_on_site') }}: {{ dateFixed($data->updated_at, 'H:i:s') }})
            </div>

            @if (isAdmin())
                <div>
                    <span class="data">({{ $data->brow }}, {{ $data->ip }})</span>
                </div>
            @endif
        @endforeach
        {!! pagination($page) !!}
    @else
        {!! showError(__('main.empty_users')) !!}
    @endif

    <i class="fa fa-users"></i>

    @if ($guests)
        <a href="/online">{{ __('pages.hide_guests') }}</a><br>
    @else
        <a href="/online/all">{{ __('pages.show_guests') }}</a><br>
    @endif
@stop
