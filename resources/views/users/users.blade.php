@extends('layout')

@section('title')
    {{ trans('index.users') }} ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    <h1>{{ trans('index.users') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('index.users') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($users->isNotEmpty())
        @foreach($users as $key => $data)
            <div class="b">
                <div class="img">
                    {!! $data->getAvatar() !!}
                    {!! $data->getOnline() !!}
                </div>

                @if ($user === $data->login)
                    {{ ($page->offset + $key + 1) }}. <b>{!! $data->getProfile('#ff0000') !!}</b>
                @else
                    {{ ($page->offset + $key + 1) }}. <b>{!! $data->getProfile() !!}</b>
                @endif
                ({{ plural($data->point, setting('scorename')) }})<br>
                {!! $data->getStatus() !!}
            </div>

            <div>
                {{ trans('index.forums') }}: {{ $data->allforum }} | {{ trans('index.guestbooks') }}: {{ $data->allguest }} | {{ trans('main.comments') }}: {{ $data->allcomments }}<br>
                {{ trans('users.visits') }}: {{ $data->visits }}<br>
                {{ trans('users.moneys') }}: {{ $data->money }}<br>
                {{ trans('main.registration_date') }}: {{ dateFixed($data->created_at, 'd.m.Y') }}
            </div>
        @endforeach

        {!! pagination($page) !!}

        <div class="form">
            <form action="/users" method="post">
                <div class="form-inline">
                    <div class="form-group{{ hasError('user') }}">
                        <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user', $user) }}" placeholder="{{ trans('main.user_login') }}" required>
                    </div>

                    <button class="btn btn-primary">{{ trans('main.search') }}</button>
                </div>
                <div class="invalid-feedback">{{ textError('user') }}</div>
            </form>
        </div><br>

        {{ trans('main.total_users') }}: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError(trans('main.empty_users')) !!}
    @endif

    <i class="fa fa-users"></i> <a href="/who">{{ trans('users.novices') }}</a><br>
    <i class="fas fa-search"></i> <a href="/searchusers">{{ trans('index.search_users') }}</a><br>
@stop
