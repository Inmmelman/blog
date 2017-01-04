<?php
namespace Homepage;

use Core\MountAbstract;
use Silex\Application;
use Homepage\Controllers\HomepageController;

class Mount extends MountAbstract
{
	public function mount(Application $app)
	{
		$app['homepage.controller'] = function () use ($app) {
			return new HomepageController($app);
		};

		$app->get('/', 'homepage.controller:indexActions');
	}
}