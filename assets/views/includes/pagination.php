<div class="nav">
	Страницы:
	<?php foreach($pages as $page): ?>
		<?php if(isset($page['separator'])): ?>
			<?= $page['name'] ?>
		<?php elseif(isset($page['current'])): ?>
			<span class="navcurrent"><?= $page['name'] ?></span>
		<?php else: ?>
			<a href="<?= $url ?>start=<?= $page['start'] ?>" title="<?= $page['title'] ?>"><?= $page['name'] ?></a>
		<?php endif; ?>
	<?php endforeach; ?>
</div>
