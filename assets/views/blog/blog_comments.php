<img src="/images/img/edit.gif" alt="Статья" /> <b><a href="blog.php?act=view&amp;id=<?=$blogs['blogs_id']?>"><?=$blogs['blogs_title']?></a></b><br /><br />

<a href="blog.php?act=comments&amp;id=<?=$blogs['blogs_id']?>&amp;rand=<?=mt_rand(100, 999)?>">Обновить</a> / <a href="rss.php?id=<?=$blogs['blogs_id']?>">RSS-лента</a><hr />

<?php if ($is_admin): ?>
	<form action="blog.php?act=del&amp;id=<?=$blogs['blogs_id']?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>" method="post">
<?php endif; ?>

<?php foreach ($comments as $data): ?>

	<div class="b">
		<div class="img"><?=user_avatars($data['commblog_author'])?></div>

		<?php if ($is_admin): ?>
			<span class="imgright"><input type="checkbox" name="del[]" value="<?=$data['commblog_id']?>" /></span>
		<?php endif; ?>

		<b><?=profile($data['commblog_author'])?></b> <small>(<?=date_fixed($data['commblog_time'])?>)</small><br />
		<?=user_title($data['commblog_author'])?> <?=user_online($data['commblog_author'])?>
	</div>

		<?php if (!empty($log) && $log != $data['commblog_author']): ?>
			<div class="right">
				<a href="blog.php?act=reply&amp;id=<?=$blogs['blogs_id']?>&amp;pid=<?=$data['commblog_id']?>&amp;start=<?=$start?>">Отв</a> /
				<a href="blog.php?act=quote&amp;id=<?=$blogs['blogs_id']?>&amp;pid=<?=$data['commblog_id']?>&amp;start=<?=$start?>">Цит</a> /
				<noindex><a href="blog.php?act=spam&amp;id=<?=$blogs['blogs_id']?>&amp;pid=<?=$data['commblog_id']?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>" onclick="return confirm('Вы подтверждаете факт спама?')" rel="nofollow">Спам</a></noindex>
			</div>
		<?php endif; ?>

		<?php if ($log == $data['commblog_author'] && $data['commblog_time'] + 600 > SITETIME): ?>
			<div class="right">
				<a href="blog.php?act=edit&amp;id=<?=$blogs['blogs_id']?>&amp;pid=<?=$data['commblog_id']?>&amp;start=<?=$start?>">Редактировать</a>
			</div>
		<?php endif; ?>

		<div>
			<?=bb_code($data['commblog_text'])?><br />

		<?php if (is_admin() || empty($config['anonymity'])): ?>
			<span class="data">(<?=$data['commblog_brow']?>, <?=$data['commblog_ip']?>)</span>
		<?php endif; ?>

	</div>
<?php endforeach; ?>

<?php if ($is_admin): ?>
	<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>
<?php endif; ?>
