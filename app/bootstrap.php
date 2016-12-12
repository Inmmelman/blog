<?php

require_once __DIR__ . '/../vendor/autoload.php';

defined('CORE_ROOT_DIR') or define('CORE_ROOT_DIR', realpath(__DIR__ . '/..'));

defined('CORE_APP_DIR') or define('CORE_APP_DIR', CORE_ROOT_DIR . '/app');
defined('CORE_MODULES_DIR') or define('CORE_MODULES_DIR', CORE_ROOT_DIR . '/app/modules');

defined('CORE_CONFIG_DIR') or define('CORE_CONFIG_DIR', CORE_ROOT_DIR . '/app/config');
defined('CORE_CACHE_DIR') or define('CORE_CACHE_DIR', CORE_ROOT_DIR . '/app/cache');
defined('CORE_DEFAULT_CONFIG_FILE') or define('CORE_DEFAULT_CONFIG_FILE', 'config.yml');
defined('CORE_DEFAULT_CONFIG_FILE_FORMAT') or define('CORE_DEFAULT_CONFIG_FILE_FORMAT', 'yml');

defined('CORE_WEB_DIR') or define('CORE_WEB_DIR', CORE_ROOT_DIR . '/web');
defined('CORE_RUNTIME_DIR') or define('CORE_RUNTIME_DIR', CORE_ROOT_DIR . '/runtime');