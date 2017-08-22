<?php

class ApiController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        App::view('api/index');
    }

    /**
     * Api пользователей
     */
    public function user()
    {
        header('Content-type: application/json');
        header('Content-Disposition: inline; filename="user.json";');

        $key = check(Request::get('key'));

        if (! $key) {
            echo json_encode(['error'=>'nokey']);
            exit();
        }

        $user = User::where('apikey', $key)->first();

        if (! $user) {
            echo json_encode(['error'=>'nouser']);
            exit();
        }

        echo json_encode([
            'login'     => $user->login,
            'email'     => $user->email,
            'name'      => $user->name,
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
            'ban'       => $user->ban,
            'allprivat' => user_mail($user),
            'newprivat' => $user->newprivat,
            'status'    => user_title($user),
            'avatar'    => Setting::get('home').'/uploads/avatars/'.$user->avatar,
            'picture'   => Setting::get('home').'/uploads/photos/'.$user->picture,
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

        $key   = check(Request::get('key'));
        $count = abs(intval(Request::get('count', 10)));

        if (! $key) {
            echo json_encode(['error'=>'nokey']);
            exit();
        }

        $user = User::where('apikey', $key)->first();
        if (! $user) {
            echo json_encode(['error'=>'nouser']);
            exit();
        }

        $inbox = Inbox::where('user_id', $user->id)
            ->orderBy('created_at')
            ->get();


        if ($inbox->isEmpty()) {
            echo json_encode(['error'=>'nomessages']);
            exit();
        }

        $total = $inbox->count();

        $messages = [];
        foreach ($inbox as $data) {

            $data['text'] = str_replace('<img src="/uploads/smiles/', '<img src="'.Setting::get('home').'/uploads/smiles/', App::bbCode($data['text']));

            $messages[] = [
                'author_id'  => $data->author_id,
                'login'      => $data->getAuthor()->login,
                'text'       => $data['text'],
                'created_at' => $data['created_at'],
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

        $key = check(Request::get('key'));
        $id  = abs(intval(Request::get('id')));

        if (! $key) {
            echo json_encode(['error'=>'nokey']);
            exit();
        }

        $user = User::where('apikey', $key)->first();
        if (! $user) {
            echo json_encode(['error'=>'nouser']);
            exit();
        }

        $topic = Topic::find($id);
        if (! $topic) {
            echo json_encode(['error'=>'notopic']);
            exit();
        }

        $posts = Post::where('topic_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        $messages = [];
        foreach ($posts as $post) {

            $post['text'] = str_replace('<img src="/uploads/smiles/', '<img src="'.Setting::get('home').'/uploads/smiles/', App::bbCode($post['text']));

            $messages[] = [
                'post_id'    => $post->id,
                'user_id'    => $post->user_id,
                'login'      => $post->getUser()->login,
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
            'login'      => $topic->getUser()->login,
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
