<?php
/**
 * User: kg.korinovskiy
 * Date: 02.06.2017
 * Time: 10:36
 */

namespace cst\web;
use App;
use cst\base\InvalidConfigException;
use cst\base\InvalidParamException;


/**
 * The web Response class represents an HTTP response
 *
 * It holds the [[headers]], [[cookies]] and [[content]] that is to be sent to the client.
 * It also controls the HTTP [[statusCode|status code]].
 *
 * Response is configured as an application component in [[\core\web\Application]] by default.
 * You can access that instance via `Yii::$app->response`.
 *
 * You can modify its configuration by adding an array to your application config under `components`
 * as it is shown in the following example:
 *
 * ~~~
 * 'response' => [
 *     'format' => core\web\Response::FORMAT_JSON,
 *     'charset' => 'UTF-8',
 *     // ...
 * ]
 * ~~~
 *
 * @property CookieCollection $cookies The cookie collection. This property is read-only.
 * @property boolean $isClientError Whether this response indicates a client error. This property is
 * read-only.
 * @property boolean $isEmpty Whether this response is empty. This property is read-only.
 * @property boolean $isForbidden Whether this response indicates the current request is forbidden. This
 * property is read-only.
 * @property boolean $isInformational Whether this response is informational. This property is read-only.
 * @property boolean $isInvalid Whether this response has a valid [[statusCode]]. This property is read-only.
 * @property boolean $isNotFound Whether this response indicates the currently requested resource is not
 * found. This property is read-only.
 * @property boolean $isOk Whether this response is OK. This property is read-only.
 * @property boolean $isRedirection Whether this response is a redirection. This property is read-only.
 * @property boolean $isServerError Whether this response indicates a server error. This property is
 * read-only.
 * @property boolean $isSuccessful Whether this response is successful. This property is read-only.
 * @property integer $statusCode The HTTP status code to send with the response.
 */
class Out extends \cst\base\Out
{
    const FORMAT_RAW = 'raw';
    const FORMAT_HTML = 'html';
    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';

    /**
     * @var string the response format. This determines how to convert [[data]] into [[content]]
     * when the latter is not set. The value of this property must be one of the keys declared in the [[formatters] array.
     * By default, the following formats are supported:
     *
     * - [[FORMAT_RAW]]: the data will be treated as the response content without any conversion.
     *   No extra HTTP header will be added.
     * - [[FORMAT_HTML]]: the data will be treated as the response content without any conversion.
     *   The "Content-Type" header will set as "text/html".
     * - [[FORMAT_JSON]]: the data will be converted into JSON format, and the "Content-Type"
     *   header will be set as "application/json".
     * - [[FORMAT_JSONP]]: the data will be converted into JSONP format, and the "Content-Type"
     *   header will be set as "text/javascript". Note that in this case `$data` must be an array
     *   with "data" and "callback" elements. The former refers to the actual data to be sent,
     *   while the latter refers to the name of the JavaScript callback.
     * - [[FORMAT_XML]]: the data will be converted into XML format. Please refer to [[XmlResponseFormatter]]
     *   for more details.
     *
     * You may customize the formatting process or support additional formats by configuring [[formatters]].
     * @see formatters
     */
    public $format = self::FORMAT_HTML;
    /**
     * @var mixed the original response data. When this is not null, it will be converted into [[content]]
     * according to [[format]] when the response is being sent out.
     * @see content
     */
    public $data;
    /**
     * @var string the response content. When [[data]] is not null, it will be converted into [[content]]
     * according to [[format]] when the response is being sent out.
     * @see data
     */
    public $content;

    /**
     * @var string the HTTP status description that comes together with the status code.
     * @see httpStatuses
     */
    public $statusText = 'OK';
    /**
     * @var integer the HTTP status code to send with the response.
     */
    private $_statusCode = 200;

    /**
     * @var string the charset of the text response. If not set, it will use
     * the value of [[Application::charset]].
     */
    public $charset;
    /**
     * @var string the version of the HTTP protocol to use. If not set, it will be determined via `$_SERVER['SERVER_PROTOCOL']`,
     * or '1.1' if that is not available.
     */
    public $version;

    /**
     * @var boolean whether the response has been sent. If this is true, calling [[send()]] will do nothing.
     */
    public $isSent = false;
    /**
     * @var array
     */
    public $headers = [];
    /**
     * @var array
     */
    private $formatters;

