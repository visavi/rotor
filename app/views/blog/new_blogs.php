<?php foreach ($blogs as $data): ?>

	<div class="b">
		<i class="fa fa-pencil"></i>
		<b><a href="/blog/blog?act=view&amp;id=<?=$data['blogs_id']?>"><?=$data['blogs_title']?></a></b> (<?=format_num($data['blogs_rating'])?>)
	</div>

	<div>
		Категория: <a href="/blog/blog?cid=<?=$data['blogs_cats_id']?>"><?=$data['cats_name']?></a><br />
		Просмотров: <?=$data['blogs_read']?><br />
		Добавил: <?=profile($data['blogs_user'])?>  (<?=date_fixed($data['blogs_time'])?>)
	</div>

<?php endforeach; ?>
