@extends('layout')

@section('title', __('index.invitations'))

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.invitations') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (! setting('invite'))
        <div class="mb-3 fw-bold">
            <i class="fa fa-exclamation-circle"></i>
            <span class="text-danger fw-bold">{{ __('invitations.hint') }}</span>
        </div>
    @endif

    <div class="mb-3">
        @if ($used)
            <a class="btn btn-light btn-sm" href="/admin/invitations">{{ __('invitations.unused') }}</a>
            <a class="btn btn-primary btn-sm" href="/admin/invitations?used=1">{{ __('invitations.used') }}</a>
        @else
            <a class="btn btn-primary btn-sm" href="/admin/invitations">{{ __('invitations.unused') }}</a>
            <a class="btn btn-light btn-sm" href="/admin/invitations?used=1">{{ __('invitations.used') }}</a>
        @endif
    </div>

    @if ($invites->isNotEmpty())
        <form action="/admin/invitations/delete?used={{ $used }}&amp;page={{ $invites->currentPage() }}" method="post">
            @csrf
            @foreach ($invites as $invite)
                <div class="section mb-3 shadow">
                    <div class="section-title">
                        <i class="fa-solid fa-key"></i> {{ $invite->hash }}

                        <div class="float-end">
                            <input type="checkbox" class="form-check-input" name="del[]" value="{{ $invite->id }}">
                        </div>
                    </div>

                    <div class="section-content">
                        {{ __('invitations.owner') }}: {{ $invite->user->getProfile() }}<br>

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

            <div class="clearfix mb-3">
                <button class="btn btn-sm btn-danger float-end">{{ __('main.delete_selected') }}</button>
            </div>
        </form>

        {{ $invites->links() }}

        <div class="mb-3">
            {{ __('main.total') }}: <b>{{ $invites->total() }}</b>
        </div>
    @else
        {{ showError(__('invitations.empty_invitations')) }}
    @endif

    <i class="fa fa-check"></i> <a href="/admin/invitations/create">{{ __('invitations.create_keys') }}</a><br>
@stop
