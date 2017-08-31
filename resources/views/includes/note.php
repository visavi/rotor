<?php if (isUser()): ?>
    <?php if (!empty(user('newprivat'))): ?>
        <?php if (! App\Classes\Request::is('ban', 'key', 'private', 'rules', 'closed', 'login', 'register')): ?>
            <i class="fa fa-envelope"></i> <b><a href="/private"><span style="color:#ff0000">Приватное сообщение! (<?=user('newprivat')?>)</span></a></b><br>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty(user('newwall'))): ?>
        <?php if (! App\Classes\Request::is('ban', 'key', 'wall', 'rules', 'closed', 'login', 'register')): ?>
            <i class="fa fa-users"></i> <b><a href="/wall"><span style="color:#ff0000">Запись на стене! (<?=user('newwall')?>)</span></a></b><br>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
