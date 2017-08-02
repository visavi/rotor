@extends('layout')

@section('title')
    Изменение сообщения - @parent
@stop

@section('content')
    <h1>Изменение сообщения</h1>

    <i class="fa fa-pencil"></i> <b><?=$post->getUser()->login?></b> <small>(<?=date_fixed($post['created_at'])?>)</small><br /><br />

    <div class="form">
        <form action="/topic/<?= $post['topic_id'] ?>/<?= $post['id'] ?>/edit?page=<?= $page ?>" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ App::hasError('msg') }}">
                <label for="markItUp">Сообщение:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="msg" required>{{ App::getInput('msg', $post['text']) }}</textarea>
                {!! App::textError('msg') !!}
            </div>

            <?php if (!empty($files)): ?>
                <i class="fa fa-paperclip"></i> <b>Удаление файлов:</b><br />
                <?php foreach ($files as $file): ?>
                    <input type="checkbox" name="delfile[]" value="<?=$file['id']?>" />
                    <a href="/uploads/forum/<?=$post['topic_id']?>/<?=$file['hash']?>" target="_blank"><?=$file['name']?></a> (<?=formatsize($file['size'])?>)<br />
                <?php endforeach; ?>
                <br />
            <?php endif; ?>

            <button class="btn btn-primary">Редактировать</button>
        </form>
    </div>
    <br />
@stop
