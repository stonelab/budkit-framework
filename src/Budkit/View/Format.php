<?php


namespace Budkit\View;

interface Format {
    /**
     * Get the compiled contents of the view.
     *
     * @param  string $path
     * @param  array  $data
     *
     * @return string
     */
    public function compile($viewpath, array $data = []);
    public function addLayoutSearchPaths(array $searchPaths = []);

}