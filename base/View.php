<?php
/**
 * User: kg.korinovskiy
 * Date: 02.06.2017
 * Time: 11:03
 */

namespace cst\base;


class View extends Object
{
    /**
     * @var Controller
     */
    public $context;
    /**
     * @var array custom parameters that are shared among view templates.
     */
    public $params = [];

    /**
     * @param string $view
     * @param array $params
     * @param Controller $context
     * @return string
     */
    public function render($view, $params = [], $context = null)
    {
        return $this->renderFile(VIEW_PATH.$view.'.php', $params, $context);
    }

    /**
     * @param string $file
     * @param array $params
     * @param Controller $context
     * @return string
     */
    public function renderFile($file, $params = [], $context = null) {
        $oldContext = $this->context;
        if ($context !== null) {
            $this->context = $context;
        }
        $output = '';
        if ($this->beforeRender($file, $params)) {
            $output = $this->renderPhpFile($file, $params);
            $this->afterRender($file, $params, $output);
        }

        $this->context = $oldContext;

        return $output;
    }

    /**
     * Renders a view file as a PHP script.
     *
     * @param string $_file_ the view file.
     * @param array $_params_ the parameters (name-value pairs) that will be extracted and made available in the view file.
     * @return string the rendering result
     */
    public function renderPhpFile($_file_, $_params_ = []) {
        ob_start();
        ob_implicit_flush(false);
        extract($_params_, EXTR_OVERWRITE);
        require($_file_);

        return ob_get_clean();
    }

    /**
     * This method is invoked right before [[renderFile()]] renders a view file.
     * If you override this method, make sure you call the parent implementation first.
     * @param string $viewFile the view file to be rendered.
     * @param array $params the parameter array passed to the [[render()]] method.
     * @return boolean whether to continue rendering the view file.
     */
    public function beforeRender($viewFile, $params)
    {
        return true;
    }

    /**
     * This method is invoked right after [[renderFile()]] renders a view file.
     * If you override this method, make sure you call the parent implementation first.
     * @param string $viewFile the view file being rendered.
     * @param array $params the parameter array passed to the [[render()]] method.
     * @param string $output the rendering result of the view file. Updates to this parameter
     * will be passed back and returned by [[renderFile()]].
     */
    public function afterRender($viewFile, $params, &$output) {}
}