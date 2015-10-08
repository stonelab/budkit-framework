<?php

namespace Budkit\Filesystem;

class Directory extends File
{

    public function create($path, $permission = 0755)
    {
        if (!$this::isFolder($path)) {
            if (!mkdir($path, $permission, true)) {
                return false;
            }

            return true;
        }
    }

    /**
     * Recursively sets permissions for all files in a folder
     *
     * @param type $path
     * @param type $permission
     */
    public function chmodR($path, $filemode)
    {

        if (!$this->isFolder($path)) {
            return $this->chmod($path, $filemode);
        }

        $dirh = @opendir($path);
        while ($file = readdir($dirh)) {
            if ($file != '.' && $file != '..') {
                $fullpath = $path . '/' . $file;
                if (!$this->isFolder($fullpath)) {
                    if (!$this->chmod($fullpath, $filemode)) {
                        return false;
                    }
                } else {
                    if (!$this->chmodR($fullpath, $filemode)) {
                        return false;
                    }
                }
            }
        }

        closedir($dirh);

        if ($this->chmod($path, $filemode)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     *
     * @param type $path
     * @param type $type
     */
    public function pack($path, $type = 'zip')
    {
    }

    /**
     * Lists all the files in a directory
     *
     * @param string $path the compound path being searched and listed
     * @param array $exclude a list of folders, files or fileTypes to exclude from the list
     * @param boolean $recursive Determines whether to search subdirectories if found
     * @param interger $recursivelimit The number of deep subfolder levels to search
     * @param boolean $showfiles Include Files contained in each folder to the array
     * @param boolean $sort Sort folder/files in alphabetical order
     * @param boolean $long returns size, permission, datemodified in list if true, Slow!!
     *
     * @return array $list = array(
     *                "path/to/folder" => array(
     *                    "name" => '',
     *                    "parent" => '', //only in long
     *                    "size" => '', //only in long
     *                    "modified" => '', //only in long
     *                    "permission" => '',
     *                    "files" => array(
     *                        "path/to/file" => array(
     *                        "name" => '',
     *                        "size" => '', //only in long
     *                        "modified" => '', //only in long
     *                        "permission" => '',
     *                        "extension"  => '',
     *                        "mimetype"   => ''//only in long
     *                    )
     *                ),
     *                "children" => array()
     */
    final public function ls($path, $exclude = array(".DS_Store", ".git", ".svn", ".CVS"), $recursive = FALSE, $recursivelimit = 0, $showfiles = FALSE, $sort = TRUE, $long = FALSE)
    {

        //1. Search $name as a folder or as a file 
        if (!$this->is($path)) { //if in path is a directory
            return array();
        }

        $dirh = @opendir($path); //directory handler
//$recursion  = 0;
        $found = [];

        if ($dirh) {
            while (false !== ($file = readdir($dirh))) {
                // remove '.' and '..'
                if ($file == '.' || $file == '..' || in_array($file, $exclude)) {
                    continue;
                }

                $recursion = 0;
                $newPath = $path . $file;

                if ($this->isFolder($newPath) && $recursive && ($recursion < $recursiveLimit)) {
                    //echo $this->is($newPath)."<br />";
                    //echo $newPath."<br />";
                    $newRecursiveLimit = ((int)$recursiveLimit > 0) ? ((int)$recursiveLimit - 1) : 0;
                    $items = $this->list($newPath, $exclude, $recursive, $newRecursiveLimit);
                    $found = array_merge($items, $found);
                }

                $found[] = $newPath;
            }
            closedir($dirh);
        }

//@TODO if long, get additional info for each path;

        return $found;
    }

    /**
     * Finds and return the folder list matching $name in $inPath.
     * Use $limit to define how many occurences to return if found, default is 1
     * Method will therefore stop once the number of found response is = $limit, use $limit = 0 to find all
     *
     * @param string $name
     * @param string $inPath
     * @param interger $limit
     * @param boolean $recursive
     * @param interger $recursiveLimit
     * @param boolean $showfiles
     * @param boolean $sort
     * @param boolean $long
     */
    final public function lsFind($name, $inPath, $limit = 1, $recursive = false, $recursiveLimit = 0, $showfiles = false,
                                   $sort = true, $long = false)
    {

        //1. Search $name as a folder or as a file
        if (!$this->isFolder($inPath)) { //if in path is a directory
            return [];
        }

        $dirh = @opendir($inPath); //directory handler
        //$recursion  = 0;
        $found = [];

        if ($dirh) {
            while (false !== ($file = readdir($dirh))) {
                // remove '.' and '..'
                if ($file == '.' || $file == '..') {
                    continue;
                }

                $recursion = 0;
                $newPath = $inPath . $file . DS;

                if ($this->isFolder($newPath) && $recursive && ($recursion < $recursiveLimit)) {
                    //echo $this->is($newPath)."<br />";
                    //echo $newPath."<br />";

                    $newRecursiveLimit = ((int)$recursiveLimit > 0) ? ((int)$recursiveLimit - 1) : 0;
                    $items = $this->listFind($name, $newPath, $recursive, $newRecursiveLimit);
                    $found = array_merge($items, $found);
                }

                if (\strtolower($name) == \strtolower($file)) {
                    $found[] = $newPath;
                }
            }
            closedir($dirh);
        }

        //@TODO if long, get additional info for each path;

        return $found;
    }


    /**
     * Method to delete the contents of a folder
     *
     * @param type $folderpath
     * @param type $filterByType
     * @param type $filterByName
     * @param type $filterExcludeMode
     */
    final public function deleteContents($folderpath, $filterByExtension = [], $filterByName = [],
                                         $filterExcludeMode = true, $recursive = true)
    {

        //1. Search $name as a folder or as a file
        if (!$this->is($folderpath)) { //if in path is a directory
            return false;
        }

        $dirh = @opendir($folderpath); //directory handler

        if ($dirh) {
            while (false !== ($file = readdir($dirh))) {
                // remove '.' and '..'
                if ($filterExcludeMode) {
                    //Excluding by name as in "file.ext"
                    if ($file == '.' || $file == '..' || in_array($file, $filterByName)) {
                        continue;
                    }
                    //Excluding extension
                    if (!empty($filterByExtension)) {
                        $fhandler = $this->getFile();
                        $extension = $fhandler->getExtension($file);
                        if (in_array($extension, $filterByExtension)) {
                            continue;
                        }
                    }
                }

                //The new path
                $newPath = $folderpath . DS . $file;
                //echo $newPath;

                //If newpath is a folder and we are deleting recursively
                if ($this->isFolder($newPath) && $recursive) {
                    $this->deleteContents($newPath, $filterByExtension, $filterByName, $filterExcludeMode, $recursive);
                }
                //Now unlink the file
                if (!static::delete($newPath)) {
                    static::setError("Could not delete {$newPath}");

                    return false;
                }
            }
            closedir($dirh);
        }

        //@TODO if long, get additional info for each path;

        return true;
    }


    public function isFolder($path)
    {
        return (bool)is_dir($path);
    }

}