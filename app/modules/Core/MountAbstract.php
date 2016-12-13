<?php
/**
 * Created by PhpStorm.
 * User: dev34
 * Date: 11.12.16
 * Time: 19:20
 */

namespace Core;

use Silex\Application;

abstract class MountAbstract
{
    abstract public function mount(Application $app);
}