<?php
namespace Post\Controllers;

class PostController
{
    /**
     * @var \Silex\Application $app;
     */
    protected $app = null;

    protected $viewPath = '';

    public function __construct($app)
    {
        $this->app = $app;
        $classRelatedPart = strtr(explode('\\Controllers', get_called_class())[1], ['\\' => '/']);
        $this->viewPath = preg_replace('/Controller$/', '', $classRelatedPart) . '/';
    }

    public function listPostsActions()
	{
		return $this->render('View/post');
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