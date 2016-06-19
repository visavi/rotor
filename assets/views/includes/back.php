<?php $icon = (isset($icon)) ? $icon : 'fa-arrow-circle-left'; ?>
<?php if ($icon = 'reload.gif') $icon = 'fa-arrow-circle-up'; ?>

<i class="fa <?= $icon ?>"></i> <a href="<?=$link?>"><?=$title?></a><br />
