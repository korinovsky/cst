<?php
/**
 * User: kg.korinovskiy
 * Date: 01.06.2017
 * Time: 17:13
 */

namespace cst\base;
use cst\helpers\ArrayHelper;


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
     * @return object the created object
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

}