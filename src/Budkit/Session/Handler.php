<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 27/06/2014
 * Time: 03:58
 */

namespace Budkit\Session;


interface Handler {

    public function getId();
    public function getName();
} 