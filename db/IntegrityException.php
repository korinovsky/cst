<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 App Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace cst\db;

/**
 * Exception represents an exception that is caused by violation of DB constraints.
 */
class IntegrityException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Integrity constraint violation';
    }
}
