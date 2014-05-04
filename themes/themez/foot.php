<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
?>
</div>
<div align="center" class="c" id="down">

    <a href="<?= $config['home'] ?>"><?= $config['copy'] ?></a><br/>
    <?= show_counter() ?>
    <?= show_online() ?>

    [<a href="/">HOME</a> | <a href="/load/?">LOAD</a> | <a href="/forum/?">FORUM</a> | <a href="/book/?">GUEST</a> | <a
        href="/pages/index.php?act=menu">MAIN</a>]<br/>

    <?= navigation() ?>
    <?= perfomance() ?>
</div>

</td></tr></table>
</body></html>
