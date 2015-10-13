<?php


namespace Budkit\View;

use Budkit\Application\Support\Mock;
use Budkit\Application\Support\Mockable;
use Budkit\Protocol\Response;
use Budkit\View\Engine;


class Display implements Mockable
{

    use Mock;

    protected $rendered = false;
    protected $response;
    protected $engine;
    protected $layout = null;
    protected $searchPaths = array();

    protected $mergedData = [];


    public function __construct(Response $response, Engine $engine = null)
    {

        $this->response = $response;
        $this->engine = $engine;

    }


    public function appendLayoutSearchPath($path)
    {

        array_push($this->searchPaths, $path);

    }


    public function render($layout = null, $partial = false)
    {

        $layout = (empty($layout) && !$partial) ? $this->getLayout() : $layout;
        $handler = $this->engine->getHandler();

        $handler->addLayoutSearchPaths($this->searchPaths);
        $handler->addLayoutData($this->getDataArray());

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
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set the path to the view.
     *
     * @param  string $path
     *
     * @return void
     */
    public function setLayout($path)
    {
        $this->layout = $path;
    }

    public function getDataArray()
    {
        return $this->response->getAllParameters();
    }

    public function setDataArray(array $data)
    {
        return $this->response->addParameters($data);
    }

    public function setData($key, $value)
    {
        return $this->response->setParameter($key, $value);
    }

    public function addData($key, $value)
    {

        $existing = $this->getDataArray();

        if (!isset($existing[$key])) {

            $this->mergedData[] = $key;

            return $this->setData($key, [$value]);
        } //If we have previously merged
        else if (in_array($key, $this->mergedData)) {
            $existing[$key][] = $value;
            return $this->setData($key, $existing[$key]);
        }

        $existing[$key][] = [$existing[$key], $value];
        return $this->setData($key, $existing[$key]);
    }

    /**
     *
     *
     * @param $position
     * @param $content use import://layout/name to import a layout at position
     */
    public function addToBlock($position, $content)
    {

        $blocks = $this->getData("block");

        if (empty($blocks)) $blocks = [];

        if (!isset($blocks[$position])) {
            $blocks[$position] = [];
        }

        $blocks[$position][] = $content;

        return $this->setData("block", $blocks);

    }

    public function getData($key)
    {
        return $this->response->getParameter($key);
    }

}