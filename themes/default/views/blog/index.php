<?php if (is_user()): ?>
	Мои: <a href="active.php?act=blogs">статьи</a>, <a href="active.php?act=comments">комментарии</a> /
<?php endif; ?>

Новые: <a href="new.php?act=blogs">статьи</a>, <a href="new.php?act=comments">комментарии</a><hr />

<?php foreach($blogs as $key => $data): ?>
	<img src="/images/img/dir.gif" alt="image" /> <b><a href="blog.php?cid=<?=$data['cats_id']?>"><?=$data['cats_name']?></a></b>

	<?php if (empty($data['new'])): ?>
		(<?=$data['cats_count']?>)<br />
	<?php else: ?>
		(<?=$data['cats_count']?>/+<?=$data['new']?>)<br />
	<?php endif; ?>
<?php endforeach; ?>

<br />
<a href="top.php">Топ статей</a> /
<a href="tags.php">Облако тегов</a> /
<a href="search.php">Поиск</a> /
<a href="blog.php?act=blogs">Все статьи</a> /
<a href="blog.php?act=new">Написать</a><br />
