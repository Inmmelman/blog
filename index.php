<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = new Silex\Application();

$app->register(
	new Silex\Provider\MonologServiceProvider(),
	array(
		'monolog.logfile' => __DIR__ . '/logs/development.log',
	)
);

$m = new \Posts\Mount();
$m->mount($app);
$app->get(
	'/',
	function () {
		return 'Hello!';
	}
);

$app->run();
