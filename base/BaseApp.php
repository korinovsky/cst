<?php
/**
 * User: kg.korinovskiy
 * Date: 01.06.2017
 * Time: 17:13
 */

namespace cst\base;
use cst\helpers\ArrayHelper;
use cst\helpers\Inflector;
use cst\log\Logger;


class BaseApp
{
    /**
     * @var integer Тип приложения
     */
    public static $type;
    /**
     * Типы приложения
     */
    const TYPE_CONSOLE = 0;
    const TYPE_WEB = 1;

    /**
     * @var array
     */
    public static $params = [];

    /**
     * @var \cst\web\In|\cst\console\In
     */
    public static $in;

    /**
     * @var \cst\web\Out|\cst\console\Out
     */
    public static $out;

    /**
     * @var \cst\web\ErrorHandler|\cst\console\ErrorHandler
     */
    public static $errorHandler;

    /**
     * @var \cst\web\Controller|\cst\console\Controller
     */
    public static $controller;

    /**
     * @var string
     */
    public static $language = 'en';

    /**
     * @var string
     */
    public static $sourceLanguage = 'en';

    /**
     * @var string
     */
    public static $version = '0.1';

    /**
     * @var array
     */
    private static $_components = [
        'logger' => ['class' => 'cst\log\Logger'],
        'formatter' => ['class' => 'cst\i18n\Formatter'],
        'i18n' => ['class' => 'cst\i18n\I18N'],
        'mailer' => ['class' => 'cst\mail\Mailer'],
        'security' => ['class' => 'cst\base\Security'],
//        'view' => ['class' => 'cst\web\View'],
//        'urlManager' => ['class' => 'cst\web\UrlManager'],
//        'assetManager' => ['class' => 'cst\web\AssetManager'],
        'in' => ['class' => 'cst\base\In'],
        'out' => ['class' => 'cst\base\Out'],
        'errorHandler' => ['class' => 'cst\base\ErrorHandler'],
    ];


    public static function web() {
        self::init(self::TYPE_WEB);
    }

    public static function console() {
        self::init(self::TYPE_CONSOLE);
    }

    private static function init($type) {
        self::$type = $type;
    }

    /**
     * Configures an object with the initial property values.
     * @param object $object the object to be configured
     * @param array $properties the property initial values given in terms of name-value pairs.
     * @return object the object itself
     */
    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

    public static function runAction($action = null) {
        if ($action) {
            if ($parsed = self::$in->parseAction($action)) {
                self::$in->initAction($parsed);
            }
            else {
                throw new InvalidConfigException();
            }
        }
        //var_dump(self::$out);

        // Инициализируем контроллер
        self::$controller = new self::$in->controller();
        //var_dump(self::$controller);

        // Выполняем действие
        /** @var \cst\web\Out|\cst\console\Out|string $result */
        $result = self::$controller->runAction(self::$in->action, self::$in->params);
        return $result;
    }

    /**
     * Creates a new object using the given configuration.
     *
     * You may view this method as an enhanced version of the `new` operator.
     * The method supports creating an object based on a class name, a configuration array or
     * an anonymous function.
     *
     * Below are some usage examples:
     *
     * ```php
     * // create an object using a class name
     * $object = self::createObject('cst\db\Connection');
     *
     * // create an object using a configuration array
     * $object = self::createObject([
     *     'class' => 'cst\db\Connection',
     *     'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
     *     'username' => 'root',
     *     'password' => '',
     *     'charset' => 'utf8',
     * ]);
     *
     * // create an object with two constructor parameters
     * $object = \self::createObject('MyClass', [$param1, $param2]);
     * ```
     *
     * @param string|array|callable $type the object type. This can be specified in one of the following forms:
     *
     * - a string: representing the class name of the object to be created
     * - a configuration array: the array must contain a `class` element which is treated as the object class,
     *   and the rest of the name-value pairs will be used to initialize the corresponding object properties
     * - a PHP callable: either an anonymous function or an array representing a class method (`[$class or $object, $method]`).
     *   The callable should return a new instance of the object being created.
     *
     * @param array $params the constructor parameters
     * @return Object the created object
     * @throws InvalidConfigException if the configuration is invalid.
     */
    public static function createObject($type, array $params = [])
    {
        if (is_string($type)) {
            return Object::createObject($type, $params);
        } elseif (is_array($type) && isset($type['class'])) {
            $class = $type['class'];
            unset($type['class']);
            return Object::createObject($class, $params, $type);
        } elseif (is_callable($type, true)) {
            return call_user_func($type, $params);
        } elseif (is_array($type)) {
            throw new InvalidConfigException('Object configuration must be an array containing a "class" element.');
        } else {
            throw new InvalidConfigException("Unsupported configuration type: " . gettype($type));
        }
    }

