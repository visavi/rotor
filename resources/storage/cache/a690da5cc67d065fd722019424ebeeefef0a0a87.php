<?php $__env->startSection('title'); ?>
    Мое меню - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <h1>Мое меню</h1>

    <div class="b"><i class="fa fa-envelope fa-lg text-muted"></i> <b>Почта / Контакты</b></div>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/private">Сообщения</a> (<?=user_mail(App::user())?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/private/send">Отправить письмо</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/contact">Контакт-лист</a> (<?=user_contact(App::user())?>)<br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/ignore">Игнор-лист</a> (<?=user_ignore(App::user())?>)<br>

    <div class="b"><i class="fa fa-wrench fa-lg text-muted"></i> <b>Анкета / Настройки</b></div>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/user/<?= App::getUsername() ?>">Моя анкета</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/profile">Мой профиль</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/account">Мои данные</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/setting">Настройки</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/wall">Моя стена</a> (<?=user_wall(App::user())?>)<br>

    <div class="b"><i class="fa fa-star fa-lg text-muted"></i> <b>Активность</b></div>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/notebook">Блокнот</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/rathist">Голосования</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/reklama">Реклама</a><br>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/authlog">История авторизаций</a><br>

    <div class="b"><i class="fa fa-sign-out fa-lg text-muted"></i> <b>Выход</b></div>
    <i class="fa fa-circle-o fa-lg text-muted"></i> <a href="/logout">Выход [Exit]</a><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>