<?php
$links = [
    ['url' => '/gallery/album?act=photo', 'label' => 'Мои фото', 'show' => is_user()],
    ['url' => '/gallery/comments?act=comments', 'label' => 'Мои комментарии', 'show' => is_user()],
    ['url' => '/gallery/album', 'label' => 'Все альбомы'],
    ['url' => '/gallery/comments', 'label' => 'Все комментарии'],
    ['url' => '/admin/gallery?page='.$page['current'], 'label' => 'Управление', 'show' => is_admin()],
];

render('includes/link', ['links' => $links]);
?>

<?php if ($total > 0): ?>
    <?php foreach($photos as $data): ?>

        <div class="b"><i class="fa fa-picture-o"></i>
            <b><a href="/gallery?act=view&amp;gid=<?= $data['id'] ?>&amp;page=<?= $page['current'] ?>"><?= $data['title'] ?></a></b>
            (<?= read_file(HOME.'/upload/pictures/'.$data['link']) ?>) (Рейтинг: <?= format_num($data['rating']) ?>)
        </div>

        <div>
            <a href="/gallery?act=view&amp;gid=<?= $data['id'] ?>&amp;page=<?= $page['current'] ?>"><?= resize_image('upload/pictures/', $data['link'], App::setting('previewsize'), ['alt' => $data['title']]) ?></a><br />

            <?php if (!empty($data['text'])): ?>
                <?php App::bbCode($data['text']) ?><br />
            <?php endif; ?>

            Добавлено: <?= profile($data['user']) ?> (<?= date_fixed($data['time']) ?>)<br />
            <a href="/gallery?act=comments&amp;gid=<?= $data['id'] ?>">Комментарии</a> (<?= $data['comments'] ?>)
            <a href="/gallery?act=end&amp;gid=<?= $data['id'] ?>">&raquo;</a>
        </div>
    <?php endforeach; ?>

    <?php App::pagination($page) ?>

    Всего фотографий: <b><?= $total ?></b><br /><br />

<?php else: ?>
    <?= show_error('Фотографий нет, будь первым!'); ?>
<?php endif; ?>

<?php
$links = [
    ['url' => '/gallery/top', 'label' => 'Топ фото'],
    ['url' => '/gallery?act=addphoto', 'label' => 'Добавить фото'],
];

render('includes/link', ['links' => $links]);
?>
