<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Message;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;

class ApiController extends BaseController
{
    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        return view('api/index');
    }

    /**
     * Api пользователей
     *
     * @param Request $request
     * @return void
     */
    public function users(Request $request): void
    {
        header('Content-type: application/json');
        header('Content-Disposition: inline; filename="users.json";');

        $token = check($request->input('token'));

        if (! $token) {
            echo json_encode(['error' => 'no token']);
            exit();
        }

        $user = User::query()->where('apikey', $token)->first();

        if (! $user) {
            echo json_encode(['error' => 'no user']);
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
            'gender'    => $user->gender,
            'birthday'  => $user->birthday,
            'newwall'   => $user->newwall,
            'point'     => $user->point,
            'money'     => $user->money,
            'allprivat' => $user->getCountMessages(),
            'newprivat' => $user->newprivat,
            'status'    => $user->getStatus(),
            'avatar'    => $user->avatar ? siteUrl(true) . $user->avatar : null,
            'picture'   => $user->picture ? siteUrl(true) . $user->picture : null,
            'rating'    => $user->rating,
            'lastlogin' => $user->updated_at,
        ]);
    }

    /**
     * Api приватных сообщений
     *
     * @param Request $request
     * @return void
     */
    public function messages(Request $request): void
    {
        header('Content-type: application/json');
        header('Content-Disposition: inline; filename="messages.json";');

        $token = check($request->input('token'));
        $count = int($request->input('count', 10));

        if (! $token) {
            echo json_encode(['error' => 'no token']);
            exit();
        }

        $user = User::query()->where('apikey', $token)->first();
        if (! $user) {
            echo json_encode(['error' => 'no user']);
            exit();
        }

        $messages = Message::query()
            ->where('user_id', $user->id)
            ->where('type', 'in')
            ->orderByDesc('created_at')
            ->limit($count)
            ->get();

        if ($messages->isEmpty()) {
            echo json_encode(['error' => 'no messages']);
            exit();
        }

        $total = $messages->count();

        $msg = [];
        foreach ($messages as $data) {
            $data->text = bbCode($data->text);

            $msg[] = [
                'author_id'  => $data->author_id,
                'login'      => $data->author->id ? $data->author->login : 'Система',
                'text'       => $data->text,
                'reading'    => $data->reading,
                'created_at' => $data->created_at,
            ];
        }

        echo json_encode([
            'total'    => $total,
            'messages' => $msg
        ]);
    }

    /**
     * Api постов темы в форуме
     *
     * @param Request $request
     * @return void
     */
    public function forums(Request $request): void
    {
        header('Content-type: application/json');
        header('Content-Disposition: inline; filename="forums.json";');

        $token = check($request->input('token'));
        $id    = int($request->input('id'));

        if (! $token) {
            echo json_encode(['error' => 'no token']);
            exit();
        }

        $user = User::query()->where('apikey', $token)->first();
        if (! $user) {
            echo json_encode(['error' => 'no user']);
            exit();
        }

        /** @var Topic $topic */
        $topic = Topic::query()->find($id);
        if (! $topic) {
            echo json_encode(['error' => 'no topic']);
            exit();
        }

        $posts = Post::query()
            ->where('topic_id', $id)
            ->orderBy('created_at')
            ->get();

        $messages = [];
        foreach ($posts as $post) {
            $post->text = bbCode($post->text);

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
