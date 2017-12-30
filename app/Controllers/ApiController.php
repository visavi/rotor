<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Models\Inbox;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;

class ApiController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        return view('api/index');
    }

    /**
     * Api пользователей
     */
    public function getUser()
    {
        header('Content-type: application/json');
        header('Content-Disposition: inline; filename="user.json";');

        $token = check(Request::input('token'));

        if (! $token) {
            echo json_encode(['error'=>'no token']);
            exit();
        }

        $user = User::query()->where('apikey', $token)->first();

        if (! $user) {
            echo json_encode(['error'=>'no user']);
            exit();
        }

        echo json_encode([
            'login'     => $user->login,
            'email'     => $user->email,
            'name'      => $user->name,
            'level'     => $user->level,
            'country'   => $user->country,
            'city'      => $user->city,
            'site'      => $user->site,
            'icq'       => $user->icq,
            'skype'     => $user->skype,
            'gender'    => $user->gender,
            'birthday'  => $user->birthday,
            'newwall'   => $user->newwall,
            'point'     => $user->point,
            'money'     => $user->money,
            'allprivat' => userMail($user),
            'newprivat' => $user->newprivat,
            'status'    => userStatus($user),
            'avatar'    => siteUrl().'/uploads/avatars/'.$user->avatar,
            'picture'   => siteUrl().'/uploads/photos/'.$user->picture,
            'rating'    => $user->rating,
            'lastlogin' => $user->timelastlogin,
        ]);
    }

    /**
     * Api приватных сообщений
     */
    public function private()
    {
        header('Content-type: application/json');
        header('Content-Disposition: inline; filename="private.json";');

        $token = check(Request::input('token'));
        $count = int(Request::input('count', 10));

        if (! $token) {
            echo json_encode(['error'=>'no token']);
            exit();
        }

        $user = User::query()->where('apikey', $token)->first();
        if (! $user) {
            echo json_encode(['error'=>'no user']);
            exit();
        }

        $inbox = Inbox::query()->where('user_id', $user->id)
            ->orderBy('created_at')
            ->limit($count)
            ->get();

        if ($inbox->isEmpty()) {
            echo json_encode(['error'=>'no messages']);
            exit();
        }

        $total = $inbox->count();

        $messages = [];
        foreach ($inbox as $data) {

            $data['text'] = str_replace('<img src="/uploads/smiles/', '<img src="'.siteUrl().'/uploads/smiles/', bbCode($data->text));

            $messages[] = [
                'author_id'  => $data->author_id,
                'login'      => $data->author->login,
                'text'       => $data->text,
                'created_at' => $data->created_at,
            ];
        }

        echo json_encode([
            'total'    => $total,
            'messages' => $messages
        ]);
    }

    /**
     * Api постов темы в форуме
     */
    public function forum()
    {
        header('Content-type: application/json');
        header('Content-Disposition: inline; filename="forum.json";');

        $token = check(Request::input('token'));
        $id    = int(Request::input('id'));

        if (! $token) {
            echo json_encode(['error'=>'no token']);
            exit();
        }

        $user = User::query()->where('apikey', $token)->first();
        if (! $user) {
            echo json_encode(['error'=>'no user']);
            exit();
        }

        $topic = Topic::query()->find($id);
        if (! $topic) {
            echo json_encode(['error'=>'no topic']);
            exit();
        }

        $posts = Post::query()
            ->where('topic_id', $id)
            ->orderBy('created_at')
            ->get();

        $messages = [];
        foreach ($posts as $post) {

            $post['text'] = str_replace('<img src="/uploads/smiles/', '<img src="'.siteUrl().'/uploads/smiles/', bbCode($post['text']));

            $messages[] = [
                'post_id'    => $post->id,
                'user_id'    => $post->user_id,
                'login'      => $post->user->login,
                'text'       => $post->text,
                'rating'     => $post->rating,
                'updated_at' => $post->updated_at,
                'created_at' => $post->created_at,
            ];
        }

        echo json_encode([
            'id'         => $topic->id,
            'forum_id'   => $topic->forum_id,
            'user_id'    => $topic->user_id,
            'login'      => $topic->user->login,
            'title'      => $topic->title,
            'closed'     => $topic->closed,
            'locked'     => $topic->locked,
            'note'       => $topic->note,
            'moderators' => $topic->moderators,
            'updated_at' => $topic->updated_at,
            'created_at' => $topic->created_at,
            'messages'   => $messages,
        ]);
    }
}
