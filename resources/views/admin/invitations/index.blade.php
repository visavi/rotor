@extends('layout')

@section('title')
    Приглашения
@stop

@section('content')

    <h1>Приглашения</h1>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">Панель</a></li>
            <li class="breadcrumb-item active">Приглашения</li>
        </ol>
    </nav>

    @if (! setting('invite'))
        <i class="fa fa-exclamation-circle"></i> <span style="color:#ff0000"><b>Регистрация по приглашения выключена!</b></span><br><br>
    @endif

    @if ($used)
        <a href="/admin/invitations">Неиспользованные</a> / <b>Использованные</b><hr>
    @else
        <b>Неиспользованные</b> / <a href="/admin/invitations?used=1">Использованные</a><hr>
    @endif

    @if ($invites->isNotEmpty())

        <form action="/admin/invitations/delete?used={{ $used }}&amp;page={{ $page->current }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
            @foreach ($invites as $invite)
                <div class="b">
                    <input type="checkbox" name="del[]" value="{{ $invite->id }}">
                    <b>{{ $invite->hash }}</b>
                </div>

                <div>
                    Владелец: {!! $invite->user->getProfile() !!}<br>

                    @if ($invite->invite_user_id)
                        Приглашенный: {!! $invite->inviteUser->getProfile() !!}<br>
                    @endif

                    Создан: {{ dateFixed($invite->created_at) }}<br>
                </div>
            @endforeach

            <button class="btn btn-sm btn-danger">Удалить выбранное</button>
        </form>

    {!! pagination($page) !!}

    Всего ключей: <b>{{ $page->total }}</b><br><br>

    @else
        {!! showError('Приглашений еще нет!') !!}
    @endif

    <i class="fa fa-check"></i> <a href="/admin/invitations/create">Создать ключи</a><br>
    <i class="fa fa-key"></i> <a href="/admin/invitations/keys">Список ключей</a><br>
@stop
