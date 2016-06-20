<a href="index.php?rand=<?=mt_rand(100, 999)?>">Обновить</a>  /
<a href="/pages/rules.php">Правила</a> /
<a href="/pages/smiles.php">Смайлы</a> /
<a href="/pages/tags.php">Теги</a>

<?php if (is_admin()):?>
	/ <a href="/admin/book.php?start=<?=$start?>">Управление</a>
<?php endif;?>
<hr />


<?php if ($total > 0): ?>
	<?php foreach ($posts as $data): ?>
		<div class="post">
			<div class="b">

				<?php if (!empty($log) && $log != $data['guest_user']): ?>

					<div class="pull-right">
						<a href="#" onclick="return postReply('<?= nickname($data['guest_user']) ?>')" title="Ответить"><i class="fa fa-reply text-muted"></i></a>

						<a href="#" onclick="return postQuote(this)" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>

						<noindex><a href="index.php?act=spam&amp;id=<?=$data['guest_id']?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>" onclick="return confirm('Вы подтверждаете факт спама?')" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a></noindex>
					</div>

				<?php endif; ?>

				<?php if ($log == $data['guest_user'] && $data['guest_time'] + 600 > SITETIME): ?>
					<div class="pull-right">
						<a href="index.php?act=edit&amp;id=<?=$data['guest_id']?>&amp;start=<?=$start?>" title="Редактировать"><i class="fa fa-pencil text-muted"></i></a>
					</div>
				<?php endif; ?>

				<div class="img"><?=user_avatars($data['guest_user'])?></div>

				<?php if ($data['guest_user'] == $config['guestsuser']): ?>
					<b><?=$data['guest_user']?></b> <small>(<?=date_fixed($data['guest_time'])?>)</small>
				<?php else: ?>
					<b><?=profile($data['guest_user'])?></b> <small>(<?=date_fixed($data['guest_time'])?>)</small><br />
					<?=user_title($data['guest_user'])?> <?=user_online($data['guest_user'])?>
				<?php endif; ?>
			</div>

			<div class="message"><?=bb_code($data['guest_text'])?></div>

			<?php if (!empty($data['guest_edit'])): ?>
				<small><i class="fa fa-exclamation-circle text-danger"></i> Отредактировано: <?=nickname($data['guest_edit'])?> (<?=date_fixed($data['guest_edit_time'])?>)</small><br />
			<?php endif; ?>

			<?php if (is_admin() || empty($config['anonymity'])): ?>
				<span class="data">(<?=$data['guest_brow']?>, <?=$data['guest_ip']?>)</span>
			<?php endif; ?>

			<?php if (!empty($data['guest_reply'])): ?>
				<br /><span style="color:#ff0000">Ответ: <?=bb_code($data['guest_reply'])?></span>
			<?php endif; ?>

		</div>
	<?php endforeach; ?>

	<?php page_strnavigation('index.php?', $config['bookpost'], $start, $total); ?>

<?php else: ?>
	<?php show_error('Сообщений нет, будь первым!'); ?>
<?php endif; ?>


<?php if (is_user()): ?>
	<div class="form">
		<form action="index.php?act=add&amp;uid=<?=$_SESSION['token']?>" method="post">

		<textarea id="markItUp" cols="25" rows="5" name="msg"></textarea>
		<input class="btn btn-primary" type="submit" value="Написать" /></form>
	</div><br />

<?php elseif ($config['bookadds'] == 1): ?>

	<div class="form">
		<form action="index.php?act=add&amp;uid=<?=$_SESSION['token']?>" method="post">
		Сообщение:<br />
		<textarea cols="25" rows="5" name="msg"></textarea><br />

		Проверочный код:<br />
		<img src="/gallery/protect.php" alt="" /><br />
		<input name="provkod" size="6" maxlength="6" />

		<input class="btn btn-primary" type="submit" value="Написать" /></form>
	</div><br />

<?php else: ?>
	<?php show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо'); ?>
<?php endif; ?>

