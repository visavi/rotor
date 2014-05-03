<h2>Ответ на сообщение</h2>

<div class="b">
	<div class="img"><?=user_avatars($post['guest_user'])?></div>

	<?php if ($post['guest_user'] == $config['guestsuser']): ?>
		<b><?=$post['guest_user']?></b> <small>(<?=date_fixed($post['guest_time'])?>)</small>
	<?php else: ?>
		<b><?=profile($post['guest_user'])?></b> <small>(<?=date_fixed($post['guest_time'])?>)</small><br />
		<?=user_title($post['guest_user'])?> <?=user_online($post['guest_user'])?>
	<?php endif; ?>
</div>

<div>Сообщение: <?=bb_code($post['guest_text'])?></div><hr />

<div class="form">
	<form action="index.php?act=add&amp;uid=<?=$_SESSION['token']?>" method="post">
	<textarea id="markItUp" cols="25" rows="5" name="msg">[b]<?=nickname($post['guest_user'])?>[/b], </textarea><br />
	<input type="submit" value="Ответить" /></form>
</div><br />
