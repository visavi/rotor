<a href="/blog">Блоги</a> / <a href="/blog/blog?cid=<?=$blogs['cats_id']?>"><?=$blogs['cats_name']?></a> / <a href="/blog/print?id=<?=$blogs['blogs_id']?>">Скачать</a> / <a href="/blog/rss?id=<?=$blogs['blogs_id']?>">RSS-лента</a>

<?php if ($blogs['blogs_user'] == App::getUsername()): ?>
	 / <a href="/blog/blog?act=editblog&amp;id=<?=$blogs['blogs_id']?>">Изменить</a>
<?php endif; ?>

<br /><br />

<i class="fa fa-file-text-o fa-lg text-muted"></i> <b><?=$blogs['blogs_title']?></b> (Оценка: <?=format_num($blogs['blogs_rating'])?>)

<?php if (is_admin()): ?>
	<br /> <a href="/admin/blog?act=editblog&amp;cid=<?=$blogs['cats_id']?>&amp;id=<?=$blogs['blogs_id']?>">Редактировать</a> /
	<a href="/admin/blog?act=moveblog&amp;cid=<?=$blogs['cats_id']?>&amp;id=<?=$blogs['blogs_id']?>">Переместить</a> /
	<a href="/admin/blog?act=delblog&amp;cid=<?=$blogs['cats_id']?>&amp;del=<?=$blogs['blogs_id']?>&amp;uid=<?=$_SESSION['token']?>" onclick="return confirm('Вы действительно хотите удалить данную статью?')">Удалить</a>
<?php endif; ?>
<hr />

<?=$blogs['text']?>

<?php page_strnavigation('/blog/blog?act=view&amp;id='.$blogs['blogs_id'].'&amp;', 1, $start, $total); ?>

Автор статьи: <?=profile($blogs['blogs_user'])?> (<?=date_fixed($blogs['blogs_time'])?>)<br />

<i class="fa fa-tag"></i> <?=$tags?>

<hr />

Рейтинг: <a href="/blog/blog?act=vote&amp;id=<?=$blogs['blogs_id']?>&amp;vote=down&amp;uid=<?=$_SESSION['token']?>"><img src="/assets/img/images/thumb-down.gif" alt="Минус" /></a> <big><b><?=format_num($blogs['blogs_rating'])?></b></big> <a href="/blog/blog?act=vote&amp;id=<?=$blogs['blogs_id']?>&amp;vote=up&amp;uid=<?=$_SESSION['token']?>"><img src="/assets/img/images/thumb-up.gif" alt="Плюс" /></a><br /><br />

<i class="fa fa-eye"></i> Просмотров: <?=$blogs['blogs_read']?><br />
<i class="fa fa-comment"></i> <a href="/blog/blog?act=comments&amp;id=<?=$blogs['blogs_id']?>">Комментарии</a> (<?=$blogs['blogs_comments']?>)
<a href="/blog/blog?act=end&amp;id=<?=$blogs['blogs_id']?>">&raquo;</a><br /><br />
