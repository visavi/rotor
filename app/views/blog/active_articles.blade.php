<?php foreach ($blogs as $data): ?>

    <div class="b">
        <i class="fa fa-pencil"></i>
        <b><a href="/blog/blog?act=view&amp;id=<?=$data['id']?>"><?=$data['title']?></a></b> (<?=format_num($data['rating'])?>)
    </div>

    <div>Автор: <?=profile($data['user'])?> (<?=date_fixed($data['time'])?>)<br>
        <i class="fa fa-comment"></i> <a href="/blog/blog?act=comments&amp;id=<?=$data['id']?>">Комментарии</a> (<?=$data['comments']?>)
        <a href="/blog/blog?act=end&amp;id=<?=$data['id']?>">&raquo;</a>
    </div>
<?php endforeach; ?>
