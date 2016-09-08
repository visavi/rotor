@extends('layout')

@section('title', e($topics['topics_title']).' - @parent')

@section('content')

    <h1>{{ $topics['topics_title'] }}</h1>
    <a href="/forum">Форум</a> /

    <?php if (!empty($topics['subparent'])): ?>
        <a href="/forum/<?=$topics['subparent']['forums_id']?>"><?=$topics['subparent']['forums_title']?></a> /
    <?php endif; ?>

    <a href="/forum/<?=$topics['forums_id']?>"><?=$topics['forums_title']?></a> /
    <a href="/topic/<?=$tid?>/print">Скачать</a> / <a href="/topic/<?=$tid?>/rss">RSS-лента</a>

    <?php if (is_user()): ?>
        <?php if ($topics['topics_author'] == $log && empty($topics['topics_closed']) && App::user('users_point') >= $config['editforumpoint']): ?>
           / <a href="topic.php?act=closed&amp;tid=<?=$tid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>">Закрыть</a>
           / <a href="topic.php?act=edittopic&amp;tid=<?=$tid?>">Изменить</a>
        <?php endif; ?>

        <?php if (empty($topics['bookmark'])): ?>
            / <a href="bookmark.php?act=add&amp;tid=<?=$tid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>">В закладки</a>
        <?php else: ?>
            / <a href="bookmark.php?act=remove&amp;tid=<?=$tid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>">Из закладок</a>
        <?php endif; ?>

        <br />
    <?php endif; ?>

    <?php if (!empty($topics['curator'])): ?>
        Кураторы темы:
        <?php foreach ($topics['curator'] as $key => $curator): ?>
            <?php $comma = (empty($key)) ? '' : ', '; ?>
            <?=$comma?><?=profile($curator)?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($topics['topics_note'])): ?>
        <div class="info"><?=bb_code($topics['topics_note'])?></div>
    <?php endif; ?>

    <hr />

    <?php if (is_admin()): ?>
        <?php if (empty($topics['topics_closed'])): ?>
            <a href="/admin/forum.php?act=acttopic&amp;do=closed&amp;tid=<?=$tid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>">Закрыть</a> /
        <?php else: ?>
            <a href="/admin/forum.php?act=acttopic&amp;do=open&amp;tid=<?=$tid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>">Открыть</a> /
        <?php endif; ?>

        <?php if (empty($topics['topics_locked'])): ?>
            <a href="/admin/forum.php?act=acttopic&amp;do=locked&amp;tid=<?=$tid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>">Закрепить</a> /
        <?php else: ?>
            <a href="/admin/forum.php?act=acttopic&amp;do=unlocked&amp;tid=<?=$tid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>">Открепить</a> /
        <?php endif; ?>

        <a href="/admin/forum.php?act=edittopic&amp;tid=<?=$tid?>&amp;start=<?=$start?>">Изменить</a> /
        <a href="/admin/forum.php?act=movetopic&amp;tid=<?=$tid?>">Переместить</a> /
        <a href="/admin/forum.php?act=deltopics&amp;fid=<?=$topics['forums_id']?>&amp;del=<?=$tid?>&amp;uid=<?=$_SESSION['token']?>" onclick="return confirm('Вы действительно хотите удалить данную тему?')">Удалить</a> /
        <a href="/admin/forum.php?act=topic&amp;tid=<?=$tid?>&amp;start=<?=$start?>">Управление</a><br />
    <?php endif; ?>

    <?php if (!empty($topics['is_moder'])): ?>
        <form action="topic.php?act=del&amp;tid=<?=$tid?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>" method="post">
    <?php endif; ?>

    <?php if ($total > 0): ?>
        <?php foreach ($topics['posts'] as $key=>$data): ?>
            <?php $num = ($start + $key + 1); ?>
            <div class="post">
            <div class="b" id="post_<?=$data['posts_id']?>">

                <?php if (!empty($log) && $log != $data['posts_user']): ?>
                    <div class="pull-right">
                        <a href="#" onclick="return postReply('<?= nickname($data['posts_user']) ?>')" title="Ответить"><i class="fa fa-reply text-muted"></i></a>

                        <a href="#" onclick="return postQuote(this)" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>

                        <noindex><a href="topic.php?act=spam&amp;tid=<?=$tid?>&amp;pid=<?=$data['posts_id']?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>" onclick="return confirm('Вы подтверждаете факт спама?')" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a></noindex>
                    </div>
                <?php endif; ?>

                <?php if ($log == $data['posts_user'] && $data['posts_time'] + 600 > SITETIME): ?>
                    <div class="pull-right">
                        <a href="topic.php?act=edit&amp;tid=<?=$tid?>&amp;pid=<?=$data['posts_id']?>&amp;start=<?=$start?>" title="Редактировать"><i class="fa fa-pencil text-muted"></i></a>
                    </div>
                <?php endif; ?>

                <div class="img"><?=user_avatars($data['posts_user'])?></div>

                <?php if (!empty($topics['is_moder'])): ?>
                    <span class="imgright">
                        <a href="topic.php?act=modedit&amp;tid=<?=$tid?>&amp;pid=<?=$data['posts_id']?>&amp;start=<?=$start?>">Ред.</a> <input type="checkbox" name="del[]" value="<?=$data['posts_id']?>" />
                    </span>
                <?php endif; ?>

                <?=$num?>. <b><?=profile($data['posts_user'])?></b> <small>(<?=date_fixed($data['posts_time'])?>)</small><br />
                <?=user_title($data['posts_user'])?> <?=user_online($data['posts_user'])?>
            </div>


            <div class="message"><?=bb_code($data['posts_text'])?></div>

            <?php if (!empty($topics['posts_files'])): ?>
                <?php if (isset($topics['posts_files'][$data['posts_id']])): ?>
                    <div class="hiding"><i class="fa fa-paperclip" aria-hidden="true"></i> <b>Прикрепленные файлы:</b><br />
                    <?php foreach ($topics['posts_files'][$data['posts_id']] as $file): ?>
                        <?php $ext = getExtension($file['file_hash']); ?>


                        <img src="/images/icons/<?=icons($ext)?>" alt="image" />
                        <a href="/upload/forum/<?=$topics['topics_id']?>/<?=$file['file_hash']?>"><?=$file['file_name']?></a> (<?=formatsize($file['file_size'])?>)<br />
                        <?php if (in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))): ?>
                            <a href="/upload/forum/<?=$topics['topics_id']?>/<?=$file['file_hash']?>"><?= resize_image('upload/forum/', $topics['topics_id'].'/'.$file['file_hash'], $config['previewsize'], array('alt' => $file['file_name'])) ?></a><br />
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($data['posts_edit'])): ?>
                <small><i class="fa fa-exclamation-circle text-danger"></i> Отредактировано: <?=nickname($data['posts_edit'])?> (<?=date_fixed($data['posts_edit_time'])?>)</small><br />
            <?php endif; ?>

            <?php if (is_admin() || empty($config['anonymity'])): ?>
                <span class="data">(<?=$data['posts_brow']?>, <?=$data['posts_ip']?>)</span>
            <?php endif; ?>

            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <?php show_error('Сообщений еще нет, будь первым!'); ?>
    <?php endif; ?>

    <?php if (!empty($topics['is_moder'])): ?>
        <span class="imgright">
            <input type="submit" value="Удалить выбранное" />
        </span>
        </form>
    <?php endif; ?>

    <?php page_strnavigation('/topic/'.$tid.'?', $config['forumpost'], $start, $total); ?>

    <?php if (is_user()): ?>
        <?php if (empty($topics['topics_closed'])): ?>
            <div class="form">
                <form action="/topic/<?=$tid?>/create" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>" />

                    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>" />

                    <div class="form-group{{ App::hasError('msg') }}">
                        <label for="markItUp">Сообщение:</label>
                        <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="Текст сообщения" required>{{ App::getInput('msg') }}</textarea>
                        {!! App::textError('msg') !!}
                    </div>

                    <?php if (App::user('users_point') >= $config['forumloadpoints']): ?>
                        <div class="js-attach-form" style="display: none;">

                            <label class="btn btn-sm btn-default" for="inputFile">
                                <input id="inputFile" type="file" name="file"  style="display:none;" onchange="$('#upload-file-info').html($(this).val().replace('C:\\fakepath\\', ''));">
                                Выбрать файл
                            </label>
                            <span class='label label-info' id="upload-file-info"></span>

                            <div class="info">
                                Максимальный вес файла: <b><?=round($config['forumloadsize']/1024)?></b> Kb<br />
                                Допустимые расширения: <?=str_replace(',', ', ', $config['forumextload'])?>
                            </div><br />
                        </div>

                        <span class="imgright js-attach-button">
                            <a href="#" onclick="return showAttachForm();">Загрузить файл</a>
                        </span>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary">Написать</button>
                </form>
            </div><br />

        <?php else: ?>
            <?php show_error('Данная тема закрыта для обсуждения!'); ?>
        <?php endif; ?>
    <?php else: ?>
        <?php show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо'); ?>
    <?php endif; ?>

    <a href="/smiles">Смайлы</a>  /
    <a href="/tags">Теги</a>  /
    <a href="/rules">Правила</a> /
    <a href="/forum/top/themes">Топ тем</a> /
    <a href="/forum/search?fid=<?=$topics['forums_id']?>">Поиск</a><br />
@stop
