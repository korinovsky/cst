<?php
/**
 * User: kg.korinovskiy
 * Date: 02.06.2017
 * Time: 10:43
 */

namespace cst\base;


/**
 * NotSupportedException represents an exception caused by accessing features that are not supported.
 */
class NotSupportedException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Not Supported';
    }
}
