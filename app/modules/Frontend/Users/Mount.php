<?php
namespace Users;

use Core\MountAbstract;
use Post\Controllers\PostController;
use Silex\Application;
use Users\Controllers\UsersController;

class Mount extends MountAbstract
{
	public function mount(Application $app)
	{
		$app['users.controller'] = function () use ($app) {
			return new UsersController($app);
		};

		$app->get('/login', 'users.controller:loginActions')->bind('login');
		$app->get('/admin/', 'users.controller:testActions');
	}
}