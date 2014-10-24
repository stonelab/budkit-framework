<?php


namespace Budkit\View;

use Budkit\Application\Support\Mock;
use Budkit\Application\Support\Mockable;
use Budkit\Parameter\Factory as Parameters;
use Budkit\Protocol\Response;
use Budkit\View\Engine;


class Display extends Parameters implements Mockable {

    use Mock;

    protected $rendered = false;

    protected $response;

    protected $engine;

    protected $layout = null;


    public function __construct(array $data = [], Response $response, Engine $engine = null) {

        $this->response = $response;
        $this->engine   = $engine;

        parent::__construct("display", $data);

    }


    public function render($layout = null, $partial = false) {

        $layout  = (empty($layout) && !$partial) ? $this->getLayout() : $layout;
        $handler = $this->engine->getHandler();

        //We can only render layouts
        if ($this->rendered || empty($layout)) return null;

        $contents = $handler->compile($layout, $this->getDataArray());

        if (!$partial) $this->rendered = true;

        return $contents;

    }


    /**
     * Get the path to the view file.
     *
     * @return string
     */
    public function getLayout() {
        return $this->layout;
    }

    /**
     * Set the path to the view.
     *
     * @param  string $path
     *
     * @return void
     */
    public function setLayout($path) {
        $this->layout = $path;
    }

    public function getDataArray() {
        return $this->getAllParameters();
    }

    public function setDataArray(array $values) {
        foreach ($data as $key => $value) {
            $this->setData($key, $value);
        }

        return $this;
    }

    public function setData($key, $value = '') {
        return $this->setParameter($key, $value);
    }

    public function getData($key) {
        return $this->getParameter($key);
    }

}