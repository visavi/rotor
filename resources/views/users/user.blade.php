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

// В базе лежит только сдвиг в часах, название пояса по нему не восстановить,
// поэтому в анкете показывается местное время пользователя
$localTime = now()->addHours((int) $user->timezone)->format('H:i');

// Название языка берётся из его же переводов, как в модалке выбора языка.
// Ключа может не быть — тогда остаётся код
$language = (string) $user->language;
$langName = __('main.lang', [], $language);
$langName = $langName === 'main.lang' ? strtoupper($language) : $langName;

// Поля анкеты: пустые не показываются, поэтому собираются заранее.
// Список разбит надвое — между частями встают поля модулей: они продолжают
// рассказ о человеке, а настройки отображения и даты замыкают список
$fields = array_filter([
    __('users.gender')   => $user->gender === 'male' ? __('main.male') : __('main.female'),
    __('users.country')  => $user->country,
    __('users.city')     => $user->city,
    __('users.birthday') => $user->birthday,
]);

$serviceFields = array_filter([
    __('users.theme')            => $user->themes,
    __('users.language')         => $language ? $langName : null,
    __('users.local_time')       => $localTime,
    __('main.registration_date') => dateFixed($user->created_at, 'd.m.Y'),
    __('users.last_visit')       => $user->getVisit(),
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

                    {{-- Метрики модулей идут теми же плитками (компонент profile-stat) --}}
                    @hook('userStats', $user)
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

                {{-- Поля модулей идут тем же списком: хук отдаёт пары компонентом profile.field --}}
                @hook('userFields', $user)

                @foreach ($serviceFields as $label => $value)
                    <dt>{{ $label }}</dt>
                    <dd>{!! $value !!}</dd>
                @endforeach
            </dl>

            @hook('userEnd', $user)
        </div>
    </div>

    @if ($user->info)
        <div class="section mb-3 shadow">
            <div class="section-title"><i class="fas fa-circle-info"></i> {{ __('users.about') }}</div>
            <div class="section-body section-message">{{ $user->getInfo() }}</div>
        </div>
    @endif

    {{-- Ссылки на разделы приходят из модулей карточками (компонент profile.link) --}}
    <x-section :title="__('users.activity')" icon="fas fa-chart-simple" body-class="profile-links">
        @hook('userProfileLinks', $user)
    </x-section>

    {{-- Свои секции модулей: идут между разделами и блоком действий --}}
    @hook('userSections', $user)

    {{-- Блок состоит из хуков и ссылок для авторизованных, у гостя он пустой.
         Строки рисует компонент profile.action, модули отдают его через хуки --}}
    <x-section body-class="profile-actions">
        @hook('userActionStart', $user)

        @if ($user->site)
            <x-profile.action icon="fa fa-home" :label="__('users.go_website') . ' ' . $user->getName()" :url="$user->site" />
        @endif
        @hook('userActionMiddle', $user)

        @if (getUser())
            @if ($user->login === getUser('login'))
                @hook('userPersonalStart')
                <x-profile.action icon="fa fa-user-circle" :label="__('index.my_profile')" url="/profile" />
                <x-profile.action icon="fa fa-cog" :label="__('index.my_details')" url="/accounts" />
                <x-profile.action icon="fa fa-wrench" :label="__('index.my_settings')" url="/settings" />
                @hook('userPersonalEnd')
            @else
                @hook('userNotPersonalStart', $user)
                <x-profile.action icon="fa fa-envelope" :label="__('users.send_message')" url="/messages/talk/{{ $user->login }}" />

                @if (isAdmin('moder'))
                    <x-profile.action icon="fa fa-ban" :label="__('index.ban_unban')" url="/admin/bans/edit?user={{ $user->login }}" />
                    <x-profile.action icon="fa fa-history" :label="__('index.ban_history')" url="/admin/banhists/view?user={{ $user->login }}" />
                @endif

                @if (isAdmin('boss'))
                    <x-profile.action icon="fa fa-wrench" :label="__('main.edit')" url="/admin/users/edit?user={{ $user->login }}" />
                @endif
                @hook('userNotPersonalEnd', $user)
            @endif
        @endif
        @hook('userActionEnd', $user)
    </x-section>
@stop
