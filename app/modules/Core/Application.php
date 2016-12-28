<?php
namespace Core;

use Core\Security\User\UserProvider;
use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\DriverChain;
use Post\Mount;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;

class Application extends \Silex\Application {

    public $debug = true;

    /**
     * Access to services like they are properties
     *
     * @param string $prop
     * @return mixed
     */
    public function __get($prop)
    {
        if (isset($this[$prop])) {
            return $this[$prop];
        }

        return $this->$prop;
    }

    /**
     * Useful if service registered to be called as protected
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $service = $this->$method;
        return call_user_func_array($service, $args);
    }

    public function init(){
        $this->initTwig();

        $this->register(new ServiceControllerServiceProvider());
        $this->register(
            new MonologServiceProvider(),
            [
                'monolog.logfile' => CORE_RUNTIME_DIR . '/logs/development.log',
            ]
        );
        

	    $this->initDoctrine();
	    $this->initSessionsService();
	    $this->initSecurityServiceProvider();
	    
        $this->registerModules();
	}

	protected function getModuleDirectories(){
		$includePaths = ['app/modules'];
		$finder = new Finder();

        $moduleDirectories = array_map(
            function ($path) use ($finder) {
                $path = CORE_ROOT_DIR . '/' . $path;
                try {
                    $finder->in($path);
                } catch (InvalidArgumentException $e) {
                    // In case if directory listed in config does not exists
                    $path = null;
                }

                return $path;
            },
            $includePaths
        );
        $moduleDirectories = array_filter($moduleDirectories);

        /**
         * Finder needs to be re-initialized,
         * @see https://github.com/symfony/symfony/issues/8871
         */
        $finder = new Finder();

        return $finder->in($moduleDirectories)->depth('<1');
	}
	
	protected function initSessionsService() {
		$this->register(new SessionServiceProvider());
		$this['session.storage.handler'] = null;
	}

	protected function registerModules(){
		/** @var Application $app */
        $moduleDirectories = $this->getModuleDirectories();
	    /** @var SplFileInfo $moduleDirectory */
        foreach ($moduleDirectories as $moduleDirectory) {
	        $moduleName = $moduleDirectory->getRelativePathname();
            $moduleClassName = $moduleDirectory->getRelativePathname() . '\\Mount';
            if ( class_exists($moduleClassName)) {
                /** @var Mount $module */
                $module = new $moduleClassName();
                $module->mount($this);
            }
        }
	}
    protected function initTwig()
    {
        $paths = array_map(
            function ($path) {
                return CORE_ROOT_DIR . '/' . $path;
            },
            ['app/layouts', 'app/modules']
        );
        $this->register(
            new TwigServiceProvider(),
            [
                'twig.path' => $paths,
                'twig.options' => [
                    'cache' => CORE_CACHE_DIR,
                    'debug' => true
                ],
                'twig.class_path' => CORE_ROOT_DIR . '/vendor/twig/lib',
            ]
        );
        $this->twig->addExtension(new \Twig_Extension_Debug());
		$this->twig->disableDebug();
        if (!$this->debug) {
            $this->twig->disableDebug();
        }
    }

	protected function initDoctrine(){
		$this->register(new DoctrineServiceProvider(), [
		    'db.options' => array(
		        'dbname' => 'blog',
		    ),
		]);

		// Register Doctrine ORM
		$this->register(new DoctrineOrmServiceProvider(), [
		    'orm.proxies_dir' => CORE_ROOT_DIR.'/Proxies',
		    'orm.em.options' => array(
		        'mappings' => array(
		            // Using actual filesystem paths
		            array(
		                'type' => 'annotation',
		                'namespace' => 'Entities',
			            'use_simple_annotation_reader' => false,
		                'path' => CORE_MODULES_DIR.'/Entities',
		            )
		        ),
		    )
			
		]);
	}
	
	protected function initSecurityServiceProvider()
	{
		$this['security.firewalls'] = [];
		$this->register(
			new SecurityServiceProvider(),
			[
				'security.firewalls' => [
	                'main' => [
	                    'pattern' => '^/',
	                    'anonymous' => true,
		                'security' => true,
	                    'form' => [
	                        'login_path' => 'login',
	                        'check_path' => 'security_check',
	                    ],
	                    'logout' => [
	                        'logout_path' => '/logout',
	                    ],
	                    'users' => new UserProvider($this),
	                ]
				]
			]
		);
		
		  
		 
		$mappingDriverChains = new MappingDriverChain();
		$mappingDriverChain = new AnnotationDriver(
            new CachedReader(new AnnotationReader(), new ArrayCache()),
            (array) [CORE_MODULES_DIR.'/Core/Security/User']
        );
        $mappingDriverChains->addDriver($mappingDriverChain, 'Core\Security\User');

		$this['orm.add_mapping_driver']($mappingDriverChains, 'Core\Security\User');
		
		
		$this->initCoreSecurity();
		
	}
	
	protected function initCoreSecurity()
	{
		$this['accessDispatcher'] = $this->protect(
			function () {
				return new Dispatcher($this);
			}
		);
//
//		$this['security.voters'] = $this->extend(
//			'security.voters',
//			function ($voters) {
//				$voters[] = $this['ipRestrictionVoter'];
//
//				return $voters;
//			}
//		);

		$this['security.access_manager'] =  new AccessDecisionManager($this['security.voters'], 'unanimous');
	}
}