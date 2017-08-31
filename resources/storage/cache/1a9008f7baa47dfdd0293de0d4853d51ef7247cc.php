<?php $__env->startSection('title'); ?>
    Анкета пользователя <?php echo e($user['login']); ?> - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1><?php echo user_avatars($user); ?> <?php echo e($user['login']); ?> <small>#<?php echo e($user['id']); ?> <?php echo e(user_visit($user)); ?></small></h1>

    <?php if($user['confirmreg'] == 1): ?>
        <b><span style="color:#ff0000">Внимание, аккаунт требует подтверждение регистрации!</span></b><br>
    <?php endif; ?>

    <?php if($user['ban'] == 1 && $user['timeban'] > SITETIME): ?>
        <div class="form">
            <b><span style="color:#ff0000">Внимание, пользователь забанен!</span></b><br>
            До окончания бана осталось <?php echo e(formattime($user['timeban'] - SITETIME)); ?><br>
            Причина: <?php echo e(App::bbCode($user['reasonban'])); ?>

        </div>
    <?php endif; ?>

    <?php if($user['level'] >= 101 && $user['level'] <= 105): ?>
        <div class="alert alert-info">Должность: <b><?php echo e(user_status($user['level'])); ?></b></div>
    <?php endif; ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 push-md-6">
                <div class="float-right">

                    <?php if(!empty($user['picture']) && file_exists(HOME.'/uploads/photos/'.$user['picture'])): ?>
                        <a class="gallery" href="/uploads/photos/<?php echo e($user['picture']); ?>">
                            <?php echo resize_image('uploads/photos/', $user['picture'], Setting::get('previewsize'), ['alt' => $user['login'], 'class' => 'img-fluid rounded']); ?></a>
                    <?php else: ?>
                        <img src="/assets/img/images/photo.jpg" alt="Фото" class="float-right img-fluid rounded">
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6 pull-md-6">
                Cтатус: <b><a href="/statusfaq"><?php echo user_title($user); ?></a></b><br>

                <?php echo user_gender($user); ?>

                Пол:
                <?php echo e(($user['gender'] == 1) ? 'Мужской' : 'Женский'); ?><br>

                Логин: <b><?php echo e($user['login']); ?></b><br>

                <?php if(!empty($user['name'])): ?>
                    Имя: <b><?php echo e($user['name']); ?><br></b>
                <?php endif; ?>

                <?php if(!empty($user['country'])): ?>
                    Страна: <b><?php echo e($user['country']); ?><br></b>
                <?php endif; ?>

                <?php if(!empty($user['city'])): ?>
                    Откуда: <?php echo e($user['city']); ?><br>
                <?php endif; ?>

                <?php if(!empty($user['birthday'])): ?>
                    Дата рождения: <?php echo e($user['birthday']); ?><br>
                <?php endif; ?>

                <?php if(!empty($user['icq'])): ?>
                    ICQ: <?php echo e($user['icq']); ?><br>
                <?php endif; ?>

                <?php if(!empty($user['skype'])): ?>
                    Skype: <?php echo e($user['skype']); ?><br>
                <?php endif; ?>

                Всего посeщений: <?php echo e($user['visits']); ?><br>
                Сообщений на форуме: <?php echo e($user['allforum']); ?><br>
                Сообщений в гостевой: <?php echo e($user['allguest']); ?><br>
                Комментариев: <?php echo e($user['allcomments']); ?><br>
                Актив: <?php echo e(points($user['point'])); ?><br>
                Денег: <?php echo e(moneys($user['money'])); ?><br>

                <?php if(!empty($user['themes'])): ?>
                Используемый скин: <?php echo e($user['themes']); ?><br>
                <?php endif; ?>
                Дата регистрации: <?php echo e(date_fixed($user['joined'], 'j F Y')); ?><br>

                <?php $invite = Invite::where('invite_user_id', $user['id'])->first(); ?>
                <?php if(!empty($invite)): ?>
                    Зарегистрирован по приглашению: <?php echo profile($invite->user); ?><br>
                <?php endif; ?>

                Последняя авторизация: <?php echo e(date_fixed($user['timelastlogin'])); ?><br>

                <a href="/banhist?uz=<?php echo e($user['login']); ?>">Строгих нарушений: <?php echo e($user['totalban']); ?></a><br>

                <a href="/rating/<?php echo e($user->login); ?>/received">Репутация: <b><?php echo format_num($user['rating']); ?></b> (+<?php echo e($user['posrating']); ?>/-<?php echo e($user['negrating']); ?>)</a><br>

                <?php if(is_user() && App::getUsername() != $user['login']): ?>
                    [ <a href="/user/<?php echo e($user['login']); ?>/rating?vote=1"><i class="fa fa-thumbs-up"></i><span style="color:#0099cc"> Плюс</span></a> /
                    <a href="/user/<?php echo e($user['login']); ?>/rating?vote=0"><span style="color:#ff0000">Минус</span> <i class="fa fa-thumbs-down"></i></a> ]<br>
                <?php endif; ?>

            </div>

            <div class="col-md-12">

                <?php if(!empty($user['info'])): ?>
                    <div class="alert alert-warning"><b>О себе</b>:<br><?php echo App::bbCode($user['info']); ?></div>
                <?php endif; ?>

                <b><a href="/forum/active/themes?user=<?php echo e($user['login']); ?>">Форум</a></b> (<a href="/forum/active/posts?user=<?php echo e($user['login']); ?>">Сообщ.</a>) /
                <b><a href="/load/active?act=files&amp;uz=<?php echo e($user['login']); ?>">Загрузки</a></b> (<a href="/load/active?act=comments&amp;uz=<?php echo e($user['login']); ?>">комм.</a>) /
                <b><a href="/blog/active/articles?user=<?php echo e($user['login']); ?>">Блоги</a></b> (<a href="/blog/active/comments?user=<?php echo e($user['login']); ?>">комм.</a>) /
                <b><a href="/gallery/album/<?php echo e($user['login']); ?>">Галерея</a></b> (<a href="/gallery/comments/<?php echo e($user['login']); ?>">комм.</a>)<br>
            </div>
        </div>
    </div>

    <?php if(is_admin()): ?>
        <?php $usernote = Note::where('user_id', $user['id'])->first(); ?>
    <div class="alert alert-success">
        <i class="fa fa-thumb-tack"></i> <b>Заметка:</b> (<a href="/user/<?php echo e($user['login']); ?>/note">Изменить</a>)<br>

        <?php if(!empty($usernote['text'])): ?>
            <?php echo App::bbCode($usernote['text']); ?><br>
            Изменено: <?php echo profile($usernote->editUser); ?> (<?php echo e(date_fixed($usernote['updated_at'])); ?>)<br>
        <?php else: ?>
            Записей еще нет!<br>
        <?php endif; ?>

        </div>
    <?php endif; ?>

    <div class="alert alert-info">
        <i class="fa fa-sticky-note"></i> <a href="/wall?uz=<?php echo e($user['login']); ?>">Стена сообщений</a> (<?php echo e(user_wall($user)); ?>)<br>

        <?php if($user['login'] != App::getUsername()): ?>
            <i class="fa fa-address-book"></i> Добавить в
            <a href="/contact?act=add&amp;uz=<?php echo e($user['login']); ?>&amp;uid=<?php echo e($_SESSION['token']); ?>">контакт</a> /
            <a href="/ignore?act=add&amp;uz=<?php echo e($user['login']); ?>&amp;uid=<?php echo e($_SESSION['token']); ?>">игнор</a><br>
            <i class="fa fa-envelope"></i> <a href="/private/send?user=<?php echo e($user['login']); ?>">Отправить сообщение</a><br>

            <i class="fa fa-money"></i> <a href="/transfer?uz=<?php echo e($user['login']); ?>">Перечислить денег</a><br>

            <?php if(!empty($user['site'])): ?>
                <i class="fa fa-home"></i> <a href="<?php echo e($user['site']); ?>">Перейти на сайт <?php echo e($user['login']); ?></a><br>
            <?php endif; ?>

            <?php if(is_admin([101, 102, 103])): ?>
                <?php if(!empty(Setting::get('invite'))): ?>
                    <i class="fa fa-ban"></i> <a href="/admin/invitations?act=send&amp;user=<?php echo e($user['login']); ?>&amp;uid=<?php echo e($_SESSION['token']); ?>">Отправить инвайт</a><br>
                <?php endif; ?>
            <i class="fa fa-ban"></i> <a href="/admin/ban?act=edit&amp;uz=<?php echo e($user['login']); ?>">Бан / Разбан</a><br>
            <?php endif; ?>

            <?php if(is_admin([101, 102])): ?>
                <i class="fa fa-wrench"></i> <a href="/admin/users?act=edit&amp;uz=<?php echo e($user['login']); ?>">Редактировать</a><br>
            <?php endif; ?>
        <?php else: ?>
        <i class="fa fa-user-circle-o"></i> <a href="/profile">Мой профиль</a><br>
        <i class="fa fa-cog"></i> <a href="/account">Мои данные</a><br>
        <i class="fa fa-wrench"></i> <a href="/setting">Настройки</a><br>
        <?php endif; ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>