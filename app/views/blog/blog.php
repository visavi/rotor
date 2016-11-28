<a href="/blog">Блоги</a> / <a href="/blog/blog?act=new&amp;cid=<?=$cats['id']?>">Написать</a><br /><br />

<i class="fa fa-folder-open"></i> <b><?=$cats['name']?></b> (Статей: <?=$cats['count']?>)

<?php if (is_admin()): ?>
	(<a href="/admin/blog?act=blog&amp;cid=<?=$cats['id']?>&amp;page=<?=$page['current']?>">Управление</a>)
<?php endif; ?>
<hr />

<?php foreach ($blogs as $data): ?>

	<div class="b">
		<i class="fa fa-pencil"></i>
		<b><a href="/blog/blog?act=view&amp;id=<?=$data['id']?>"><?=$data['title']?></a></b> (<?=format_num($data['rating'])?>)
	</div>

	<div>
		Автор: <?=profile($data['user'])?> (<?=date_fixed($data['time'])?>)<br />
		Просмотров: <?=$data['visits']?><br />
		<a href="/blog/blog?act=comments&amp;id=<?=$data['id']?>">Комментарии</a> (<?=$data['comments']?>)
		<a href="/blog/blog?act=end&amp;id=<?=$data['id']?>">&raquo;</a>
	</div>
<?php endforeach; ?>

<a href="/blog/top">Топ статей</a> /
<a href="/blog/tags">Облако тегов</a> /
<a href="/blog/search">Поиск</a> /
<a href="/blog/blog?act=blogs">Все статьи</a> /
<a href="/blog/blog?act=new&amp;cid=<?=$cats['id']?>">Написать</a><br />
