@extends('layout')

@section('title')
    Кто в онлайне - @parent
@stop

@section('content')

    <h1>Кто в онлайне</h1>

    Всего на сайте: <b>{{ $all }}</b><br>
    Зарегистрированных:  <b>{{ $page['total'] }}</b><br><br>


    @if ($online->isNotEmpty())

        @foreach ($online as $data)
            <div class="b">
                {!!  user_gender($data->user) !!} <b>{!! profile($data->user) !!}</b> (Время: {{ date_fixed($data['updated_at'], 'H:i:s') }})
            </div>

            @if (is_admin())
                <div>
                    <span class="data">({{ $data['brow'] }}, {{ $data['ip'] }})</span>
                </div>
            @endif
        @endforeach
        {{ App::pagination($page) }}
    @else
        {{ App::showError('Авторизованных пользователей нет!') }}
    @endif

    <i class="fa fa-users"></i> <a href="/online/all">Показать гостей</a><br>
@stop
