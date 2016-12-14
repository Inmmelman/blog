<?php
namespace Core;

use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Post\Mount;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Form\Exception\InvalidArgumentException;

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
		                'path' => CORE_ROOT_DIR.'/Entities',
		            )
		        ),
		    ),
		]);
	}
}