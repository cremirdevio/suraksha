<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config
set('application', 'Suraksha');
set('repository', 'git@github.com:cremirdevio/suraksha.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('prod')
    ->set('port', '65002')
    ->set('hostname', '82.180.175.94')
    ->set('remote_user', 'u737857919')
    ->set('deploy_path', '~/domains/surakshaproject.in/public_html/prod-api/current');

// Tasks

task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'artisan:storage:link',
    'artisan:config:cache',
    'artisan:view:cache',
    'artisan:migrate',
//    'artisan:scribe:generate',
    'artisan:down',
    'deploy:publish',
    'artisan:up',
]);

// Hooks

after('deploy:failed', 'deploy:unlock');
