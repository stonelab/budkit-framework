<?php

// Include Directly
$loader = require '../../../autoload.php';


if (!class_exists('Budkit\Docs\Documentor')){

    header('HTTP/1.0 404 Not Found');
    die("404 - To see these docs you need to have the budkit/docs dependency installed");
}



$dir = realpath('../src') . '/';

$default_file_to_show = null;
$requested_file_to_show = isset($_GET['file']) ? $_GET['file'] : null;

$docs = new \Budkit\Docs\Documentor();

if ( isset( $_GET['save'] ) ){

    if( $docs->saveHTML($dir) ) {

        //header('Location: '.dirname($dir)."/docs/index.html", true, 301);
        exit("<a href='file://".dirname($dir)."/docs/index.html'>Read The Docs</a><br /><br />NB. Clicking on this link (link to newly created index) does not work in some browsers (e.g safari does now allow opening local files), right-click and copy link or open in new tab.<br/>P.S. Docs from source <code>".$dir."</code> saved in <code>".dirname($dir)."/docs/</code>");

    }

}else {

    if (!$docs->display($dir, $default_file_to_show, $requested_file_to_show)) {

        header('HTTP/1.0 404 Not Found');
        die("404 - File not Found");

    }
}