<?php
$links = array(
	array('url' => '/gallery/album.php?act=photo', 'label' => 'Мои фото', 'show' => is_user()),
	array('url' => '/gallery/comments.php?act=comments', 'label' => 'Мои комментарии', 'show' => is_user()),
	array('url' => '/gallery/album.php', 'label' => 'Все альбомы'),
	array('url' => '/gallery/comments.php', 'label' => 'Все комментарии'),
	array('url' => '/admin/gallery.php?start='.$start, 'label' => 'Управление', 'show' => is_admin()),
);

render('includes/link', array('links' => $links));
?>

<?php if ($total > 0): ?>
	<?php foreach($photos as $data): ?>

		<div class="b"><img src="/images/img/gallery.gif" alt="image" />
			<b><a href="index.php?act=view&amp;gid=<?= $data['photo_id'] ?>&amp;start=<?= $start ?>"><?= $data['photo_title'] ?></a></b>
			(<?= read_file(BASEDIR.'/upload/pictures/'.$data['photo_link']) ?>) (Рейтинг: <?= format_num($data['photo_rating']) ?>)
		</div>

		<div>
			<a href="index.php?act=view&amp;gid=<?= $data['photo_id'] ?>&amp;start=<?= $start ?>"><?= resize_image('upload/pictures/', $data['photo_link'], $config['previewsize'], $data['photo_title']) ?></a><br />

			<?php if (!empty($data['photo_text'])): ?>
				<?php bb_code($data['photo_text']) ?><br />
			<?php endif; ?>

			Добавлено: <?= profile($data['photo_user']) ?> (<?= date_fixed($data['photo_time']) ?>)<br />
			<a href="index.php?act=comments&amp;gid=<?= $data['photo_id'] ?>">Комментарии</a> (<?= $data['photo_comments'] ?>)
			<a href="index.php?act=end&amp;gid=<?= $data['photo_id'] ?>">&raquo;</a>
		</div>
	<?php endforeach; ?>

	<?php page_strnavigation('index.php?', $config['fotolist'], $start, $total); ?>

	Всего фотографий: <b><?= $total ?></b><br /><br />

<?php else: ?>
	<?= show_error('Фотографий нет, будь первым!'); ?>
<?php endif; ?>

<?php
$links = array(
	array('url' => '/gallery/top.php', 'label' => 'Топ фото'),
	array('url' => '/gallery/?act=addphoto', 'label' => 'Добавить фото'),
);

render('includes/link', array('links' => $links));
?>
