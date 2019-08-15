@extends('layout')

@section('title')
    {{ trans('index.account') }} {{ $user->login }}
@stop

@section('header')
    <div class="avatar-box">
        <div class="avatar-box_image">{!! $user->getAvatar() !!}</div>
        <h1 class="avatar-box_login">
            {{ $user->login }} <small>#{{ $user->id }}</small>
        </h1>
    </div>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('index.account') }} {{ $user->login }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($user->level === 'pended')
        <div class="p-1 bg-danger text-white">
            <i class="fas fa-exclamation-triangle"></i>
            {{ trans('users.user_not_active') }}
        </div>
    @endif

    @if ($user->level === 'banned' && $user->timeban > SITETIME)
        <div class="alert alert-danger">
            <b>{{ trans('users.user_banned') }}</b><br>
            {{ trans('users.ending_ban') }}: {{ formatTime($user->timeban - SITETIME) }}<br>

            @if ($user->lastBan->id)
                {{ trans('users.reason_ban') }}: {!! bbCode($user->lastBan->reason) !!}<br>
            @endif
        </div>
    @endif

    @if (in_array($user->level, $adminGroups, true))
        <div class="alert alert-info">{{ trans('users.position') }}: <b>{{ $user->getLevel() }}</b></div>
    @endif

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                {{ trans('users.status') }}: <b><a href="/statusfaq">{!! $user->getStatus() !!}</a></b><br>

                {!! $user->getGender() !!}
                {{ trans('users.gender') }}:
                {{ $user->gender === 'male' ? trans('main.male') : trans('main.female') }}<br>

                {{ trans('users.login') }}: <b>{{ $user->login }}</b><br>

                @if (! empty($user->name))
                    {{ trans('users.name') }}: <b>{{ $user->name }}<br></b>
                @endif

                @if (! empty($user->country))
                    {{ trans('users.country') }}: <b>{{ $user->country }}<br></b>
                @endif

                @if (! empty($user->city))
                    {{ trans('users.city') }}: {{ $user->city }}<br>
                @endif

                @if (! empty($user->birthday))
                    {{ trans('users.birthday') }}: {{ $user->birthday }}<br>
                @endif

                @if (! empty($user->phone))
                    {{ trans('users.phone') }}: {{ $user->phone }}<br>
                @endif

                {{ trans('users.visits') }}: {{ $user->visits }}<br>
                {{ trans('users.forum_posts') }}: {{ $user->allforum }}<br>
                {{ trans('users.guest_posts') }}: {{ $user->allguest }}<br>
                {{ trans('main.comments') }}: {{ $user->allcomments }}<br>
                {{ trans('users.assets') }}: {{ plural($user->point, setting('scorename')) }}<br>
                {{ trans('users.moneys') }}: {{ plural($user->money, setting('moneyname')) }}<br>

                @if ($user->themes)
                    {{ trans('users.theme') }}: {{ $user->themes }}<br>
                @endif
                {{ trans('main.registration_date') }}: {{ dateFixed($user->created_at, 'd.m.Y') }}<br>

                @if ($invite)
                    {{ trans('users.invitation') }}: {!! $invite->user->getProfile() !!}<br>
                @endif

                {{ trans('users.last_visit') }}: {{ dateFixed($user->updated_at) }}<br>

                @if (getUser())
                    <a href="/ratings/{{ $user->login }}">{{ trans('main.reputation') }}: <b>{!! formatNum($user->rating) !!}</b> (+{{  $user->posrating }}/-{{  $user->negrating }})</a><br>
                    @if (getUser('login') !== $user->login)
                        <a href="/users/{{ $user->login }}/rating?vote=plus"><i class="fa fa-thumbs-up"></i><span style="color:#0099cc"> {{ trans('main.plus') }}</span></a> /
                        <a href="/users/{{ $user->login }}/rating?vote=minus"><span style="color:#ff0000">{{ trans('main.minus') }}</span> <i class="fa fa-thumbs-down"></i></a><br>
                    @endif
                @else
                    {{ trans('main.reputation') }}: <b>{!! formatNum($user->rating) !!}</b> (+{{  $user->posrating }}/-{{  $user->negrating }})<br>
                @endif
            </div>

            <div class="col-md-6">
                @if (!empty($user->picture) && file_exists(HOME . '/' . $user->picture))
                    <a class="gallery" href="{{ $user->picture }}">
                        {!! resizeImage($user->picture, ['alt' => $user->login, 'class' => 'float-right img-fluid rounded']) !!}</a>
                @else
                    <img src="/assets/img/images/photo.png" alt="Photo" class="float-right img-fluid rounded">
                @endif
            </div>
            <div class="col-md-12">
                @if (!empty($user->info))
                    <div class="alert alert-warning">
                        <b>{{ trans('users.about') }}:</b><br>
                        {!! bbCode($user->info) !!}
                    </div>
                @endif

                <b><a href="/forums/active/topics?user={{ $user->login }}">{{ trans('index.forums') }}</a></b> (<a href="/forums/active/posts?user={{ $user->login }}">{{ trans('main.messages') }}</a>) /
                <b><a href="/downs/active/files?user={{ $user->login }}">{{ trans('index.loads') }}</a></b> (<a href="/downs/active/comments?user={{ $user->login }}">{{ trans('main.comments') }}</a>) /
                <b><a href="/blogs/active/articles?user={{ $user->login }}">{{ trans('index.blogs') }}</a></b> (<a href="/blogs/active/comments?user={{ $user->login }}">{{ trans('main.comments') }}</a>) /
                <b><a href="/photos/albums/{{ $user->login }}">{{ trans('index.photos') }}</a></b> (<a href="/photos/comments/active/{{ $user->login }}">{{ trans('main.comments') }}</a>)<br>
            </div>
        </div>
    </div>

    @if (isAdmin())
    <div class="alert alert-success">
        <i class="fa fa-thumbtack"></i> <b>{{ trans('main.note') }}:</b> (<a href="/users/{{ $user->login }}/note">{{ trans('main.change') }}</a>)<br>

        @if (! empty($user->note->text))
            {!! bbCode($user->note->text) !!}<br>
            {{ trans('main.changed') }}: {!! $user->note->editUser->getProfile() !!} ({{ dateFixed($user->note->updated_at) }})<br>
        @else
            {{ trans('users.empty_note') }}<br>
        @endif

        </div>
    @endif

    <div class="alert alert-info">
        <i class="fa fa-sticky-note"></i> <a href="/walls/{{ $user->login }}">{{ trans('index.wall_posts') }}</a> ({{ $user->getCountWall() }})<br>

        @if (!empty($user->site))
            <i class="fa fa-home"></i> <a href="{{ $user->site }}">{{ trans('users.go_website') }} {{ $user->login }}</a><br>
        @endif

        @if ($user->login !== getUser('login'))
            <i class="fa fa-address-book"></i> {{ trans('users.add_to') }}
            <a href="/contacts?user={{ $user->login }}">{{ trans('index.contacts') }}</a> /
            <a href="/ignores?user={{ $user->login }}">{{ trans('index.ignores') }}</a><br>
            <i class="fa fa-envelope"></i> <a href="/messages/talk/{{ $user->login }}">{{ trans('users.send_message') }}</a><br>
            <i class="fa fa-money-bill-alt"></i> <a href="/transfers?user={{ $user->login }}">{{ trans('index.money_transfer') }}</a><br>

            @if (isAdmin('moder'))
                @if (setting('invite'))
                    <i class="fa fa-ban"></i> <a href="/admin/invitations/send?user={{ $user->login }}&amp;token={{ $_SESSION['token'] }}">{{ trans('users.send_invite') }}</a><br>
                @endif
            <i class="fa fa-ban"></i> <a href="/admin/bans/edit?user={{ $user->login }}">{{ trans('index.ban_unban') }}</a><br>
            <i class="fa fa-history"></i> <a href="/admin/banhists/view?user={{ $user->login }}">{{ trans('index.ban_history') }}</a><br>
            @endif

            @if (isAdmin('boss'))
                <i class="fa fa-wrench"></i> <a href="/admin/users/edit?user={{ $user->login }}">{{ trans('main.edit') }}</a><br>
            @endif
        @else
            <i class="fa fa-user-circle"></i> <a href="/profile">{{ trans('index.my_profile') }}</a><br>
            <i class="fa fa-cog"></i> <a href="/accounts">{{ trans('index.my_details') }}</a><br>
            <i class="fa fa-wrench"></i> <a href="/settings">{{ trans('index.my_settings') }}</a><br>
        @endif

    </div>
@stop
