<?php
namespace Posts;

use Silex\Application;

class Mount extends \Core\Modules\Mount
{
	public function mount(Application $app)
	{
		$app['posts.controller2'] = function () {
			return new PostControllers();
		};

		$app->get('/post', 'posts.controller:listPostsActions');
	}
}