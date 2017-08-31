<?php $__env->startSection('title'); ?>
    Авторизация - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Авторизация</h1>

    <?php if(isset($_SESSION['social'])): ?>
        <div class="bg-success padding">
            <img class="img-circle border" alt="photo" src="<?php echo e($_SESSION['social']->photo); ?>" style="width: 48px; height: 48px;">
            <span class="label label-primary"><?php echo e($_SESSION['social']->network); ?></span> <?php echo e($_SESSION['social']->first_name); ?> <?php echo e($_SESSION['social']->last_name); ?> <?php echo e(isset($_SESSION['social']->nickname) ? '('.$_SESSION['social']->nickname.')' : ''); ?>

        </div>
        <div class="bg-info padding" style="margin-bottom: 30px;">
            Профиль не связан с какой-либо учетной записью на сайте. Войдите на сайт или зарегистирируйтесь, чтобы связать свою учетную запись с профилем социальной сети.<br>
            Или выберите другую социальную сеть для входа.
        </div>
    <?php endif; ?>

    <script src="//ulogin.ru/js/ulogin.js"></script>
    <div style="padding: 5px;" id="uLogin" data-ulogin="display=panel;fields=first_name,last_name,photo;optional=sex,email,nickname;providers=vkontakte,odnoklassniki,mailru,facebook,twitter,google,yandex;redirect_uri=<?php echo e(Setting::get('home')); ?>%2Flogin">
    </div>

    <div class="form">
        <form method="post">


            <div class="form-group">
                <label for="inputLogin">Логин:</label>
                <input class="form-control" name="login" id="inputLogin" maxlength="20" value="<?php echo e(App::getInput('login')); ?>" required>

                <label for="inputPassword">Пароль:</label>
                <input class="form-control" name="pass" type="password" id="inputPassword" maxlength="20" required>
                <label>
                    <input name="remember" type="checkbox" value="1" checked="checked"> Запомнить меня
                </label>
            </div>

            <button class="btn btn-primary">Войти</button>
        </form>
    </div>
    <br>
    <a href="/register">Регистрация</a><br>
    <a href="/recovery">Забыли пароль?</a><br><br>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>