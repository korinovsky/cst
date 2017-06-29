<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 App Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace cst\mail;

use App;

/**
 * Logger is a SwiftMailer plugin, which allows passing of the SwiftMailer internal logs to the
 * App logging mechanism. Each native SwiftMailer log message will be converted into App 'info' log entry.
 *
 * This logger will be automatically created and applied to underlying [[\Swift_Mailer]] instance, if [[Mailer::$enableSwiftMailerLogging]]
 * is enabled. For example:
 *
 * ```php
 * [
 *     'components' => [
 *         'mailer' => [
 *             'class' => 'cst\mail\Mailer',
 *             'enableSwiftMailerLogging' => true,
 *         ],
 *      ],
 *     // ...
 * ],
 * ```
 *
 *
 * In order to catch logs written by this class, you need to setup a log route for 'cst\mail\Logger::add' category.
 * For example:
 *
 * ```php
 * [
 *     'components' => [
 *         'log' => [
 *             'targets' => [
 *                 [
 *                     'class' => 'cst\log\FileTarget',
 *                     'categories' => ['cst\mail\Logger::add'],
 *                 ],
 *             ],
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ],
 * ```
 */
class Logger implements \Swift_Plugins_Logger
{
    /**
     * @inheritdoc
     */
    public function add($entry)
    {
        $categoryPrefix = substr($entry, 0, 2);
        switch ($categoryPrefix) {
            case '++':
                $level = \cst\log\Logger::LEVEL_TRACE;
                break;
            case '>>':
            case '<<':
                $level = \cst\log\Logger::LEVEL_INFO;
                break;
            case '!!':
                $level = \cst\log\Logger::LEVEL_WARNING;
                break;
            default:
                $level = \cst\log\Logger::LEVEL_INFO;
        }

        App::getLogger()->log($entry, $level, __METHOD__);
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        // do nothing
    }

    /**
     * @inheritdoc
     */
    public function dump()
    {
        return '';
    }
}