    public static function get($id) {
        return isset(self::$$id) ? self::$$id : (method_exists('self', $method = 'get'.ucfirst($id)) ? self::$method() : null);
    }
    
    /**
     * Returns the component instance with the specified ID.
     *
     * @param string $id component ID (e.g. `db`).
     * @param boolean $throwException whether to throw an exception if `$id` is not registered with the locator before.
     * @return object|null the component of the specified ID. If `$throwException` is false and `$id`
     * is not registered before, null will be returned.
     * @throws InvalidConfigException if `$id` refers to a nonexistent component ID
     * @see has()
     * @see set()
     */
    public static function getComponent($id, $throwException = true) {
        if (isset(self::$_components[$id])) {
            if (is_object(self::$_components[$id])) {
                return self::$_components[$id];
            }
            if (isset(self::$params['components'][$id])) {
                $definition = self::$params['components'][$id];
                if (is_string($definition)) {
                    $definition = ['class' => $definition];
                }
                return self::$_components[$id] = self::createObject(ArrayHelper::merge(self::$_components[$id], $definition));
            }
            return self::$_components[$id] = self::createObject(self::$_components[$id]);
        }
        if (isset(self::$params['components'][$id])) {
            return self::$_components[$id] = self::createObject(self::$params['components'][$id]);
        }
        elseif ($throwException) {
            throw new InvalidConfigException("Unknown component ID: $id");
        }
        else {
            return null;
        }
    }

    /**
     * Returns the internationalization (i18n) component
     * @return \cst\i18n\I18N|object the internationalization application component.
     */
    public static function getI18n()
    {
        return self::getComponent('i18n');
    }

    /**
     * Returns the formatter component.
     * @return \cst\i18n\Formatter|object the formatter application component.
     */
    public static function getFormatter()
    {
        return self::getComponent('formatter');
    }

    /**
     * Returns the database connection component.
     * @return \cst\db\Connection|object the database connection.
     */
    public static function getDb()
    {
        return self::getComponent('db');
    }

    /**
     * @return \cst\mail\Mailer|object
     * @throws InvalidConfigException
     */
    public static function getMailer()
    {
        return self::getComponent('mailer');
    }

    /**
     * @return \cst\log\Logger|object message logger
     * @throws InvalidConfigException
     */
    public static function getLogger()
    {
        return self::getComponent('logger');
    }

    /**
     * Returns the security component.
     * @return \cst\base\Security|object the security application component.
     */
    public static function getSecurity()
    {
        return self::getComponent('security');
    }

    /**
     * Returns the cache component.
     * @return \cst\caching\Cache|object the cache application component.
     */
    public static function getCache()
    {
        return self::getComponent('cache');
    }


    /**
     * Translates a message to the specified language.
     *
     * The translation will be conducted according to the message category and the target language will be used.
     *
     * You can add parameters to a translation message that will be substituted with the corresponding value after
     * translation. The format for this is to use curly brackets around the parameter name as you can see in the following example:
     *
     * ```php
     * $username = 'Alexander';
     * echo \self::t('mvc', 'Hello, {username}!', ['username' => $username]);
     * ```
     *
     * Further formatting of message parameters is supported using the [PHP intl extensions](http://www.php.net/manual/en/intro.intl.php)
     * message formatter.
     *
     * @param string $category the message category.
     * @param string $message the message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\self::language|application language]] will be used.
     * @return string the translated message.
     */
    public static function t($category, $message, $params = [], $language = null)
    {
//        if (self::$app !== null) {
        return self::getI18n()->translate($category, $message, $params, $language ?: self::$language);
//        } else {
//            $p = [];
//            foreach ((array) $params as $name => $value) {
//                $p['{' . $name . '}'] = $value;
//            }
//
//            return ($p === []) ? $message : strtr($message, $p);
//        }
    }

