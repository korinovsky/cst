<?php
/**
 * User: kg.korinovskiy
 * Date: 01.06.2017
 * Time: 17:12
 */

defined('APP_PATH') || define('APP_PATH', empty($_SERVER['DOCUMENT_ROOT']) ? dirname($_SERVER['PHP_SELF']).'/app/' : realpath($_SERVER['DOCUMENT_ROOT'].'/../app').'/');

defined('BEGIN_TIME') || define('BEGIN_TIME', microtime(true));

defined('CONF_PATH') || define('CONF_PATH', APP_PATH.'conf/');
defined('CONTROL_PATH') || define('CONTROL_PATH', APP_PATH.'controllers/');
defined('MODEL_PATH') || define('MODEL_PATH', APP_PATH.'models/');
defined('VIEW_PATH') || define('VIEW_PATH', APP_PATH.'views/');
defined('RUNTIME_PATH') || define('RUNTIME_PATH', APP_PATH.'runtime/');

define('CST_PATH', __DIR__.'/');
define('CST_VIEW_PATH', CST_PATH.'views/');

defined('ENV_TEST') || define('ENV_TEST', !defined('ENV_PROD') || !ENV_PROD);
defined('ENV_PROD') || define('ENV_PROD', !ENV_TEST);
defined('DEBUG') || define('DEBUG', ENV_TEST);

class App extends \cst\base\BaseApp
{
    public static function hello() {
        echo APP_PATH;
    }
}
