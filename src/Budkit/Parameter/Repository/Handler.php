<?php

namespace Budkit\Parameter\Repository;

/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 13/09/15
 * Time: 19:29
 */

interface Handler{

    public function getParams($filepath = "");
    public function saveParams(array $namespaces, $filepath="");
    public function readParams($filepath);

}