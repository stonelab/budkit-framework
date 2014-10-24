<?php


namespace Budkit\View\Layout;

use Budkit\Dependency\Container as Application;
use Budkit\FileSystem\File;
use Exception;

class Loader {

    protected $paths;

    protected $defaultExt;


    public function __construct($defaultExt = '.tpl', Application $application) {

        $this->paths      = $application['paths'];
        $this->defaultExt = $defaultExt;

    }

    public function find($layout) {
        //use the app config?
        $file = new File();

        if (!$file->getExtension($layout)) {
            $layout = $layout . $this->defaultExt;
        }

        if (!$file->exists($layout)) {
            $layout = $this->paths['app'] . '/layouts/' . $layout;
            if (!$file->exists($layout)) {
                throw new Exception("Could not locate the layout file {$layout}");
            }
        }

        $this->startBuffer();
        $file->requireOnce($layout);

        return $this->getBuffer();
    }

    private function startBuffer($handler = "ob_gzhandler") {
        if (!ob_start($handler)) {
            ob_start();
        }
    }

    private function getBuffer($handler = "ob_gzhandler", $end = true) {

        $content = ob_get_contents();
        if ($end) ob_end_clean();

        return $content;
    }

    public function getLayoutDirectory($layout = null) {
        //get the directory in which a layout is contained;
    }

    private function stopBuffer($endflush = true) {

        ob_flush();
        if ($flush) ob_end_flush();


    }

}