<?php
/**
 * User: kg.korinovskiy
 * Date: 14.06.2017
 * Time: 12:02
 */

namespace cst\web;


/**
 * NotFoundHttpException represents a "Not Found" HTTP exception with status code 404.
 */
class NotFoundHttpException extends HttpException
{
    /**
     * Constructor.
     * @param string $message error message
     * @param integer $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(404, $message, $code, $previous);
    }
}
