<?php


namespace Budkit\View;

use Budkit\Protocol\Response;

interface Format
{
    /**
     * Get the compiled contents of the view.
     *
     * @param  string $path
     * @param  array $data
     *
     * @return string
     */
    public function compile($viewpath, array $data = []);

    public function addLayoutSearchPaths(array $searchPaths = []);

    public function addLayoutData(array $layoutData);

    public function setResponse(Response $response);

}