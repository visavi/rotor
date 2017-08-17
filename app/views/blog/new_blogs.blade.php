<?php foreach ($blogs as $data): ?>

	<div class="b">
		<i class="fa fa-pencil"></i>
		<b><a href="/blog/blog?act=view&amp;id=<?=$data['id']?>"><?=$data['title']?></a></b> (<?=format_num($data['rating'])?>)
	</div>

	<div>
		Категория: <a href="/blog/blog?cid=<?=$data['id']?>"><?=$data['name']?></a><br>
		Просмотров: <?=$data['visits']?><br>
		Добавил: <?=profile($data['user'])?>  (<?=date_fixed($data['time'])?>)
	</div>

<?php endforeach; ?>
