<?php
declare(strict_types = 1);
namespace Deployer;

require 'recipe/common.php';

set('repository', 'https://github.com/klapuch/Remembrall.git');
set('git_tty', true);
set('shared_files', []);
set('shared_dirs', []);
set('writable_mode', 'chmod');
set('writable_dirs', ['log']);
set('allow_anonymous_stats', false);

host('81.95.108.74')
	->user('root')
	->set('deploy_path', '/var/www/html/Remembrall');

task('apache:restart', function () {
	run('service apache2 restart');
});

task('composer:preinstall', function () {
	cd('{{release_path}}');
	run('composer install --prefer-dist --no-progress --no-suggest --no-interaction --no-scripts');
});

task('composer:optimize', function () {
	cd('{{release_path}}');
	run('composer update --prefer-dist --no-progress --no-suggest --no-interaction --no-scripts --no-dev --optimize-autoloader --classmap-authoritative');
});

task('database:migrations', function () {
	cd('{{release_path}}');
	run('vendor/bin/phing migrations');
});

task('frontend:assets', function () {
	cd('{{release_path}}');
	run('vendor/bin/phing assets');
});

task('configs:copy', function () {
	run('ln -sf {{deploy_path}}/Remembrall_config.local.ini {{release_path}}/App/Configuration/.config.local.ini');
	run('ln -sf {{deploy_path}}/Remembrall_htaccess {{release_path}}/www/.htaccess');
	run('ln -sf {{deploy_path}}/Remembrall_phinx.yml {{release_path}}/phinx.yml');
});

desc('Deploy your project');
task('deploy', [
	'deploy:prepare',
	'deploy:lock',
	'deploy:release',
	'deploy:update_code',
	'deploy:shared',
	'deploy:writable',
	'deploy:clear_paths',
	'deploy:symlink',
	'configs:copy',
	'composer:preinstall',
	'database:migrations',
	'frontend:assets',
	'composer:optimize',
	'apache:restart',
	'deploy:unlock',
	'cleanup',
	'success'
]);

after('deploy:failed', 'deploy:unlock');