@extends('layout')

@section('title', __('index.riches_rating') . ' (' . __('main.page_num', ['page' => $users->currentPage()]) . ')')

@section('header')
    <h1>{{ __('index.riches_rating') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.riches_rating') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($users->isNotEmpty())
        @foreach ($users as $key => $user)
            <div class="section mb-3 shadow">
                <div class="user-avatar">
                    {!! $user->getAvatar() !!}
                    {!! $user->getOnline() !!}
                </div>

                <div class="section-user">
                    {{ $users->firstItem() + $key }}.

                    @if ($user === $user->login)
                        <b>{!! $user->getProfile('#ff0000') !!}</b>
                    @else
                        <b>{!! $user->getProfile() !!}</b>
                    @endif

                    ({{ plural($user->money, setting('moneyname')) }})<br>
                    {!! $user->getStatus() !!}
                </div>

                <div class="section-body border-top">
                    {{ __('main.pluses') }}: {{ $user->posrating }} / {{ __('main.minuses') }}: {{ $user->negrating }}<br>
                    {{ __('main.registration_date') }}: {{ dateFixed($user->created_at, 'd.m.Y') }}
                </div>
            </div>
        @endforeach

        <div class="my-3">
            <form action="/ratinglists" method="post">
                <div class="form-inline">
                    <div class="form-group{{ hasError('user') }}">
                        <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user', $login) }}" placeholder="{{ __('main.user_login') }}" required>
                    </div>

                    <button class="btn btn-primary">{{ __('main.search') }}</button>
                </div>
                <div class="invalid-feedback">{{ textError('user') }}</div>
            </form>
        </div>

        {{ __('main.total_users') }}: <b>{{ $users->total() }}</b><br>
    @else
        {!! showError(__('main.empty_users')) !!}
    @endif

    {{ $users->links() }}
@stop
