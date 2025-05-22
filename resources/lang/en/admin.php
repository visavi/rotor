<?php

return [
    'antimat' => [
        'text' => '
        All words from the list will be replaced by ***<br>
        To delete a word, click on it, you can add a word in the form below<br>',
        'words'         => 'Word list',
        'total_words'   => 'Total words',
        'confirm_clear' => 'Are you sure you want to delete all words?',
        'empty_words'   => 'No words yet!',
        'enter_word'    => 'Enter the word',

        'not_enter_word' => 'You have not entered the word for listing!',
        'word_listed'    => 'The entered word is already in the list!',
    ],

    'backup' => [
        'create_backup'   => 'Create backup',
        'total_backups'   => 'Total backups',
        'empty_backups'   => 'No backups yet!',
        'total_tables'    => 'Total tables',
        'records'         => 'Records',
        'size'            => 'Size',
        'compress_method' => 'Compression method',
        'not_compress'    => 'Do not compress',
        'compress_ratio'  => 'Compression ratio',
        'empty_tables'    => 'There are no tables for backup!',

        'no_tables_save'           => 'No tables to save!',
        'wrong_compression_method' => 'Wrong compression method!',
        'wrong_compression_ratio'  => 'Wrong compression ratio!',
        'database_success_saved'   => 'Database successfully processed and saved!',
        'backup_not_indicated'     => 'The name of the backup for deletion is not specified!',
        'invalid_backup_name'      => 'Invalid backup name!',
        'backup_not_exist'         => 'File to delete does not exist!',
        'backup_success_deleted'   => 'Backup successfully deleted!',
    ],

    'banhists' => [
        'history'       => 'Story',
        'search_user'   => 'Search by user',
        'empty_history' => 'There is no ban history yet!',
        'view_history'  => 'View History',
    ],

    'bans' => [
        'login_hint'    => 'Enter the username of the user you want to edit',
        'user_ban'      => 'User Ban',
        'change_ban'    => 'Ban Change',
        'time_ban'      => 'Ban time',
        'banned'        => 'Ban',
        'ban_hint'      => 'Attention! Try to describe as much as possible the reason for the ban',
        'confirm_unban' => 'Do you really want to unban the user?',

        'forbidden_ban'      => 'It is forbidden to ban the administration of the site!',
        'user_banned'        => 'This user is already blocked!',
        'user_not_banned'    => 'This user is not banned!',
        'time_not_indicated' => 'You did not indicate the time of the ban!',
        'time_not_selected'  => 'No ban time selected!',
        'time_empty'         => 'Too little ban time!',
        'success_banned'     => 'User successfully banned!',
        'success_unbanned'   => 'User successfully unbanned!',
    ],

    'blacklists' => [
        'email'      => 'Email',
        'logins'     => 'Logins',
        'domains'    => 'Domains',
        'empty_list' => 'The list is still empty!',

        'type_not_found' => 'The specified type was not found!',
        'invalid_login'  => 'Invalid characters in login!',
    ],

    'caches' => [
        'files'           => 'Files',
        'images'          => 'Images',
        'views'           => 'Views',
        'clear'           => 'Clear cache',
        'empty_files'     => 'No files yet!',
        'success_cleared' => 'Cache successfully cleared!',
        'only_file_cache' => 'Only the file cache is displayed',
    ],

    'chat' => [
        'clear'           => 'Clear chat',
        'confirm_clear'   => 'Do you really want to clear the admin chat?',
        'edit_message'    => 'Editing a message',
        'success_cleared' => 'Admin chat successfully cleared!',
    ],

    'checkers' => [
        'new_files'          => 'New files and new file options',
        'old_files'          => 'Deleted files and old file settings',
        'empty_changes'      => 'No change!',
        'initial_scan'       => 'An initial scan is necessary!',
        'information_scan'   => 'Scanning the system allows you to find out which files or folders have changed over a period of time',
        'invalid_extensions' => 'Warning, the service does not take into account some file extensions',
        'scan'               => 'Scan',
        'success_crawled'    => 'Site successfully crawled!',
    ],

    'delivery' => [
        'online'                  => 'Online',
        'active'                  => 'Active',
        'admins'                  => 'Administration',
        'users'                   => 'To all users',
        'not_recipients_selected' => 'You have not selected mailing recipients!',
        'not_recipients'          => 'Missing mailing recipients!',
        'success_sent'            => 'Message sent successfully!',
    ],

    'delusers' => [
        'condition'         => 'Delete users who have not visited the site',
        'minimum_asset'     => 'Minimum Asset',
        'deleted_condition' => 'Users who have not visited the site more will be deleted',
        'asset_condition'   => 'And having in their assets no more',
        'deleted_users'     => 'Users will be deleted',
        'delete_users'      => 'Delete users',
        'invalid_period'    => 'The specified time for deletion is invalid!',
        'users_not_found'   => 'There are no users to delete!',
        'success_deleted'   => 'Users successfully deleted!',
    ],

    'errors' => [
        'hint'            => 'Attention! Logging is turned off in the settings! ',
        'errors'          => 'Errors',
        'autobans'        => 'Autobahns',
        'logs_not_exist'  => 'The specified logs do not exist!',
        'success_cleared' => 'Logs successfully cleared!',
    ],

    'files' => [
        'confirm_delete_dir'        => 'Do you really want to delete this directory?',
        'confirm_delete_file'       => 'Do you really want to delete this file?',
        'objects'                   => 'Objects',
        'lines'                     => 'Rows',
        'changed'                   => 'Modified',
        'empty_objects'             => 'There are no objects!',
        'create_object'             => 'Create a new object',
        'directory_name'            => 'Directory Name',
        'create_directory'          => 'Create directory',
        'file_name'                 => 'File name (without extension)',
        'create_file'               => 'Create file',
        'create_hint'               => 'Latin characters and numbers are allowed, as well as hyphens and underscores',
        'file_editing'              => 'Editing a file',
        'edit_hint'                 => 'Press Ctrl + Enter to translate a line',
        'writable'                  => 'Attention! The file is not writable! ',
        'file_not_exist'            => 'This file does not exist!',
        'directory_not_exist'       => 'This directory does not exist!',
        'directory_not_writable'    => 'Directory :dir is not writable!',
        'file_required'             => 'You must enter a file name!',
        'directory_required'        => 'You must enter a directory name!',
        'file_invalid'              => 'Invalid file name!',
        'directory_invalid'         => 'Invalid directory name!',
        'file_success_saved'        => 'File saved successfully!',
        'file_success_created'      => 'New file created successfully!',
        'directory_success_created' => 'New directory successfully created!',
        'file_success_deleted'      => 'File deleted successfully!',
        'directory_success_deleted' => 'Directory successfully deleted!',
        'file_exist'                => 'A file with the same name already exists!',
        'directory_exist'           => 'A directory with the given name already exists!',
    ],

    'invitations' => [
        'key_generation'        => 'Generation of new keys',
        'send_to_user'          => 'Send key to user',
        'sending_keys'          => 'Key distribution',
        'send_to_active_users'  => 'Send keys to active users',
        'keys_not_amount'       => 'Not specified the number of keys!',
        'keys_success_created'  => 'Keys successfully created!',
        'keys_success_sent'     => 'Keys sent successfully!',
        'keys_empty_recipients' => 'Missing key recipients!',
        'keys_success_deleted'  => 'Selected keys successfully deleted!',
    ],

    'ipbans' => [
        'history'             => 'History of the autobahns',
        'empty_ip'            => 'The ban list is still empty!',
        'confirm_clear'       => 'Do you really want to clear the IP list?',
        'ip_invalid'          => 'You entered an invalid IP address',
        'ip_exists'           => 'The IP you entered is already on the list!',
        'ip_success_added'    => 'IP successfully added to the list!',
        'ip_selected_deleted' => 'The selected IPs have been successfully removed from the list!',
        'ip_success_cleared'  => 'IP list cleared successfully!',
    ],

    'logs' => [
        'page'            => 'Page',
        'referer'         => 'Referer',
        'confirm_clear'   => 'Are you sure you want to clear the logs?',
        'empty_logs'      => 'There are no logs yet!',
        'success_cleared' => 'The log file has been cleared successfully!',
    ],

    'modules' => [
        'module'                   => 'Module',
        'migrations'               => 'Migrations',
        'symlink'                  => 'Symlink',
        'config'                   => 'Config',
        'routes'                   => 'Routes',
        'hooks'                    => 'Hooks',
        'empty_modules'            => 'Modules not loaded yet!',
        'confirm_delete'           => 'Do you really want to remove the module?',
        'hint'                     => 'Attention! When you remove the module, all migrations and changes to the database will be deleted',
        'module_not_found'         => 'This module was not found!',
        'module_success_installed' => 'Module successfully installed!',
        'module_success_updated'   => 'Module updated successfully!',
        'module_success_enabled'   => 'Module successfully enabled!',
        'module_success_disabled'  => 'Module turned off successfully!',
        'module_success_deleted'   => 'Module successfully removed!',
    ],

    'notices' => [
        'confirm_delete'         => 'Are you sure you want to delete this template?',
        'empty_notices'          => 'There are no templates yet!',
        'edit'                   => 'Editing a template',
        'edit_system_template'   => 'You are editing a system template',
        'system_template'        => 'System template',
        'create'                 => 'Create a template',
        'notice_invalid'         => 'Invalid template type name!',
        'notice_length'          => 'Template type is too long or short!',
        'notice_exists'          => 'This type is already on the list!',
        'notice_success_saved'   => 'Template saved successfully!',
        'notice_not_found'       => 'This template was not found!',
        'notice_protect'         => 'It is forbidden to delete a protected template!',
        'notice_success_deleted' => 'Template successfully deleted!',
    ],

    'reglists' => [
        'enabled'                => 'Registration confirmation is enabled!',
        'disabled'               => 'Registration confirmation is turned off!',
        'empty_users'            => 'There are no users requiring registration confirmation!',
        'users_not_selected'     => 'Missing selected users!',
        'users_success_approved' => 'Selected users successfully approved!',
        'users_success_deleted'  => 'Selected users successfully deleted!',
    ],

    'rules' => [
        'empty_rules'         => 'Site rules have not yet been established!',
        'editing_rules'       => 'Editing rules',
        'variables'           => 'Internal variables',
        'sitename'            => 'Name of the site',
        'rules_empty'         => 'You have not entered a text with the site rules!',
        'rules_success_saved' => 'Rules saved successfully!',
    ],

    'spam' => [
        'go_to_message' => 'Go to post',
        'empty_spam'    => 'There are no complaints yet!',
    ],

    'paid_adverts' => [
        'top_all'               => 'All top',
        'top'                   => 'Main top',
        'forum'                 => 'Forum',
        'bottom_all'            => 'Bottom all',
        'bottom'                => 'Main bottom',
        'create_advert'         => 'Ad advert',
        'edit_advert'           => 'Editing ads',
        'expires'               => 'Expires',
        'expired'               => 'Expired',
        'color'                 => 'Color',
        'bold'                  => 'Bold text',
        'place'                 => 'Place',
        'link'                  => 'Site url',
        'names'                 => 'Names',
        'name'                  => 'Name',
        'term'                  => 'Placement period',
        'empty_links'           => 'There are no sponsored links yet!',
        'confirm_delete_advert' => 'Do you confirm deletion of the sponsored link?',
        'not_found'             => 'Sponsored link not found!',
        'place_invalid'         => 'Placement does not exist!',
        'term_invalid'          => 'The placement period must be longer than the current time!',
        'names_count'           => 'You must specify at least 1 name!',
    ],

    'not_exists_boss' => '
        Attention! Boss profile missing<br>
        No user with boss privileges found in the database',
    'exists_install' => '
        Attention! It is necessary to delete the file app/Http/Controllers/InstallController.php<br>
        The presence of this file may compromise the security of the site. Remove it now!',

    'user_fields' => [
        'required'     => 'Required',
        'edit_field'   => 'Edit field',
        'create_field' => 'Create field',
        'empty_fields' => 'No user fields yet!',
        'input'        => 'Оne-line field',
        'textarea'     => 'Multiline field',
    ],
];