    /**
     * Logs a trace message.
     * Trace messages are logged mainly for development purpose to see
     * the execution work flow of some code.
     * @param string $message the message to be logged.
     * @param string $category the category of the message.
     */
    public static function trace($message, $category = 'app')
    {
        if (DEBUG) {
            self::getLogger()->log($message, Logger::LEVEL_TRACE, $category);
        }
    }

    /**
     * Logs an error message.
     * An error message is typically logged when an unrecoverable error occurs
     * during the execution of an application.
     * @param string $message the message to be logged.
     * @param string $category the category of the message.
     */
    public static function error($message, $category = 'app')
    {
        self::getLogger()->log($message, Logger::LEVEL_ERROR, $category);
    }

    /**
     * Logs a warning message.
     * A warning message is typically logged when an error occurs while the execution
     * can still continue.
     * @param string $message the message to be logged.
     * @param string $category the category of the message.
     */
    public static function warning($message, $category = 'app')
    {
        self::getLogger()->log($message, Logger::LEVEL_WARNING, $category);
    }

    /**
     * Logs an informative message.
     * An informative message is typically logged by an application to keep record of
     * something important (e.g. an administrator logs in).
     * @param string $message the message to be logged.
     * @param string $category the category of the message.
     */
    public static function info($message, $category = 'app')
    {
        self::getLogger()->log($message, Logger::LEVEL_INFO, $category);
    }

    /**
     * Marks the beginning of a code block for profiling.
     * This has to be matched with a call to [[endProfile]] with the same category name.
     * The begin- and end- calls must also be properly nested. For example,
     *
     * ~~~
     * \App::beginProfile('block1');
     * // some code to be profiled
     *     \App::beginProfile('block2');
     *     // some other code to be profiled
     *     \App::endProfile('block2');
     * \App::endProfile('block1');
     * ~~~
     * @param string $token token for the code block
     * @param string $category the category of this log message
     * @see endProfile()
     */
    public static function beginProfile($token, $category = 'application')
    {
        static::getLogger()->log($token, Logger::LEVEL_PROFILE_BEGIN, $category);
    }

    /**
     * Marks the end of a code block for profiling.
     * This has to be matched with a previous call to [[beginProfile]] with the same category name.
     * @param string $token token for the code block
     * @param string $category the category of this log message
     * @see beginProfile()
     */
    public static function endProfile($token, $category = 'application')
    {
        static::getLogger()->log($token, Logger::LEVEL_PROFILE_END, $category);
    }

    /**
     * Returns an HTML hyperlink that can be displayed on your Web page showing "Powered by App Framework" information.
     * @return string an HTML hyperlink that can be displayed on your Web page showing "Powered by App Framework" information
     */
    public static function powered()
    {
        return self::t('cst', 'Powered by {0}', ['<a href="http://www.roscap.ru/" rel="external">RosCap Framework</a>']);
    }

    /**
     * Returns the time zone used by this application.
     * This is a simple wrapper of PHP function date_default_timezone_get().
     * If time zone is not configured in php.ini or application config,
     * it will be set to UTC by default.
     * @return string the time zone used by this application.
     * @see http://php.net/manual/en/function.date-default-timezone-get.php
     */
    public static function getTimeZone()
    {
        return date_default_timezone_get();
    }

    /**
     * Sets the time zone used by this application.
     * This is a simple wrapper of PHP function date_default_timezone_set().
     * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
     * @param string $value the time zone used by this application.
     * @see http://php.net/manual/en/function.date-default-timezone-set.php
     */
    public static function setTimeZone($value)
    {
        date_default_timezone_set($value);
    }

