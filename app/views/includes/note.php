<?php if (isset($_SESSION['note'])): ?>
    <?php if (! is_array($_SESSION['note'])) {
        $_SESSION['note'] = ['success' => $_SESSION['note']];
    }?>

    <?php foreach ($_SESSION['note'] as $status => $messages): ?>
        <?php if (is_array($messages)): ?>
            <?php $messages = implode('</div><div>', $messages); ?>
        <?php endif; ?>
        <div class="alert alert-<?= $status ?> alert-block">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <div><?= $messages ?></div>
        </div>
    <?php endforeach ?>
<?php endif; ?>

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
