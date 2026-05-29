@extends('layout')

@section('title', __('index.account') . ' ' . $user->getName())

@section('header')
    <h1>
        {{ $user->getName() }}

        <small>
            @if ($user->login !== $user->getName())
                ({{ $user->login }})
            @endif
            #{{ $user->id }}
        </small>
    </h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.account') }} {{ $user->getName() }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($user->level === 'pended')
        <div class="p-1 my-1 bg-danger text-white">
            <i class="fas fa-exclamation-triangle"></i>
            {{ __('users.user_not_active') }}
        </div>
    @endif

    @if ($user->level === 'banned' && $user->timeban > SITETIME)
        <div class="alert alert-danger">
            <b>{{ __('users.user_banned') }}</b><br>
            {{ __('users.ending_ban') }}: {{ formatTime($user->timeban - SITETIME) }}<br>

            @if ($user->lastBan->id)
                {{ __('users.reason_ban') }}: {{ $user->lastBan->getReason() }}<br>
            @endif
        </div>
    @endif

    @if (in_array($user->level, $adminGroups, true))
        <div class="alert alert-info">{{ __('users.position') }}: <b>{{ $user->getLevel() }}</b></div>
    @endif

    <div class="container-fluid mb-3">
        <div class="row">
            <div class="col-md-6">
                @hook('userStart', $user)
                {{ __('users.status') }}: <b><a href="/statusfaq">{{ $user->getStatus() }}</a></b><br>

                {{ $user->getGender() }}
                {{ __('users.gender') }}:
                {{ $user->gender === 'male' ? __('main.male') : __('main.female') }}<br>

                {{ __('users.login') }}: <b>{{ $user->login }}</b><br>

                @if (! empty($user->name))
                    {{ __('users.name') }}: <b>{{ $user->name }}<br></b>
                @endif

                @if (! empty($user->country))
                    {{ __('users.country') }}: <b>{{ $user->country }}<br></b>
                @endif

                @if (! empty($user->city))
                    {{ __('users.city') }}: {{ $user->city }}<br>
                @endif

                @if (! empty($user->birthday))
                    {{ __('users.birthday') }}: {{ $user->birthday }}<br>
                @endif

                @if (! empty($user->phone))
                    {{ __('users.phone') }}: <a href="tel:{{ $user->phone }}">{{ $user->phone }}</a><br>
                @endif

                {{ __('users.assets') }}: {{ plural($user->point, setting('scorename')) }}<br>
                {{ __('users.moneys') }}: {{ plural($user->money, setting('moneyname')) }}<br>

                @if ($user->themes)
                    {{ __('users.theme') }}: {{ $user->themes }}<br>
                @endif
                {{ __('main.registration_date') }}: {{ dateFixed($user->created_at, 'd.m.Y') }}<br>

                {{ __('users.last_visit') }}: {{ $user->getVisit() }}<br>

                @foreach($fields as $field)
                    {{ $field->name }}:
                    @if ($field->type === 'textarea')
                        {{ renderHtml($field->value) }}
                    @else
                        {{ $field->value }}
                    @endif
                    <br>
                @endforeach

                @if (getUser())
                    <a href="/ratings/{{ $user->login }}">{{ __('main.reputation') }}: <b>{{ formatNum($user->rating) }}</b> (+{{ $user->posrating }}/-{{ $user->negrating }})</a><br>
                    @if (getUser('login') !== $user->login)
                        <a href="/users/{{ $user->login }}/rating?vote=plus"><i class="fa fa-arrow-up"></i><span style="color:#0099cc"> {{ __('main.plus') }}</span></a> /
                        <a href="/users/{{ $user->login }}/rating?vote=minus"><span style="color:#ff0000">{{ __('main.minus') }}</span> <i class="fa fa-arrow-down"></i></a><br>
                    @endif
                @else
                    {{ __('main.reputation') }}: <b>{{ formatNum($user->rating) }}</b> (+{{ $user->posrating }}/-{{ $user->negrating }})<br>
                @endif
                @hook('userEnd', $user)
            </div>

            <div class="col-md-6">
                @if (!empty($user->picture) && file_exists(public_path($user->picture)))
                    <a href="{{ $user->picture }}" data-fancybox="gallery">
                        <img src="{{ $user->picture }}" alt="{{ $user->getName() }}" class="float-end img-fluid rounded"></a>
                @else
                    <img src="/assets/img/images/photo.svg" alt="Photo" class="float-end img-fluid rounded">
                @endif
            </div>
            <div class="col-md-12 mt-3">
                @if (!empty($user->info))
                    <div class="alert alert-warning">
                        <b>{{ __('users.about') }}:</b><br>
                        {{ $user->getInfo() }}
                    </div>
                @endif

                <ul class="list-inline mb-0">@hook('userProfileLinks', $user)</ul>
            </div>
        </div>
    </div>

<div class="alert alert-info mb-3">
        @hook('userActionStart', $user)

        @if (!empty($user->site))
            <i class="fa fa-home"></i> <a href="{{ $user->site }}">{{ __('users.go_website') }} {{ $user->getName() }}</a><br>
        @endif
        @hook('userActionMiddle', $user)

        @if (getUser())
            @if ($user->login === getUser('login'))
                @hook('userPersonalStart')
                <i class="fa fa-user-circle"></i> <a href="/profile">{{ __('index.my_profile') }}</a><br>
                <i class="fa fa-cog"></i> <a href="/accounts">{{ __('index.my_details') }}</a><br>
                <i class="fa fa-wrench"></i> <a href="/settings">{{ __('index.my_settings') }}</a><br>
                @hook('userPersonalEnd')
            @else
                @hook('userNotPersonalStart', $user)
                <i class="fa fa-envelope"></i> <a href="/messages/talk/{{ $user->login }}">{{ __('users.send_message') }}</a><br>

                @if (isAdmin('moder'))
                    <i class="fa fa-ban"></i> <a href="/admin/bans/edit?user={{ $user->login }}">{{ __('index.ban_unban') }}</a><br>
                    <i class="fa fa-history"></i> <a href="/admin/banhists/view?user={{ $user->login }}">{{ __('index.ban_history') }}</a><br>
                @endif

                @if (isAdmin('boss'))
                    <i class="fa fa-wrench"></i> <a href="/admin/users/edit?user={{ $user->login }}">{{ __('main.edit') }}</a><br>
                @endif
                @hook('userNotPersonalEnd', $user)
            @endif
        @endif
        @hook('userActionEnd', $user)
    </div>
@stop
