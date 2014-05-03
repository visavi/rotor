<div style="text-align:center">
	<?php foreach ($tags as $key => $val): ?>

		<?php $fontsize = ($min != $max) ? (round((($val - $min) / ($max - $min)) * 110 + 90)) : 90; ?>

		<a href="tags.php?act=search&amp;tags=<?=urlencode($key)?>"><span style="font-size:<?=$fontsize?>%"><?=$key?></span></a>
	<?php endforeach; ?>
</div><br />
