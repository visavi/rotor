@extends('layout')

@section('title', __('index.invitations'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/menu">{{ __('main.menu') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.invitations') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3">
        @if ($used)
            <a class="btn btn-light btn-sm" href="/invitations">{{ __('invitations.unused') }}</a>
            <a class="btn btn-primary btn-sm" href="/invitations?used=1">{{ __('invitations.used') }}</a>
        @else
            <a class="btn btn-primary btn-sm" href="/invitations">{{ __('invitations.unused') }}</a>
            <a class="btn btn-light btn-sm" href="/invitations?used=1">{{ __('invitations.used') }}</a>
        @endif
    </div>

    @if ($invites->isNotEmpty())
        @foreach ($invites as $invite)
            <div class="section mb-3 shadow">
                <div class="section-title">
                    <i class="fa-solid fa-key"></i> {{ $invite->hash }}
                </div>

                <div class="section-content">
                    @if ($invite->invite_user_id)
                        {{ __('invitations.invited') }}: {{ $invite->inviteUser->getProfile() }}<br>
                    @endif

                    @if ($invite->used_at)
                        {{ __('main.used') }}: {{ dateFixed($invite->used_at) }}<br>
                    @endif

                    {{ __('main.created') }}: {{ dateFixed($invite->created_at) }}<br>
                </div>
            </div>
        @endforeach
    @else
        {{ showError(__('invitations.empty_invitations')) }}
    @endif

    {{ $invites->links() }}

    @if (! $lastInvite)
        <form action="/invitations/create" method="post">
            @csrf
            <button class="btn btn-success">{{ __('invitations.create_keys') }}</button>
        </form>
    @else
        <div class="alert alert-info">{{ __('invitations.limit_reached') }}</div>
    @endif
@stop
