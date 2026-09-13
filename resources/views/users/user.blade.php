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

@php
use Illuminate\Support\Str;

$hasPicture = $user->picture && file_exists(public_path($user->picture));

// plural() отдаёт «511 баллов» одной строкой, плитке нужно число и слово раздельно
$point = plural($user->point, setting('scorename'));
$money = plural($user->money, setting('moneyname'));

// Поля анкеты: пустые не показываются, поэтому собираются заранее
$fields = array_filter([
    __('users.gender')            => $user->gender === 'male' ? __('main.male') : __('main.female'),
    __('users.country')           => $user->country,
    __('users.city')              => $user->city,
    __('users.birthday')          => $user->birthday,
    __('users.phone')             => $user->phone ? new Illuminate\Support\HtmlString('<a href="tel:' . e($user->phone) . '">' . e($user->phone) . '</a>') : null,
    __('users.theme')             => $user->themes,
    __('main.registration_date')  => dateFixed($user->created_at, 'd.m.Y'),
    __('users.last_visit')        => $user->getVisit(),
]);
@endphp

@section('content')
    @if ($user->level === 'pended')
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            {{ __('users.user_not_active') }}
        </div>
    @endif

    @if ($user->level === 'banned' && $user->timeban?->isFuture())
        <div class="alert alert-danger">
            <b>{{ __('users.user_banned') }}</b><br>
            {{ __('users.ending_ban') }}: {{ formatTime((int) now()->diffInSeconds($user->timeban)) }}
            @if ($user->lastBan->id)
                <br>{{ __('users.reason_ban') }}: {{ $user->lastBan->getReason() }}
            @endif
        </div>
    @endif

    <div class="section mb-3 shadow">
        <div class="section-body profile-header">
            <div class="profile-avatar">
                @if ($hasPicture)
                    <a href="{{ $user->picture }}" data-fancybox="gallery">
                        <img src="{{ $user->picture }}" alt="{{ $user->getName() }}">
                    </a>
                @else
                    <img src="/assets/img/images/photo.svg" alt="Photo">
                @endif
            </div>

            <div class="profile-summary">
                <div class="profile-name">
                    {{ $user->getName() }}

                    @if ($user->isOnline())
                        <span class="badge bg-success">{{ __('main.online') }}</span>
                    @endif
                </div>

                <div class="profile-badges">
                    <a class="badge bg-adaptive" href="/statusfaq">{{ $user->getStatus() }}</a>

                    @if (in_array($user->level, $adminGroups, true))
                        <span class="badge bg-info">{{ $user->getLevel() }}</span>
                    @endif
                </div>

                <div class="profile-stats">
                    <span class="profile-stat">
                        <b>{{ Str::beforeLast($point, ' ') }}</b>
                        <small>{{ Str::afterLast($point, ' ') }}</small>
                    </span>

                    <span class="profile-stat">
                        <b>{{ Str::beforeLast($money, ' ') }}</b>
                        <small>{{ Str::afterLast($money, ' ') }}</small>
                    </span>
                </div>

            </div>
        </div>
    </div>

    <div class="section mb-3 shadow">
        <div class="section-body">
            @hook('userStart', $user)

            <dl class="profile-fields">
                @foreach ($fields as $label => $value)
                    <dt>{{ $label }}</dt>
                    <dd>{!! $value !!}</dd>
                @endforeach
            </dl>

            @hook('userFields', $user)
            @hook('userEnd', $user)
        </div>
    </div>

    @if ($user->info)
        <div class="section mb-3 shadow">
            <div class="section-title"><i class="fas fa-circle-info"></i> {{ __('users.about') }}</div>
            <div class="section-body section-message">{{ $user->getInfo() }}</div>
        </div>
    @endif

    <ul class="list-inline mb-3">@hook('userProfileLinks', $user)</ul>

    <?php ob_start(); ?>
        @hook('userActionStart', $user)

        @if ($user->site)
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
    <?php $actions = ob_get_clean(); ?>

    {{-- Блок состоит из хуков и ссылок для авторизованных, у гостя он пустой.
         Разметка строками «иконка + ссылка», как её отдают модули --}}
    @if (trim(strip_tags($actions)))
        <div class="section mb-3 shadow">
            <div class="section-body profile-actions">{!! $actions !!}</div>
        </div>
    @endif
@stop
