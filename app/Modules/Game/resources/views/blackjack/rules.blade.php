@extends('layout')

@section('title')
    Правила игры
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/games">Игры / Развлечения</a></li>
            <li class="breadcrumb-item"><a href="/games/blackjack">21 (Очко)</a></li>
            <li class="breadcrumb-item active">Правила игры</li>
        </ol>
    </nav>

    <h1>Правила игры</h1>

    Для участия в игре сделайте ставку и нажмите <b>Играть</b><br>
    Ваша ставка будет получена Банкиром и он начнет сдавать Вам карты.<br>
    В игре участвуют двое - Вы и Банкир, на кону - двойная ставка (Ваша ставка и ставка Банкира). Взяв карты, Вы подсчитываете суммарное количество их очков.<br><br>

    <b>Очки считаются следующим образом:</b><br>
    <img src="/assets/modules/games/cards/2.gif" alt="image"> шестерка - 6 очков<br>
    <img src="/assets/modules/games/cards/6.gif" alt="image"> семерка - 7 очков<br>
    <img src="/assets/modules/games/cards/10.gif" alt="image"> восьмерка - 8 очков<br>
    <img src="/assets/modules/games/cards/14.gif" alt="image"> девятка - 9 очков<br>
    <img src="/assets/modules/games/cards/18.gif" alt="image"> десятка - 10 очков<br>
    <img src="/assets/modules/games/cards/22.gif" alt="image"> валет - 2 очков<br>
    <img src="/assets/modules/games/cards/26.gif" alt="image"> дама - 3 очков<br>
    <img src="/assets/modules/games/cards/30.gif" alt="image"> король - 4 очков<br>
    <img src="/assets/modules/games/cards/34.gif" alt="image"> туз - 11 очков.<br><br>

    Сумма очков не зависит от масти карт.<br>
    Для взятия очередной карты нужно нажать кнопку <b>Взять карту</b>.<br>
    Если сумма Ваших очков больше 21, то Вы проиграли - перебор, исключение - 2 туза(22 очка).<br>
    Очко(21) главнее чем 2 туза(22)!<br><br>

    Взяв необходимое количество карт, Вы нажимаете кнопку <b>Открыться</b>, и Банкир открывает свои карты (если Вы набираете 20, 21 или 22 (2 туза) очка то Банкир открывается автоматически).
    Выигрывает тот, у кого больше очков. Победитель забирает кон размером в 2 ставки.
    При равном количестве очков объявляется ничья!<br>
@stop
