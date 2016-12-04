<?php if (is_user()): ?>
	Мои: <a href="/blog/active?act=blogs">статьи</a>, <a href="/blog/active?act=comments">комментарии</a> /
<?php endif; ?>

Новые: <a href="/blog/new?act=blogs">статьи</a>, <a href="/blog/new?act=comments">комментарии</a><hr />

<?php foreach($blogs as $key => $data): ?>
	<i class="fa fa-folder-open"></i> <b><a href="/blog/blog?cid=<?=$data['id']?>"><?=$data['name']?></a></b>

	<?php if (empty($data['new'])): ?>
		(<?=$data['count']?>)<br />
	<?php else: ?>
		(<?=$data['count']?>/+<?=$data['new']?>)<br />
	<?php endif; ?>
<?php endforeach; ?>

<br />
<a href="/blog/top">Топ статей</a> /
<a href="/blog/tags">Облако тегов</a> /
<a href="/blog/search">Поиск</a> /
<a href="/blog/blog?act=blogs">Все статьи</a> /
<a href="/blog/blog?act=new">Написать</a> /
<a href="/blog/rss">RSS</a><br />
