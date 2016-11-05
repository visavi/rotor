<i class="fa fa-pencil"></i> <b><a href="/blog/blog?act=view&amp;id=<?=$blogs['id']?>"><?=$blogs['title']?></a></b><br /><br />

<a href="/blog/blog?act=comments&amp;id=<?=$blogs['id']?>&amp;rand=<?=mt_rand(100, 999)?>">Обновить</a> / <a href="/blog/rss?id=<?=$blogs['id']?>">RSS-лента</a><hr />

<?php if ($is_admin): ?>
	<form action="/blog/blog?act=del&amp;id=<?=$blogs['id']?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>" method="post">
<?php endif; ?>

<?php foreach ($comments as $data): ?>

	<div class="b">
		<div class="img"><?=user_avatars($data['author'])?></div>

		<?php if ($is_admin): ?>
			<span class="imgright"><input type="checkbox" name="del[]" value="<?=$data['id']?>" /></span>
		<?php endif; ?>

		<b><?=profile($data['author'])?></b> <small>(<?=date_fixed($data['time'])?>)</small><br />
		<?=user_title($data['author'])?> <?=user_online($data['author'])?>
	</div>

		<?php if (!empty($log) && $log != $data['author']): ?>
			<div class="right">
				<a href="/blog/blog?act=reply&amp;id=<?=$blogs['id']?>&amp;pid=<?=$data['id']?>&amp;start=<?=$start?>">Отв</a> /
				<a href="/blog/blog?act=quote&amp;id=<?=$blogs['id']?>&amp;pid=<?=$data['id']?>&amp;start=<?=$start?>">Цит</a> /
				<noindex><a href="/blog/blog?act=spam&amp;id=<?=$blogs['id']?>&amp;pid=<?=$data['id']?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>" onclick="return confirm('Вы подтверждаете факт спама?')" rel="nofollow">Спам</a></noindex>
			</div>
		<?php endif; ?>

		<?php if ($log == $data['author'] && $data['time'] + 600 > SITETIME): ?>
			<div class="right">
				<a href="/blog/blog?act=edit&amp;id=<?=$blogs['id']?>&amp;pid=<?=$data['id']?>&amp;start=<?=$start?>">Редактировать</a>
			</div>
		<?php endif; ?>

		<div>
			<?=bb_code($data['text'])?><br />

		<?php if (is_admin() || empty($config['anonymity'])): ?>
			<span class="data">(<?=$data['brow']?>, <?=$data['ip']?>)</span>
		<?php endif; ?>

	</div>
<?php endforeach; ?>

<?php if ($is_admin): ?>
	<span class="imgright"><input type="submit" value="Удалить выбранное" /></span></form>
<?php endif; ?>
