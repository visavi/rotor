<?php if (!empty($photo)): ?>
    <?php show_title($photo['photo_title']); ?>

    <?php
    $links = array(
        array('url' => '/admin/gallery?act=edit&amp;start='.$start.'&amp;gid='.$photo['photo_id'], 'label' => 'Редактировать', 'show' => is_admin()),
        array('url' => '/admin/gallery?act=del&amp;del='.$photo['photo_id'].'&amp;start='.$start.'&amp;uid='.$_SESSION['token'], 'label' => 'Удалить', 'params' => array('onclick' => "return confirm('Вы подтверждаете удаление изображения?')"), 'show' => is_admin()),
        array('url' => '/gallery?act=edit&amp;gid='.$photo['photo_id'].'&amp;start='.$start, 'label' => 'Редактировать', 'show' => (($photo['photo_user'] == App::getUsername()) && !is_admin())),
        array('url' => '/gallery?act=delphoto&amp;gid='.$photo['photo_id'].'&amp;start='.$start.'&amp;uid='.$_SESSION['token'], 'label' => 'Удалить', 'params' => array('onclick' => "return confirm('Вы подтверждаете удаление изображения?')"), 'show' => (($photo['photo_user'] == App::getUsername()) && !is_admin())),
    );

    render('includes/link', array('links' => $links));
    ?>

    <div>
        <a href="/upload/pictures/<?= $photo['photo_link'] ?>"><img  class="img-responsive" src="/upload/pictures/<?= $photo['photo_link'] ?>" alt="image" /></a><br />

        <?php if (!empty($photo['photo_text'])): ?>
            <?= bb_code($photo['photo_text']) ?><br />
        <?php endif; ?>

        Рейтинг: <a href="/gallery?act=vote&amp;gid=<?= $photo['photo_id'] ?>&amp;vote=down&amp;uid=<?= $_SESSION['token'] ?>"><img src="/images/img/thumb-down.gif" alt="Минус" /></a> <big><b><?= format_num($photo['photo_rating']) ?></b></big> <a href="/gallery?act=vote&amp;gid=<?= $photo['photo_id'] ?>&amp;vote=up&amp;uid=<?= $_SESSION['token'] ?>"><img src="/images/img/thumb-up.gif" alt="Плюс" /></a><br />

        Размер: <?= read_file(BASEDIR.'/upload/pictures/'.$photo['photo_link']) ?><br />
        Добавлено: <?= profile($photo['photo_user'])?> (<?= date_fixed($photo['photo_time']) ?>)<br />
        <a href="/gallery?act=comments&amp;gid=<?= $photo['photo_id'] ?> ">Комментарии</a> (<?= $photo['photo_comments'] ?>)
        <a href="/gallery?act=end&amp;gid=<?= $photo['photo_id'] ?>">&raquo;</a>
    </div>
    <br />

    <?php $nav = photo_navigation($photo['photo_id']); ?>

    <?php if ($nav['next'] || $nav['prev']): ?>
        <div class="form" style="text-align:center">

            <?php if ($nav['next']): ?>
                <a href="/gallery?act=view&amp;gid=<?= $nav['next'] ?>">&laquo; Назад</a> &nbsp;
            <?php endif; ?>

            <?php if ($nav['prev']): ?>
                &nbsp; <a href="/gallery?act=view&amp;gid=<?= $nav['prev'] ?>">Вперед &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <i class="fa fa-arrow-circle-up"></i> <a href="/gallery/album?act=photo&amp;uz=<?= $photo['photo_user'] ?>">В альбом</a><br />

<?php else: ?>
    <?= show_error('Ошибка! Данного изображения нет в базе'); ?>
<?php endif; ?>

<i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br />
