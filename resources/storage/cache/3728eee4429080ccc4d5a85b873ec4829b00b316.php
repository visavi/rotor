<?php $__env->startSection('title'); ?>
    Форум - ##parent-placeholder-3c6de1b7dd91465d437ef415f94f36afc1fbc8a8##
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <h1>Создание новой темы</h1>
    <div class="form">
        <form action="/forum/create" method="post">
            <input type="hidden" name="token" value="<?php echo e($_SESSION['token']); ?>">

            <div class="form-group<?php echo e(App::hasError('fid')); ?>">
                <label for="inputForum">Форум</label>
                <select class="form-control" id="inputForum" name="fid">

                    <?php $__currentLoopData = $forums; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($data['id']); ?>"<?php echo ($fid == $data['id']) ? ' selected="selected"' : ''; ?><?php echo !empty($data['closed']) ? ' disabled="disabled"' : ''; ?>><?php echo e($data['title']); ?></option>

                        <?php if(!$data->children->isEmpty()): ?>
                            <?php $__currentLoopData = $data->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $datasub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($datasub['id']); ?>"<?php echo $fid == $datasub['id'] ? ' selected="selected"' : ''; ?><?php echo !empty($datasub['closed']) ? ' disabled="disabled"' : ''; ?>>– <?php echo e($datasub['title']); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </select>
                <?php echo App::textError('fid'); ?>

            </div>

            <div class="form-group<?php echo e(App::hasError('title')); ?>">
                <label for="inputTitle">Название темы</label>
                <input name="title" class="form-control" id="inputTitle" maxlength="50" placeholder="Название темы" value="<?php echo e(App::getInput('title')); ?>" required>
                <?php echo App::textError('title'); ?>

            </div>

            <div class="form-group<?php echo e(App::hasError('msg')); ?>">
                <label for="markItUp">Сообщение:</label>
                <textarea class="form-control" id="markItUp" rows="5" name="msg" required><?php echo e(App::getInput('msg')); ?></textarea>
                <?php echo App::textError('msg'); ?>

            </div>

            <?php $checkVote = App::getInput('vote') ? true : false; ?>
            <?php $checked = $checkVote ? ' checked="checked"' : ''; ?>
            <?php $display = $checkVote ? '' : ' style="display: none"'; ?>

            <label>
                <input name="vote" onchange="return showVoteForm();" type="checkbox"<?php echo $checked; ?>> Создать
                голосование
            </label><br>

            <div class="js-vote-form"<?php echo $display; ?>>
                <div class="form-group<?php echo e(App::hasError('question')); ?>">

                    <label for="inputQuestion">Вопрос:</label>
                    <input type="text" name="question" class="form-control" id="inputQuestion" value="<?php echo e(App::getInput('question')); ?>" maxlength="100">
                    <?php echo App::textError('question'); ?>

                </div>

                <div class="form-group<?php echo e(App::hasError('answer')); ?>">

                    <?php $answers = array_diff((array) App::getInput('answer'), ['']) ?>

                    <?php for($i=0; $i<10; $i++): ?>
                        <label for="inputAnswer<?php echo e($i); ?>">Ответ <?php echo e($i + 1); ?></label>
                        <input type="text" name="answer[]" class="form-control" id="inputAnswer<?php echo e($i); ?>" value="<?php echo e(isset($answers[$i]) ? $answers[$i] : ''); ?>" maxlength="50">
                    <?php endfor; ?>
                    <?php echo App::textError('answer'); ?>

                </div>
            </div>
            <button class="btn btn-primary">Создать тему</button>
        </form>
    </div><br>

    Прежде чем создать новую тему необходимо ознакомиться с правилами<br>
    <a href="/rules">Правила сайта</a><br>
    Также убедись что такой темы нет, чтобы не создавать одинаковые, для этого введи ключевое слово в поиске<br>
    <a href="/forum/search">Поиск по форуму</a><br>
    И если после этого вы уверены, что ваша тема будет интересна другим пользователям, то можете ее создать<br><br>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>