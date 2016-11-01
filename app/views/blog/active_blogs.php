<?php foreach ($blogs as $data): ?>

    <div class="b">
        <i class="fa fa-pencil"></i>
        <b><a href="/blog/blog?act=view&amp;id=<?=$data['blogs_id']?>"><?=$data['blogs_title']?></a></b> (<?=format_num($data['blogs_rating'])?>)
    </div>

    <div>Автор: <?=profile($data['blogs_user'])?> (<?=date_fixed($data['blogs_time'])?>)<br />
        <i class="fa fa-comment"></i> <a href="/blog/blog?act=comments&amp;id=<?=$data['blogs_id']?>">Комментарии</a> (<?=$data['blogs_comments']?>)
        <a href="/blog/blog?act=end&amp;id=<?=$data['blogs_id']?>">&raquo;</a>
    </div>
<?php endforeach; ?>
