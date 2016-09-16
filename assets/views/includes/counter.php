<?php if (App::setting('incount') == 1): ?>
	<a href="/pages/counter.php"><?= $count['count_dayhosts'] ?> | <?= $count['count_allhosts'] ?></a><br />
<?php endif; ?>

<?php if (App::setting('incount') == 2): ?>
	<a href="/pages/counter.php"><?= $count['count_dayhits'] ?> | <?= $count['count_allhits'] ?></a><br />
<?php endif; ?>

<?php if (App::setting('incount') == 3): ?>
	<a href="/pages/counter.php"><?= $count['count_dayhosts'] ?> | <?= $count['count_dayhits'] ?></a><br />
<?php endif; ?>

<?php if (App::setting('incount') == 4): ?>
	<a href="/pages/counter.php"><?= $count['count_allhosts'] ?> | <?= $count['count_allhits'] ?></a><br />
<?php endif; ?>

<?php if (App::setting('incount') == 5): ?>
	<a href="/pages/counter.php"><img src="/upload/counters/counter.png?<?= date_fixed(SITETIME, "dmYHi") ?>" alt="counter" /></a><br />
<?php endif; ?>
