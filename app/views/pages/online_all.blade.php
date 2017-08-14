@extends('layout')

@section('title')
    Кто в онлайне - @parent
@stop

@section('content')

    <h1>Кто в онлайне</h1>

    Всего на сайте: <b>{{ $page['total'] }}</b><br />
    Зарегистрированных:  <b>{{ $registered }}</b><br /><br />


    @if ($online->isNotEmpty())

        @foreach ($online as $data)

            <div class="b">
                @if ($data->user)
                    {!!  user_gender($data->user) !!} <b>{!! profile($data->user) !!}</b> (Время: {{ date_fixed($data['updated_at'], 'H:i:s') }})
                @else
                    <i class="fa fa-user-circle-o"></i> <b>{{ Setting::get('guestsuser') }}</b>  (Время: {{ date_fixed($data['updated_at'], 'H:i:s') }})
                @endif
            </div>

            @if (is_admin())
                <div>
                    <span class="data">({{ $data['brow'] }}, {{ $data['ip'] }})</span>
                </div>
            @endif
        @endforeach
        {{ App::pagination($page) }}
    @else
        {{ show_error('На сайте никого нет!') }}
    @endif

    <i class="fa fa-users"></i> <a href="/online">Скрыть гостей</a><br />
@stop
