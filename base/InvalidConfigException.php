<?php
/**
 * User: kg.korinovskiy
 * Date: 02.06.2017
 * Time: 10:42
 */

namespace cst\base;


/**
 * InvalidConfigException represents an exception caused by incorrect object configuration.
 */
class InvalidConfigException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid Configuration';
    }
}
