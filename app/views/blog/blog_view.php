<a href="/blog">Блоги</a> / <a href="/blog/blog?cid=<?=$blogs['category_id']?>"><?=$blogs['name']?></a> / <a href="/blog/print?id=<?=$blogs['id']?>">Скачать</a> / <a href="/blog/rss?id=<?=$blogs['id']?>">RSS-лента</a>

<?php if ($blogs['user'] == App::getUsername()): ?>
	 / <a href="/blog/blog?act=editblog&amp;id=<?=$blogs['id']?>">Изменить</a>
<?php endif; ?>

<br /><br />

<i class="fa fa-file-text-o fa-lg text-muted"></i> <b><?=$blogs['title']?></b> (Оценка: <?=format_num($blogs['rating'])?>)

<?php if (is_admin()): ?>
	<br /> <a href="/admin/blog?act=editblog&amp;cid=<?=$blogs['category_id']?>&amp;id=<?=$blogs['id']?>">Редактировать</a> /
	<a href="/admin/blog?act=moveblog&amp;cid=<?=$blogs['category_id']?>&amp;id=<?=$blogs['id']?>">Переместить</a> /
	<a href="/admin/blog?act=delblog&amp;cid=<?=$blogs['category_id']?>&amp;del=<?=$blogs['id']?>&amp;uid=<?=$_SESSION['token']?>" onclick="return confirm('Вы действительно хотите удалить данную статью?')">Удалить</a>
<?php endif; ?>
<hr />

<?=$blogs['text']?>

<?php App::pagination($page); ?>

Автор статьи: <?=profile($blogs['user'])?> (<?=date_fixed($blogs['time'])?>)<br />

<i class="fa fa-tag"></i> <?=$tags?>

<hr />

Рейтинг: <a href="/blog/blog?act=vote&amp;id=<?=$blogs['id']?>&amp;vote=down&amp;uid=<?=$_SESSION['token']?>"><i class="fa fa-thumbs-down"></i></a> <big><b><?=format_num($blogs['rating'])?></b></big> <a href="/blog/blog?act=vote&amp;id=<?=$blogs['id']?>&amp;vote=up&amp;uid=<?=$_SESSION['token']?>"><i class="fa fa-thumbs-up"></i></a><br /><br />

<i class="fa fa-eye"></i> Просмотров: <?=$blogs['visits']?><br />
<i class="fa fa-comment"></i> <a href="/blog/blog?act=comments&amp;id=<?=$blogs['id']?>">Комментарии</a> (<?=$blogs['comments']?>)
<a href="/blog/blog?act=end&amp;id=<?=$blogs['id']?>">&raquo;</a><br /><br />
