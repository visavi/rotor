@extends('layout')

@section('title')
    {{ trans('index.invitations') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('index.panel') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('index.invitations') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if (! setting('invite'))
        <i class="fa fa-exclamation-circle"></i> <span class="text-danger">{{ trans('admin.invitations.hint') }}</span><br><br>
    @endif

    @if ($used)
        <a href="/admin/invitations">{{ trans('admin.invitations.unused') }}</a> / <b>{{ trans('admin.invitations.used') }}</b><hr>
    @else
        <b>{{ trans('admin.invitations.unused') }}</b> / <a href="/admin/invitations?used=1">{{ trans('admin.invitations.used') }}</a><hr>
    @endif

    @if ($invites->isNotEmpty())

        <form action="/admin/invitations/delete?used={{ $used }}&amp;page={{ $page->current }}" method="post">
            @csrf
            @foreach ($invites as $invite)
                <div class="b">
                    <input type="checkbox" name="del[]" value="{{ $invite->id }}">
                    <b>{{ $invite->hash }}</b>
                </div>

                <div>
                    {{ trans('admin.invitations.owner') }}: {!! $invite->user->getProfile() !!}<br>

                    @if ($invite->invite_user_id)
                        {{ trans('admin.invitations.invited') }}: {!! $invite->inviteUser->getProfile() !!}<br>
                    @endif

                    {{ trans('main.created') }}: {{ dateFixed($invite->created_at) }}<br>
                </div>
            @endforeach

            <button class="btn btn-sm btn-danger">{{ trans('main.delete_selected') }}</button>
        </form>

    {!! pagination($page) !!}

        {{ trans('main.total') }}: <b>{{ $page->total }}</b><br><br>

    @else
        {!! showError(trans('admin.invitations.empty_invitations')) !!}
    @endif

    <i class="fa fa-check"></i> <a href="/admin/invitations/create">{{ trans('admin.invitations.create_keys') }}</a><br>
    <i class="fa fa-key"></i> <a href="/admin/invitations/keys">{{ trans('admin.invitations.list_keys') }}</a><br>
@stop
