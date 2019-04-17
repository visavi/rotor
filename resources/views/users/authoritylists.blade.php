@extends('layout')

@section('title')
    Рейтинг репутации ({{ trans('main.page_num', ['page' => $page->current]) }})
@stop

@section('header')
    <h1>Рейтинг репутации</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Рейтинг репутации</li>
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
                (Репутация: {{ $data->rating }})<br>
                {!! $data->getStatus() !!}
            </div>

            <div>
                Плюсов: {{ $data->posrating }} / Минусов: {{ $data->negrating }}<br>
                Дата регистрации: {{ dateFixed($data->created_at, 'd.m.Y') }}
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
                {!! textError('user') !!}
            </form>
        </div><br>

        Всего пользователей: <b>{{ $page->total }}</b><br><br>
    @else
        {!! showError('Пользователей еще нет!') !!}
    @endif
@stop
