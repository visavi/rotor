<?php

$path  = null;
$data  = false;
$id    = abs(intval(Request::input('id')));
$type  = check(Request::input('type'));
$page  = check(Request::input('page'));
$token = check(Request::input('token'));

switch ($type):
    case 'Blog':
        $data = Comment::where('relate_type', $type)
            ->where('id', $id)
            ->first();
        $path = '/blog?page='.$page;
        break;

    case 'Photo':
        $data = Comment::where('relate_type', $type)
            ->where('id', $id)
            ->first();
        $path = '/gallery/'.$data['relate_id'].'/comments?page='.$page;
        break;

    case 'Guest':
        $data = $type::find($id);
        $path = '/book?page='.$page;
        break;

    case 'Post':
        $data = $type::find($id);
        $path = '/topic/'.$data['topic_id'].'?page='.$page;
        break;

    case 'Inbox':
        $data = $type::find($id);
        break;
endswitch;

$spam = Spam::where(['relate_type' => $type, 'relate_id' => $id])->first();

$validation = new Validation();
$validation->addRule('bool', Request::ajax(), 'Это не ajax запрос!')
    ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
    ->addRule('bool', is_user(), 'Для отправки жалобы необходимо авторизоваться')
    ->addRule('bool', $data, 'Выбранное вами сообщение для жалобы не существует!')
    ->addRule('bool', ! $spam, 'Жалоба на данное сообщение уже отправлена!');

if ($validation->run()) {
    $spam = new Spam();
    $spam->relate_type = $type;
    $spam->relate_id   = $data['id'];
    $spam->user_id     = App::getUserId();
    $spam->path        = $path;
    $spam->created_at  = SITETIME;
    $spam->save();

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => current($validation->getErrors())
    ]);
}
