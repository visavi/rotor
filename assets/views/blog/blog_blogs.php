<?php foreach ($blogs as $data): ?>
	<img src="/images/img/edit.gif" alt="image" />
	<b><a href="active.php?act=blogs&amp;uz=<?=$data['blogs_user']?>"><?=nickname($data['blogs_user'])?></a></b> (<?=$data['cnt']?>)<br />
<?php endforeach; ?>

<br /><br />Всего пользователей: <b><?=$total?></b>
