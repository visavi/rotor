@extends('layout')

@section('title')
    {{ __('index.users') }} ({{ __('main.page_num', ['page' => $users->currentPage()]) }})
@stop

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
    @if ($users->isNotEmpty())
        @foreach ($users as $key => $data)
            <div class="b">
                <div class="img">
                    {!! $data->getAvatar() !!}
                    {!! $data->getOnline() !!}
                </div>

                {{ $users->firstItem() + $key }}.
                @if ($user === $data->login)
                    <b>{!! $data->getProfile('#ff0000') !!}</b>
                @else
                    <b>{!! $data->getProfile() !!}</b>
                @endif
                ({{ plural($data->point, setting('scorename')) }})<br>
                {!! $data->getStatus() !!}
            </div>

            <div>
                {{ __('index.forums') }}: {{ $data->allforum }} | {{ __('index.guestbook') }}: {{ $data->allguest }} | {{ __('main.comments') }}: {{ $data->allcomments }}<br>
                {{ __('users.visits') }}: {{ $data->visits }}<br>
                {{ __('users.moneys') }}: {{ $data->money }}<br>
                {{ __('main.registration_date') }}: {{ dateFixed($data->created_at, 'd.m.Y') }}
            </div>
        @endforeach

        <div class="form mt-3">
            <form action="/users" method="post">
                <div class="form-inline">
                    <div class="form-group{{ hasError('user') }}">
                        <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user', $user) }}" placeholder="{{ __('main.user_login') }}" required>
                    </div>

                    <button class="btn btn-primary">{{ __('main.search') }}</button>
                </div>
                <div class="invalid-feedback">{{ textError('user') }}</div>
            </form>
        </div><br>

        {{ __('main.total_users') }}: <b>{{ $users->total() }}</b><br>
    @else
        {!! showError(__('main.empty_users')) !!}
    @endif

    {{ $users->links() }}

    <i class="fa fa-users"></i> <a href="/who">{{ __('users.novices') }}</a><br>
    <i class="fas fa-search"></i> <a href="/searchusers">{{ __('index.search_users') }}</a><br>
@stop
