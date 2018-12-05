@extends('layout')

@section('title')
    Правила игры
@stop

@section('content')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/games">Игры / Развлечения</a></li>
            <li class="breadcrumb-item"><a href="/games/bandit">Однорукий бандит</a></li>
            <li class="breadcrumb-item active">Правила игры</li>
        </ol>
    </nav>

    <h1>Правила игры</h1>

    Правила предельно просты. Нажимайте на кнопку Играть и выигрывайте деньги.<br>
    За каждое нажатие у вас со счета списывают {{ plural(5, setting('moneyname')) }}<br>
    Если у вам повезет и вы выиграете деньги, то то они сразу же будут перечислены вам на счет<br><br>
    Комбинации картинок считаются по вертикали и горизонтали<br><br>
    Список выигрышных комбинаций:<br>

    <img src="/assets/modules/games/bandit/1.gif" alt="image"> * 3 вишенки = {{ plural(10, setting('moneyname')) }} средний ряд/столбец  (5 - нижний или верхний ряд/столбец)<br>
    <img src="/assets/modules/games/bandit/2.gif" alt="image"> * 3 апельсина = {{ plural(15, setting('moneyname')) }} средний ряд/столбец  (10 - нижний или верхний ряд/столбец)<br>
    <img src="/assets/modules/games/bandit/3.gif" alt="image"> * 3 винограда = {{ plural(25, setting('moneyname')) }} средний ряд/столбец  (15 - нижний или верхний ряд/столбец)<br>
    <img src="/assets/modules/games/bandit/4.gif" alt="image"> * 3 банана = {{ plural(35, setting('moneyname')) }} средний ряд/столбец  (25 - нижний или верхний ряд/столбец)<br>
    <img src="/assets/modules/games/bandit/5.gif" alt="image"> * 3 яблока = {{ plural(50, setting('moneyname')) }} средний ряд/столбец  (30 - нижний или верхний ряд/столбец)<br>
    <img src="/assets/modules/games/bandit/6.gif" alt="image"> * 3 BAR = {{ plural(70, setting('moneyname')) }} средний ряд/столбец  (50 - нижний или верхний ряд/столбец)<br>
    <img src="/assets/modules/games/bandit/8.gif" alt="image"> * 3 $$$ = {{ plural(100, setting('moneyname')) }} средний ряд/столбец  (60 - нижний или верхний ряд/столбец)<br>
    <img src="/assets/modules/games/bandit/7.gif" alt="image"> * 3 777 = {{ plural(177, setting('moneyname')) }} средний столбец  (100 - правый или левый столбец)<br>
    <img src="/assets/modules/games/bandit/7.gif" alt="image"> * 3 777 = {{ plural(777, setting('moneyname')) }} средний ряд  (177 - нижний или верхний ряд)<br>
@stop
