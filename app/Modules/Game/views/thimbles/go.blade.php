@extends('layout')

@section('title')
    Игра
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/games">Игры / Развлечения</a></li>
            <li class="breadcrumb-item"><a href="/games/thimbles">Наперстки</a></li>
            <li class="breadcrumb-item"><a href="/games/thimbles/choice">Выбор наперстка</a></li>
            <li class="breadcrumb-item active">Игра</li>
        </ol>
    </nav>

    <h1>Игра</h1>

    <a href="/games/thimbles/go?thimble=1&amp;rand={{ random_int(1000, 99999) }}"><img src="/assets/modules/games/thimbles/{{ $randThimble === 1 ? 3 : 2 }}.gif" alt="image"></a>
    <a href="/games/thimbles/go?thimble=2&amp;rand={{ random_int(1000, 99999) }}"><img src="/assets/modules/games/thimbles/{{ $randThimble === 2 ? 3 : 2 }}.gif" alt="image"></a>
    <a href="/games/thimbles/go?thimble=3&amp;rand={{ random_int(1000, 99999) }}"><img src="/assets/modules/games/thimbles/{{ $randThimble === 3 ? 3 : 2 }}.gif" alt="image"></a><br><br>

    Выберите наперсток в котором может находится шарик<br><br>

    <div class="font-weight-bold">
        <i class="fas fa-trophy"></i> {!! $result !!}
    </div>

    У вас в наличии: {{ plural($user->money, setting('moneyname')) }}<br><br>
@stop
