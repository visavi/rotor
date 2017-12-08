@section('title')
    Мое меню
@stop

<h1>Мое меню</h1>

<div class="b"><i class="fa fa-envelope fa-lg text-muted"></i> <b>Почта / Контакты</b></div>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/private">Сообщения</a> ({{ userMail(getUser()) }})<br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/private/send">Отправить письмо</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/contact">Контакт-лист</a> ({{ userContact(getUser()) }})<br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/ignore">Игнор-лист</a> ({{ userIgnore(getUser()) }})<br>

<div class="b"><i class="fa fa-wrench fa-lg text-muted"></i> <b>Анкета / Настройки</b></div>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/user/{{ getUser('login') }}">Моя анкета</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/profile">Мой профиль</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/account">Мои данные</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/setting">Настройки</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/wall/{{ getUser('login') }}">Моя стена</a> ({{ userWall(getUser()) }})<br>

<div class="b"><i class="fa fa-star fa-lg text-muted"></i> <b>Активность</b></div>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/notebook">Блокнот</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/reklama">Реклама</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/rating/{{ getUser('login') }}">История репутации</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/authlog">История авторизаций</a><br>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/transfer">Перевод денег</a><br>

<div class="b"><i class="fa fa-sign-out-alt fa-lg text-muted"></i> <b>Выход</b></div>
<i class="far fa-circle fa-lg text-muted"></i> <a href="/logout">Выход [Exit]</a><br>
