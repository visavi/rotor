</div>
<div class="lol" id="down">
    <a href="/"><?= $config['copy'] ?></a><br />
    <?= show_online() ?>
    <?= show_counter() ?>
</div>
<div class="site" style="text-align:center">
    <?= navigation() ?>
    <?= perfomance() ?>
</div>

<?php
include_once (BASEDIR."/includes/counters.php");

include_once (DATADIR.'/advert/bottom_all.dat');
?>
</body>
</html>
