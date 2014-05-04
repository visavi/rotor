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

</td>
<td class="right_mid">&nbsp;</td></tr>
<tr>
    <td align="left" valign="top" class="lefbot"></td>
    <td class="borbottom"></td>
    <td align="right" valign="top" class="rightbot"></td>
</tr>
</table>

<table class="tab2" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
        <td width="120" valign="top" class="fottopleft"></td>
        <td class="ftop"></td>
        <td width="120" valign="top" class="fottopright"></td>
    </tr>

    <tr>
        <td align="center" colspan="3" class="ftexttd">

            <?= show_counter() ?>
            <?= show_online() ?>
            <a href="<?= $config['home'] ?>"><?= $config['copy'] ?></a><br/>

        </td>
    </tr>

    <tr>
        <td valign="top" class="footer_left"></td>
        <td valign="top" class="fbottom"></td>
        <td valign="top" class="footer_right"></td>
    </tr>
</table>

<table class="tab2" align="center">
    <tr>
        <td align="center">

            <?= navigation() ?>
            <?= perfomance() ?>

        </td>
    </tr>
</table>
</body></html>
