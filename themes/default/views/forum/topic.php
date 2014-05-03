<a href="index.php">Форум</a> /

<?php if (!empty($topics['subparent'])): ?>
	<a href="forum.php?fid=<?=$topics['subparent']['forums_id']?>"><?=$topics['subparent']['forums_title']?></a> /
<?php endif; ?>

<a href="forum.php?fid=<?=$topics['forums_id']?>"><?=$topics['forums_title']?></a> /
<a href="topic.php?tid=<?=$tid?>&amp;start=<?=$start?>&amp;rand=<?=mt_rand(1000, 9999)?>">Обновить</a> /
<a href="print.php?tid=<?=$tid?>">Скачать</a> / <a href="rss.php?tid=<?=$tid?>">RSS-лента</a><br /><br />

<img src="/images/img/themes.gif" alt="image" /> <b><?=$topics['topics_title']?></b>


<?php if (is_user()): ?>
	(
	<?php if ($topics['topics_author'] == $log && empty($topics['topics_closed']) && $udata['users_point'] >= $config['editforumpoint']): ?>
		<a href="topic.php?act=closed&amp;tid=<?=$tid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>">Закрыть</a> /
		<a href="topic.php?act=edittopic&amp;tid=<?=$tid?>">Изменить</a> /
	<?php endif; ?>

	<?php if (empty($topics['bookmark'])): ?>
		<a href="bookmark.php?act=add&amp;tid=<?=$tid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>">В закладки</a>
	<?php else: ?>
		<a href="bookmark.php?act=remove&amp;tid=<?=$tid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>">Из закладок</a>
	<?php endif; ?>

	)<br />
<?php endif; ?>

<?php if (!empty($topics['curator'])): ?>
	Кураторы темы:
	<?php foreach ($topics['curator'] as $key => $curator): ?>
		<?php $comma = (empty($key)) ? '' : ', '; ?>
		<?=$comma?><?=profile($curator)?>
	<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($topics['topics_note'])): ?>
	<div class="info"><?=bb_code($topics['topics_note'])?></div>
<?php endif; ?>

<hr />

<?php if (is_admin()): ?>
	<?php if (empty($topics['topics_closed'])): ?>
		<a href="/admin/forum.php?act=acttopic&amp;do=closed&amp;tid=<?=$tid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>">Закрыть</a> /
	<?php else: ?>
		<a href="/admin/forum.php?act=acttopic&amp;do=open&amp;tid=<?=$tid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>">Открыть</a> /
	<?php endif; ?>

	<?php if (empty($topics['topics_locked'])): ?>
		<a href="/admin/forum.php?act=acttopic&amp;do=locked&amp;tid=<?=$tid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>">Закрепить</a> /
	<?php else: ?>
		<a href="/admin/forum.php?act=acttopic&amp;do=unlocked&amp;tid=<?=$tid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>">Открепить</a> /
	<?php endif; ?>

	<a href="/admin/forum.php?act=edittopic&amp;tid=<?=$tid?>&amp;start=<?=$start?>">Изменить</a> /
	<a href="/admin/forum.php?act=movetopic&amp;tid=<?=$tid?>">Переместить</a> /
	<a href="/admin/forum.php?act=deltopics&amp;fid=<?=$topics['forums_id']?>&amp;del=<?=$tid?>&amp;uid=<?=$_SESSION['token']?>" onclick="return confirm('Вы действительно хотите удалить данную тему?')">Удалить</a> /
	<a href="/admin/forum.php?act=topic&amp;tid=<?=$tid?>&amp;start=<?=$start?>">Управление</a><br />
<?php endif; ?>

<?php if (!empty($params['topics']['is_moder'])): ?>
	<form action="topic.php?act=del&amp;tid=<?=$params['tid']?>&amp;start=<?=$params['start']?>&amp;uid=<?=$_SESSION['token']?>" method="post">
<?php endif; ?>

