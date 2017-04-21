<?php foreach ($comments as $data): ?>
    <div class="b">

        <i class="fa fa-comment"></i> <b><a href="/blog/blog?act=comments&amp;id=<?=$data['relate_id']?>"><?=$data['title']?></a></b> (<?=$data['comments']?>)

        <?php if (is_admin()): ?>
            — <a href="/blog/active?act=del&amp;id=<?=$data['id']?>&amp;uz=<?=$data['user']?>&amp;page=<?=$page['current']?>&amp;uid=<?=$_SESSION['token']?>">Удалить</a>
        <?php endif; ?>

    </div>
    <div>
        <?=App::bbCode($data['text'])?>
        <br />

        Написал: <?=$data['user']?> <small>(<?=date_fixed($data['time'])?>)</small><br />

        <?php if (is_admin() || empty(App::setting('anonymity'))): ?>
            <span class="data">(<?=$data['brow']?>, <?=$data['ip']?>)</span>
        <?php endif; ?>

    </div>
<?php endforeach; ?>
