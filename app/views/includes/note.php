<?php if (is_user()): ?>
    <?php if (!empty(App::user('newprivat'))): ?>
        <?php if (! Request::is('ban', 'key', 'private', 'rules', 'closed', 'login', 'register')): ?>
            <i class="fa fa-envelope"></i> <b><a href="/private"><span style="color:#ff0000">Приватное сообщение! (<?=App::user('newprivat')?>)</span></a></b><br>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty(App::user('newwall'))): ?>
        <?php if (! Request::is('ban', 'key', 'wall', 'rules', 'closed', 'login', 'register')): ?>
            <i class="fa fa-users"></i> <b><a href="/wall"><span style="color:#ff0000">Запись на стене! (<?=App::user('newwall')?>)</span></a></b><br>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
