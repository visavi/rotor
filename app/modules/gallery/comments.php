<?php

switch ($act):
    /**
     * Выводит все комментарии
     */
    case 'index':
        $total = Comment::where('relate_type', Photo::class)->count();
        $page = App::paginate(App::setting('postgallery'), $total);

        $comments = Comment::select('comments.*', 'title')
            ->where('relate_type', Photo::class)
            ->leftJoin('photo', 'comments.relate_id', '=', 'photo.id')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('comments.created_at', 'desc')
            ->with('user')
            ->get();

        App::view('gallery/all_comments', compact('comments', 'page'));
    break;

    /**
     * Выводит комментарии пользователя
     */
    case 'comments':
        //show_title('Список всех комментариев '.$uz);

        $user = User::where('login', $uz)->first();

        if (! $user) {
            App::abort('default', 'Пользователь не найден!');
        }

        $total = Comment::where('relate_type', Photo::class)
            ->where('user_id', $user->id)
            ->count();

        $page = App::paginate(App::setting('postgallery'), $total);

        if ($total > 0) {
            //App::setting('newtitle') = 'Список всех комментариев '.$uz.' (Стр. '.$page['current'].')';

            $comments = Comment::select('comments.*', 'title')
                ->where('relate_type', Photo::class)
                ->where('comments.user_id', $user->id)
                ->leftJoin('photo', 'comments.relate_id', '=', 'photo.id')
                ->offset($page['offset'])
                ->limit($page['limit'])
                ->orderBy('comments.created_at')
                ->with('user')
                ->get();

            foreach ($comments as $data) {

                echo '<div class="b"><i class="fa fa-comment"></i> <b><a href="/gallery/comments?act=viewcomm&amp;gid='.$data['relate_id'].'&amp;cid='.$data['id'].'">'.$data['title'].'</a></b>';

                if (is_admin()) {
                    echo ' — <a href="/gallery/comments?act=del&amp;id='.$data['id'].'&amp;uz='.$uz.'&amp;page='.$page['current'].'&amp;token='.$_SESSION['token'].'">Удалить</a>';
                }

                echo '</div>';


                echo '<div>'.App::bbCode($data['text']).'<br />';
                echo 'Написал: '.profile($data['user']).'</b> <small>('.date_fixed($data['created_at']).')</small><br />';

                if (is_admin()) {
                    echo '<span class="data">('.$data['brow'].', '.$data['ip'].')</span>';
                }

                echo '</div>';
            }

            App::pagination($page);

        } else {
            show_error('Комментариев еще нет!');
        }
    break;

    /**
     * Переход к сообщению
     */
    case 'viewcomment':

        $id  = abs(intval(param('id')));
        $gid = abs(intval(param('gid')));

        $total = Comment::where('relate_type', Photo::class)
            ->where('relate_id', $gid)
            ->where('id', '<=', $id)
            ->orderBy('created_at')
            ->count();

        if ($total) {
            $end = ceil($total / App::setting('postgallery'));

            App::redirect('/gallery/'.$gid.'/comments?page='.$end);
        } else {
            App::setFlash('success', 'Комментариев к данному изображению не существует!');
            App::redirect("/gallery/comments");
        }
    break;

    /**
     * Удаление комментариев
     */
    case 'del':

        $token = check(Request::input('token'));
        $id = abs(intval(Request::input('id')));
        $page = abs(intval(Request::input('page', 1)));

        if (is_admin()) {
            if ($token == $_SESSION['token']) {

                $comment = Comment::where('relate_type', Photo::class)
                    ->where('id', $id)
                    ->first();

                if ($comment) {

                    $comment->delete();

                    Photo::where('id', $comment->relate_id)
                        ->update([
                            'comments' => Capsule::raw('comments - 1'),
                        ]);

                    App::setFlash('success', 'Комментарий успешно удален!');
                    App::redirect("/gallery/comments?act=comments&uz=$uz&page=$page");
                } else {
                    show_error('Ошибка! Данного комментария не существует!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }
        } else {
            show_error('Ошибка! Удалять комментарии могут только модераторы!');
        }

        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/gallery/comments?act=comments&amp;uz='.$uz.'&amp;page='.$page.'">Вернуться</a><br />';
    break;

endswitch;
