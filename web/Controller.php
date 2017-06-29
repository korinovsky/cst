<?php
/**
 * User: kg.korinovskiy
 * Date: 02.06.2017
 * Time: 11:03
 */

namespace cst\web;


use App;
use ReflectionMethod;

class Controller extends \cst\base\Controller
{
    /**
     * @var boolean whether to enable CSRF validation for the actions in this controller.
     * CSRF validation is enabled only when both this property and [[Request::enableCsrfValidation]] are true.
     */
    public $enableCsrfValidation = true;

    /**
     * @var Session
     */
    public $session;

    /**
     * @inheritdoc
     */
    function init() {
        $this->view = new View();
        parent::init();
        $this->session = new Session();
    }

    /**
     * Redirects the browser to the specified URL.
     * This method is a shortcut to [[Response::redirect()]].
     * @param string|array $url the URL to be redirected to.
     * Any relative URL will be converted into an absolute one by prepending it with the host info
     * of the current request.
     * @param integer $statusCode the HTTP status code. Defaults to 302.
     * See <http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html>
     * for details about HTTP status code
     * @return Out the response object itself
     */
    public function redirect($url, $statusCode = 302)
    {
        return App::$out->redirect($url, $statusCode);
    }

    /**
     * Refreshes the current page.
     * This method is a shortcut to [[Response::refresh()]].
     * @param string $anchor the anchor that should be appended to the redirection URL.
     * Defaults to empty. Make sure the anchor starts with '#' if you want to specify it.
     * @return Out the response object itself
     */
    public function refresh($anchor = '')
    {
        return App::$out->redirect(App::$request->getUrl() . $anchor);
    }

    /**
     * Redirects the browser to the home page.
     *
     * You can use this method in an action by returning the [[Response]] directly:
     *
     * ```php
     * // stop executing this action and redirect to home page
     * return $this->goHome();
     * ```
     *
     * @return Response the current response object
     */
    public function goHome()
    {
        return App::$out->redirect(App::$request->getBaseUrl() . '/');
    }

    public function resultAjax($result)
    {
        App::$out->format = Response::FORMAT_JSON;
        return $result;
    }

    public function resultXml($result)
    {
        App::$out->format = Response::FORMAT_XML;
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if ($this->enableCsrfValidation && App::$errorHandler->exception === null && !App::$request->validateCsrfToken()) {
                throw new BadRequestHttpException(App::t('mvc', 'Unable to verify your data submission.'));
            }
            return true;
        }
        return false;
    }

    /**
     * Binds the parameters to the action.
     * This method is invoked by [[\core\Action]] when it begins to run with the given parameters.
     * This method will check the parameter names that the action requires and return
     * the provided parameters according to the requirement. If there is any missing parameter,
     * an exception will be thrown.
     * @param string $action the action to be bound with parameters
     * @param array $params the parameters to be bound to the action
     * @return array the valid parameters that the action can run with.
     * @throws BadRequestHttpException if there are missing or invalid parameters.
     */
    public function bindActionParams($action, $params)
    {
        $method = new ReflectionMethod($this, $action);
        $args = [];
        $missing = [];
        $actionParams = [];
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $params)) {
                if ($param->isArray()) {
                    $args[] = $actionParams[$name] = (array) $params[$name];
                } elseif (!is_array($params[$name])) {
                    $args[] = $actionParams[$name] = $params[$name];
                } else {
                    throw new BadRequestHttpException(App::t('mvc', 'Invalid data received for parameter "{param}".', [
                        'param' => $name,
                    ]));
                }
                unset($params[$name]);
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $actionParams[$name] = $param->getDefaultValue();
            } else {
                $missing[] = $name;
            }
        }

        if (!empty($missing)) {
            throw new BadRequestHttpException(App::t('mvc', 'Missing required parameters: {params}', [
                'params' => implode(', ', $missing),
            ]));
        }
        return $args;
    }
}
