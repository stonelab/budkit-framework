<?php


namespace Budkit\View\Layout;

use Budkit\Dependency\Container as Application;
use Budkit\FileSystem\File;
use Exception;

class Loader
{

    protected $paths = [];


    protected $data = [];

    protected $defaultExt;

    public function __construct($defaultExt = '.tpl', Application $application)
    {

        $this->defaultExt = $defaultExt;

        $this->paths = array(
            PATH_PUBLIC . '/layouts/',
            PATH_APP . '/layouts/'
        );

    }


    public function addSearchPaths(array $paths = [])
    {

        $this->paths = array_merge($this->paths, $paths);

    }


    public function addData(array $data)
    {
        $this->data = $data;
    }


    public function getData()
    {
        return $this->data;
    }


    public function find($layout)
    {


        //use the app config?
        $file = new File();

        if (!$file->getExtension($layout)) {
            $layout = $layout . $this->defaultExt;
        }

        $found = null;

        foreach ($this->paths as $path) {

            if ($file->exists($path . $layout)) {
                $found = $path . $layout;
            }
        }

        if ($found == null) {

            //@TODO find layout first by checking multiple directories
            //Check the package directory
            //Check the application/layouts directory
            //check the public/themes/layouts directory for any overwrites.


            if (!$file->exists($layout)) {
                throw new Exception("Could not locate the layout file {$layout}");
            }
        }

        $layout = $found;

        $this->startBuffer();
        $file->requireFile($layout);

        return $this->getBuffer();
    }

    private function startBuffer($handler = "ob_gzhandler")
    {
        if (!ob_start($handler)) {
            ob_start($handler);
        }
    }

    private function getBuffer($handler = "ob_gzhandler", $end = true)
    {

        $content = ob_get_contents();
        if ($end) ob_end_clean();

        return $content;
    }

    public function getLayoutDirectory($layout = null)
    {
        //get the directory in which a layout is contained;
    }


    private function stopBuffer($endflush = true)
    {

        ob_flush();
        if ($flush) ob_end_flush();


    }

}