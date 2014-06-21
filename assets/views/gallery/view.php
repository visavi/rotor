<?php if (!empty($photo)): ?>
	<?php show_title($photo['photo_title']); ?>

	<?php
	$links = array(
		array('url' => '/admin/gallery.php?act=edit&amp;start='.$start.'&amp;gid='.$photo['photo_id'], 'label' => 'Редактировать', 'show' => is_admin()),
		array('url' => '/admin/gallery.php?act=del&amp;del='.$photo['photo_id'].'&amp;start='.$start.'&amp;uid='.$_SESSION['token'], 'label' => 'Удалить', 'params' => array('onclick' => "return confirm('Вы подтверждаете удаление изображения?')"), 'show' => is_admin()),
		array('url' => 'index.php?act=edit&amp;gid='.$photo['photo_id'].'&amp;start='.$start, 'label' => 'Редактировать', 'show' => (($photo['photo_user'] == $log) && !is_admin())),
		array('url' => 'index.php?act=delphoto&amp;gid='.$photo['photo_id'].'&amp;start='.$start.'&amp;uid='.$_SESSION['token'], 'label' => 'Удалить', 'params' => array('onclick' => "return confirm('Вы подтверждаете удаление изображения?')"), 'show' => (($photo['photo_user'] == $log) && !is_admin())),
	);

	render('includes/link', array('links' => $links));
	?>

	<div>
		<a href="/upload/pictures/<?= $photo['photo_link'] ?>"><img src="/upload/pictures/<?= $photo['photo_link'] ?>" alt="image" /></a><br />

		<?php if (!empty($photo['photo_text'])): ?>
			<?= bb_code($photo['photo_text']) ?><br />
		<?php endif; ?>

		Рейтинг: <a href="index.php?act=vote&amp;gid=<?= $photo['photo_id'] ?>&amp;vote=down&amp;uid=<?= $_SESSION['token'] ?>"><img src="/images/img/thumb-down.gif" alt="Минус" /></a> <big><b><?= format_num($photo['photo_rating']) ?></b></big> <a href="index.php?act=vote&amp;gid=<?= $photo['photo_id'] ?>&amp;vote=up&amp;uid=<?= $_SESSION['token'] ?>"><img src="/images/img/thumb-up.gif" alt="Плюс" /></a><br />

		Размер: <?= read_file(BASEDIR.'/upload/pictures/'.$photo['photo_link']) ?><br />
		Добавлено: <?= profile($photo['photo_user'])?> (<?= date_fixed($photo['photo_time']) ?>)<br />
		<a href="index.php?act=comments&amp;gid=<?= $photo['photo_id'] ?> ">Комментарии</a> (<?= $photo['photo_comments'] ?>)
		<a href="index.php?act=end&amp;gid=<?= $photo['photo_id'] ?>">&raquo;</a>
	</div>
	<br />

	<?php $nav = photo_navigation($photo['photo_id']); ?>
	<?php if ($nav): ?>
		<div class="form" style="text-align:center">

			<?php if ($nav['next']): ?>
				<a href="index.php?act=view&amp;gid=<?= $nav['next'] ?>">&laquo; Назад</a> &nbsp;
			<?php endif; ?>

			<?php if ($nav['prev']): ?>
				&nbsp; <a href="index.php?act=view&amp;gid=<?= $nav['prev'] ?>">Вперед &raquo;</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<img src="/images/img/reload.gif" alt="image" /> <a href="album.php?act=photo&amp;uz=<?= $photo['photo_user'] ?>">В альбом</a><br />

<?php else: ?>
	<?= show_error('Ошибка! Данного изображения нет в базе'); ?>
<?php endif; ?>

<img src="/images/img/back.gif" alt="image" /> <a href="/gallery">В галерею</a><br />
