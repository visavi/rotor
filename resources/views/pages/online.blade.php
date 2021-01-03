@extends('layout')

@section('title', __('pages.online'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('pages.online') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3">
        @if ($guests)
            {{ __('pages.total_online') }}: <b>{{ $online->total() }}</b><br>
        @else
            {{ __('pages.authorized') }}: <b>{{ $online->total() }}</b><br>
        @endif
    </div>

    @if ($online->isNotEmpty())
        @foreach ($online as $data)
            <div class="section mb-3 shadow">
                <div class="user-avatar">
                    @if ($data->user_id)
                        {!! $data->user->getAvatar() !!}
                    @else
                        {!! $data->user->getAvatarGuest() !!}
                    @endif
                </div>

                <div class="section-user d-flex align-items-center">
                    <div class="flex-grow-1">
                        @if ($data->user_id)
                            {!! $data->user->getProfile() !!}
                            <small class="section-date text-muted font-italic">{{ dateFixed($data->updated_at, 'H:i:s') }}</small><br>
                            <small class="font-italic">{!! $data->user->getStatus() !!}</small>
                        @else

                            <span class="section-author font-weight-bold" data-login="{{ setting('guestsuser') }}">{{ setting('guestsuser') }}</span>

                            <small class="section-date text-muted font-italic">{{ dateFixed($data->updated_at, 'H:i:s') }}</small>
                        @endif
                    </div>
                </div>

                @if (isAdmin())
                    <div class="section-body border-top">
                        <div class="small text-muted font-italic mt-2">
                            {{ $data->brow }}, {{ $data->ip }}
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    @else
        {!! showError(__('main.empty_users')) !!}
    @endif

    {{ $online->links() }}

    <i class="fa fa-users"></i>
    @if ($guests)
        <a href="/online">{{ __('pages.hide_guests') }}</a><br>
    @else
        <a href="/online/all">{{ __('pages.show_guests') }}</a><br>
    @endif
@stop
