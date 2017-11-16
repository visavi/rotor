@extends('layout')

@section('title')
    Кто в онлайне
@stop

@section('content')

    <h1>Кто в онлайне</h1>

    Всего на сайте: <b>{{ $all }}</b><br>
    Зарегистрированных:  <b>{{ $page['total'] }}</b><br><br>


    @if ($online->isNotEmpty())

        @foreach ($online as $data)
            <div class="b">
                {!! $data->user->getGender() !!} <b>{!! profile($data->user) !!}</b> (Время: {{ dateFixed($data['updated_at'], 'H:i:s') }})
            </div>

            @if (isAdmin())
                <div>
                    <span class="data">({{ $data['brow'] }}, {{ $data['ip'] }})</span>
                </div>
            @endif
        @endforeach
        {!! pagination($page) !!}
    @else
        {{ showError('Авторизованных пользователей нет!') }}
    @endif

    <i class="fa fa-users"></i> <a href="/online/all">Показать гостей</a><br>
@stop
