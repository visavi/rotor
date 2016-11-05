<?php foreach ($comments as $data): ?>
    <div class="b">

        <i class="fa fa-comment"></i> <b><a href="/blog/blog?act=comments&amp;id=<?=$data['blog']?>"><?=$data['title']?></a></b> (<?=$data['comments']?>)

        <?php if (is_admin()): ?>
            — <a href="/blogactive?act=del&amp;id=<?=$data['id']?>&amp;uz=<?=$data['author']?>&amp;start=<?=$start?>&amp;uid=<?=$_SESSION['token']?>">Удалить</a>
        <?php endif; ?>

    </div>
    <div>
        <?=bb_code($data['text'])?>
        <br />

        Написал: <?=nickname($data['author'])?> <small>(<?=date_fixed($data['time'])?>)</small><br />

        <?php if (is_admin() || empty($config['anonymity'])): ?>
            <span class="data">(<?=$data['brow']?>, <?=$data['ip']?>)</span>
        <?php endif; ?>

    </div>
<?php endforeach; ?>
