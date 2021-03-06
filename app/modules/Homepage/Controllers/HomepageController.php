<?php
namespace Homepage\Controllers;

use Doctrine\ORM\EntityManager;
use Entities\Post;

class HomepageController
{
    /**
     * @var \Silex\Application $app;
     */
    protected $app = null;
	/**
	 * @var EntityManager $em;
	 */
	protected $em = null;
    protected $viewPath = '';

    public function __construct($app)
    {
        $this->app = $app;

        $classRelatedPart = strtr(explode('\\Controllers', get_called_class())[1], ['\\' => '/']);
        $this->viewPath = preg_replace('/Controller$/', '', $classRelatedPart) . '/';
    }


	public function indexActions()
	{
		/**
		 * @var Post $article
		 */
		return $this->render('View/homepage');
	}

    protected function resolveTemplate($templateName)
    {
        return $this->viewPath . $templateName . '.twig';
    }

    protected function render($templateName, $data = [])
    {
        return $this->app->twig->render(
            $this->resolveTemplate($templateName),
            $data
        );
    }

}