@extends('layout')

@section('title', __('index.invitations') )

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ __('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ __('index.invitations') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (! setting('invite'))
        <div class="mb-3 font-weight-bold">
            <i class="fa fa-exclamation-circle"></i>
            <span class="text-danger font-weight-bold">{{ __('admin.invitations.hint') }}</span>
        </div>
    @endif

    @if ($used)
        <a href="/admin/invitations">{{ __('admin.invitations.unused') }}</a> / <b>{{ __('admin.invitations.used') }}</b><hr>
    @else
        <b>{{ __('admin.invitations.unused') }}</b> / <a href="/admin/invitations?used=1">{{ __('admin.invitations.used') }}</a><hr>
    @endif

    @if ($invites->isNotEmpty())
        <form action="/admin/invitations/delete?used={{ $used }}&amp;page={{ $invites->currentPage() }}" method="post">
            @csrf
            @foreach ($invites as $invite)
                <div class="b">
                    <input type="checkbox" name="del[]" value="{{ $invite->id }}">
                    <b>{{ $invite->hash }}</b>
                </div>

                <div>
                    {{ __('admin.invitations.owner') }}: {!! $invite->user->getProfile() !!}<br>

                    @if ($invite->invite_user_id)
                        {{ __('admin.invitations.invited') }}: {!! $invite->inviteUser->getProfile() !!}<br>
                    @endif

                    {{ __('main.created') }}: {{ dateFixed($invite->created_at) }}<br>
                </div>
            @endforeach

            <button class="btn btn-sm btn-danger">{{ __('main.delete_selected') }}</button>
        </form>
        <br>{{ __('main.total') }}: <b>{{ $invites->total() }}</b><br>
    @else
        {!! showError(__('admin.invitations.empty_invitations')) !!}
    @endif

    {{ $invites->links() }}

    <i class="fa fa-check"></i> <a href="/admin/invitations/create">{{ __('admin.invitations.create_keys') }}</a><br>
    <i class="fa fa-key"></i> <a href="/admin/invitations/keys">{{ __('admin.invitations.list_keys') }}</a><br>
@stop
