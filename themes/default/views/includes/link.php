<?php $links = prepare_array($links); ?>

<?php if (count($links)): ?>
	<div class="breadcrumbs">

		<?php foreach ($links as $key=>$link): ?>
			<?php $params = null;
			if (isset($link['params'])) {
				foreach ($link['params'] as $key=>$val){
					$params .= " {$key}=\"{$val}\"";
				}
			} ?>
			<?php if (!empty($key)) echo '/'; ?>
			<a href="<?= $link['url'] ?>"<?= $params ?>><?= $link['label'] ?></a>

		<?php endforeach; ?>
	</div>
<?php endif; ?>
