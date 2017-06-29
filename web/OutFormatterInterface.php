<?php
/**
 * User: kg.korinovskiy
 * Date: 27.06.2017
 * Time: 10:55
 */

namespace cst\web;


/**
 * OutFormatterInterface specifies the interface needed to format a response before it is sent out.
 */
interface OutFormatterInterface
{
    /**
     * Formats the specified response.
     * @param Out $response the response to be formatted.
     */
    public function format($response);
}
