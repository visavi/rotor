<?php $__env->startSection('title'); ?>
    <?php echo e($topic['title']); ?> (Стр. <?php echo e($page['current']); ?>) - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('description', 'Обсуждение темы: '.$topic['title'].' (Стр. '.$page['current'].')'); ?>

<?php $__env->startSection('content'); ?>
    <h1><?php echo e($topic['title']); ?></h1>
    <a href="/forum">Форум</a> /

    <?php if($topic->getForum()->parent): ?>
        <a href="/forum/<?php echo e($topic->getForum()->parent->id); ?>"><?php echo e($topic->getForum()->parent->title); ?></a> /
    <?php endif; ?>

    <a href="/forum/<?php echo e($topic->getForum()->id); ?>"><?php echo e($topic->getForum()->title); ?></a> /
    <a href="/topic/<?php echo e($topic['id']); ?>/print">Печать</a> / <a href="/topic/<?php echo e($topic['id']); ?>/rss">RSS-лента</a>

    <?php if(is_user()): ?>
        <?php if($topic->getUser()->id == App::getUserId() && empty($topic['closed']) && App::user('point') >= Setting::get('editforumpoint')): ?>
           / <a href="/topic/<?php echo e($topic['id']); ?>/close?token=<?php echo e($_SESSION['token']); ?>">Закрыть</a>
           / <a href="/topic/<?php echo e($topic['id']); ?>/edit">Изменить</a>
        <?php endif; ?>

        <?php $bookmark = $topic['bookmark_posts'] ? 'Из закладок' : 'В закладки'; ?>
        / <a href="#" onclick="return bookmark(this)" data-tid="<?php echo e($topic['id']); ?>" data-token="<?php echo e($_SESSION['token']); ?>"><?php echo e($bookmark); ?></a>
    <?php endif; ?>

    <?php if(!empty($topic['curators'])): ?>
       <div>
            <span class="label label-info">
                <i class="fa fa-wrench"></i> Кураторы темы:
                <?php $__currentLoopData = $topic['curators']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $curator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $comma = (empty($key)) ? '' : ', '; ?>
                    <?php echo e($comma); ?><?php echo profile($curator); ?>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </span>
        </div>
    <?php endif; ?>

    <?php if(!empty($topic['note'])): ?>
        <div class="info"><?php echo App::bbCode($topic['note']); ?></div>
    <?php endif; ?>

    <hr>

    <?php if(is_admin()): ?>
        <?php if(empty($topic['closed'])): ?>
            <a href="/admin/forum?act=acttopic&amp;do=closed&amp;tid=<?php echo e($topic['id']); ?>&amp;page=<?php echo e($page['current']); ?>&amp;token=<?php echo e($_SESSION['token']); ?>">Закрыть</a> /
        <?php else: ?>
            <a href="/admin/forum?act=acttopic&amp;do=open&amp;tid=<?php echo e($topic['id']); ?>&amp;page=<?php echo e($page['current']); ?>&amp;token=<?php echo e($_SESSION['token']); ?>">Открыть</a> /
        <?php endif; ?>

        <?php if(empty($topic['locked'])): ?>
            <a href="/admin/forum?act=acttopic&amp;do=locked&amp;tid=<?php echo e($topic['id']); ?>&amp;page=<?php echo e($page['current']); ?>&amp;token=<?php echo e($_SESSION['token']); ?>">Закрепить</a> /
        <?php else: ?>
            <a href="/admin/forum?act=acttopic&amp;do=unlocked&amp;tid=<?php echo e($topic['id']); ?>&amp;page=<?php echo e($page['current']); ?>&amp;token=<?php echo e($_SESSION['token']); ?>">Открепить</a> /
        <?php endif; ?>

        <a href="/admin/forum?act=edittopic&amp;tid=<?php echo e($topic['id']); ?>&amp;page=<?php echo e($page['current']); ?>">Изменить</a> /
        <a href="/admin/forum?act=movetopic&amp;tid=<?php echo e($topic['id']); ?>">Переместить</a> /
        <a href="/admin/forum?act=deltopics&amp;fid=<?php echo e($topic['forum_id']); ?>&amp;del=<?php echo e($topic['id']); ?>&amp;token=<?php echo e($_SESSION['token']); ?>" onclick="return confirm('Вы действительно хотите удалить данную тему?')">Удалить</a> /
        <a href="/admin/forum?act=topic&amp;tid=<?php echo e($topic['id']); ?>&amp;page=<?php echo e($page['current']); ?>">Управление</a><br>
    <?php endif; ?>

    <?php if($vote['answers']): ?>
        <h3><?php echo e($vote['title']); ?></h3>

        <?php if(!is_user() || $vote['poll'] || $vote['closed']): ?>
            <?php $__currentLoopData = $vote['voted']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $proc = round(($data * 100) / $vote['sum'], 1); ?>
                <?php $maxproc = round(($data * 100) / $vote['max']); ?>

                <b><?php echo e($key); ?></b> (Голосов: <?php echo e($data); ?>)<br>
                <?php echo App::progressBar($maxproc, $proc.'%'); ?>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php else: ?>
            <form action="/topic/<?php echo e($topic['id']); ?>/vote?page=<?php echo e($page['current']); ?>" method="post">
                <input type="hidden" name="token" value="<?php echo e($_SESSION['token']); ?>">
                <?php $__currentLoopData = $vote['answers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $answer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label><input name="poll" type="radio" value="<?php echo e($answer['id']); ?>"> <?php echo e($answer['answer']); ?></label><br>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <br><button class="btn btn-sm btn-primary">Голосовать</button>
            </form><br>
        <?php endif; ?>

        Всего проголосовало: <?php echo e($vote['count']); ?>

    <?php endif; ?>

    <?php if($topic['isModer']): ?>
        <form action="/topic/<?php echo e($topic['id']); ?>/delete?page=<?php echo e($page['current']); ?>" method="post">
            <input type="hidden" name="token" value="<?php echo e($_SESSION['token']); ?>">
        <?php endif; ?>

    <?php if($page['total'] > 0): ?>
        <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $num = ($page['offset'] + $key + 1); ?>
            <div class="post">
            <div class="b" id="post_<?php echo e($data['id']); ?>">

                <div class="float-right">
                    <?php if(App::getUserId() != $data['user_id']): ?>
                        <a href="#" onclick="return postReply(this)" title="Ответить"><i class="fa fa-reply text-muted"></i></a>

                        <a href="#" onclick="return postQuote(this)" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>

                        <a href="#" onclick="return sendComplaint(this)" data-type="<?php echo e(Post::class); ?>" data-id="<?php echo e($data['id']); ?>" data-token="<?php echo e($_SESSION['token']); ?>" data-page="<?php echo e($page['current']); ?>" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a>
                    <?php endif; ?>

                    <?php if((App::getUserId() == $data['user_id'] && $data['created_at'] + 600 > SITETIME) || $topic['isModer']): ?>
                        <a href="/topic/<?php echo e($topic['id']); ?>/<?php echo e($data['id']); ?>/edit?page=<?php echo e($page['current']); ?>" title="Редактировать"><i class="fa fa-pencil text-muted"></i></a>
                        <?php if($topic['isModer']): ?>
                            <input type="checkbox" name="del[]" value="<?php echo e($data['id']); ?>">
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="js-rating">
                        <?php if (! (App::getUserId() == $data['user_id'])): ?>
                            <a class="post-rating-down<?php echo e($data->vote == -1 ? ' active' : ''); ?>" href="#" onclick="return changeRating(this);" data-id="<?php echo e($data['id']); ?>" data-type="Post" data-vote="-1" data-token="<?php echo e($_SESSION['token']); ?>"><i class="fa fa-minus"></i></a>
                        <?php endif; ?>
                        <span><?php echo format_num($data['rating']); ?></span>
                        <?php if (! (App::getUserId() == $data['user_id'])): ?>
                            <a class="post-rating-up<?php echo e($data->vote == 1 ? ' active' : ''); ?>" href="#" onclick="return changeRating(this);" data-id="<?php echo e($data['id']); ?>" data-type="Post" data-vote="1" data-token="<?php echo e($_SESSION['token']); ?>"><i class="fa fa-plus"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="img"><?php echo user_avatars($data->user); ?></div>

                <?php echo e($num); ?>. <b><?php echo profile($data->user); ?></b> <small>(<?php echo e(date_fixed($data['created_at'])); ?>)</small><br>
                <?php echo user_title($data->user); ?> <?php echo user_online($data->user); ?>

            </div>

            <div class="message">
                <?php echo App::bbCode($data['text']); ?>

            </div>

            <?php if(!$data->files->isEmpty()): ?>
                <div class="hiding"><i class="fa fa-paperclip"></i> <b>Прикрепленные файлы:</b><br>
                <?php $__currentLoopData = $data->files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $ext = getExtension($file['hash']); ?>

                    <?php echo icons($ext); ?>

                    <a href="/uploads/forum/<?php echo e($topic['id']); ?>/<?php echo e($file['hash']); ?>"><?php echo e($file['name']); ?></a> (<?php echo e(formatsize($file['size'])); ?>)<br>
                    <?php if(in_array($ext, ['jpg', 'jpeg', 'gif', 'png'])): ?>
                        <a href="/uploads/forum/<?php echo e($topic['id']); ?>/<?php echo e($file['hash']); ?>" class="gallery" data-group="<?php echo e($data['id']); ?>"><?php echo resize_image('uploads/forum/', $topic['id'].'/'.$file['hash'], Setting::get('previewsize'), ['alt' => $file['name']]); ?></a><br>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>

            <?php if($data['edit_user_id']): ?>
                <small><i class="fa fa-exclamation-circle text-danger"></i> Отредактировано: <?php echo e($data->getEditUser()->login); ?>(<?php echo e(date_fixed($data['updated_at'])); ?>)</small><br>
            <?php endif; ?>

            <?php if(is_admin()): ?>
                <span class="data">(<?php echo e($data['brow']); ?>, <?php echo e($data['ip']); ?>)</span>
            <?php endif; ?>

            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php else: ?>
        <?php echo e(App::showError('Сообщений еще нет, будь первым!')); ?>

    <?php endif; ?>

    <?php if($topic['isModer']): ?>
        <span class="float-right">
            <button class="btn btn-danger">Удалить выбранное</button>
        </span>
        </form>
    <?php endif; ?>

    <?php echo e(App::pagination($page)); ?>


    <?php if(is_user()): ?>
        <?php if(empty($topic['closed'])): ?>
            <div class="form">
                <form action="/topic/<?php echo e($topic['id']); ?>/create" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?php echo e($_SESSION['token']); ?>">

                    <div class="form-group<?php echo e(App::hasError('msg')); ?>">
                        <label for="markItUp">Сообщение:</label>
                        <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="Текст сообщения" required><?php echo e(App::getInput('msg')); ?></textarea>
                        <?php echo App::textError('msg'); ?>

                    </div>

                    <?php if(App::user('point') >= Setting::get('forumloadpoints')): ?>
                        <div class="js-attach-form" style="display: none;">

                            <label class="btn btn-sm btn-secondary" for="inputFile">
                                <input id="inputFile" type="file" name="file"  style="display:none;" onchange="$('#upload-file-info').html($(this).val().replace('C:\\fakepath\\', ''));">
                                Выбрать файл
                            </label>
                            <span class='label label-info' id="upload-file-info"></span>

                            <div class="info">
                                Максимальный вес файла: <b><?php echo e(round(Setting::get('forumloadsize')/1024)); ?></b> Kb<br>
                                Допустимые расширения: <?php echo e(str_replace(',', ', ', Setting::get('forumextload'))); ?>

                            </div><br>
                        </div>

                        <span class="imgright js-attach-button">
                            <a href="#" onclick="return showAttachForm();">Загрузить файл</a>
                        </span>
                    <?php endif; ?>

                    <button class="btn btn-primary">Написать</button>
                </form>
            </div><br>

        <?php else: ?>
            <?php echo e(App::showError('Данная тема закрыта для обсуждения!')); ?>

        <?php endif; ?>
    <?php else: ?>
        <?php echo e(App::showError('Для добавления сообщения необходимо авторизоваться')); ?>

    <?php endif; ?>

    <a href="/smiles">Смайлы</a>  /
    <a href="/tags">Теги</a>  /
    <a href="/rules">Правила</a> /
    <a href="/forum/top/themes">Топ тем</a> /
    <a href="/forum/top/posts">Топ постов</a> /
    <a href="/forum/search?fid=<?php echo e($topic['forum_id']); ?>">Поиск</a><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>