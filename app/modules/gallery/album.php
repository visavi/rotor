<?php

$login = param('login');

switch ($act):
############################################################################################
##                                  Вывод комментариев                                    ##
############################################################################################
    case 'index':
        $total = Photo::distinct('user_id')
            ->join('users', 'photo.user_id', '=', 'users.id')
            ->count('user_id');

        $page = App::paginate(App::setting('photogroup'), $total);

        $albums = Photo::select('user_id', 'login')
            ->selectRaw('count(*) as cnt, sum(comments) as comments')
            ->join('users', 'photo.user_id', '=', 'users.id')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->groupBy('user_id')
            ->orderBy('cnt', 'desc')
            ->get();

        App::view('gallery/album', compact('albums', 'page'));
    break;

    ############################################################################################
    ##                               Просмотр по пользователям                                ##
    ############################################################################################
    case 'photo':

        //show_title('Список всех фотографий '.$uz);

        $user = User::where('login', $login)->first();

        if (! $user) {
            App::abort('default', 'Пользователь не найден!');
        }

        $total = Photo::where('user_id', $user->id)->count();

        $page = App::paginate(App::setting('fotolist'), $total);

        if ($total > 0) {

            //App::setting('newtitle') = 'Список всех фотографий '.$uz.' (Стр. '.$page['current'].')';

            $photos = Photo::where('user_id', $user->id)
                ->offset($page['offset'])
                ->limit($page['limit'])
                ->orderBy('created_at', 'desc')
                ->get();

            $moder = (App::getUserId() == $user->id) ? 1 : 0;

            foreach ($photos as $data) {
                echo '<div class="b"><i class="fa fa-picture-o"></i> ';
                echo '<b><a href="/gallery?act=view&amp;gid='.$data['id'].'&amp;page='.$page['current'].'">'.$data['title'].'</a></b> ('.read_file(HOME.'/uploads/pictures/'.$data['link']).')<br />';

                if (!empty($moder)) {
                    echo '<a href="/gallery?act=edit&amp;gid='.$data['id'].'&amp;page='.$page['current'].'">Редактировать</a> / ';
                    echo '<a href="/gallery?act=delphoto&amp;gid='.$data['id'].'&amp;page='.$page['current'].'&amp;uid='.$_SESSION['token'].'">Удалить</a>';
                }

                echo '</div><div>';
                echo '<a href="/gallery?act=view&amp;gid='.$data['id'].'&amp;page='.$page['current'].'">'.resize_image('uploads/pictures/', $data['link'], App::setting('previewsize'), ['alt' => $data['title']]).'</a><br />';

                if (!empty($data['text'])){
                    echo App::bbCode($data['text']).'<br />';
                }

                echo 'Добавлено: '.profile($data['user']).' ('.date_fixed($data['created_at']).')<br />';
                echo '<a href="/gallery?act=comments&amp;gid='.$data['id'].'">Комментарии</a> ('.$data['comments'].')';
                echo '</div>';
            }

            App::pagination($page);

            echo 'Всего фотографий: <b>'.$total.'</b><br /><br />';
        } else {
            show_error('Фотографий в альбоме еще нет!');
        }

        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/gallery/album">Альбомы</a><br />';
    break;

endswitch;
