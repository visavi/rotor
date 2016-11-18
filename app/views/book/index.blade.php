@extends('layout')

@section('title', 'Гостевая книга (Стр. '.$page['current'].') - @parent')

@section('content')

    <h1>Гостевая книга</h1>

    <a href="/rules">Правила</a> /
    <a href="/smiles">Смайлы</a> /
    <a href="/tags">Теги</a>

    <?php if (is_admin()):?>
        / <a href="/admin/book?page=<?=$page['current']?>">Управление</a>
    <?php endif;?>
    <hr />

    <?php if ($total > 0): ?>
        <?php foreach ($posts as $data): ?>
            <div class="post">
                <div class="b">

                    <?php if (!empty($log) && $log != $data['user']): ?>

                        <div class="pull-right">
                            <a href="#" onclick="return postReply('<?= nickname($data['user']) ?>')" title="Ответить"><i class="fa fa-reply text-muted"></i></a>

                            <a href="#" onclick="return postQuote(this)" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>

                            <noindex><a href="#" onclick="return sendComplaint(this)" data-type="/book" data-id="{{ $data['id'] }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $page['current'] }}" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a></noindex>
                        </div>

                    <?php endif; ?>

                    <?php if ($log == $data['user'] && $data['time'] + 600 > SITETIME): ?>
                        <div class="pull-right">
                            <a href="/book/edit/<?=$data['id']?>" title="Редактировать"><i class="fa fa-pencil text-muted"></i></a>
                        </div>
                    <?php endif; ?>

                    <div class="img"><?=user_avatars($data['user'])?></div>

                    <?php if ($data['user'] == $config['guestsuser']): ?>
                        <b><?=$data['user']?></b> <small>(<?=date_fixed($data['time'])?>)</small>
                    <?php else: ?>
                        <b><?=profile($data['user'])?></b> <small>(<?=date_fixed($data['time'])?>)</small><br />
                        <?=user_title($data['user'])?> <?=user_online($data['user'])?>
                    <?php endif; ?>
                </div>

                <div class="message"><?=App::bbCode($data['text'])?></div>

                <?php if (!empty($data['edit'])): ?>
                    <small><i class="fa fa-exclamation-circle text-danger"></i> Отредактировано: <?=nickname($data['edit'])?> (<?=date_fixed($data['edit_time'])?>)</small><br />
                <?php endif; ?>

                <?php if (is_admin() || empty($config['anonymity'])): ?>
                    <span class="data">(<?=$data['brow']?>, <?=$data['ip']?>)</span>
                <?php endif; ?>

                <?php if (!empty($data['reply'])): ?>
                    <br /><span style="color:#ff0000">Ответ: <?=App::bbCode($data['reply'])?></span>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>

        <?php App::pagination($page) ?>

    <?php else: ?>
        <?php show_error('Сообщений нет, будь первым!'); ?>
    <?php endif; ?>


    <?php if (is_user()): ?>
        <div class="form">
            <form action="book/add" method="post">
                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>" />
                <div class="form-group{{ App::hasError('msg') }}">
                    <label for="markItUp">Сообщение:</label>
                    <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="Текст сообщения" required>{{ App::getInput('msg') }}</textarea>
                    {!! App::textError('msg') !!}
                </div>

                <button type="submit" class="btn btn-primary">Написать</button>
            </form>
        </div><br />

    <?php elseif ($config['bookadds'] == 1): ?>

        <div class="form">
            <form action="book/add" method="post">
                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>" />

                <div class="form-group{{ App::hasError('msg') }}">
                    <label for="inputText">Сообщение:</label>
                    <textarea class="form-control" id="inputText" rows="5" name="msg" placeholder="Текст сообщения" required>{{ App::getInput('msg') }}</textarea>
                    {!! App::textError('msg') !!}
                </div>

                <div class="form-group{{ App::hasError('protect') }}">
                    <label for="inputProtect">Проверочный код:</label>
                    <img src="/captcha" id="captcha" onclick="this.src='/captcha?'+Math.random()" class="img-rounded" alt="" style="cursor: pointer;">
                    <input class="form-control" name="protect" id="inputProtect" maxlength="6" required>
                    {!! App::textError('protect') !!}
                </div>

                <button type="submit" class="btn btn-primary">Написать</button>
            </form>
        </div><br />

    <?php else: ?>
        <?php show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо'); ?>
    <?php endif; ?>
@stop
