<?php
namespace Post;

use Post\Controllers\PostController;
use Silex\Application;

class Mount extends \Core\Mount
{
	public function mount(Application $app)
	{
		$app['posts.controller'] = function () use ($app) {
			return new PostController($app);
		};

		$app->get('/post', 'posts.controller:listPostsActions');
	}
}