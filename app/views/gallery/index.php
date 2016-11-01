<?php
$links = array(
    array('url' => '/gallery/album?act=photo', 'label' => 'Мои фото', 'show' => is_user()),
    array('url' => '/gallery/comments?act=comments', 'label' => 'Мои комментарии', 'show' => is_user()),
    array('url' => '/gallery/album', 'label' => 'Все альбомы'),
    array('url' => '/gallery/comments', 'label' => 'Все комментарии'),
    array('url' => '/admin/gallery?start='.$start, 'label' => 'Управление', 'show' => is_admin()),
);

render('includes/link', array('links' => $links));
?>

<?php if ($total > 0): ?>
    <?php foreach($photos as $data): ?>

        <div class="b"><i class="fa fa-picture-o"></i>
            <b><a href="/gallery?act=view&amp;gid=<?= $data['photo_id'] ?>&amp;start=<?= $start ?>"><?= $data['photo_title'] ?></a></b>
            (<?= read_file(HOME.'/upload/pictures/'.$data['photo_link']) ?>) (Рейтинг: <?= format_num($data['photo_rating']) ?>)
        </div>

        <div>
            <a href="/gallery?act=view&amp;gid=<?= $data['photo_id'] ?>&amp;start=<?= $start ?>"><?= resize_image('upload/pictures/', $data['photo_link'], App::setting('previewsize'), array('alt' => $data['photo_title'])) ?></a><br />

            <?php if (!empty($data['photo_text'])): ?>
                <?php bb_code($data['photo_text']) ?><br />
            <?php endif; ?>

            Добавлено: <?= profile($data['photo_user']) ?> (<?= date_fixed($data['photo_time']) ?>)<br />
            <a href="/gallery?act=comments&amp;gid=<?= $data['photo_id'] ?>">Комментарии</a> (<?= $data['photo_comments'] ?>)
            <a href="/gallery?act=end&amp;gid=<?= $data['photo_id'] ?>">&raquo;</a>
        </div>
    <?php endforeach; ?>

    <?php page_strnavigation('/gallery?', App::setting('fotolist'), $start, $total); ?>

    Всего фотографий: <b><?= $total ?></b><br /><br />

<?php else: ?>
    <?= show_error('Фотографий нет, будь первым!'); ?>
<?php endif; ?>

<?php
$links = array(
    array('url' => '/gallery/top', 'label' => 'Топ фото'),
    array('url' => '/gallery?act=addphoto', 'label' => 'Добавить фото'),
);

render('includes/link', array('links' => $links));
?>
