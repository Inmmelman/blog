<?php
namespace Post\Controllers;

use Core\Security\User\Role;
use Core\Security\User\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class PostController
{
    /**
     * @var \Silex\Application $app;
     */
    protected $app = null;

    /**
     * @var EntityManager;
     */
    protected $em = null;
    protected $viewPath = '';

    public function __construct($app)
    {
	    $this->app = $app;
	    $this->em = $this->app['orm.em'];
	    $classRelatedPart = strtr(explode('\\Controllers', get_called_class())[1], ['\\' => '/']);
        $this->viewPath = preg_replace('/Controller$/', '', $classRelatedPart) . '/';
    }

    public function listPostsActions()
	{
        $manager = $this->em;

        $role = new Role();
        $role->setName('ROLE_ADMIN');

        $manager->persist($role);

        // создание пользователя
        $user = new User();
        $user->setUsername('john.doe');
        $user->setSalt(md5(time()));

        // шифрует и устанавливает пароль для пользователя,
        // эти настройки совпадают с конфигурационными файлами
        $encoder = new BCryptPasswordEncoder(10);
        $password = $encoder->encodePassword('admin', $user->getSalt());
        $user->setPassword($password);

        $user->getUserRoles()->add($role);

        $manager->persist($user);
        $manager->flush();
		return $this->render('View/post', ['posts' => $this->getPostList()]);
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
    	return []; // $this->em->getRepository('\Entities\Post')->findAll();
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