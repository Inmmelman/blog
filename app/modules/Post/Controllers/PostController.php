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
        $this->em = $this->app['orm.em'];
        $this->app = $app;
        $classRelatedPart = strtr(explode('\\Controllers', get_called_class())[1], ['\\' => '/']);
        $this->viewPath = preg_replace('/Controller$/', '', $classRelatedPart) . '/';
    }

    public function listPostsActions()
	{
		return $this->render('View/post', ['posts' => $this->app['orm.em']]);
	}

	public function addPost()
	{
	    //		$article = new Post();
		//		$article->setContent('Hello world!');
		//		$em = $this->app['orm.em'];
		//		$em->persist($article);
		//		$em->flush();
	}
	
	/**
	 * Get posts list
	 * @return array
	 */
    protected function getPostList(){
    	return $this->em->getRepository('\Entities\Post')->findAll();
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