<?php

return [
    'install'               => 'Install',
    'update'                => 'Update',
    'step1'                 => 'Step 1 - verification of requirements',
    'step2_install'         => 'Step 2 - database status check (installation)',
    'step3_install'         => 'Step 3 - performing migrations (installation)',
    'step4_install'         => 'Step 4 - filling in the database (installation)',
    'step5_install'         => 'Step 5 - creating an administrator (installation)',
    'step2_update'          => 'Step 2 - database status check (update)',
    'install_completed'     => 'Installation completed',
    'update_completed'      => 'Update completed',
    'debug'                 => 'If during the installation of the engine any error occurs, to find out the cause of the error, enable error output, change the value of APP_DEBUG to true',
    'env'                   => 'To install the engine, you need to register data from the database into the .env file',
    'app_key'               => 'Do not forget to change the value of APP_KEY, this data is necessary to encrypt cookies and passwords in sessions',
    'requirements'          => 'The minimum version of PHP necessary for the PHP :php and MySQL :mysql engine to work',
    'check_requirements'    => 'Verification of requirements',
    'ffmpeg'                => 'For processing video, it is advisable to install the FFmpeg library',
    'chmod_views'           => 'Additionally, you can set permissions on directories and files with templates inside resources / views - this is necessary for editing website templates',
    'chmod_rights'          => 'Chmod right',
    'chmod'                 => 'If any item is highlighted in red, you need to go over FTP and set the CHMOD permission record',
    'errors'                => 'Some settings are recommended for full compatibility, but the script is able to work even if the recommended settings do not match the current ones.',
    'continue'              => 'You can continue to install the engine!',
    'requirements_pass'     => 'All modules and libraries are present, the settings are correct, the necessary files and folders are writable',
    'requirements_not_pass' => 'These warnings are not critical, but nevertheless, for the full, stable and safe operation of the engine, it is desirable to eliminate them',
    'continue_restrict'     => 'You can continue to install the script, but there is no guarantee that the engine will work stably',
    'check_status'          => 'Check database status',
    'requirements_warning'  => 'You have warnings!',
    'requirements_failed'   => 'There are critical errors!',
    'resolve_errors'        => 'You won’t be able to proceed with the installation until you resolve the critical errors',
    'migrations'            => 'Perform migrations',
    'seeds'                 => 'Fill in the database',
    'create_admin'          => 'Create Admin',
    'create_admin_info'     => 'Before you go to administer your site, you must create an administrator account.',
    'create_admin_errors'   => 'Before clicking the Create button, make sure that there are no error notifications on the previous page, otherwise the process cannot be completed successfully',
    'delete_install'        => 'After the installation is complete, you must delete the install directory with all the contents, you can change the password and other data in your profile',
    'welcome'               => 'Welcome!',
    'text_message'          => 'Hello :login! Congratulations on the successful installation of our Rotor engine.
    New versions, upgrades, as well as many other add-ons can be found on our website. [url=https://visavi.net]visavi.net[/url]',
    'text_news'             => 'Welcome to the Rotor engine demo page
    Rotor is a functionally complete open source content management system written in PHP. It uses a MySQL database to store the contents of your site. Rotor is a flexible, powerful and intuitive system with minimal hosting requirements, a high level of protection and is an excellent choice for building a site of any complexity
    The main feature of Rotor is the low load on system resources, even with a very large audience of the site, the server load will not be minimal, and you will not experience any problems with the display of information.
    You can download the Rotor engine on the official website [url=https://visavi.net]visavi.net[/url]',
    'success_install'       => 'Congratulations, Rotor has been successfully installed!',
    'success_update'        => 'Congratulations, Rotor has been successfully updated!',
    'main_page'             => 'Go to the main page of the site',
];
