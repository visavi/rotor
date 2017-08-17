Сортировать:

<?php $active = ($order == 'visits') ? ' style="font-weight: bold;"' : ''; ?>
<a href="/blog/top?sort=visits"<?=$active?>>Просмотры</a>,

<?php $active = ($order == 'rating') ? ' style="font-weight: bold;"' : ''; ?>
<a href="/blog/top?sort=rated"<?=$active?>>Оценки</a>,

<?php $active = ($order == 'comments') ? ' style="font-weight: bold;"' : ''; ?>
<a href="/blog/top?sort=comm"<?=$active?>>Комментарии</a>
<hr>

<?php foreach ($blogs as $data): ?>

	<div class="b">
		<i class="fa fa-pencil"></i>
		<b><a href="/blog/blog?act=view&amp;id=<?=$data['id']?>"><?=$data['title']?></a></b> (<?=format_num($data['rating'])?>)
	</div>

	<div>
		Категория: <a href="/blog/blog?cid=<?=$data['category_id']?>"><?=$data['name']?></a><br>
		Просмотров: <?=$data['visits']?><br>
		Рейтинг: <b><?=format_num($data['rating'])?></b><br>
		<a href="/blog/blog?act=comments&amp;id=<?=$data['id']?>">Комментарии</a> (<?=$data['comments']?>)
		<a href="/blog/blog?act=end&amp;id=<?=$data['id']?>">&raquo;</a>
	</div>
<?php endforeach; ?>
