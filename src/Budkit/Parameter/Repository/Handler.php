<?php

namespace Budkit\Parameter\Repository;

/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 13/09/15
 * Time: 19:29
 */

interface Handler
{

    public function getParams($filepath = "");

    public function saveParams(array $parameters, $environment = "");

    public function readParams($filepath);

}