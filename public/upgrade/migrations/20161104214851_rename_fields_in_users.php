<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInUsers extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('users');
        $table->renameColumn('users_id', 'id');
        $table->renameColumn('users_login', 'login');
        $table->renameColumn('users_pass', 'pass');
        $table->renameColumn('users_email', 'email');
        $table->renameColumn('users_joined', 'joined');
        $table->renameColumn('users_level', 'level');
        $table->renameColumn('users_nickname', 'nickname');
        $table->renameColumn('users_name', 'name');
        $table->renameColumn('users_country', 'country');
        $table->renameColumn('users_city', 'city');
        $table->renameColumn('users_info', 'info');
        $table->renameColumn('users_site', 'site');
        $table->renameColumn('users_icq', 'icq');
        $table->renameColumn('users_skype', 'skype');
        $table->renameColumn('users_gender', 'gender');
        $table->renameColumn('users_birthday', 'birthday');
        $table->renameColumn('users_visits', 'visits');
        $table->renameColumn('users_newprivat', 'newprivat');
        $table->renameColumn('users_newwall', 'newwall');
        $table->renameColumn('users_allforum', 'allforum');
        $table->renameColumn('users_allguest', 'allguest');
        $table->renameColumn('users_allcomments', 'allcomments');
        $table->renameColumn('users_themes', 'themes');
        $table->renameColumn('users_postguest', 'postguest');
        $table->renameColumn('users_postnews', 'postnews');
        $table->renameColumn('users_postprivat', 'postprivat');
        $table->renameColumn('users_postforum', 'postforum');
        $table->renameColumn('users_themesforum', 'themesforum');
        $table->renameColumn('users_postboard', 'postboard');
        $table->renameColumn('users_timezone', 'timezone');
        $table->renameColumn('users_point', 'point');
        $table->renameColumn('users_money', 'money');
        $table->renameColumn('users_ban', 'ban');
        $table->renameColumn('users_timeban', 'timeban');
        $table->renameColumn('users_timelastban', 'timelastban');
        $table->renameColumn('users_reasonban', 'reasonban');
        $table->renameColumn('users_loginsendban', 'loginsendban');
        $table->renameColumn('users_totalban', 'totalban');
        $table->renameColumn('users_explainban', 'explainban');
        $table->renameColumn('users_status', 'status');
        $table->renameColumn('users_avatar', 'avatar');
        $table->renameColumn('users_picture', 'picture');
        $table->renameColumn('users_rating', 'rating');
        $table->renameColumn('users_posrating', 'posrating');
        $table->renameColumn('users_negrating', 'negrating');
        $table->renameColumn('users_keypasswd', 'keypasswd');
        $table->renameColumn('users_timepasswd', 'timepasswd');
        $table->renameColumn('users_timelastlogin', 'timelastlogin');
        $table->renameColumn('users_timebonus', 'timebonus');
        $table->renameColumn('users_sendprivatmail', 'sendprivatmail');
        $table->renameColumn('users_confirmreg', 'confirmreg');
        $table->renameColumn('users_confirmregkey', 'confirmregkey');
        $table->renameColumn('users_secquest', 'secquest');
        $table->renameColumn('users_secanswer', 'secanswer');
        $table->renameColumn('users_timenickname', 'timenickname');
        $table->renameColumn('users_ipbinding', 'ipbinding');
        $table->renameColumn('users_navigation', 'navigation');
        $table->renameColumn('users_newchat', 'newchat');
        $table->renameColumn('users_privacy', 'privacy');
        $table->renameColumn('users_apikey', 'apikey');
        $table->renameColumn('users_subscribe', 'subscribe');
        $table->renameColumn('users_sumcredit', 'sumcredit');
        $table->renameColumn('users_timecredit', 'timecredit');
    }
}
