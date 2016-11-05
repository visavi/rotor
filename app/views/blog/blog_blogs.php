<?php foreach ($blogs as $data): ?>
	<i class="fa fa-pencil"></i>
	<b><a href="/blog/active?act=blogs&amp;uz=<?=$data['user']?>"><?=nickname($data['user'])?></a></b> (<?=$data['cnt']?>)<br />
<?php endforeach; ?>

<br /><br />Всего пользователей: <b><?=$total?></b>
