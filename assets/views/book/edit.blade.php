@extends('layout')

@section('title', 'Редактирование сообщения - @parent')

@section('content')

    <i class="fa fa-pencil text-muted"></i> <b><?=profile($post['guest_user'])?></b> (<?=date_fixed($post['guest_time'])?>)<br /><br />

    <div class="form">
        <form action="/book/edit/<?= $id ?>" method="post">
            <input type="hidden" name="uid" value="<?= $_SESSION['token'] ?>" />

            <div class="form-group{{ App::hasError('msg') }}">
                <label for="inputText">Сообщение:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="Текст сообщения" required>{{ App::getInput('msg', $post['guest_text']) }}</textarea>
                {!! App::textError('msg') !!}
            </div>

            <button type="submit" class="btn btn-primary">Редактировать</button>
        </form>
    </div><br />

    <?php render('includes/back', array('link' => '/book', 'title' => 'Вернуться')); ?>
@stop
