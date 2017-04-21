<?php
App::view(App::setting('themes').'/index');

switch ($act):
/**
 * RSS всех блогов
 */
case 'index':
    //show_title('RSS блогов');

    $blogs = Blog::order_by_desc('time')->limit(15)->find_many();
    if ($blogs) {
        while (ob_get_level()) {
            ob_end_clean();
        }

        header("Content-Encoding: none");
        header("Content-type:application/rss+xml; charset=utf-8");
        die(App::view('blog/rss', compact('blogs')));
    } else {
        show_error('Ошибка! Нет блогов для отображения!');
    }

    App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам']);
break;

/**
 * RSS комментариев к блогу
 */
case 'comments':

    //show_title('RSS комментарии');

    $id = param('id');
    $blog = Blog::with('lastComments')->find_one($id);

    if ($blog) {
        while (ob_get_level()) {
            ob_end_clean();
        }

        header("Content-Encoding: none");
        header("Content-type:application/rss+xml; charset=utf-8");
        die(App::view('blog/rss_comments', compact('blog')));

    } else {
        show_error('Ошибка! Выбранная вами статья не существует, возможно она была удалена!');
    }

    App::view('includes/back', ['link' => '/blog', 'title' => 'К блогам']);

break;
endswitch;

App::view(App::setting('themes').'/foot');
