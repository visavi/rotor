<?php if (is_user()): ?>
	<?php if (!empty($udata['users_newprivat'])): ?>
		<?php if (!strsearch(App::server('PHP_SELF'), ['/pages/ban.php', '/pages/key.php', '/pages/private.php', '/pages/rules.php', '/pages/closed.php'])): ?>
			<i class="fa fa-envelope"></i> <b><a href="/private"><span style="color:#ff0000">Приватное сообщение! (<?=$udata['users_newprivat']?>)</span></a></b><br />
		<?php endif; ?>
	<?php endif; ?>

	<?php if (!empty($udata['users_newwall'])): ?>
		<?php if (!strsearch(App::server('PHP_SELF'), ['/pages/ban.php', '/pages/key.php', '/pages/wall.php', '/pages/rules.php', '/pages/closed.php'])): ?>
			<i class="fa fa-users"></i> <b><a href="/wall"><span style="color:#ff0000">Запись на стене! (<?=$udata['users_newwall']?>)</span></a></b><br />
		<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>
