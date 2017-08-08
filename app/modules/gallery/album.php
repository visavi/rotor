<?php

$login = param('login');

switch ($action):
############################################################################################
##                                  Вывод комментариев                                    ##
############################################################################################
    case 'index':
        $total = Photo::distinct('user_id')
            ->join('users', 'photo.user_id', '=', 'users.id')
            ->count('user_id');

        $page = App::paginate(Setting::get('photogroup'), $total);

        $albums = Photo::select('user_id', 'login')
            ->selectRaw('count(*) as cnt, sum(comments) as comments')
            ->join('users', 'photo.user_id', '=', 'users.id')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->groupBy('user_id')
            ->orderBy('cnt', 'desc')
            ->get();

        App::view('gallery/albums', compact('albums', 'page'));
    break;

    ############################################################################################
    ##                               Просмотр по пользователям                                ##
    ############################################################################################
    case 'photo':

        $user = User::where('login', $login)->first();

        if (! $user) {
            App::abort('default', 'Пользователь не найден!');
        }

        $total = Photo::where('user_id', $user->id)->count();

        $page = App::paginate(Setting::get('fotolist'), $total);

        $photos = Photo::where('user_id', $user->id)
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->get();

        $moder = (App::getUserId() == $user->id) ? 1 : 0;

        App::view('gallery/user_albums', compact('photos', 'moder', 'page', 'user'));
    break;

endswitch;
