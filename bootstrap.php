<?php
/**
 * User: kg.korinovskiy
 * Date: 01.06.2017
 * Time: 17:12
 */

defined('APP_DIR') || define('APP_DIR', empty($_SERVER['DOCUMENT_ROOT']) ? dirname($_SERVER['PHP_SELF']).'/app/' : realpath($_SERVER['DOCUMENT_ROOT'].'/../app').'/');

defined('BEGIN_TIME') || define('BEGIN_TIME', microtime(true));

defined('CONF_DIR') || define('CONF_DIR', APP_DIR.'conf/');
defined('CONTROL_DIR') || define('CONTROL_DIR', APP_DIR.'controllers/');
defined('MODEL_DIR') || define('MODEL_DIR', APP_DIR.'models/');
defined('VIEW_DIR') || define('VIEW_DIR', APP_DIR.'views/');
defined('RUNTIME_DIR') || define('RUNTIME_DIR', APP_DIR.'runtime/');

define('MVC_DIR', __DIR__.'/');
define('MVC_VIEW_DIR', MVC_DIR.'views/');

defined('ENV_TEST') || define('ENV_TEST', !defined('ENV_PROD') || !ENV_PROD);
defined('ENV_PROD') || define('ENV_PROD', !ENV_TEST);
defined('DEBUG') || define('DEBUG', ENV_TEST);

class App extends \cst\base\BaseApp
{
    public static function hello() {
        echo APP_DIR;
    }
}
