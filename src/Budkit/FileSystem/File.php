<?php

namespace Budkit\Filesystem;

class File{
	
	
    /**
     * Path to the file current being processed
     * @var type 
     */
    protected static $file = NULL;

    /**
     * File Path Info
     * @var type 
     */
    protected static $pathinfo = array();

    /**
     * Get File Name
     * 
     * @param type $file
     * @param type $default
     * @return type 
     */
    public function getName($file = "", $default = NULL) {

        $file = ( empty($file) && isset($this->file) ) ? $this->file : $file;

        if (empty($file)) {
            return $default;
        }

        //Determine the filename
        return pathinfo($file, PATHINFO_FILENAME);
    }

    /**
     * Gets the file extension;
     * 
     * @param type $file
     * @param type $default
     * @return string 
     */
    public function getExtension($file = "", $default = NULL) {

        $file = ( empty($file) && isset($this->file) ) ? $this->file : $file;

        if (empty($file)) {
            return $default;
        }

        //Determine the file extension
        return pathinfo($file, PATHINFO_EXTENSION);
    }
    
    /**
     * Returns only the directory name from the filepath;
     * 
     * @param type $file
     * @param type $default
     */
    final public function getPath( $file="", $default=NULL){
        
        $file = ( empty($file) && isset($this->file) ) ? $this->file : $file;

        if (empty($file)) {
            return $default;
        }
        //Determine the file extension
        return pathinfo($file, PATHINFO_DIRNAME);
    }

    /**
     * Reads the contents of a file;
     * 
     * @param type $file
     * @return type 
     */
    public function read($file) {
        //@TODO Rewrite; 
        return file_get_contents($file);
    }

    /**
     *  Write File
     * 
     * @param type $file
     * @param type $content 
     */
    public function write($file, $content = "", $mode="w+") {

        $stream = $this->getFileStream($file, $mode);

        //Write the contents
        fwrite($stream, $content);
        fclose($stream);
        
        return true;
    }

    /**
     * Get the file stream
     * 
     * @param type $file
     * @param type $mode
     * @return boolean 
     */
    public function getFileStream($file, $mode = "w+") {
        
        //Throw some errors
        if (($handle = fopen($file, $mode)) === FALSE) { //this fopen with w will attempt to create the file
            //@Throw error
            return false;
        }
        return $handle;
    }

    /**
     * Returns the UNIX timestamp representation
     * of the last time the folder was modified
     * 
     * @param string $path 
     */
    public function getModifiedDate($path) {

        //Check for the last modified 
        $lmodified = 0;
        $files = glob($path . '/*');

        foreach ($files as $file) {
            if (is_dir($file)) {
                $modified = dirmtime($file);
            } else {
                $modified = filemtime($file);
            }
            if ($modified > $lmodified) {
                $lmodified = $modified;
            }
        }
        return $lmodified;
    }

    /**
     * Returns in int representation of the file size in bytes
     * 
     * @param string $path 
     */
    public function getSize($path) {}


		/**
		 * Get the returned value of a file.
		 *
		 * @param  string  $path
		 * @return mixed
		 *
		 * @throws FileNotFoundException
		 */
		public function getRequire($path)
		{
			if ($this->isFile($path)) return require $path;

			throw new FileNotFoundException("File does not exist at path {$path}");
		}

		/**
		 * Require the given file once.
		 *
		 * @param  string  $file
		 * @return mixed
		 */
		public function requireOnce($file)
		{
			require_once $file;
		}

		/**
		 * Write the contents of a file.
		 *
		 * @param  string  $path
		 * @param  string  $contents
		 * @return int
		 */
		public function put($path, $contents)
		{
			return file_put_contents($path, $contents);
		}

	/**
	 * Prepend to a file.
	 *
	 * @param  string  $path
	 * @param  string  $data
	 * @return int
	 */
	public function prepend($path, $data)
	{
		if ($this->exists($path))
		{
			return $this->put($path, $data.$this->get($path));
		}
		else
		{
			return $this->put($path, $data);
		}
	}

	/**
	 * Append to a file.
	 *
	 * @param  string  $path
	 * @param  string  $data
	 * @return int
	 */
	public function append($path, $data)
	{
		return file_put_contents($path, $data, FILE_APPEND);
	}




    /**
     * Moves the folder to a new location
     * 
     * @param type $path
     * @param type $toPath
     * @param type $replace 
     * @todo Will always replace for now.
     */
    public function move($path, $toPath, $deleteOriginal = TRUE) {
        if(copy($path, $toPath)){
            if($deleteOriginal){
                if(!$this->remove($path)){
                    //@todo say you could not delete the original
                }
            }
            return true;
        }
        return false;
    }
    
     /**
     * Copies the file or folder to a new destination
     * 
     * @param type $path
     * @param type $toPath
     * @param type $replace 
     * @todo Will always replace for now.
     */
    public function copy($path, $toPath) {
        if(empty($path)||empty($toPath)) return false;
        return copy($path, $toPath);
    }

    /**
     * Deletes a folder
     * 
     * @param type $path
     * @param type $backup 
     */
    public function remove($path) {    
        return $this->delete($path);
    }


    public function hasBackup($path) {}
    public function restoreBackup($path) {}
    public function chmod($path, $permission) {   
        chmod($path,$permission);		
    }
    
    public function getPermission($filepath) {    
        return substr(sprintf('%o', fileperms($filepath)), -4);
    }
	
	
    /**
     * Sets the file for execution
     * 
     * @param string $file
     * @return object An instance of the file class
     */
    public function setFile($file) {

        //Return false if file does not exists;
        if (!$this->isFile($file)) {
            return false;
        }
        //Get the file info
        $this->file = $file;
        $this->pathinfo[$file] = pathinfo($file);

        //Return an instance of the file object;
        return $this;
    }



    /**
     * Unpacks and archived file
     * 
     * @param type $path
     * @param type $type
     */
    public function unpack($path, $type = 'tar.gz') {
        
    }



    /**
     * Get the MimeType of a file;
     * @param type $path
     * @param type $default
     */
    public function getMimeType($path, $default="application/octet-stream") {
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
        $fmtype = finfo_file($finfo, $path);
        
        finfo_close($finfo);
        
        return !empty($fmtype) ? $fmtype : $default;
    }

    /**
     * Creates a new file
     * 
     * @param type $path
     */
    public function create($filepath) {
        //@1 Check that we have permission to write to the directory
        //@2 Attempt to create the file
        if (!($file = $this->getFileStream($filepath))) {
            return false;
        }
        return $file;
    }
    

    /**
     * Determines if a path links to a folder or file
     * 
     * @param string $path
     * @param boolean $folder, value to return if is folder
     *  
     */
    final public function isFile($filepath) {
        $return = false;
        if (file_exists($filepath)):
            $return = is_file($filepath);
        endif;
        return (bool) $return;
    }
    
    
    
    
    /**
     * Deletes a file or folder if exists
     * 
     * @param type $path
     */
    final public function delete($path){
        
        //If we have permission to remove this file
        if ($this->isWritable($path)) {
            if (!@unlink($path)) //This is highly unreliable as unlink returns a warning not a bool
                return false;
        }else {
            if (!@unlink($path)) //This is highly unreliable as unlink returns a warning not a bool
                return false;
        }
        return true;
    }

    /**
     * Determines if a path is credible
     * 
     * @param type $path 
     */
    final public function exists($path) {
        return file_exists( $path );
    }
    
    /**
     * Checks if a file or folder is writable 
     * 
     * @param type $path
     */
    final public function isWritable($path, $writable = TRUE){
        return (bool) is_writable($path) ? $writable : !$writable;
    }

}