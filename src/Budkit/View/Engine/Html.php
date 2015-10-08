<?php

namespace Budkit\View\Engine;

use Budkit\View\Format;
use Budkit\View\Layout\Compiler;
use Budkit\View\Layout\Loader;

class Html implements Format {

    protected $loader;
    protected $compiler;

    public function __construct(Loader $loader, Compiler $compiler) {
        //Need a layout resolver here which can also find layouts in templates;
        $this->loader   = $loader;
        $this->compiler = $compiler;

    }

    public function compile($viewpath, array $data = []) {

        //for now just import the file;
        return $this->compiler->execute($this->loader->find($viewpath), $data);

    }

    public function  addLayoutSearchPaths(array $searchPaths = []){

        $this->loader->addSearchPaths( $searchPaths );

    }

    public function addLayoutData( array $layoutData ){

        $this->loader->addData( $layoutData );

    }

}