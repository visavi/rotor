<?php foreach($pages as $page): ?>
	<?php if(isset($page['separator'])): ?>
		<?= $page['name'] ?>
	<?php else: ?>
		<a href="<?= $url ?>start=<?= $page['start'] ?>"><?= $page['name'] ?></a>
	<?php endif; ?>
<?php endforeach; ?>
<br />
