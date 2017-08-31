@extends('layout')

@section('title')
    Анкета пользователя {{ $user['login'] }} - @parent
@stop

@section('content')

    <h1>{!! userAvatar($user) !!} {{ $user['login'] }} <small>#{{ $user['id'] }} {{ user_visit($user) }}</small></h1>

    @if ($user['confirmreg'] == 1)
        <b><span style="color:#ff0000">Внимание, аккаунт требует подтверждение регистрации!</span></b><br>
    @endif

    @if ($user['ban'] == 1 && $user['timeban'] > SITETIME)
        <div class="form">
            <b><span style="color:#ff0000">Внимание, пользователь забанен!</span></b><br>
            До окончания бана осталось {{ formatTime($user['timeban'] - SITETIME) }}<br>
            Причина: {{ bbCode($user['reasonban']) }}
        </div>
    @endif

    @if ($user['level'] >= 101 && $user['level'] <= 105)
        <div class="alert alert-info">Должность: <b>{{ userLevel($user['level']) }}</b></div>
    @endif

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 push-md-6">
                <div class="float-right">

                    @if (!empty($user['picture']) && file_exists(HOME.'/uploads/photos/'.$user['picture']))
                        <a class="gallery" href="/uploads/photos/{{ $user['picture'] }}">
                            {!! resize_image('uploads/photos/', $user['picture'], setting('previewsize'), ['alt' => $user['login'], 'class' => 'img-fluid rounded']) !!}</a>
                    @else
                        <img src="/assets/img/images/photo.jpg" alt="Фото" class="float-right img-fluid rounded">
                    @endif
                </div>
            </div>

            <div class="col-md-6 pull-md-6">
                Cтатус: <b><a href="/statusfaq">{!! userStatus($user) !!}</a></b><br>

                {!! user_gender($user) !!}
                Пол:
                {{  ($user['gender'] == 1) ? 'Мужской' : 'Женский' }}<br>

                Логин: <b>{{ $user['login'] }}</b><br>

                @if (!empty($user['name']))
                    Имя: <b>{{  $user['name'] }}<br></b>
                @endif

                @if (!empty($user['country']))
                    Страна: <b>{{ $user['country'] }}<br></b>
                @endif

                @if (!empty($user['city']))
                    Откуда: {{ $user['city'] }}<br>
                @endif

                @if (!empty($user['birthday']))
                    Дата рождения: {{  $user['birthday'] }}<br>
                @endif

                @if (!empty($user['icq']))
                    ICQ: {{  $user['icq'] }}<br>
                @endif

                @if (!empty($user['skype']))
                    Skype: {{ $user['skype'] }}<br>
                @endif

                Всего посeщений: {{ $user['visits'] }}<br>
                Сообщений на форуме: {{ $user['allforum'] }}<br>
                Сообщений в гостевой: {{ $user['allguest'] }}<br>
                Комментариев: {{ $user['allcomments'] }}<br>
                Актив: {{ points($user['point']) }}<br>
                Денег: {{ moneys($user['money']) }}<br>

                @if (!empty($user['themes']))
                Используемый скин: {{ $user['themes'] }}<br>
                @endif
                Дата регистрации: {{ dateFixed($user['joined'], 'j F Y') }}<br>

                <?php $invite = Invite::where('invite_user_id', $user['id'])->first(); ?>
                @if (!empty($invite))
                    Зарегистрирован по приглашению: {!! profile($invite->user) !!}<br>
                @endif

                Последняя авторизация: {{ dateFixed($user['timelastlogin']) }}<br>

                <a href="/banhist?uz={{ $user['login'] }}">Строгих нарушений: {{ $user['totalban'] }}</a><br>

                <a href="/rating/{{ $user->login }}/received">Репутация: <b>{!! format_num($user['rating']) !!}</b> (+{{  $user['posrating'] }}/-{{  $user['negrating'] }})</a><br>

                @if (is_user() && getUsername() != $user['login'])
                    [ <a href="/user/{{ $user['login'] }}/rating?vote=1"><i class="fa fa-thumbs-up"></i><span style="color:#0099cc"> Плюс</span></a> /
                    <a href="/user/{{ $user['login'] }}/rating?vote=0"><span style="color:#ff0000">Минус</span> <i class="fa fa-thumbs-down"></i></a> ]<br>
                @endif

            </div>

            <div class="col-md-12">

                @if (!empty($user['info']))
                    <div class="alert alert-warning"><b>О себе</b>:<br>{!! bbCode($user['info']) !!}</div>
                @endif

                <b><a href="/forum/active/themes?user={{ $user['login'] }}">Форум</a></b> (<a href="/forum/active/posts?user={{ $user['login'] }}">Сообщ.</a>) /
                <b><a href="/load/active?act=files&amp;uz={{ $user['login'] }}">Загрузки</a></b> (<a href="/load/active?act=comments&amp;uz={{ $user['login'] }}">комм.</a>) /
                <b><a href="/blog/active/articles?user={{ $user['login'] }}">Блоги</a></b> (<a href="/blog/active/comments?user={{ $user['login'] }}">комм.</a>) /
                <b><a href="/gallery/album/{{ $user['login'] }}">Галерея</a></b> (<a href="/gallery/comments/{{ $user['login'] }}">комм.</a>)<br>
            </div>
        </div>
    </div>

    @if (is_admin())
        <?php $usernote = Note::where('user_id', $user['id'])->first(); ?>
    <div class="alert alert-success">
        <i class="fa fa-thumb-tack"></i> <b>Заметка:</b> (<a href="/user/{{ $user['login'] }}/note">Изменить</a>)<br>

        @if (!empty($usernote['text']))
            {!! bbCode($usernote['text']) !!}<br>
            Изменено: {!! profile($usernote->editUser) !!} ({{ dateFixed($usernote['updated_at']) }})<br>
        @else
            Записей еще нет!<br>
        @endif

        </div>
    @endif

    <div class="alert alert-info">
        <i class="fa fa-sticky-note"></i> <a href="/wall?uz={{ $user['login'] }}">Стена сообщений</a> ({{ userWall($user) }})<br>

        @if ($user['login'] != getUsername())
            <i class="fa fa-address-book"></i> Добавить в
            <a href="/contact?act=add&amp;uz={{ $user['login'] }}&amp;uid={{ $_SESSION['token'] }}">контакт</a> /
            <a href="/ignore?act=add&amp;uz={{ $user['login'] }}&amp;uid={{ $_SESSION['token'] }}">игнор</a><br>
            <i class="fa fa-envelope"></i> <a href="/private/send?user={{ $user['login'] }}">Отправить сообщение</a><br>

            <i class="fa fa-money"></i> <a href="/transfer?uz={{ $user['login'] }}">Перечислить денег</a><br>

            @if (!empty($user['site']))
                <i class="fa fa-home"></i> <a href="{{ $user['site'] }}">Перейти на сайт {{ $user['login'] }}</a><br>
            @endif

            @if (is_admin([101, 102, 103]))
                @if (!empty(setting('invite')))
                    <i class="fa fa-ban"></i> <a href="/admin/invitations?act=send&amp;user={{ $user['login'] }}&amp;uid={{ $_SESSION['token'] }}">Отправить инвайт</a><br>
                @endif
            <i class="fa fa-ban"></i> <a href="/admin/ban?act=edit&amp;uz={{ $user['login'] }}">Бан / Разбан</a><br>
            @endif

            @if (is_admin([101, 102]))
                <i class="fa fa-wrench"></i> <a href="/admin/users?act=edit&amp;uz={{ $user['login'] }}">Редактировать</a><br>
            @endif
        @else
        <i class="fa fa-user-circle-o"></i> <a href="/profile">Мой профиль</a><br>
        <i class="fa fa-cog"></i> <a href="/account">Мои данные</a><br>
        <i class="fa fa-wrench"></i> <a href="/setting">Настройки</a><br>
        @endif

    </div>
@stop
