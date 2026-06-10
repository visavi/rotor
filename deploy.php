<?php

namespace Deployer;

require 'recipe/laravel.php';

set('default_stage', 'production');
set('keep_releases', 5); // Max releases
set('default_timeout', null);

// Project repository
set('repository', 'git@github.com:visavi/rotor.git');

add('shared_files', []);
add('shared_dirs', [
    'public/uploads',
]);

add('writable_dirs', [
    'public/uploads/*',
]);

// Hosts
host('production')
    ->setHostname('hostname')
    ->set('remote_user', 'www-data')
    ->set('deploy_path', '/var/www/rotor');

// Tasks
task('build', function () {
    runLocally('npm run build');
    upload('public/build/', '{{release_path}}/public/build/');
});

before('deploy:success', artisan('module:link'));
after('deploy:update_code', 'build');
after('deploy:failed', 'deploy:unlock');
