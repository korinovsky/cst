<?php
/**
 * User: kg.korinovskiy
 * Date: 02.06.2017
 * Time: 12:23
 */

namespace cst\base;


/**
 * JsExpression marks a string as a JavaScript expression.
 *
 * When using [[\helpers\Json::encode()]] or [[\helpers\Json::htmlEncode()]] to encode a value, JsonExpression objects
 * will be specially handled and encoded as a JavaScript expression instead of a string.
 */
class JsExpression
{
    /**
     * @var string the JavaScript expression represented by this object
     */
    public $expression;

    /**
     * Constructor.
     * @param string $expression the JavaScript expression represented by this object
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    /**
     * The PHP magic function converting an object into a string.
     * @return string the JavaScript expression.
     */
    public function __toString()
    {
        return $this->expression;
    }
}
