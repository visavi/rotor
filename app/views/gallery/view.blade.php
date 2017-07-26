@extends('layout')

@section('title')
    {{ $photo->title }} - @parent
@stop

@section('content')

    <h1>{{ $photo['title'] }}</h1>

    <?php if (!empty($photo)): ?>

    <?php
    $links = [
        ['url' => '/admin/gallery?act=edit&amp;page='.$page.'&amp;gid='.$photo['id'], 'label' => 'Редактировать', 'show' => is_admin()],
        ['url' => '/admin/gallery?act=del&amp;del='.$photo['id'].'&amp;page='.$page.'&amp;uid='.$_SESSION['token'], 'label' => 'Удалить', 'params' => ['onclick' => "return confirm('Вы подтверждаете удаление изображения?')"], 'show' => is_admin()],
        ['url' => '/gallery?act=edit&amp;gid='.$photo['id'].'&amp;page='.$page, 'label' => 'Редактировать', 'show' => (($photo['user'] == App::getUsername()) && !is_admin())],
        ['url' => '/gallery?act=delphoto&amp;gid='.$photo['id'].'&amp;page='.$page.'&amp;uid='.$_SESSION['token'], 'label' => 'Удалить', 'params' => ['onclick' => "return confirm('Вы подтверждаете удаление изображения?')"], 'show' => (($photo['user'] == App::getUsername()) && !is_admin())],
    ];
    ?>

    <ol class="breadcrumb">
        <?php foreach ($links as $link): ?>
            <?php if (isset($link['show']) && $link['show'] == false) continue; ?>
            <li><a href="<?= $link['url'] ?>"><?= $link['label'] ?></a></ li>
        <?php endforeach; ?>
    </ol>

    <div>
        <a href="/uploads/pictures/<?= $photo['link'] ?>" class="gallery"><img  class="img-responsive" src="/uploads/pictures/<?= $photo['link'] ?>" alt="image" /></a><br />

        <?php if (!empty($photo['text'])): ?>
            <?= App::bbCode($photo['text']) ?><br />
        <?php endif; ?>

        Рейтинг: <a href="/gallery?act=vote&amp;gid=<?= $photo['id'] ?>&amp;vote=down&amp;token=<?= $_SESSION['token'] ?>"><i class="fa fa-thumbs-down"></i></a> <big><b><?= format_num($photo['rating']) ?></b></big> <a href="/gallery?act=vote&amp;gid=<?= $photo['id'] ?>&amp;vote=up&amp;token=<?= $_SESSION['token'] ?>"><i class="fa fa-thumbs-up"></i></a><br />

        Размер: <?= read_file(HOME.'/uploads/pictures/'.$photo['link']) ?><br />
        Добавлено: <?= profile($photo['user'])?> (<?= date_fixed($photo['time']) ?>)<br />
        <a href="/gallery/<?= $photo['id'] ?>/comments">Комментарии</a> (<?= $photo['comments'] ?>)
        <a href="/gallery/<?= $photo['id'] ?>/end">&raquo;</a>
    </div>
    <br />

    <?php $nav = photo_navigation($photo['id']); ?>

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
    <i class="fa fa-arrow-circle-up"></i> <a href="/gallery/album?act=photo&amp;user=<?= $photo->getUser()->login ?>">В альбом</a><br />

    <?php else: ?>
        <?= show_error('Ошибка! Данного изображения нет в базе'); ?>
    <?php endif; ?>

    <i class="fa fa-arrow-circle-left"></i> <a href="/gallery">В галерею</a><br />
@stop
