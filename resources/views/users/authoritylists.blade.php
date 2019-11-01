@extends('layout')

@section('title')
    {{ __('index.reputation_rating') }} ({{ __('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    <h1>{{ __('index.reputation_rating') }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">{{ __('index.reputation_rating') }}</li>
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
                ({{ __('main.reputation') }}: {{ $data->rating }})<br>
                {!! $data->getStatus() !!}
            </div>

            <div>
                {{ __('main.pluses') }}: {{ $data->posrating }} / {{ __('main.minuses') }}: {{ $data->negrating }}<br>
                {{ __('main.registration_date') }}: {{ dateFixed($data->created_at, 'd.m.Y') }}
            </div>
        @endforeach

        {!! pagination($page) !!}

        <div class="form">
            <form action="/authoritylists" method="post">
                <div class="form-inline">
                    <div class="form-group{{ hasError('user') }}">
                        <input type="text" class="form-control" id="user" name="user" maxlength="20" value="{{ getInput('user', $user) }}" placeholder="{{ __('main.user_login') }}" required>
                    </div>

                    <button class="btn btn-primary">{{ __('main.search') }}</button>
                </div>
                <div class="invalid-feedback">{{ textError('user') }}</div>
            </form>
        </div><br>

        {{ __('main.total_users') }}: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError(__('main.empty_users')) !!}
    @endif
@stop
