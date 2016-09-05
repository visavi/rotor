@extends('layout')

@section('title', 'Гостевая книга - @parent')

@section('content')

    <a href="/pages/rules.php">Правила</a> /
    <a href="/pages/smiles.php">Смайлы</a> /
    <a href="/pages/tags.php">Теги</a>

    <?php if (is_admin()):?>
        / <a href="/admin/book.php?start=<?=$start?>">Управление</a>
    <?php endif;?>
    <hr />


    <?php if ($total > 0): ?>
        <?php foreach ($posts as $data): ?>
            <div class="post">
                <div class="b">

                    <?php if (!empty($log) && $log != $data['guest_user']): ?>

                        <div class="pull-right">
                            <a href="#" onclick="return postReply('<?= nickname($data['guest_user']) ?>')" title="Ответить"><i class="fa fa-reply text-muted"></i></a>

                            <a href="#" onclick="return postQuote(this)" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>

                            <noindex><a href="#" onclick="return sendComplaint(this)" data-type="/book" data-id="{{ $data['guest_id'] }}" data-token="{{ $_SESSION['token'] }}" rel="nofollow" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a></noindex>
                        </div>

                    <?php endif; ?>

                    <?php if ($log == $data['guest_user'] && $data['guest_time'] + 600 > SITETIME): ?>
                        <div class="pull-right">
                            <a href="/book/edit/<?=$data['guest_id']?>" title="Редактировать"><i class="fa fa-pencil text-muted"></i></a>
                        </div>
                    <?php endif; ?>

                    <div class="img"><?=user_avatars($data['guest_user'])?></div>

                    <?php if ($data['guest_user'] == $config['guestsuser']): ?>
                        <b><?=$data['guest_user']?></b> <small>(<?=date_fixed($data['guest_time'])?>)</small>
                    <?php else: ?>
                        <b><?=profile($data['guest_user'])?></b> <small>(<?=date_fixed($data['guest_time'])?>)</small><br />
                        <?=user_title($data['guest_user'])?> <?=user_online($data['guest_user'])?>
                    <?php endif; ?>
                </div>

                <div class="message"><?=bb_code($data['guest_text'])?></div>

                <?php if (!empty($data['guest_edit'])): ?>
                    <small><i class="fa fa-exclamation-circle text-danger"></i> Отредактировано: <?=nickname($data['guest_edit'])?> (<?=date_fixed($data['guest_edit_time'])?>)</small><br />
                <?php endif; ?>

                <?php if (is_admin() || empty($config['anonymity'])): ?>
                    <span class="data">(<?=$data['guest_brow']?>, <?=$data['guest_ip']?>)</span>
                <?php endif; ?>

                <?php if (!empty($data['guest_reply'])): ?>
                    <br /><span style="color:#ff0000">Ответ: <?=bb_code($data['guest_reply'])?></span>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>

        <?php page_strnavigation('/book?', $config['bookpost'], $start, $total); ?>

    <?php else: ?>
        <?php show_error('Сообщений нет, будь первым!'); ?>
    <?php endif; ?>


    <?php if (is_user()): ?>
        <div class="form">
            <form action="book/add" method="post">
                <input type="hidden" name="uid" value="<?= $_SESSION['token'] ?>" />
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
                <input type="hidden" name="uid" value="<?= $_SESSION['token'] ?>" />

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
