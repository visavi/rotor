@extends('layout')

@section('title', 'Мои закладки - @parent')

@section('content')
    <h1>Мои закладки</h1>

    <a href="/forum">Форум</a>

    @if ($page['total'] > 0)
        <form action="/forum/bookmark/delete?page=<?=$page['current']?>" method="post">
            <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>" />
            <?php foreach ($topics as $data): ?>
                <div class="b">
                    <input type="checkbox" name="del[]" value="<?=$data['id']?>" />

                    <?php
                    if ($data['locked']) {
                        $icon = 'fa-thumb-tack';
                    } elseif ($data['closed']) {
                        $icon = 'fa-lock';
                    } else {
                        $icon = 'fa-folder-open';
                    }
                    ?>

                    <i class="fa <?=$icon?> text-muted"></i>

                    <?php $newpost = ($data['posts'] > $data['book_posts']) ? '/<span style="color:#00cc00">+'.($data['posts'] - $data['book_posts']).'</span>' : ''; ?>

                    <b><a href="/topic/<?=$data['id']?>"><?=$data['title']?></a></b> (<?=$data['posts']?><?=$newpost?>)
                </div>

                <div>
                    <?= App::forumPagination($data)?>
                    Автор: <?=nickname($data['author'])?> / Посл.: <?=nickname($data['last_user'])?> (<?=date_fixed($data['last_time'])?>)
                </div>
            <?php endforeach; ?>

            <br />
            <input type="submit" value="Удалить выбранное" />
        </form>

        <?php App::pagination($page) ?>
    @else
        <?= show_error('Закладок еще нет!'); ?>
    @endif
@stop