    /**
     * @var array list of HTTP status codes and the corresponding texts
     */
    public static $httpStatuses = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        118 => 'Connection timed out',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        210 => 'Content Different',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        310 => 'Too many Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range unsatisfiable',
        417 => 'Expectation failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable entity',
        423 => 'Locked',
        424 => 'Method failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway or Proxy Error',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        507 => 'Insufficient storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    public function init()
    {
        if ($this->version === null) {
            if (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.0') {
                $this->version = '1.0';
            } else {
                $this->version = '1.1';
            }
        }
        if ($this->charset === null) {
            $this->charset = 'UTF-8';
        }
        if ($this->formatters === null) {
            $this->formatters = [
                self::FORMAT_HTML => '\\cst\\web\\HtmlResponseFormatter',
                self::FORMAT_XML => '\\cst\\web\\XmlResponseFormatter',
                self::FORMAT_JSON => '\\cst\\web\\JsonResponseFormatter',
            ];
        }
    }

    /**
     * @return integer the HTTP status code to send with the response.
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    /**
     * Sets the response status code.
     * This method will set the corresponding status text if `$text` is null.
     * @param integer $value the status code
     * @param string $text the status text. If not set, it will be set automatically based on the status code.
     * @throws InvalidParamException if the status code is invalid.
     */
    public function setStatusCode($value, $text = null)
    {
        if ($value === null) {
            $value = 200;
        }
        $this->_statusCode = (int) $value;
        if ($this->getIsInvalid()) {
            throw new InvalidParamException("The HTTP status code is invalid: $value");
        }
        if ($text === null) {
            $this->statusText = isset(static::$httpStatuses[$this->_statusCode]) ? static::$httpStatuses[$this->_statusCode] : '';
        } else {
            $this->statusText = $text;
        }
    }

    /**
     * @return boolean whether this response has a valid [[statusCode]].
     */
    public function getIsInvalid()
    {
        return $this->getStatusCode() < 100 || $this->getStatusCode() >= 600;
    }

    /**
     * @return boolean whether this response is informational
     */
    public function getIsInformational()
    {
        return $this->getStatusCode() >= 100 && $this->getStatusCode() < 200;
    }

    /**
     * @return boolean whether this response is successful
     */
    public function getIsSuccessful()
    {
        return $this->getStatusCode() >= 200 && $this->getStatusCode() < 300;
    }

    /**
     * @return boolean whether this response is a redirection
     */
    public function getIsRedirection()
    {
        return $this->getStatusCode() >= 300 && $this->getStatusCode() < 400;
    }

    /**
     * @return boolean whether this response indicates a client error
     */
    public function getIsClientError()
    {
        return $this->getStatusCode() >= 400 && $this->getStatusCode() < 500;
    }

    /**
     * @return boolean whether this response indicates a server error
     */
    public function getIsServerError()
    {
        return $this->getStatusCode() >= 500 && $this->getStatusCode() < 600;
    }

    /**
     * @return boolean whether this response is OK
     */
    public function getIsOk()
    {
        return $this->getStatusCode() == 200;
    }

    /**
     * @return boolean whether this response indicates the current request is forbidden
     */
    public function getIsForbidden()
    {
        return $this->getStatusCode() == 403;
    }

    /**
     * @return boolean whether this response indicates the currently requested resource is not found
     */
    public function getIsNotFound()
    {
        return $this->getStatusCode() == 404;
    }

    /**
     * @return boolean whether this response is empty
     */
    public function getIsEmpty()
    {
        return in_array($this->getStatusCode(), [201, 204, 304]);
    }

    /**
     * Sends the response to the client.
     */
    public function send()
    {
        if ($this->isSent) {
            return;
        }
        $this->prepare();
        $this->sendHeaders();
        $this->sendContent();
        $this->isSent = true;
    }

    /**
     * Clears the headers, cookies, content, status code of the response.
     */
    public function clear()
    {
        $this->headers = null;
        $this->cookies = null;
        $this->_statusCode = 200;
        $this->statusText = 'OK';
        $this->data = null;
        $this->content = null;
        $this->isSent = false;
    }

