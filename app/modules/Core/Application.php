<?php
namespace Core;

use Post\Mount;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;

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
            array(
                'monolog.logfile' => CORE_RUNTIME_DIR . '/logs/development.log',
            )
        );

        $m = new Mount();
        $m->mount($this);
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
}