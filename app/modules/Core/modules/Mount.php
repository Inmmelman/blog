<?php
namespace Core\Modules;

use Silex\Application;

/**
 * Created by PhpStorm.
 * User: emty
 * Date: 10.12.2016
 * Time: 22:00
 */
abstract class Mount
{
	abstract public function mount(Application $app);
}