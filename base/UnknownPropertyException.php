<?php
/**
 * User: kg.korinovskiy
 * Date: 02.06.2017
 * Time: 10:44
 */

namespace cst\base;


/**
 * UnknownPropertyException represents an exception caused by accessing unknown object properties.
 */
class UnknownPropertyException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Unknown Property';
    }
}
