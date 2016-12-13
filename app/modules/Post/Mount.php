<?php
namespace Post;

use Core\MountAbstract;
use Post\Controllers\PostController;
use Silex\Application;

class Mount extends MountAbstract
{
	public function mount(Application $app)
	{
		$app['posts.controller'] = function () use ($app) {
			return new PostController($app);
		};

		$app->get('/post', 'posts.controller:listPostsActions');
		$app->get('/test-c', 'posts.controller:testActions');
	}
}