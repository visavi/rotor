<div class="menu">
<?php if (is_user()): ?>
	<?= user_gender($log) ?>
	<?= profile($log) ?> &bull;
	<?php if (is_admin()): ?>
		<a href="/admin">Панель</a> &bull;
		<?php if (stats_spam()>0): ?>
			<a href="/admin/spam.php"><span style="color:#ff0000">Спам!</span></a> &bull;
		<?php endif; ?>
		<?php if ($udata['users_newchat']<stats_newchat()): ?>
			<a href="/admin/chat.php"><span style="color:#ff0000">Чат</span></a> &bull;
		<?php endif; ?>
	<?php endif; ?>
		<a href="/menu">Меню</a> &bull;
		<a href="/logout" onclick="return confirm('Вы действительно хотите выйти?')">Выход</a>
<?php else: ?>
	<i class="fa fa-lock fa-lg"></i> <a href="/login" rel="nofollow">Авторизация</a> &bull;
	<a href="/register" rel="nofollow">Регистрация</a>
<?php endif; ?>
</div>
