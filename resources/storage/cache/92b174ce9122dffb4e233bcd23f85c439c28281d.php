<?php $__env->startSection('title'); ?>
    Обратная связь - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Обратная связь</h1>

<?php
    echo '<div class="form">';
        echo '<form method="post" action="/mail">';

            if (! is_user()) {
            echo 'Ваше имя:<br><input name="name" maxlength="20"><br>';
            echo 'Ваш email:<br><input name="email" maxlength="50"><br>';
            } else {
            if (empty(App::user('email'))) {
            echo 'Ваш email:<br><input name="email" maxlength="50"><br>';
            }
            }

            echo 'Сообщение:<br>';
            echo '<textarea cols="25" rows="5" name="message"></textarea><br>';

            echo 'Проверочный код:<br>';
            echo '<img src="/captcha" onclick="this.src=\'/captcha?\'+Math.random()" class="rounded" alt="" style="cursor: pointer;" alt=""><br>';

            echo '<input name="protect" size="6" maxlength="6"><br>';
            echo '<input value="Отправить" type="submit"></form></div><br>';

    echo 'Обновите страницу если вы не видите проверочный код!<br><br>';
?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>