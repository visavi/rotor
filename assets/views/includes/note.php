<?php if (isset($_SESSION['note'])): ?>
    <?php if (! is_array($_SESSION['note'])) {
        $_SESSION['note'] = array('success' => $_SESSION['note']);
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
    <?php if (!empty(App::user('users_newprivat'))): ?>
        <?php if (!strsearch(App::server('PHP_SELF'), ['/ban', '/key', '/private', '/rules', '/closed'])): ?>
            <i class="fa fa-envelope"></i> <b><a href="/private"><span style="color:#ff0000">Приватное сообщение! (<?=App::user('users_newprivat')?>)</span></a></b><br />
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty(App::user('users_newwall'))): ?>
        <?php if (!strsearch(App::server('PHP_SELF'), ['/ban', '/key', '/wall', '/rules', '/closed'])): ?>
            <i class="fa fa-users"></i> <b><a href="/wall"><span style="color:#ff0000">Запись на стене! (<?=App::user('users_newwall')?>)</span></a></b><br />
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
