<?php
namespace Deployer;

require 'recipe/common.php';

set('default_stage', 'production');

// Project name
set('application', 'Site name');

// Deploy path
set('deploy_path', '/var/www/rotor');

// Project repository
set('repository', 'git@github.com:visavi/rotor.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
set('shared_files', ['.env']);
set('shared_dirs', [
    'storage',
    'public/uploads',
    'public/assets/modules',
]);
set('writable_mode', 'chmod');
set('writable_chmod_mode', '0777');

// Writable dirs by web server
set('writable_dirs', [
    'storage/*',
    'public/uploads/*',
    'public/assets/modules',
]);
set('allow_anonymous_stats', false);

set('composer_options', '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader --classmap-authoritative');

set('artisan', static function () {
    return parse('{{bin/php}} {{release_path}}/artisan');
});

set('bin/npm', function () {
    return run('which npm');
});

// Hosts
host('hostname')
    ->roles('php')
    ->stage('production')
    ->configFile('~/.ssh/config')
    ->identityFile('~/.ssh/id_rsa')
    ->forwardAgent(true)
    ->multiplexing(true)
    ->addSshOption('UserKnownHostsFile', '/dev/null')
    ->addSshOption('StrictHostKeyChecking', 'no');

// Tasks
desc('PHP reload');
task('reload:php-fpm', static function () {
    run('sudo /usr/sbin/service php8.0-fpm reload');
})->onRoles('php');

desc('Env copy');
task('deploy:env:copy', static function () {
    run('cp -n {{release_path}}/.env.example {{release_path}}/.env');
});

desc('Migrate database');
task('database:migrate', static function () {
    run('{{artisan}} migrate');
})->onRoles('php')->once();

desc('Npm install');
task('deploy:npm', static function () {
    run("cd {{release_path}} && {{bin/npm}} ci");
    run('cd {{release_path}} && {{bin/npm}} run prod');
})->onStage('production')->once();

desc('Deploy your project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:env:copy',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);

// Npm ci and run
after('deploy:update_code', 'deploy:npm');

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Reload php-fpm
before('success', 'reload:php-fpm');

// Migrate database before symlink new release.
before('deploy:symlink', 'database:migrate');
