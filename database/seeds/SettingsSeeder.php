<?php

use Phinx\Seed\AbstractSeed;

class SettingsSeeder extends AbstractSeed
{
    /**
     * Run Method.
     */
    public function run(): void
    {
        $data = [
            ['name'  => 'app_installed', 'value' => 1],
            ['name'  => 'addbansend', 'value' => 1],
            ['name'  => 'addofferspoint', 'value' => 50],
            ['name'  => 'advertpoint', 'value' => 2000],
            ['name'  => 'allowextload', 'value' => 'zip,rar,txt,jpg,jpeg,gif,png,mp3,mp4,3gp,wav,mmf,mid,midi,sis,jar,jad'],
            ['name'  => 'allvotes', 'value' => 10],
            ['name'  => 'avtorlist', 'value' => 10],
            ['name'  => 'banlist', 'value' => 10],
            ['name'  => 'blacklist', 'value' => 10],
            ['name'  => 'blogcomm', 'value' => 10],
            ['name'  => 'bloggroup', 'value' => 10],
            ['name'  => 'blogpost', 'value' => 10],
            ['name'  => 'blogvotepoint', 'value' => 50],
            ['name'  => 'blog_create', 'value' => 1],
            ['name'  => 'bonusmoney', 'value' => 500],
            ['name'  => 'bookadds', 'value' => 1],
            ['name'  => 'bookpost', 'value' => 10],
            ['name'  => 'bookscores', 'value' => 0],
            ['name'  => 'captcha_angle', 'value' => 20],
            ['name'  => 'captcha_distortion', 'value' => 1],
            ['name'  => 'captcha_interpolation', 'value' => 1],
            ['name'  => 'captcha_maxlength', 'value' => 5],
            ['name'  => 'captcha_offset', 'value' => 5],
            ['name'  => 'captcha_spaces', 'value' => 0],
            ['name'  => 'captcha_symbols', 'value' => '1234567890'],
            ['name'  => 'captcha_type', 'value' => 'graphical'],
            ['name'  => 'chatpost', 'value' => 10],
            ['name'  => 'closedsite', 'value' => 0],
            ['name'  => 'comment_length', 'value' => 1000],
            ['name'  => 'contactlist', 'value' => 10],
            ['name'  => 'copy', 'value' => '© Rotor'],
            ['name'  => 'copyfoto', 'value' => 1],
            ['name'  => 'currency', 'value' => __('seeds.settings.currency')],
            ['name'  => 'deleted_user', 'value' => __('seeds.settings.deleted_user')],
            ['name'  => 'description', 'value' => __('seeds.settings.description')],
            ['name'  => 'doslimit', 'value' => 0],
            ['name'  => 'downcomm', 'value' => 10],
            ['name'  => 'downlist', 'value' => 10],
            ['name'  => 'downupload', 'value' => 1],
            ['name'  => 'editforumpoint', 'value' => 500],
            ['name'  => 'editratingpoint', 'value' => 150],
            ['name'  => 'editstatusmoney', 'value' => 3000],
            ['name'  => 'editstatuspoint', 'value' => 1000],
            ['name'  => 'errorlog', 'value' => 1],
            ['name'  => 'filesize', 'value' => 5242880],
            ['name'  => 'fileupload', 'value' => 10485760],
            ['name'  => 'floodstime', 'value' => 30],
            ['name'  => 'forumextload', 'value' => 'zip,rar,txt,jpg,jpeg,gif,png,mp3,mp4,3gp,wav,pdf'],
            ['name'  => 'forumloadpoints', 'value' => 150],
            ['name'  => 'forumloadsize', 'value' => 1048576],
            ['name'  => 'forumpost', 'value' => 10],
            ['name'  => 'forumtem', 'value' => 10],
            ['name'  => 'forumtextlength', 'value' => 3000],
            ['name'  => 'fotolist', 'value' => 5],
            ['name'  => 'guestsuser', 'value' => __('seeds.settings.guest_user')],
            ['name'  => 'guesttextlength', 'value' => 1000],
            ['name'  => 'ignorlist', 'value' => 10],
            ['name'  => 'incount', 'value' => 5],
            ['name'  => 'invite', 'value' => 0],
            ['name'  => 'ipbanlist', 'value' => 10],
            ['name'  => 'lastnews', 'value' => 5],
            ['name'  => 'language', 'value' => __('seeds.settings.language')],
            ['name'  => 'language_fallback', 'value' => 'ru'],
            ['name'  => 'limitcontact', 'value' => 1000],
            ['name'  => 'limitignore', 'value' => 1000],
            ['name'  => 'listbanhist', 'value' => 10],
            ['name'  => 'listinvite', 'value' => 20],
            ['name'  => 'listtransfers', 'value' => 10],
            ['name'  => 'loginauthlist', 'value' => 10],
            ['name'  => 'loglist', 'value' => 10],
            ['name'  => 'logos', 'value' => __('seeds.settings.logos')],
            ['name'  => 'logotip', 'value' => '/assets/img/images/logo.png'],
            ['name'  => 'maxblogpost', 'value' => 50000],
            ['name'  => 'maxfiles', 'value' => 5],
            ['name'  => 'moneyname', 'value' => __('seeds.settings.moneyname')],
            ['name'  => 'nocheck', 'value' => 'txt,dat,gif,jpg,jpeg,png,zip'],
            ['name'  => 'onlinelist', 'value' => 10],
            ['name'  => 'onlines', 'value' => 1],
            ['name'  => 'openreg', 'value' => 1],
            ['name'  => 'performance', 'value' => 1],
            ['name'  => 'photogroup', 'value' => 10],
            ['name'  => 'postcommoffers', 'value' => 10],
            ['name'  => 'postgallery', 'value' => 10],
            ['name'  => 'postnews', 'value' => 10],
            ['name'  => 'postoffers', 'value' => 10],
            ['name'  => 'previewsize', 'value' => 500],
            ['name'  => 'privatpost', 'value' => 10],
            ['name'  => 'privatprotect', 'value' => 50],
            ['name'  => 'ratinglist', 'value' => 20],
            ['name'  => 'registermoney', 'value' => 1000],
            ['name'  => 'regkeys', 'value' => 0],
            ['name'  => 'reglist', 'value' => 10],
            ['name'  => 'recaptcha_private', 'value' => ''],
            ['name'  => 'recaptcha_public', 'value' => ''],
            ['name'  => 'rekuseroptprice', 'value' => 100],
            ['name'  => 'rekuserpost', 'value' => 10],
            ['name'  => 'rekuserprice', 'value' => 1000],
            ['name'  => 'rekusershow', 'value' => 1],
            ['name'  => 'rekusertime', 'value' => 12],
            ['name'  => 'rekusertotal', 'value' => 10],
            ['name'  => 'rekuserpoint', 'value' => 50],
            ['name'  => 'scorename', 'value' => __('seeds.settings.scorename')],
            ['name'  => 'screensize', 'value' => 1000],
            ['name'  => 'sendmailpacket', 'value' => 3],
            ['name'  => 'sendmoneypoint', 'value' => 150],
            ['name'  => 'sendprivatmailday', 'value' => 3],
            ['name'  => 'stickerlist', 'value' => 10],
            ['name'  => 'stickermaxsize', 'value' => 1048576],
            ['name'  => 'stickermaxweight', 'value' => 500],
            ['name'  => 'stickerminweight', 'value' => 16],
            ['name'  => 'spamlist', 'value' => 10],
            ['name'  => 'statusdef', 'value' => 'Дух'],
            ['name'  => 'themes', 'value' => 'default'],
            ['name'  => 'timeonline', 'value' => 600],
            ['name'  => 'timezone', 'value' => 'Europe/Moscow'],
            ['name'  => 'title', 'value' => 'Rotor'],
            ['name'  => 'userlist', 'value' => 10],
            ['name'  => 'usersearch', 'value' => 30],
            ['name'  => 'wallmaxpost', 'value' => 1000],
            ['name'  => 'wallpost', 'value' => 10],
            ['name'  => 'webthemes', 'value' => 'motor'],
            ['name'  => 'ziplist', 'value' => 20],
        ];

        $this->execute('TRUNCATE settings');

        $table = $this->table('settings');
        $table->insert($data)->save();

        clearCache('settings');
    }
}
