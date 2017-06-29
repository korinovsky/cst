<?php
/**
 * User: kg.korinovskiy
 * Date: 02.06.2017
 * Time: 10:41
 */

namespace cst\base;


/**
 * ExitException represents a normal termination of an application.
 *
 * Do not catch ExitException. App will handle this exception to terminate the application gracefully.
 */
class ExitException extends \Exception
{
    /**
     * @var integer the exit status code
     */
    public $statusCode;


    /**
     * Constructor.
     * @param integer $status the exit status code
     * @param string $message error message
     * @param integer $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($status = 0, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->statusCode = $status;
        parent::__construct($message, $code, $previous);
    }
}
