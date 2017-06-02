<?php
/**
 * User: kg.korinovskiy
 * Date: 02.06.2017
 * Time: 10:50
 */

namespace cst\base;


/**
 * InvalidCallException represents an exception caused by calling a method in a wrong way.
 */
class InvalidCallException extends \BadMethodCallException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid Call';
    }
}
