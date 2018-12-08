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
    <img src="/assets/modules/games/cards/3.png" alt="image"> двойка - 2 очка<br>
    <img src="/assets/modules/games/cards/7.png" alt="image"> тройка - 3 очка<br>
    <img src="/assets/modules/games/cards/11.png" alt="image"> четверка - 5 очка<br>
    <img src="/assets/modules/games/cards/15.png" alt="image"> пятерка - 5 очков<br>
    <img src="/assets/modules/games/cards/19.png" alt="image"> шестерка - 6 очков<br>
    <img src="/assets/modules/games/cards/23.png" alt="image"> семерка - 7 очков<br>
    <img src="/assets/modules/games/cards/27.png" alt="image"> восьмерка - 8 очков<br>
    <img src="/assets/modules/games/cards/31.png" alt="image"> девятка - 9 очков<br>
    <img src="/assets/modules/games/cards/35.png" alt="image"> десятка - 10 очков<br>
    <img src="/assets/modules/games/cards/39.png" alt="image"> валет - 2 очка<br>
    <img src="/assets/modules/games/cards/43.png" alt="image"> дама - 3 очка<br>
    <img src="/assets/modules/games/cards/47.png" alt="image"> король - 4 очка<br>
    <img src="/assets/modules/games/cards/51.png" alt="image"> туз - 11 очков<br><br>

    Сумма очков не зависит от масти карт.<br>
    Для взятия очередной карты нужно нажать кнопку <b>Взять карту</b>.<br>
    Если сумма Ваших очков больше 21, то Вы проиграли - перебор, исключение - 2 туза(22 очка).<br>
    Очко(21) главнее чем 2 туза(22)!<br><br>

    Взяв необходимое количество карт, Вы нажимаете кнопку <b>Открыться</b>, и Банкир открывает свои карты (если Вы набираете 20, 21 или 22 (2 туза) очка то Банкир открывается автоматически).
    Выигрывает тот, у кого больше очков. Победитель забирает кон размером в 2 ставки.
    При равном количестве очков объявляется ничья!<br>
@stop
