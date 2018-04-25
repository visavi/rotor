@extends('layout')

@section('title')
    Анкета пользователя {{ $user->login }}
@stop

@section('content')

    <div class="avatar-box">
        <div class="avatar-box_image">{!! userAvatar($user) !!}</div>
        <h1 class="avatar-box_login">
            {{ $user->login }} <small>#{{ $user->id }}</small>
        </h1>
    </div>

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Анкета {{ getUser('login') }}</li>
        </ol>
    </nav>

    @if ($user->level === 'pended')
        <b><span style="color:#ff0000">Внимание, аккаунт требует подтверждение регистрации!</span></b><br>
    @endif

    @if ($user->level === 'banned' && $user->timeban > SITETIME)
        <div class="form">
            <b><span style="color:#ff0000">Внимание, данный пользователь заблокирован!</span></b><br>
            До окончания бана: {{ formatTime($user->timeban - SITETIME) }}<br>

            @if ($user->lastBan->id)
                Причина: {!! bbCode($user->lastBan->reason) !!}<br>
            @endif
        </div>
    @endif

    @if (in_array($user->level, $adminGroups))
        <div class="alert alert-info">Должность: <b>{{ userLevel($user->level) }}</b></div>
    @endif

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                Cтатус: <b><a href="/statusfaq">{!! userStatus($user) !!}</a></b><br>

                {!! $user->getGender() !!}
                Пол:
                {{  ($user->gender === 'male') ? 'Мужской' : 'Женский' }}<br>

                Логин: <b>{{ $user->login }}</b><br>

                @if (!empty($user->name))
                    Имя: <b>{{ $user->name }}<br></b>
                @endif

                @if (!empty($user->country))
                    Страна: <b>{{ $user->country }}<br></b>
                @endif

                @if (!empty($user->city))
                    Откуда: {{ $user->city }}<br>
                @endif

                @if (!empty($user->birthday))
                    Дата рождения: {{ $user->birthday }}<br>
                @endif

                @if (!empty($user->icq))
                    ICQ: {{ $user->icq }}<br>
                @endif

                @if (!empty($user->skype))
                    Skype: {{ $user->skype }}<br>
                @endif

                Всего посeщений: {{ $user->visits }}<br>
                Сообщений на форуме: {{ $user->allforum }}<br>
                Сообщений в гостевой: {{ $user->allguest }}<br>
                Комментариев: {{ $user->allcomments }}<br>
                Актив: {{ plural($user->point, setting('scorename')) }}<br>
                Денег: {{ plural($user->money, setting('moneyname')) }}<br>

                @if ($user->themes)
                    Используемый скин: {{ $user->themes }}<br>
                @endif
                Дата регистрации: {{ dateFixed($user->created_at, 'd.m.Y') }}<br>

                @if ($invite)
                    Зарегистрирован по приглашению: {!! profile($invite->user) !!}<br>
                @endif

                Последняя визит: {{ dateFixed($user->updated_at) }}<br>

                <a href="/rating/{{ $user->login }}">Репутация: <b>{!! formatNum($user->rating) !!}</b> (+{{  $user->posrating }}/-{{  $user->negrating }})</a><br>

                @if (getUser() && getUser('login') != $user->login)
                    <a href="/users/{{ $user->login }}/rating?vote=plus"><i class="fa fa-thumbs-up"></i><span style="color:#0099cc"> Плюс</span></a> /
                    <a href="/users/{{ $user->login }}/rating?vote=minus"><span style="color:#ff0000">Минус</span> <i class="fa fa-thumbs-down"></i></a><br>
                @endif

            </div>

            <div class="col-md-6">
                @if (!empty($user->picture) && file_exists(UPLOADS.'/photos/'.$user->picture))
                    <a class="gallery" href="/uploads/photos/{{ $user->picture }}">
                        {!! resizeImage('/uploads/photos/' . $user->picture, ['alt' => $user->login, 'class' => 'float-right img-fluid rounded']) !!}</a>
                @else
                    <img src="/assets/img/images/photo.jpg" alt="Фото" class="float-right img-fluid rounded">
                @endif
            </div>
            <div class="col-md-12">
                @if (!empty($user->info))
                    <div class="alert alert-warning"><b>О себе</b>:<br>{!! bbCode($user->info) !!}</div>
                @endif

                <b><a href="/forums/active/themes?user={{ $user->login }}">Форум</a></b> (<a href="/forums/active/posts?user={{ $user->login }}">Сообщ.</a>) /
                <b><a href="/loads/active?act=files&amp;user={{ $user->login }}">Загрузки</a></b> (<a href="/loads/active?act=comments&amp;uz={{ $user->login }}">комм.</a>) /
                <b><a href="/blogs/active/articles?user={{ $user->login }}">Блоги</a></b> (<a href="/blogs/active/comments?user={{ $user->login }}">комм.</a>) /
                <b><a href="/photos/album/{{ $user->login }}">Галерея</a></b> (<a href="/photos/comments/{{ $user->login }}">комм.</a>)<br>
            </div>
        </div>
    </div>

    @if (isAdmin())
    <div class="alert alert-success">
        <i class="fa fa-thumbtack"></i> <b>Заметка:</b> (<a href="/users/{{ $user->login }}/note">Изменить</a>)<br>

        @if (! empty($user->note->text))
            {!! bbCode($user->note->text) !!}<br>
            Изменено: {!! profile($user->note->editUser) !!} ({{ dateFixed($user->note->updated_at) }})<br>
        @else
            Записей еще нет!<br>
        @endif

        </div>
    @endif

    <div class="alert alert-info">
        <i class="fa fa-sticky-note"></i> <a href="/wall/{{ $user->login }}">Стена сообщений</a> ({{ userWall($user) }})<br>

        @if ($user->login != getUser('login'))
            <i class="fa fa-address-book"></i> Добавить в
            <a href="/contacts?act=add&amp;user={{ $user->login }}&amp;token={{ $_SESSION['token'] }}">контакт</a> /
            <a href="/ignores?act=add&amp;user={{ $user->login }}&amp;token={{ $_SESSION['token'] }}">игнор</a><br>
            <i class="fa fa-envelope"></i> <a href="/messages/send?user={{ $user->login }}">Отправить сообщение</a><br>

            <i class="fa fa-money-bill-alt"></i> <a href="/transfers?user={{ $user->login }}">Перечислить денег</a><br>

            @if (!empty($user->site))
                <i class="fa fa-home"></i> <a href="{{ $user->site }}">Перейти на сайт {{ $user->login }}</a><br>
            @endif

            @if (isAdmin('moder'))
                @if (!empty(setting('invite')))
                    <i class="fa fa-ban"></i> <a href="/admin/invitations/send?user={{ $user->login }}&amp;token={{ $_SESSION['token'] }}">Отправить инвайт</a><br>
                @endif
            <i class="fa fa-ban"></i> <a href="/admin/bans/edit?user={{ $user->login }}">Бан / Разбан</a><br>
            <i class="fa fa-history"></i> <a href="/banhists/view?user={{ $user->login }}">История банов</a><br>
            @endif

            @if (isAdmin('admin'))
                <i class="fa fa-wrench"></i> <a href="/admin/users/edit?user={{ $user->login }}">Редактировать</a><br>
            @endif
        @else
        <i class="fa fa-user-circle"></i> <a href="/profile">Мой профиль</a><br>
        <i class="fa fa-cog"></i> <a href="/account">Мои данные</a><br>
        <i class="fa fa-wrench"></i> <a href="/settings">Настройки</a><br>
        @endif

    </div>
@stop
