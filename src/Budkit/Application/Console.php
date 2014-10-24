<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 21/06/2014
 * Time: 17:25
 */

namespace Budkit\Application;

use Budkit\Protocol\Request;

/**
 * The Base CommandLine Console Application Class
 *
 * Class Console
 *
 * @package Budkit\Application
 */
class Console extends Support\Application {

    public function execute(Request $request = null) {
        echo 'run the console application';
    }

} 