<?php
namespace Dashboard;

use Dashboard\Controllers\DashboardController;
use Core\MountAbstract;
use Silex\Application;

class Mount extends MountAbstract
{
	public function mount(Application $app)
	{
		$app['dashboard.controller'] = function () use ($app) {
			return new DashboardController($app);
		};

		$app->get('/admin', 'dashboard.controller:indexActions');
	}
}