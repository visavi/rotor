@extends('layout')

@section('title')
    {{ trans('index.reputation_rating') }} ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    <h1>{{ trans('index.reputation_rating') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ trans('index.reputation_rating') }}</li>
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

                {{ ($page->offset + $key + 1) }}.

                @if ($user === $data->login)
                    <b>{!! $data->getProfile('#ff0000') !!}</b>
                @else
                    <b>{!! $data->getProfile() !!}</b>
                @endif
                ({{ trans('main.reputation') }}: {{ $data->rating }})<br>
                {!! $data->getStatus() !!}
            </div>

            <div>
                {{ trans('main.pluses') }}: {{ $data->posrating }} / {{ trans('main.minuses') }}: {{ $data->negrating }}<br>
                {{ trans('main.registration_date') }}: {{ dateFixed($data->created_at, 'd.m.Y') }}
            </div>
        @endforeach

        {!! pagination($page) !!}

        <div class="form">
            <form action="/authoritylist" method="post">
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
@stop
