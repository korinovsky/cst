<?php
/**
 * User: kg.korinovskiy
 * Date: 02.06.2017
 * Time: 10:42
 */

namespace cst\base;


/**
 * InvalidParamException represents an exception caused by invalid parameters passed to a method.
 */
class InvalidParamException extends \Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid Parameter';
    }
}
