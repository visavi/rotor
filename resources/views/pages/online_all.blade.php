@extends('layout')

@section('title')
    Кто в онлайне
@stop

@section('content')

    <h1>Кто в онлайне</h1>

    Всего на сайте: <b>{{ $page['total'] }}</b><br>
    Зарегистрированных:  <b>{{ $registered }}</b><br><br>


    @if ($online->isNotEmpty())

        @foreach ($online as $data)

            <div class="b">
                @if ($data->user)
                    {!! $data->user->getGender() !!} <b>{!! profile($data->user) !!}</b> (Время: {{ dateFixed($data['updated_at'], 'H:i:s') }})
                @else
                    <i class="fa fa-user-circle-o"></i> <b>{{ setting('guestsuser') }}</b>  (Время: {{ dateFixed($data['updated_at'], 'H:i:s') }})
                @endif
            </div>

            @if (isAdmin())
                <div>
                    <span class="data">({{ $data['brow'] }}, {{ $data['ip'] }})</span>
                </div>
            @endif
        @endforeach
        {!! pagination($page) !!}
    @else
        {{ showError('На сайте никого нет!') }}
    @endif

    <i class="fa fa-users"></i> <a href="/online">Скрыть гостей</a><br>
@stop
