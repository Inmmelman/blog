<?php
namespace Test;

use Core\MountAbstract;
use Post\Controllers\PostController;
use Silex\Application;
use Test\Controllers\TestController;

class Mount extends MountAbstract
{
	public function mount(Application $app)
	{
		$app['test.controller'] = function () use ($app) {
			return new TestController($app);
		};

		$app->get('/new-controller', 'test.controller:testActions');
	}
}