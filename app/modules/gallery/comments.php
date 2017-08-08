<?php

switch ($action):
    /**
     * Выводит все комментарии
     */
    case 'index':
        $total = Comment::where('relate_type', Photo::class)->count();
        $page = App::paginate(Setting::get('postgallery'), $total);

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
        $login = param('login');

        $user = User::where('login', $login)->first();

        if (! $user) {
            App::abort('default', 'Пользователь не найден!');
        }

        $total = Comment::where('relate_type', Photo::class)
            ->where('user_id', $user->id)
            ->count();

        $page = App::paginate(Setting::get('postgallery'), $total);

        $comments = Comment::select('comments.*', 'title')
            ->where('relate_type', Photo::class)
            ->where('comments.user_id', $user->id)
            ->leftJoin('photo', 'comments.relate_id', '=', 'photo.id')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('comments.created_at', 'desc')
            ->with('user')
            ->get();

        App::view('gallery/user_comments', compact('comments', 'page', 'user'));
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
            $end = ceil($total / Setting::get('postgallery'));

            App::redirect('/gallery/'.$gid.'/comments?page='.$end);
        } else {
            App::setFlash('success', 'Комментариев к данному изображению не существует!');
            App::redirect("/gallery/comments");
        }
    break;
endswitch;
