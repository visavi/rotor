<a href="index.php">Блоги</a> / <a href="blog.php?cid=<?=$blogs['cats_id']?>"><?=$blogs['cats_name']?></a> / <a href="print.php?id=<?=$blogs['blogs_id']?>">Скачать</a> / <a href="rss.php?id=<?=$blogs['blogs_id']?>">RSS-лента</a>

<?php if ($blogs['blogs_user'] == $log): ?>
	 / <a href="blog.php?act=editblog&amp;id=<?=$blogs['blogs_id']?>">Изменить</a>
<?php endif; ?>

<br /><br />

<img src="/images/img/themes.gif" alt="Статья" /> <b><?=$blogs['blogs_title']?></b> (Оценка: <?=format_num($blogs['blogs_rating'])?>)

<?php if (is_admin()): ?>
	<br /> <a href="/admin/blog.php?act=editblog&amp;cid=<?=$blogs['cats_id']?>&amp;id=<?=$blogs['blogs_id']?>">Редактировать</a> /
	<a href="/admin/blog.php?act=moveblog&amp;cid=<?=$blogs['cats_id']?>&amp;id=<?=$blogs['blogs_id']?>">Переместить</a> /
	<a href="/admin/blog.php?act=delblog&amp;cid=<?=$blogs['cats_id']?>&amp;del=<?=$blogs['blogs_id']?>&amp;uid=<?=$_SESSION['token']?>" onclick="return confirm('Вы действительно хотите удалить данную статью?')">Удалить</a>
<?php endif; ?>
<hr />

<?=$blogs['text']?>

<?php page_strnavigation('blog.php?act=view&amp;id='.$blogs['blogs_id'].'&amp;', 1, $start, $total); ?>

Автор статьи: <?=profile($blogs['blogs_user'])?> (<?=date_fixed($blogs['blogs_time'])?>)<br />

<img src="/images/img/tag.gif" alt="Метки" /> <?=$tags?>

<hr />

Рейтинг: <a href="blog.php?act=vote&amp;id=<?=$blogs['blogs_id']?>&amp;vote=down&amp;uid=<?=$_SESSION['token']?>"><img src="/images/img/thumb-down.gif" alt="Минус" /></a> <big><b><?=format_num($blogs['blogs_rating'])?></b></big> <a href="blog.php?act=vote&amp;id=<?=$blogs['blogs_id']?>&amp;vote=up&amp;uid=<?=$_SESSION['token']?>"><img src="/images/img/thumb-up.gif" alt="Плюс" /></a><br /><br />

<img src="/images/img/eye.gif" alt="Просмотры" /> Просмотров: <?=$blogs['blogs_read']?><br />
<img src="/images/img/balloon.gif" alt="Комментарии" /> <a href="blog.php?act=comments&amp;id=<?=$blogs['blogs_id']?>">Комментарии</a> (<?=$blogs['blogs_comments']?>)
<a href="blog.php?act=end&amp;id=<?=$blogs['blogs_id']?>">&raquo;</a><br /><br />
