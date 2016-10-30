@extends('layout')

@section('title', 'Изменение сообщения - @parent')

@section('content')
    <h1>Изменение сообщения</h1>

    <i class="fa fa-pencil"></i> <b><?=nickname($post['posts_user'])?></b> <small>(<?=date_fixed($post['posts_time'])?>)</small><br /><br />

    <div class="form">
        <form action="/topic/<?= $post['posts_topics_id'] ?>/<?= $post['posts_id'] ?>/edit?start=<?= $start ?>" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ App::hasError('msg') }}">
                <label for="markItUp">Сообщение:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="msg" required>{{ App::getInput('msg', $post['posts_text']) }}</textarea>
                {!! App::textError('msg') !!}
            </div>

            <?php if (!empty($files)): ?>
                <i class="fa fa-paperclip"></i> <b>Удаление файлов:</b><br />
                <?php foreach ($files as $file): ?>
                    <input type="checkbox" name="delfile[]" value="<?=$file['file_id']?>" />
                    <a href="<?= HOME ?>/upload/forum/<?=$file['file_topics_id']?>/<?=$file['file_hash']?>" target="_blank"><?=$file['file_name']?></a> (<?=formatsize($file['file_size'])?>)<br />
                <?php endforeach; ?>
                <br />
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Редактировать</button>
        </form>
    </div>
    <br />
@stop