    /**
     * Prepares for sending the response.
     * The default implementation will convert [[data]] into [[content]] and set headers accordingly.
     * @throws InvalidConfigException if the formatter for the specified format is invalid or [[format]] is not supported
     */
    protected function prepare()
    {
        if (isset($this->formatters[$this->format])) {
            $formatter = $this->formatters[$this->format];
            if (!is_object($formatter)) {
                $this->formatters[$this->format] = $formatter = App::createObject($formatter);
            }
            if ($formatter instanceof OutFormatterInterface) {
                $formatter->format($this);
            } else {
                throw new InvalidConfigException("The '{$this->format}' response formatter is invalid. It must implement the ResponseFormatterInterface.");
            }
        } elseif ($this->format === self::FORMAT_RAW) {
            if ($this->data !== null) {
                $this->content = $this->data;
            }
        } else {
            throw new InvalidConfigException("Unsupported response format: {$this->format}");
        }

        if (is_array($this->content)) {
            throw new InvalidParamException("Response content must not be an array.");
        } elseif (is_object($this->content)) {
            if (method_exists($this->content, '__toString')) {
                $this->content = $this->content->__toString();
            } else {
                throw new InvalidParamException("Response content must be a string or an object implementing __toString().");
            }
        }
    }

    /**
     * Sends the response headers to the client
     */
    protected function sendHeaders()
    {
        if (headers_sent()) {
            return;
        }
        $statusCode = $this->getStatusCode();
        header("HTTP/{$this->version} $statusCode {$this->statusText}");
        foreach ($this->headers as $name => $values) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            if (!is_array($values)) {
                $values = [$values];
            }
            // set replace for first occurrence of header but false afterwards to allow multiple
            $replace = true;
            foreach ($values as $value) {
                header("$name: $value", $replace);
                $replace = false;
            }
        }
        $this->sendCookies();
    }

    private $_cookies;

    /**
     * Returns the cookie collection.
     * Through the returned cookie collection, you add or remove cookies as follows,
     *
     * ~~~
     * // add a cookie
     * $response->cookies->add(new Cookie([
     *     'name' => $name,
     *     'value' => $value,
     * ]);
     *
     * // remove a cookie
     * $response->cookies->remove('name');
     * // alternatively
     * unset($response->cookies['name']);
     * ~~~
     *
     * @return CookieCollection the cookie collection.
     */
    public function getCookies()
    {
        if ($this->_cookies === null) {
            $this->_cookies = new CookieCollection;
        }
        return $this->_cookies;
    }

    /**
     * Sends the cookies to the client.
     */
    protected function sendCookies()
    {
        if ($this->_cookies === null) {
            return;
        }
        $security = App::getSecurity();
        if ($security->enableCookieValidation) {
            if ($security->cookieValidationKey == '') {
                throw new InvalidConfigException(get_class($security) . '::cookieValidationKey must be configured with a secret key.');
            }
            $validationKey = $security->cookieValidationKey;
        }

        foreach ($this->getCookies() as $cookie) {
            $value = $cookie->value;
            if ($cookie->expire != 1  && isset($validationKey)) {
                $value = $security->hashData(serialize([$cookie->name, $value]), $validationKey);
            }
            setcookie($cookie->name, $value, $cookie->expire, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httpOnly);
        }
    }

    /**
     * Sends the response content to the client
     */
    protected function sendContent()
    {
        echo $this->content;
    }

    /**
     * Redirects the browser to the specified URL.
     *
     * This method adds a "Location" header to the current response. Note that it does not send out
     * the header until [[send()]] is called. In a controller action you may use this method as follows:
     *
     * ~~~
     * return Yii::$app->getResponse()->redirect($url);
     * ~~~
     *
     * In other places, if you want to send out the "Location" header immediately, you should use
     * the following code:
     *
     * ~~~
     * Yii::$app->getResponse()->redirect($url)->send();
     * return;
     * ~~~
     *
     * In AJAX mode, this normally will not work as expected unless there are some
     * client-side JavaScript code handling the redirection. To help achieve this goal,
     * this method will send out a "X-Redirect" header instead of "Location".
     *
     * If you use the "yii" JavaScript module, it will handle the AJAX redirection as
     * described above. Otherwise, you should write the following JavaScript code to
     * handle the redirection:
     *
     * ~~~
     * $document.ajaxComplete(function (event, xhr, settings) {
     *     var url = xhr.getResponseHeader('X-Redirect');
     *     if (url) {
     *         window.location = url;
     *     }
     * });
     * ~~~
     *
     * @param string|array $url the URL to be redirected to. This can be in one of the following formats:
     *
     * - a string representing a URL (e.g. "http://example.com")
     * - a string representing a URL alias (e.g. "@example.com")
     * - an array in the format of `[$route, ...name-value pairs...]` (e.g. `['site/index', 'ref' => 1]`).
     *   Note that the route is with respect to the whole application, instead of relative to a controller or module.
     *   [[Url::to()]] will be used to convert the array into a URL.
     *
     * Any relative URL will be converted into an absolute one by prepending it with the host info
     * of the current request.
     *
     * @param integer $statusCode the HTTP status code. Defaults to 302.
     * See <http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html>
     * for details about HTTP status code
     * @param boolean $checkAjax whether to specially handle AJAX (and PJAX) requests. Defaults to true,
     * meaning if the current request is an AJAX or PJAX request, then calling this method will cause the browser
     * to redirect to the given URL. If this is false, a `Location` header will be sent, which when received as
     * an AJAX/PJAX response, may NOT cause browser redirection.
     * @return $this the response object itself
     */
    public function redirect($url, $statusCode = 302, $checkAjax = true)
    {
//        if (is_array($url) && isset($url[0])) {
//            // ensure the route is absolute
//            $url[0] = '/' . ltrim($url[0], '/');
//        }
//        $url = Url::to($url);
//        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
//            $url = App::$in->getHostInfo() . $url;
//        }
        $url = App::createAbsoluteUrl($url);

        if ($checkAjax) {
            if (App::$in->getIsAjax()) {
                $this->headers['X-Redirect'] = $url;
            } else {
                $this->headers['Location'] = $url;
            }
        } else {
            $this->headers['Location'] = $url;
        }

        $this->setStatusCode($statusCode);

        return $this;
    }

    /**
     * Refreshes the current page.
     * The effect of this method call is the same as the user pressing the refresh button of his browser
     * (without re-posting data).
     *
     * In a controller action you may use this method like this:
     *
     * ~~~
     * return Yii::$app->getResponse()->refresh();
     * ~~~
     *
     * @param string $anchor the anchor that should be appended to the redirection URL.
     * Defaults to empty. Make sure the anchor starts with '#' if you want to specify it.
     * @return Out the response object itself
     */
    public function refresh($anchor = '')
    {
        return $this->redirect(App::$in->getUrl() . $anchor);
    }

    /**
     * @param $action
     * @return array
     */
    public function parseAction($action, &$error = null) {
        $result = [
            $this->defaultController,
            '',
            $this->defaultRoute
        ];
        if ($action) {
            if ($this->rules) {
                foreach ($this->rules as $r => $a) {
                    if (preg_match($r, $action, $matches)) {
                        $action = preg_replace($r, $a, $action);
                        foreach ($matches as $k => $v) {
                            if (is_string($k)) {
                                $this->params[$k] = $v;
                            }
                        }
                        break;
                    }
                }
            }
            $path = explode('/', $action);
            $name = Inflector::id2camel($path0 = array_pop($path));
            if (file_exists($f = CONTROL_DIR.($path ? implode('/', $path).'/' : '').$name.'.php')) {
                $result[0] = '\\app\\controllers\\'.($path ? implode('\\', $path).'\\' : '').$name;
                $result[2][0] = $result[2][1] = ($path ? implode('/', $path).'/' : '').$path0;
            }
            else {
                if ($path) {
                    $name2 = Inflector::id2camel($path1 = array_pop($path));
                    $result[0] = '\\app\\controllers\\'.($path ? implode('\\', $path).'\\' : '').$name2;
                    $result[2][0] = $result[2][1] = ($path ? implode('/', $path).'/' : '').$path1;
                }
                $result[1] = 'action'.$name;
                $result[2][0] .= '/'.($result[2][2] = $path0);
            }
        }
        if ($result[0] && ($methods = get_class_methods($result[0]))) {
            $result[2][3] = Inflector::camel2id(substr($default = get_class_vars($result[0])['defaultAction'], 6));
            if (!$result[1]) {
                $result[1] = $default;
                $result[2][0] .= '/'.$result[2][3];
            }
            if (in_array($result[1], $methods)) {
                return $result;
            }
            if (in_array($func = 'errorAction', $methods)) {
                $error = [
                    $result[0],
                    $func,
                ];
            }
        }
        return false;
    }
}