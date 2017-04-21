@extends('layout')

@section('title')
    {{ $topic['title'] }} (Стр. {{ $page['current'] }}) - @parent
@stop

@section('description', 'Обсуждение темы: '.$topic['title'].' (Стр. '.$page['current'].')')

@section('content')
    <h1>{{ $topic['title'] }}</h1>
    <a href="/forum">Форум</a> /

    <?php if ($topic->getForum()->parent): ?>
        <a href="/forum/<?= $topic->getForum()->parent->id ?>"><?= $topic->getForum()->parent->title ?></a> /
    <?php endif; ?>

    <a href="/forum/<?= $topic->getForum()->id ?>"><?= $topic->getForum()->title ?></a> /
    <a href="/topic/<?=$topic['id']?>/print">Печать</a> / <a href="/topic/<?=$topic['id']?>/rss">RSS-лента</a>

    <?php if (is_user()): ?>
        <?php if ($topic->getUser()->id == App::getUserId() && empty($topic['closed']) && App::user('point') >= App::setting('editforumpoint')): ?>
           / <a href="/topic/<?= $topic['id'] ?>/close?token=<?=$_SESSION['token']?>">Закрыть</a>
           / <a href="/topic/<?= $topic['id'] ?>/edit">Изменить</a>
        <?php endif; ?>

        <?php $bookmark = $topic['bookmark_posts'] ? 'Из закладок' : 'В закладки'; ?>
        / <a href="#" onclick="return bookmark(this)" data-tid="{{ $topic['id'] }}" data-token="{{ $_SESSION['token'] }}">{{ $bookmark }}</a>
    <?php endif; ?>

    <?php if (!empty($topic['curators'])): ?>
       <div>
            <span class="label label-info">
                <i class="fa fa-wrench"></i> Кураторы темы:
                <?php foreach ($topic['curators'] as $key => $curator): ?>
                    <?php $comma = (empty($key)) ? '' : ', '; ?>
                    <?=$comma?><?=profile($curator)?>
                <?php endforeach; ?>
            </span>
        </div>
    <?php endif; ?>

    <?php if (!empty($topic['note'])): ?>
        <div class="info"><?=App::bbCode($topic['note'])?></div>
    <?php endif; ?>

    <hr />

    <?php if (is_admin()): ?>
        <?php if (empty($topic['closed'])): ?>
            <a href="/admin/forum?act=acttopic&amp;do=closed&amp;tid=<?=$topic['id']?>&amp;page=<?=$page['current']?>&amp;token=<?=$_SESSION['token']?>">Закрыть</a> /
        <?php else: ?>
            <a href="/admin/forum?act=acttopic&amp;do=open&amp;tid=<?=$topic['id']?>&amp;page=<?=$page['current']?>&amp;token=<?=$_SESSION['token']?>">Открыть</a> /
        <?php endif; ?>

        <?php if (empty($topic['locked'])): ?>
            <a href="/admin/forum?act=acttopic&amp;do=locked&amp;tid=<?=$topic['id']?>&amp;page=<?=$page['current']?>&amp;token=<?=$_SESSION['token']?>">Закрепить</a> /
        <?php else: ?>
            <a href="/admin/forum?act=acttopic&amp;do=unlocked&amp;tid=<?=$topic['id']?>&amp;page=<?=$page['current']?>&amp;token=<?=$_SESSION['token']?>">Открепить</a> /
        <?php endif; ?>

        <a href="/admin/forum?act=edittopic&amp;tid=<?=$topic['id']?>&amp;page=<?=$page['current']?>">Изменить</a> /
        <a href="/admin/forum?act=movetopic&amp;tid=<?=$topic['id']?>">Переместить</a> /
        <a href="/admin/forum?act=deltopics&amp;fid=<?=$topic['forum_id']?>&amp;del=<?=$topic['id']?>&amp;token=<?=$_SESSION['token']?>" onclick="return confirm('Вы действительно хотите удалить данную тему?')">Удалить</a> /
        <a href="/admin/forum?act=topic&amp;tid=<?=$topic['id']?>&amp;page=<?=$page['current']?>">Управление</a><br />
    <?php endif; ?>

    @if($vote['answers'])
        <h3>{{ $vote['title'] }}</h3>

        @if(! is_user() || $vote['poll'] || $vote['closed'])
            @foreach($vote['voted'] as $key => $data)
                <?php $proc = round(($data * 100) / $vote['sum'], 1); ?>
                <?php $maxproc = round(($data * 100) / $vote['max']); ?>

                <b>{{ $key }}</b> (Голосов: {{ $data }})<br />
                {!! App::progressBar($maxproc, $proc.'%') !!}
            @endforeach
        @else
            <form action="/topic/{{ $topic['id'] }}/vote?page={{ $page['current'] }}" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}" />
                @foreach($vote['answers'] as $answer)
                    <label><input name="poll" type="radio" value="{{ $answer['id'] }}" /> {{ $answer['answer'] }}</label><br />
                @endforeach
                <br /><button type="submit" class="btn btn-sm btn-primary">Голосовать</button>
            </form><br />
        @endif

        Всего проголосовало: {{ $vote['count'] }}
    @endif

    <?php if ($topic['isModer']): ?>
    <form action="/topic/<?=$topic['id']?>/delete?page=<?=$page['current']?>" method="post">
        <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
        <?php endif; ?>

    <?php if ($page['total'] > 0): ?>
        <?php foreach ($posts as $key=>$data): ?>
            <?php $num = ($page['offset'] + $key + 1); ?>
            <div class="post">
            <div class="b" id="post_<?=$data['id']?>">

                <div class="pull-right">
                    <?php if (App::getUserId() != $data['user_id']): ?>

                        <a href="#" onclick="return postReply('<?= $data->getUser()->login ?>')" title="Ответить"><i class="fa fa-reply text-muted"></i></a>

                        <a href="#" onclick="return postQuote(this)" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>

                        <noindex>
                            <a href="#" onclick="return sendComplaint(this)" data-type="/topic" data-id="{{ $data['id'] }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $page['current'] }}" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a>
                        </noindex>
                    <?php endif; ?>

                    <?php if ((App::getUserId() == $data['user_id'] && $data['created_at'] + 600 > SITETIME) || $topic['isModer']): ?>
                        <a href="/topic/<?=$topic['id']?>/<?=$data['id']?>/edit?page=<?=$page['current']?>" title="Редактировать"><i class="fa fa-pencil text-muted"></i></a>
                        <?php if ($topic['isModer']): ?>
                        <input type="checkbox" name="del[]" value="<?=$data['id']?>" />
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="js-rating">
                        @unless (App::getUserId() == $data['user_id'])
                            <a class="post-rating-down<?= $data->vote == -1 ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $data['id'] }}" data-type="Post" data-vote="-1" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-minus"></i></a>
                        @endunless
                        <span>{{ $data['rating'] }}</span>
                        @unless (App::getUserId() == $data['user_id'])
                            <a class="post-rating-up<?= $data->vote == 1 ? ' active' : '' ?>" href="#" onclick="return changeRating(this);" data-id="{{ $data['id'] }}" data-type="Post" data-vote="1" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-plus"></i></a>
                        @endunless
                    </div>
                </div>

                <div class="img"><?=user_avatars($data->user)?></div>

                <?=$num?>. <b><?=profile($data->user)?></b> <small>(<?=date_fixed($data['created_at'])?>)</small><br />
                <?=user_title($data->user)?> <?=user_online($data->user)?>
            </div>

            <div class="message">
                <?=App::bbCode($data['text'])?>
            </div>

            <?php if (! $data->files->isEmpty()): ?>
                <div class="hiding"><i class="fa fa-paperclip"></i> <b>Прикрепленные файлы:</b><br />
                <?php foreach ($data->files as $file): ?>
                    <?php $ext = getExtension($file['hash']); ?>

                    <?= icons($ext) ?>
                    <a href="/uploads/forum/<?=$topic['id']?>/<?=$file['hash']?>"><?=$file['name']?></a> (<?=formatsize($file['size'])?>)<br />
                    <?php if (in_array($ext, ['jpg', 'jpeg', 'gif', 'png'])): ?>
                        <a href="/uploads/forum/<?=$topic['id']?>/<?=$file['hash']?>" class="gallery" data-group="{{ $data['id'] }}"><?= resize_image('uploads/forum/', $topic['id'].'/'.$file['hash'], App::setting('previewsize'), ['alt' => $file['name']]) ?></a><br />
                    <?php endif; ?>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($data['edit_user_id']): ?>
                <small><i class="fa fa-exclamation-circle text-danger"></i> Отредактировано: <?= $data->getEditUser()->login ?> (<?=date_fixed($data['updated_at'])?>)</small><br />
            <?php endif; ?>

            <?php if (is_admin() || empty(App::setting('anonymity'))): ?>
                <span class="data">(<?=$data['brow']?>, <?=$data['ip']?>)</span>
            <?php endif; ?>

            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <?php show_error('Сообщений еще нет, будь первым!'); ?>
    <?php endif; ?>

    <?php if ($topic['isModer']): ?>
        <span class="pull-right">
            <button type="submit" class="btn btn-danger">Удалить выбранное</button>
        </span>
        </form>
    <?php endif; ?>

    <?php App::pagination($page) ?>

    <?php if (is_user()): ?>
        <?php if (empty($topic['closed'])): ?>
            <div class="form">
                <form action="/topic/<?=$topic['id']?>/create" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>" />

                    <div class="form-group{{ App::hasError('msg') }}">
                        <label for="markItUp">Сообщение:</label>
                        <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="Текст сообщения" required>{{ App::getInput('msg') }}</textarea>
                        {!! App::textError('msg') !!}
                    </div>

                    <?php if (App::user('point') >= App::setting('forumloadpoints')): ?>
                        <div class="js-attach-form" style="display: none;">

                            <label class="btn btn-sm btn-default" for="inputFile">
                                <input id="inputFile" type="file" name="file"  style="display:none;" onchange="$('#upload-file-info').html($(this).val().replace('C:\\fakepath\\', ''));">
                                Выбрать файл
                            </label>
                            <span class='label label-info' id="upload-file-info"></span>

                            <div class="info">
                                Максимальный вес файла: <b><?=round(App::setting('forumloadsize')/1024)?></b> Kb<br />
                                Допустимые расширения: <?=str_replace(',', ', ', App::setting('forumextload'))?>
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
    <a href="/forum/top/posts">Топ постов</a> /
    <a href="/forum/search?fid=<?=$topic['forum_id']?>">Поиск</a><br />
@stop
