<?php if (App::setting('incount') == 1): ?>
	<a href="/counter"><?= $count['dayhosts'] ?> | <?= $count['allhosts'] ?></a><br />
<?php endif; ?>

<?php if (App::setting('incount') == 2): ?>
	<a href="/counter"><?= $count['dayhits'] ?> | <?= $count['allhits'] ?></a><br />
<?php endif; ?>

<?php if (App::setting('incount') == 3): ?>
	<a href="/counter"><?= $count['dayhosts'] ?> | <?= $count['dayhits'] ?></a><br />
<?php endif; ?>

<?php if (App::setting('incount') == 4): ?>
	<a href="/counter"><?= $count['allhosts'] ?> | <?= $count['allhits'] ?></a><br />
<?php endif; ?>

<?php if (App::setting('incount') == 5): ?>
	<a href="/counter"><img src="/upload/counters/counter.png?<?= date_fixed(SITETIME, "dmYHi") ?>" alt="counter" /></a><br />
<?php endif; ?>
