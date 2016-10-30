<?php foreach ($blogs as $data): ?>

    <div class="b">
        <img src="/assets/img/images/edit.gif" alt="image" />
        <b><a href="/blog/blog?act=view&amp;id=<?=$data['blogs_id']?>"><?=$data['blogs_title']?></a></b> (<?=format_num($data['blogs_rating'])?>)
    </div>

    <div>Автор: <?=profile($data['blogs_user'])?> (<?=date_fixed($data['blogs_time'])?>)<br />
        <img src="/assets/img/images/balloon.gif" alt="image" /> <a href="/blog/blog?act=comments&amp;id=<?=$data['blogs_id']?>">Комментарии</a> (<?=$data['blogs_comments']?>)
        <a href="/blog/blog?act=end&amp;id=<?=$data['blogs_id']?>">&raquo;</a>
    </div>
<?php endforeach; ?>
