@section('title')
    Мое меню
@stop

<h1>Мое меню</h1>

<nav>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
        <li class="breadcrumb-item active">Мое меню</li>
    </ol>
</nav>

<div class="b"><i class="fa fa-envelope fa-lg text-muted"></i> <b>Почта / Контакты</b></div>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/messages">Сообщения</a> ({{ getUser()->getCountMessages() }})<br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/contacts">Контакт-лист</a> ({{ getUser()->getCountContact() }})<br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/ignores">Игнор-лист</a> ({{ getUser()->getCountIgnore() }})<br>

<div class="b"><i class="fa fa-wrench fa-lg text-muted"></i> <b>Анкета / Настройки</b></div>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/users/{{ getUser('login') }}">Моя анкета</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/profile">Мой профиль</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/accounts">Мои данные</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/settings">Настройки</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/socials">Социальные сети</a><br>

<div class="b"><i class="fa fa-star fa-lg text-muted"></i> <b>Активность</b></div>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/walls/{{ getUser('login') }}">Моя стена</a> ({{ getUser()->getCountWall() }})<br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/notebooks">Блокнот</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/reklama">Реклама</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/ratings/{{ getUser('login') }}">История репутации</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/authlogs">История авторизаций</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/transfers">Перевод денег</a><br>

<div class="b"><i class="fa fa-sign-out-alt fa-lg text-muted"></i> <b>Выход</b></div>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/logout?token={{ $_SESSION['token'] }}">Выход [Exit]</a><br>
