@extends('layout')

@section('title', __('index.users') . ' (' . __('main.page_num', ['page' => $users->currentPage()]) . ')')

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
        <?php $active = ($type === 'users') ? 'primary' : 'light'; ?>
        <a class="btn btn-{{ $active }} btn-sm" href="/users?type=users&amp;sort={{ $sort }}">{{ __('main.users') }} <span class="badge bg-light text-dark">{{ statsUsers() }}</span></a>

        <?php $active = ($type === 'admins') ? 'primary' : 'light'; ?>
        <a class="btn btn-{{ $active }} btn-sm" href="/users?type=admins&amp;sort={{ $sort }}">{{ __('main.admins') }} <span class="badge bg-light text-dark">{{ statsAdmins() }}</span></a>

        <?php $active = ($type === 'birthdays') ? 'primary' : 'light'; ?>
        <a class="btn btn-{{ $active }} btn-sm" href="/users?type=birthdays&amp;sort={{ $sort }}">{{ __('main.birthdays') }}</a>
    </div>

    @if ($users->isNotEmpty())
        {{ __('main.sort') }}:
        <?php $active = ($sort === 'point') ? 'success' : 'light'; ?>
        <a href="/users?type={{ $type }}&amp;sort=point" class="badge bg-{{ $active }}">{{ __('users.assets') }}</a>

        <?php $active = ($sort === 'rating') ? 'success' : 'light'; ?>
        <a href="/users?type={{ $type }}&amp;sort=rating" class="badge bg-{{ $active }}">{{ __('users.reputation') }}</a>

        <?php $active = ($sort === 'money') ? 'success' : 'light'; ?>
        <a href="/users?type={{ $type }}&amp;sort=money" class="badge bg-{{ $active }}">{{ __('users.moneys') }}</a>

        <?php $active = ($sort === 'time') ? 'success' : 'light'; ?>
        <a href="/users?type={{ $type }}&amp;sort=time" class="badge bg-{{ $active }}">{{ __('main.date') }}</a>
        <hr>

        @foreach ($users as $key => $data)
            <div class="section mb-3 shadow{{ $user === $data->login ? ' bg-warning' : ''}}">
                <div class="user-avatar">
                    {{ $data->getAvatar() }}
                    {{ $data->getOnline() }}
                </div>

                <div class="section-user d-flex align-items-center">
                    <div class="flex-grow-1">
                        {{ $users->firstItem() + $key }}.
                        {{ $data->getProfile() }}<br>
                        <small class="font-italic">{{ $data->getStatus() }}</small>
                    </div>
                </div>

                <div class="section-body border-top">
                    {{ __('users.assets') }}: {{ plural($data->point, setting('scorename')) }}<br>
                    {{ __('users.reputation') }}: {{ formatNum($data->rating) }}<br>
                    {{ __('users.moneys') }}: {{ plural($data->money, setting('moneyname')) }}<br>
                    {{ __('main.registration_date') }}: {{ dateFixed($data->created_at, 'd.m.Y') }}
                </div>
            </div>
        @endforeach

        <div class="section-form mb-3 shadow">
            <form action="/users?type={{ $type }}&amp;sort={{ $sort }}" method="post">
                <div class="input-group{{ hasError('user') }}">
                    <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user', $user) }}" placeholder="{{ __('main.user_login') }}" required>
                    <div class="input-group-append">
                        <button class="btn btn-primary">{{ __('main.search') }}</button>
                    </div>
                </div>
                <div class="invalid-feedback">{{ textError('user') }}</div>
            </form>
        </div>

        {{ $users->links() }}

        <div class="mb-3">
            {{ __('main.total_users') }}: <b>{{ $users->total() }}</b>
        </div>
    @else
        {{ showError(__('main.empty_users')) }}
    @endif

    <i class="fas fa-search"></i> <a href="/searchusers">{{ __('index.search_users') }}</a><br>
@stop
