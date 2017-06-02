<?php
/**
 * User: kg.korinovskiy
 * Date: 02.06.2017
 * Time: 10:39
 */

namespace cst\base;


/**
 * Exception represents a generic exception for all purposes.
 */
class Exception extends \Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Exception';
    }
}
