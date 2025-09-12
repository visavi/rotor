<?php

namespace Deployer;

require 'recipe/laravel.php';

set('default_stage', 'production');
set('keep_releases', 5); // Max releases

// Project repository
set('repository', 'git@github.com:visavi/rotor.git');

add('shared_files', []);
add('shared_dirs', [
    'public/uploads',
]);

add('writable_dirs', [
    'public/uploads/*',
]);

set('bin/npm', function () {
    return which('npm');
});

// Hosts
host('production')
    ->setHostname('hostname')
    ->set('remote_user', 'www-data')
    ->set('deploy_path', '/var/www/rotor');

// Tasks
task('build', function () {
    cd('{{release_path}}');
    run('{{bin/npm}} ci');
    run('{{bin/npm}} run build');
});

before('deploy:success', artisan('module:link'));
after('deploy:update_code', 'build');
after('deploy:failed', 'deploy:unlock');
