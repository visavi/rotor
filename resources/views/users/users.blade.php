@extends('layout')

@section('title', sprintf('%s (%s)', __('index.users'), __('main.page_num', ['page' => $users->currentPage()])))

@section('header')
    <h1>{{ __('index.users') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.users') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="mb-3">
        <?php $active = ($type === 'users') ? 'primary' : 'adaptive'; ?>
        <a class="btn btn-{{ $active }} btn-sm" href="{{ route('users.index', array_filter(['type' => 'users', 'sort' => $sort, 'order' => $order, 'user' => $user])) }}">{{ __('main.users') }} <span class="badge bg-adaptive">{{ statsUsers() }}</span></a>

        <?php $active = ($type === 'admins') ? 'primary' : 'adaptive'; ?>
        <a class="btn btn-{{ $active }} btn-sm" href="{{ route('users.index', array_filter(['type' => 'admins', 'sort' => $sort, 'order' => $order, 'user' => $user])) }}">{{ __('main.admins') }} <span class="badge bg-adaptive">{{ statsAdmins() }}</span></a>

        <?php $active = ($type === 'birthdays') ? 'primary' : 'adaptive'; ?>
        <a class="btn btn-{{ $active }} btn-sm" href="{{ route('users.index', array_filter(['type' => 'birthdays', 'sort' => $sort, 'order' => $order, 'user' => $user])) }}">{{ __('main.birthdays') }}</a>
    </div>

    <div class="sort-links border-bottom pb-3 mb-3">
        {{ __('main.sort') }}:
        @foreach ($sorting as $key => $option)
            <a href="{{ route('users.index', array_filter(['type' => $type, 'sort' => $key, 'order' => $option['inverse'] ?? 'desc', 'user' => $user])) }}" class="badge bg-{{ $option['badge'] ?? 'adaptive' }}">
                {{ $option['label'] }}{{ $option['icon'] ?? '' }}
            </a>
        @endforeach
    </div>

    <div class="section-form mb-3 shadow">
        <form method="get" action="{{ route('users.index') }}">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="order" value="{{ $order }}">
            <div class="input-group">
                <input type="text" class="form-control" name="user" maxlength="50" value="{{ $user }}" placeholder="{{ __('users.login_or_username') }}">
                <button class="btn btn-primary">{{ __('main.search') }}</button>
            </div>
        </form>
    </div>

    @if ($users->isNotEmpty())
        @foreach ($users as $key => $data)
            <div class="section mb-3 shadow user-card">
                <div class="user-card-head">
                    <div class="user-avatar">
                        {{ $data->getAvatar() }}
                        {{ $data->getOnline() }}
                    </div>

                    <div class="user-card-title">
                        <div>
                            <span class="text-muted">{{ $users->firstItem() + $key }}.</span>
                            {{ $data->getProfile() }}
                        </div>

                        <small class="text-muted">{{ $data->getStatus() }}</small>
                    </div>

                    @if (getUser() && $data->id !== getUser('id'))
                        <a class="btn btn-sm btn-adaptive" href="/messages/talk/{{ $data->login }}" data-bs-toggle="tooltip" title="{{ __('users.send_message') }}">
                            <i class="fas fa-envelope"></i>
                        </a>
                    @endif
                </div>

                <div class="section-body border-top user-card-stats">
                    <span><i class="fas fa-bolt"></i> {{ plural($data->point, setting('scorename')) }}</span>
                    <span><i class="fas fa-coins"></i> {{ plural($data->money, setting('moneyname')) }}</span>

                    {{-- Метрики модулей идут за метриками ядра, до дат --}}
                    @hook('userCardStats', $data)

                    <span data-bs-toggle="tooltip" title="{{ __('main.registration_date') }}"><i class="fas fa-calendar-plus"></i> {{ dateFixed($data->created_at, 'd.m.Y') }}</span>
                    <span data-bs-toggle="tooltip" title="{{ __('users.last_visit') }}"><i class="fas fa-clock"></i> {{ $data->getVisit() }}</span>
                </div>
            </div>
        @endforeach

        {{ $users->links() }}

        <div class="mb-3">
            {{ __('main.total_users') }}: <b>{{ $users->total() }}</b>
        </div>
    @else
        {{ showError(__('main.empty_users')) }}
    @endif
@stop
