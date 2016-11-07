@extends('layout')

@section('title', 'Редактирование сообщения - @parent')

@section('content')

    <i class="fa fa-pencil text-muted"></i> <b><?=profile($post['user'])?></b> (<?=date_fixed($post['time'])?>)<br /><br />

    <div class="form">
        <form action="/book/edit/<?= $id ?>" method="post">
            <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>" />

            <div class="form-group{{ App::hasError('msg') }}">
                <label for="inputText">Сообщение:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="Текст сообщения" required>{{ App::getInput('msg', $post['text']) }}</textarea>
                {!! App::textError('msg') !!}
            </div>

            <button type="submit" class="btn btn-primary">Редактировать</button>
        </form>
    </div><br />

    <?php render('includes/back', ['link' => '/book', 'title' => 'Вернуться']); ?>
@stop
