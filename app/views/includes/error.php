<?php if (is_array($errors)): ?>
	<?php $errors = implode('<br><i class="fa fa-exclamation-circle fa-lg text-danger"></i> ', $errors); ?>
<?php endif; ?>

<div class="alert alert-danger alert-block"><i class="fa fa-exclamation-circle fa-lg text-danger"></i> <?= $errors ?></div>
