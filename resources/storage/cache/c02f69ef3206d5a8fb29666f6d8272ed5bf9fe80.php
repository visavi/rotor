<?php $__env->startSection('title'); ?>
    Мой профиль - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Мой профиль</h1>

    <i class="fa fa-book"></i>
    <a href="/user/<?php echo e(App::getUsername()); ?>">Моя анкета</a> /
    <b>Мой профиль</b> /
    <a href="/account">Мои данные</a> /
    <a href="/setting">Настройки</a><hr>

    <div class="form">
        <form method="post" action="/profile">
            <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">

            <div class="container-fluid">
                <div class="row">

                    <div class="col-md-6 flex-last">
                        <div class="float-right">
                            <?php if(!empty(App::user('picture')) && file_exists(HOME.'/uploads/photos/'.App::user('picture'))): ?>
                                <a class="gallery" href="/uploads/photos/<?php echo e(App::user('picture')); ?>">
                                    <?php echo resize_image('uploads/photos/', App::user('picture'), Setting::get('previewsize'), ['alt' => App::user('login'), 'class' => 'img-fluid rounded']); ?>

                                </a>
                                <a href="/pictures">Изменить</a> / <a href="/pictures/delete?token=<?php echo e($_SESSION['token']); ?>">Удалить</a>
                            <?php else: ?>
                                <img class="img-fluid rounded" src="/assets/img/images/photo.jpg" alt="Фото">
                                <a href="/pictures">Загрузить фото</a>
                            <?php endif; ?>
                            </div>
                        </div>

                    <div class="col-md-6 flex-first">

                        <div class="form-group<?php echo e(App::hasError('msg')); ?>">
                            <label for="inputName">Имя:</label>
                            <input class="form-control" id="inputName" name="name" maxlength="20" value="<?php echo e(App::getInput('name', App::user('name'))); ?>">
                            <?php echo App::textError('name'); ?>

                        </div>

                        <div class="form-group<?php echo e(App::hasError('country')); ?>">
                            <label for="inputCountry">Страна:</label>
                            <input class="form-control" id="inputCountry" name="country" maxlength="30" value="<?php echo e(App::getInput('country', App::user('country'))); ?>">
                            <?php echo App::textError('country'); ?>

                        </div>

                        <div class="form-group<?php echo e(App::hasError('city')); ?>">
                            <label for="inputCity">Город:</label>
                            <input class="form-control" id="inputCity" name="city" maxlength="50" value="<?php echo e(App::getInput('city', App::user('city'))); ?>">
                            <?php echo App::textError('city'); ?>

                        </div>

                        <div class="form-group<?php echo e(App::hasError('icq')); ?>">
                            <label for="inputIcq">ICQ:</label>
                            <input class="form-control" id="inputIcq" name="icq" maxlength="10" value="<?php echo e(App::getInput('icq', App::user('icq'))); ?>">
                            <?php echo App::textError('icq'); ?>

                        </div>

                        <div class="form-group<?php echo e(App::hasError('skype')); ?>">
                            <label for="inputSkype">Skype:</label>
                            <input class="form-control" id="inputSkype" name="skype" maxlength="32" value="<?php echo e(App::getInput('skype', App::user('skype'))); ?>">
                            <?php echo App::textError('skype'); ?>

                        </div>

                        <div class="form-group<?php echo e(App::hasError('site')); ?>">
                            <label for="inputSite">Сайт:</label>
                            <input class="form-control" id="inputSite" name="site" maxlength="50" value="<?php echo e(App::getInput('site', App::user('site'))); ?>">
                            <?php echo App::textError('site'); ?>

                        </div>


                        <div class="form-group<?php echo e(App::hasError('birthday')); ?>">
                            <label for="inputBirthday">Дата рождения (дд.мм.гггг):</label>
                            <input class="form-control" id="inputBirthday" name="birthday" maxlength="10" value="<?php echo e(App::getInput('birthday', App::user('birthday'))); ?>">
                            <?php echo App::textError('birthday'); ?>

                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group<?php echo e(App::hasError('info')); ?>">
                            <label for="markItUp">О себе:</label>
                            <textarea class="form-control" id="markItUp" cols="25" rows="5" name="info"><?php echo e(App::getInput('info', App::user('info'))); ?></textarea>
                            <?php echo App::textError('info'); ?>

                        </div>
                        <button class="btn btn-primary">Изменить</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>