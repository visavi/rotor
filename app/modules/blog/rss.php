<?php
App::view($config['themes'].'/index');

switch ($act):
/**
 * RSS всех блогов
 */
case 'index':
    show_title('RSS блогов');

    $blogs = DBM::run()->select('blogs', null, 15, null, ['time' => 'DESC']);

    if ($blogs) {
        while (ob_get_level()) {
            ob_end_clean();
        }

        header("Content-Encoding: none");
        header("Content-type:application/rss+xml; charset=utf-8");
        die(render('blog/rss', compact('blogs')));
    } else {
        show_error('Ошибка! Нет блогов для отображения!');
    }

    render('includes/back', ['link' => '/blog', 'title' => 'К блогам']);
break;

/**
 * RSS комментариев к блогу
 */
case 'comments':

    show_title('RSS комментарии');

    $id = param('id');
    $blog = DBM::run()->selectFirst('blogs', ['id' => $id]);

    if ($blog) {
        $comments = DBM::run()->select('comments', ['relate_type' => 'blog', 'relate_id' => $id], 15, null, ['time' => 'DESC']);

        while (ob_get_level()) {
            ob_end_clean();
        }

        header("Content-Encoding: none");
        header("Content-type:application/rss+xml; charset=utf-8");
        die(render('blog/rss_comments', compact('blog', 'comments')));

    } else {
        show_error('Ошибка! Выбранная вами статья не существует, возможно она была удалена!');
    }

    render('includes/back', ['link' => '/blog', 'title' => 'К блогам']);

break;
endswitch;

App::view($config['themes'].'/foot');
