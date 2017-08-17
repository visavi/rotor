<?php foreach($events as $data): ?>
    <div class="b">
        <?=$data['closed'] == 0 ? '<i class="fa fa-plus-square-o"></i> ' : '<i class="fa fa-minus-square-o"></i>'; ?>

        <b><a href="/events?act=read&amp;id=<?=$data['id']?>"><?=$data['title']?></a></b><small> (<?=date_fixed($data['time'])?>)</small>
    </div>

    <?php if (!empty($data['image'])): ?>
        <div class="img">
            <a href="/uploads/events/<?=$data['image']?>"><?=resize_image('uploads/events/', $data['image'], 75, ['alt' => $data['title']])?></a>
        </div>
    <?php endif; ?>

    <?php if (App::getUsername() == $data['author'] && $data['time'] + 3600 > SITETIME): ?>
        <div class="right">
            <a href="/events?act=editevent&amp;id=<?=$data['id']?>">Редактировать</a>
        </div>
    <?php endif; ?>

    <?php if(stristr($data['text'], '[cut]')) {
        $data['text'] = current(explode('[cut]', $data['text'])).' <a href="/events?act=read&amp;id='.$data['id'].'">Читать далее &raquo;</a>';
    } ?>

    <div><?=App::bbCode($data['text'])?></div>

    <div style="clear:both;">Добавлено: <?=profile($data['author'])?><br>
        <a href="/events?act=comments&amp;id=<?=$data['id']?>">Комментарии</a> (<?=$data['comments']?>)
        <a href="/events?act=end&amp;id=<?=$data['id']?>">&raquo;</a>
    </div>

<?php endforeach; ?>
