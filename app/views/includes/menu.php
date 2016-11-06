<div class="menu">
<?php if (is_user()): ?>
	<?= user_gender(App::getUsername()) ?>
	<?= profile(App::getUsername()) ?> &bull;
	<?php if (is_admin()): ?>
		<a href="/admin">Панель</a> &bull;
		<?php if (stats_spam()>0): ?>
			<a href="/admin/spam"><span style="color:#ff0000">Спам!</span></a> &bull;
		<?php endif; ?>
		<?php if (App::user('newchat')<stats_newchat()): ?>
			<a href="/admin/chat"><span style="color:#ff0000">Чат</span></a> &bull;
		<?php endif; ?>
	<?php endif; ?>
		<a href="/menu">Меню</a> &bull;
		<a href="/logout" onclick="return logout(this)">Выход</a>
<?php else: ?>
	<i class="fa fa-lock fa-lg"></i> <a href="/login" rel="nofollow">Авторизация</a> &bull;
	<a href="/register" rel="nofollow">Регистрация</a>
<?php endif; ?>
</div>
