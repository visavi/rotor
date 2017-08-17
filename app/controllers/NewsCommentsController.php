<?php

class NewsCommentsController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        //show_title('Список последних комментариев');
        $total = Comment::where('relate_type', News::class)->count();

        if ($total > 0) {
            if ($total > 100) {
                $total = 100;
            }
            $page = App::paginate(Setting::get('postnews'), $total);

            $comments = Comment::select('comments.*', 'title', 'comments')
                ->where('relate_type', News::class)
                ->leftJoin('news', 'comments.relate_id', '=', 'news.id')
                ->offset($page['offset'])
                ->limit($page['limit'])
                ->orderBy('created_at', 'desc')
                ->with('user')
                ->get();

            foreach ($comments as $data) {
                echo '<div class="b">';

                echo '<i class="fa fa-comment"></i> <b><a href="/news/allcomments/' . $data['relate_id'] . '/' . $data['id'] . '">' . $data['title'] . '</a></b> (' . $data['comments'] . ')</div>';

                echo '<div>' . App::bbCode($data['text']) . '<br>';

                echo 'Написал: ' . profile($data['user']) . ' <small>(' . date_fixed($data['created_at']) . ')</small><br>';

                if (is_admin()) {
                    echo '<span class="data">(' . $data['brow'] . ', ' . $data['ip'] . ')</span>';
                }

                echo '</div>';
            }

            App::pagination($page);
        } else {
            show_error('Комментарии не найдены!');
        }
        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/news">К новостям</a><br>';
    }

    /**
     * Переход к сообщению
     */
    public function viewcomm($id, $nid)
    {
        $total = Comment::where('relate_type', News::class)
            ->where('relate_id', $nid)
            ->where('id', '<=', $id)
            ->orderBy('created_at')
            ->count();

        if ($total) {

            $end = ceil($total / Setting::get('postnews'));
            App::redirect('/news/' . $nid . '/comments?page=' . $end . '#comment_' . $id);

        } else {
            show_error('Ошибка! Комментариев к данной новости не существует!');
        }
        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/news">К новостям</a><br>';
    }
}



