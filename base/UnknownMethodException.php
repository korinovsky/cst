<?php
/**
 * User: kg.korinovskiy
 * Date: 02.06.2017
 * Time: 10:43
 */

namespace cst\base;


/**
 * UnknownMethodException represents an exception caused by accessing an unknown object method.
 */
class UnknownMethodException extends \BadMethodCallException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Unknown Method';
    }
}