<?php if ($params['total'] > 0): ?>
<?php foreach ($params['topics']['posts'] as $key=>$data): ?>
	<?php $num = ($params['start'] + $key + 1); ?>

	<div class="b" id="post_<?=$data['posts_id']?>">
		<div class="img"><?=user_avatars($data['posts_user'])?></div>

		<?php if (!empty($topics['is_moder'])): ?>
			<span class="imgright">
				<a href="topic.php?act=modedit&amp;tid=<?=$tid?>&amp;pid=<?=$data['posts_id']?>&amp;start=<?=$start?>">Ред.</a> <input type="checkbox" name="del[]" value="<?=$data['posts_id']?>" />
			</span>
		<?php endif; ?>

		<?=$num?>. <b><?=profile($data['posts_user'])?></b> <small>(<?=date_fixed($data['posts_time'])?>)</small><br />
		<?=user_title($data['posts_user'])?> <?=user_online($data['posts_user'])?></div>

		<?php if (!empty($log) && $log != $data['posts_user']): ?>
			<div class="right">
				<a href="topic.php?act=reply&amp;tid=<?=$tid?>&amp;pid=<?=$data['posts_id']?>&amp;start=<?=$start?>&amp;num=<?=$num?>">Отв</a> /
				<a href="topic.php?act=quote&amp;tid=<?=$tid?>&amp;pid=<?=$data['posts_id']?>&amp;start=<?=$start?>">Цит</a> /
				<noindex><a href="topic.php?act=spam&amp;tid=<?=$tid?>&amp;pid=<?=$data['posts_id']?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>" onclick="return confirm('Вы подтверждаете факт спама?')" rel="nofollow">Спам</a></noindex>
			</div>
		<?php endif; ?>

		<?php if ($log == $data['posts_user'] && $data['posts_time'] + 600 > SITETIME): ?>
			<div class="right">
				<a href="topic.php?act=edit&amp;tid=<?=$tid?>&amp;pid=<?=$data['posts_id']?>&amp;start=<?=$start?>">Редактировать</a>
			</div>
		<?php endif; ?>

		<div><?=bb_code($data['posts_text'])?><br />

		<?php if (!empty($topics['posts_files'])): ?>
			<?php if (isset($topics['posts_files'][$data['posts_id']])): ?>
				<div class="hide"><img src="/images/img/paper-clip.gif" alt="attach" /> <b>Прикрепленные файлы:</b><br />
				<?php foreach ($topics['posts_files'][$data['posts_id']] as $file): ?>
					<?php $ext = getExtension($file['file_hash']); ?>
					<img src="/images/icons/<?=icons($ext)?>" alt="image" />

					<a href="/upload/forum/<?=$topics['topics_id']?>/<?=$file['file_hash']?>"><?=$file['file_name']?></a> (<?=formatsize($file['file_size'])?>)<br />
				<?php endforeach; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if (!empty($data['posts_edit'])): ?>
			<img src="/images/img/exclamation_small.gif" alt="image" /> <small>Отредактировано: <?=nickname($data['posts_edit'])?> (<?=date_fixed($data['posts_edit_time'])?>)</small><br />
		<?php endif; ?>

		<?php if (is_admin() || empty($config['anonymity'])): ?>
			<span class="data">(<?=$data['posts_brow']?>, <?=$data['posts_ip']?>)</span>
		<?php endif; ?>

		</div>
	<?php endforeach; ?>

<?php else: ?>
	<?php show_error('Сообщений еще нет, будь первым!'); ?>
<?php endif; ?>

<?php if (!empty($topics['is_moder'])): ?>
	<span class="imgright">
		<input type="submit" value="Удалить выбранное" />
	</span>
	</form>
<?php endif; ?>

<?php page_strnavigation('topic.php?tid='.$tid.'&amp;', $config['forumpost'], $start, $total); ?>


<?php if (is_user()): ?>
	<?php if (empty($topics['topics_closed'])): ?>
		<div class="form">
			<form action="topic.php?act=add&amp;tid=<?=$tid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>" method="post">
			<textarea name="msg" cols="25" rows="5" id="markItUp"></textarea><br />

			<input type="submit" value="Написать" />

		<?php if ($udata['users_point'] >= $config['forumloadpoints']): ?>
			<span class="imgright">
				<a href="topic.php?act=addfile&amp;tid=<?=$tid?>&amp;start=<?=$start?>">Загрузить файл</a>
			</span>
		<?php endif; ?>

			</form>
		</div><br />

	<?php else: ?>
		<?php show_error('Данная тема закрыта для обсуждения!'); ?>
	<?php endif; ?>
<?php else: ?>
	<?php show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо'); ?>
<?php endif; ?>

<a href="/pages/smiles.php">Смайлы</a>  /
<a href="/pages/tags.php">Теги</a>  /
<a href="/pages/rules.php">Правила</a> /
<a href="top.php?act=themes">Топ тем</a> /
<a href="search.php?fid=<?=$topics['forums_id']?>">Поиск</a><br />
