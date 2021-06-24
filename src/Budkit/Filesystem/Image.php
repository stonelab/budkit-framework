<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * image.php
 *
 * Requires PHP version 5.3
 *
 * LICENSE: This source file is subject to version 3.01 of the GNU/GPL License
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.txt  If you did not receive a copy of
 * the GPL License and are unable to obtain it through the web, please
 * send a note to support@stonyhillshq.com so we can mail you a copy immediately.
 *
 * @category   Library
 * @author     Livingstone Fultang <livingstone.fultang@stonyhillshq.com>
 * @copyright  1997-2012 Stonyhills HQ
 * @license    http://www.gnu.org/licenses/gpl.txt.  GNU GPL License 3.01
 * @version    Release: 1.0.0
 * @link       http://stonyhillshq/documents/index/carbon4/libraries/folder/files/image
 * @since      Class available since Release 1.0.0 Jan 14, 2012 4:54:37 PM
 *
 */

namespace Budkit\Filesystem;


class Image extends File {

    /**
     * The original file dimensions
     * @var type
     */
    public $dimensionX = NULL;

    /**
     * The original height dimension
     * @var type
     */
    public $dimensionY = NULL;

    /**
     * The new file width
     * @var type
     */
    public $width = NULL;

    /**
     * The new file height;
     * @var type
     */
    public $height = NULL;

    /**
     * The target file, path of the resized image
     * @var type
     */
    public $target = NULL;

    /**
     * Creates an Image
     *
     * @param mixed $image
     * @param mixed $ext
     * @return
     */
    private function createImage($image, $ext) {
        switch (strtolower($ext)):
            case "gif": $i = \imagecreatefromgif($image);
                break;
            case "jpg":
            case "jpeg":$i = \imagecreatefromjpeg($image);
                break;
            case "png": $i = \imagecreatefrompng($image);
                break;
        endswitch;

        return $i;
    }

    public function addImageWaterMark(){
        //Water marks an image;
        //imagecopymerge($dest, $src, 10, 10, 0, 0, 100, 47, 75);
    }

    public function addImageText(){
        //Adds text to an image;
    }




    /**
     * Resizes an image
     *
     * @param mixed $image
     * @param mixed $width
     * @param mixed $height
     * @param bool $square
     * @return
     */
    public function resizeImage($image, $target, $width, $height = null, $square = false) {

        //If target already exists, then don't bother
        if($this->isFile($target)||empty($width)){
            return true;
        }

        $this->dimensionX = $width;
        $this->dimensionY = (!empty($height) && !$square) ? (int) $height : null;
        $this->target = $target;

        //if (!$square && $height <> $width )
        //$this->dimensionY = "auto";

        $ext = $this->getExtension(basename($image));
        $ourimage = $this->createImage($image, $ext);

        $currX = \imagesx($ourimage);
        $currY = \imagesy($ourimage);

        if(empty($this->dimensionY)){

            if($currX > $currY ){
                $newX    =   $this->dimensionX;
                $newY    =   $height = $currY*($this->dimensionX/$currX);
            }
            if($currX < $currY){
                $newX    =   $this->dimensionX;
                $newY    =   $height = $currY*($this->dimensionX/$currX); //reduce the height by same percentage change in width
            }
            if($currX == $currY){
                $newX    =   $this->dimensionX;
                $newY    =   $height = $this->dimensionX;
            }

        }else{
            //If we have both the dimensions
            //Crop middle;
            //Destroy the image
            //die;
            //@imagedestroy($image);
            return $this->crop($image, $this->target,$this->dimensionX, $this->dimensionY, 0, 0);
        }

        //Destroy the image
        //\imagedestroy($image);

        if ($square)
            return $this->createSquare($image, $this->target, $newX);

        $_x = $newX;
        $_y = $newY;

        //Get True Color
        $truecolor = \imagecreatetruecolor($_x, $_y);

        if (!$truecolor) {
            throw new \Exception("could not create a true color image");
            return false;
        }
        if (!\imagecopyresampled($truecolor, $ourimage, 0, 0, 0, 0, $_x, $_y, $currX, $currY)) {
            throw new \Exception("could not create a true color image");
            return false;
        }
        if (!\imagejpeg($truecolor, $this->target, 80)) {
            throw new \Exception("save the target jpg image");
            return false;
        }
        return true;
    }

    public function crop($source, $target, $width, $height, $originX=null, $originY=null){

        //Get True Color
        $ext = $this->getExtension(basename($source));
        $ourimage = $this->createImage($source, $ext);

        $currX = \imagesx($ourimage);
        $currY = \imagesy($ourimage);

        if(empty($originX)||empty($originY)){
            $currAR = $currY/$currX;
            $newAR = $height/$width;

            $_x = $currX * ($newAR / $currAR );
            $_y = $currY * ($_x / $currX );

            $trueresize = \imagecreatetruecolor($_x, $_y);
            if (!$trueresize) {
                throw new \Exception("could not create a true color image");
                return false;
            }
            if (!\imagecopyresampled($trueresize, $ourimage, 0, 0, 0, 0, $_x, $_y, $currX, $currY)) {
                throw new \Exception("could not create a true color image");
                return false;
            }

            $ourimage = $trueresize;
        }

        $truecolor = \imagecreatetruecolor($width, $height);

        if (!$truecolor) {
            throw new \Exception("could not create a true color image");
            return false;
        }

        if (!\imagecopy($truecolor, $ourimage, 0, 0, $originX, $originY, $width, $height)) {
            throw new \Exception("could not create a true color image");
            return false;
        }

        if (!\imagejpeg($truecolor, $target, 80)) {
            throw new \Exception("save the target jpg image");
            return false;
        }
        return true;
    }
    /**
     * Creates a square image
     *
     * @param mixed $source
     * @param mixed $target
     * @param mixed $width
     * @return
     */
    private function createSquare($source, $target, $width) {

        $tWidth = $width;
        $tHeight = $width;
        $imgdata = getimagesize($source);
        $widthOrig = $imgdata[0];
        $heightOrig = $imgdata[1];
        $ext = $this->getExtension(basename($source));
        $image = $this->createImage($source, $ext);

        //Proportions
        if ($widthOrig < $heightOrig) {
            $height = ($tWidth / $widthOrig) * $heightOrig;
        } else {
            $width = ($tHeight / $heightOrig) * $widthOrig;
            $height = $tHeight;
        }

        //If square this does not really matter? but..
//        if ($width < $tWidth) {
//            $width = $tWidth;
//            $height = ($tWidth / $widthOrig) * $heightOrig;
//        }
//        //If square this does not really matter? but..
//        if ($height < $tHeight) {
//            $height = $tHeight;
//            $width = ($tHeight / $heightOrig) * $widthOrig;
//        }

        //Create True Coolor
        $thumb = imagecreatetruecolor($width, $height);

        if (!imagecopyresampled($thumb, $image, 0, 0, 0, 0, $width, $height, $widthOrig, $heightOrig)) {
            $this->setError("a problem errored");
            return false;
        }

        $w1 = ($width / 2) - ($tWidth / 2);
        $h1 = ($height / 2) - ($tHeight / 2);

        $thumb2 = imagecreatetruecolor($tWidth, $tHeight);
        if (!imagecopyresampled($thumb2, $thumb, 0, 0, $w1, $h1, $tWidth, $tHeight, $tWidth, $tHeight)) {
            $this->setError("a problem errored");
            return false;
        }
        if (!imagejpeg($thumb2, $target, 80)) {
            $this->setError("a problem errored");
            return false;
        }

        imagedestroy($thumb);
        imagedestroy($thumb2);

        return true;
    }

}