    /**
     * Creates a URL using the given route and query parameters.
     *
     * You may specify the route as a string, e.g., `site/index`. You may also use an array
     * if you want to specify additional query parameters for the URL being created. The
     * array format must be:
     *
     * ```php
     * // generates: /index.php?r=site/index&param1=value1&param2=value2
     * ['site/index', 'param1' => 'value1', 'param2' => 'value2']
     * ```
     *
     * If you want to create a URL with an anchor, you can use the array format with a `#` parameter.
     * For example,
     *
     * ```php
     * // generates: /index.php?r=site/index&param1=value1#name
     * ['site/index', 'param1' => 'value1', '#' => 'name']
     * ```
     *
     * The URL created is a relative one. Use [[createAbsoluteUrl()]] to create an absolute URL.
     *
     * Note that unlike [[\helpers\Url::toRoute()]], this method always treats the given route
     * as an absolute route.
     *
     * @param string|array $params use a string to represent a route (e.g. `site/index`),
     * or an array to represent a route with query parameters (e.g. `['site/index', 'param1' => 'value1']`).
     * @return string the created URL
     */
    public static function createUrl($params)
    {
        if (is_string($params) && (strpos($params, ':') !== false || strpos($params, '#') === 0)) {
            // с абсолютной ссылкой со схемой нечего делать
            return $params;
        }

        $params = (array) $params;

        $anchor = isset($params['#']) ? '#' . $params['#'] : '';
        unset($params['#']);

        $route = isset($params[0]) ? $params[0] : (self::$in instanceof \cst\web\Out ? self::$in->pathUri : '');
        unset($params[0]);

        $pos = null;
        if ($route) {
            if (($pos = strpos($route, '/')) === 0) {
                // абсолютная ссылка /base/controller/action
            }
            else {
                if ($pos === false) {
                    // одно слово action
                    if (self::$in->route[3] === $route) {
                        $route = '';
                    }
                    if (isset(self::$in->route[1])) {
                        $route = self::$in->route[1].'/'.$route;
                    }
                }
                else {
                    // не одно слово controller/action
                    $detail = [];
                    $path = explode('/', $route);
                    $name = Inflector::id2camel($path0 = array_pop($path));
                    $controller = null;
                    if (file_exists($f = CONTROL_PATH.($path ? implode('/', $path).'/' : '').$name.'.php')) {
                        $controller = '\\app\\controllers\\'.($path ? implode('\\', $path).'\\' : '').$name;
                        $detail[0] = ($path ? implode('/', $path).'/' : '').$path0;
                    }
                    else {
                        if ($path) {
                            $name2 = Inflector::id2camel($path1 = array_pop($path));
                            $controller = '\\app\\controllers\\'.($path ? implode('\\', $path).'\\' : '').$name2;
                            $detail[0] = ($path ? implode('/', $path).'/' : '').$path1;
                        }
                        $detail[1] = $path0;
                    }
                    if ($controller && class_exists($controller)) {
                        if ($controller === '\\app\\controllers\\Main') {
                            unset($detail[0]);
                        }
                        if (isset($detail[1]) && $detail[1] === Inflector::camel2id(substr(get_class_vars($controller)['defaultAction'], 6))) {
                            unset($detail[1]);
                        }
                        $route = implode('/', $detail);
                    }
                    else {
                        // путь не найден
                    }
                }
                $route = trim($route, '/');
            }
        }

        $url = ($pos !== 0 ? self::$in->getBaseUrl().'/' : '').$route;

        if (!empty($params) && ($query = http_build_query($params)) !== '') {
            $url .= '?' . $query;
        }

        return $url.$anchor;
    }

    /**
     * Creates an absolute URL using the given route and query parameters.
     *
     * This method prepends the URL created by [[createUrl()]] with the [[hostInfo]].
     *
     * Note that unlike [[\helpers\Url::toRoute()]], this method always treats the given route
     * as an absolute route.
     *
     * @param string|array $params use a string to represent a route (e.g. `site/index`),
     * or an array to represent a route with query parameters (e.g. `['site/index', 'param1' => 'value1']`).
     * @param string $scheme the scheme to use for the url (either `http` or `https`). If not specified
     * the scheme of the current request will be used.
     * @return string the created URL
     * @see createUrl()
     */
    public static function createAbsoluteUrl($params, $scheme = null)
    {
        $url = self::createUrl($params);
        if (strpos($url, '://') === false) {
            $url = self::$in->getHostInfo() . $url;
        }
        if (is_string($scheme) && ($pos = strpos($url, '://')) !== false) {
            $url = $scheme . substr($url, $pos);
        }

        return $url;
    }
}