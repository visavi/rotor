<?php if (is_user()): ?>
	Мои: <a href="/blog/active?act=blogs">статьи</a>, <a href="/blog/active?act=comments">комментарии</a> /
<?php endif; ?>

Новые: <a href="/blog/new?act=blogs">статьи</a>, <a href="/blog/new?act=comments">комментарии</a><hr />

<?php foreach($blogs as $key => $data): ?>
	<img src="/assets/img/images/dir.gif" alt="image" /> <b><a href="/blog/blog?cid=<?=$data['cats_id']?>"><?=$data['cats_name']?></a></b>

	<?php if (empty($data['new'])): ?>
		(<?=$data['cats_count']?>)<br />
	<?php else: ?>
		(<?=$data['cats_count']?>/+<?=$data['new']?>)<br />
	<?php endif; ?>
<?php endforeach; ?>

<br />
<a href="/blog/top">Топ статей</a> /
<a href="/blog/tags">Облако тегов</a> /
<a href="/blog/search">Поиск</a> /
<a href="/blog/blog?act=blogs">Все статьи</a> /
<a href="/blog/blog?act=new">Написать</a><br />
