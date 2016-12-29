<?php
namespace Users\Controllers;

use Symfony\Component\HttpFoundation\Request;

class UsersController
{
    /**
     * @var \Silex\Application $app;
     */
    protected $app = null;

    protected $viewPath = '';

    public function __construct($app)
    {
	    $this->app = $app;
	    $this->em = $this->app['orm.em'];
	    $classRelatedPart = strtr(explode('\\Controllers', get_called_class())[1], ['\\' => '/']);
        $this->viewPath = preg_replace('/Controller$/', '', $classRelatedPart) . '/';
    }
	
	public function loginActions(Request $request)
	{
		
		return $this->render('View/login', [
			'error'=> $this->app['security.last_error']($request),
	        'last_username' => $this->app['session']->get('_security.last_username'),
		]);
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